<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProductEditOptions
{
    public static function forProduct($product): array
    {
        $item = new \stdClass();
        $item->product_id = $product->id ?? null;
        $item->product_name = $product->name ?? null;
        $item->orderToppings = collect();
        return self::forItem($item);
    }

    public static function forItem($item): array
    {
        $product = self::loadProduct($item->product_id ?? null, $item->product_name ?? null);

        $name = trim((string) (($product->name ?? null) ?: ($item->product_name ?? '')));
        $productIds = self::relatedProductIds($product->id ?? ($item->product_id ?? null), $name);

        $selectedToppingIds = [];
        $rows = $item->orderToppings ?? null;
        if ($rows === null && is_object($item) && method_exists($item, 'orderToppings')) {
            $rows = $item->orderToppings;
        }
        if ($rows) {
            $selectedToppingIds = collect($rows)->pluck('topping_id')->map(function ($id) {
                return (int) $id;
            })->all();
        }

        $variants = self::variants($productIds);
        if ($variants->isEmpty() && $product && $product->variants && $product->variants->count()) {
            $variants = $product->variants->values();
        }

        $toppingGroups = self::toppingGroups($productIds, $selectedToppingIds);
        if (empty($toppingGroups) && $product) {
            $toppingGroups = self::toppingGroupsFromRelation($product, $selectedToppingIds);
        }

        return [
            'product' => $product,
            'variants' => $variants,
            'topping_groups' => $toppingGroups,
            'selected_topping_ids' => $selectedToppingIds,
        ];
    }

    protected static function loadProduct($productId, $name = null): ?Product
    {
        $with = ['variants', 'category.getCategory'];
        try {
            if ($productId) {
                $product = Product::with($with)->find($productId);
                if ($product) {
                    return $product;
                }
            }
            if ($name) {
                return Product::with($with)->where('name', $name)->orderByDesc('id')->first();
            }
        } catch (\Throwable $e) {
            if ($productId) {
                $product = Product::with(['variants', 'category'])->find($productId);
                if ($product) {
                    return $product;
                }
            }
            if ($name) {
                return Product::with(['variants', 'category'])->where('name', $name)->orderByDesc('id')->first();
            }
        }
        return null;
    }

    public static function relatedProductIds($productId, $name = null): array
    {
        $ids = [];
        if ($productId) {
            $ids[] = (int) $productId;
        }
        if ($name) {
            $ids = array_merge($ids, Product::where('name', $name)->pluck('id')->map(function ($id) {
                return (int) $id;
            })->all());
        }
        $ids = array_values(array_unique(array_filter($ids)));
        return $ids ?: [0];
    }

    public static function variants(array $productIds)
    {
        if (!Schema::hasTable('product_variants')) {
            return collect();
        }
        $ids = array_values(array_filter($productIds));
        if (!$ids) {
            return collect();
        }
        $rows = DB::table('product_variants')
            ->whereIn('product_id', $ids)
            ->orderBy('id')
            ->get();
        $primary = (int) $ids[0];
        $own = $rows->where('product_id', $primary)->values();
        if ($own->isNotEmpty()) {
            return $own;
        }
        return $rows->unique(function ($row) {
            return strtolower(trim((string) $row->size));
        })->values();
    }

    public static function toppingGroups(array $productIds, array $selectedToppingIds = []): array
    {
        if (!Schema::hasTable('topping_products')) {
            return [];
        }
        $ids = array_filter($productIds);
        if (!$ids) {
            return [];
        }

        $links = DB::table('topping_products')->whereIn('product_id', $ids)->get();
        $groups = [];
        $direct = [];
        $hasCategory = Schema::hasColumn('topping_products', 'category_id');
        $hasTopping = Schema::hasColumn('topping_products', 'topping_id');

        foreach ($links as $link) {
            $categoryId = $hasCategory ? (int) ($link->category_id ?? 0) : 0;
            $toppingId = $hasTopping ? (int) ($link->topping_id ?? 0) : 0;

            if ($categoryId > 0 && Schema::hasTable('category_toppings')) {
                $category = Schema::hasTable('categories')
                    ? DB::table('categories')->where('id', $categoryId)->first()
                    : null;
                $items = DB::table('category_toppings')
                    ->where('category_toppings.category_id', $categoryId)
                    ->join('toppings', 'category_toppings.topping_id', '=', 'toppings.id')
                    ->select('toppings.id', 'toppings.name', 'toppings.price')
                    ->get()
                    ->map(function ($topping) use ($categoryId, $selectedToppingIds) {
                        return [
                            'id' => (int) $topping->id,
                            'name' => $topping->name,
                            'price' => (float) $topping->price,
                            'category_id' => $categoryId,
                            'selected' => in_array((int) $topping->id, $selectedToppingIds, true),
                        ];
                    })->values()->all();
                if ($items) {
                    $groups['cat-' . $categoryId] = [
                        'category_id' => $categoryId,
                        'category_name' => $category->name ?? 'Toppings',
                        'items' => $items,
                    ];
                }
            }

            if ($toppingId > 0) {
                $topping = DB::table('toppings')->where('id', $toppingId)->first();
                if ($topping) {
                    $direct[$toppingId] = [
                        'id' => (int) $topping->id,
                        'name' => $topping->name,
                        'price' => (float) $topping->price,
                        'category_id' => $categoryId ?: 0,
                        'selected' => in_array((int) $topping->id, $selectedToppingIds, true),
                    ];
                }
            }
        }

        if ($direct) {
            $groups['direct'] = [
                'category_id' => 0,
                'category_name' => 'Toppings',
                'items' => array_values($direct),
            ];
        }

        return array_values($groups);
    }

    public static function toppingGroupsFromRelation($product, array $selectedToppingIds = []): array
    {
        if (!$product || !$product->category || $product->category->isEmpty()) {
            return [];
        }
        $groups = [];
        foreach ($product->category as $link) {
            $cat = $link->getCategory ?? null;
            if (!$cat || empty($cat->id)) {
                continue;
            }
            $items = DB::table('category_toppings')
                ->where('category_toppings.category_id', $cat->id)
                ->join('toppings', 'category_toppings.topping_id', '=', 'toppings.id')
                ->select('toppings.id', 'toppings.name', 'toppings.price')
                ->get()
                ->map(function ($topping) use ($cat, $selectedToppingIds) {
                    return [
                        'id' => (int) $topping->id,
                        'name' => $topping->name,
                        'price' => (float) $topping->price,
                        'category_id' => (int) $cat->id,
                        'selected' => in_array((int) $topping->id, $selectedToppingIds, true),
                    ];
                })->values()->all();
            if ($items) {
                $groups['cat-' . $cat->id] = [
                    'category_id' => (int) $cat->id,
                    'category_name' => $cat->name ?? 'Toppings',
                    'items' => $items,
                ];
            }
        }
        return array_values($groups);
    }
}

<?php

namespace App\Support;

use App\Models\Product;

class StorefrontApiPresenter
{
    public static function imageUrl($path): ?string
    {
        if (!$path) {
            return null;
        }
        $path = (string) $path;
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }
        return asset($path);
    }

    public static function complementaryProduct(Product $product): ?array
    {
        $row = $product->complementaryProductSingle;
        $comp = $row ? $row->complementary : null;
        if (!$comp) {
            return null;
        }

        return [
            'id' => $comp->id,
            'name' => $comp->name,
            'image' => self::imageUrl($comp->image),
            'badge' => 'BUY 1 GET 1 FREE',
        ];
    }

    public static function product(Product $product, $menu = null): array
    {
        $menu = $menu ?: ($product->relationLoaded('menu') ? $product->menu : $product->menu);
        $price = method_exists($product, 'resolvedDisplayPrice')
            ? $product->resolvedDisplayPrice()
            : (float) ($product->default_price ?? $product->price ?? 0);

        $variants = [];
        $variantRows = $product->relationLoaded('variants') ? $product->variants : collect();
        foreach ($variantRows as $variant) {
            $variants[] = [
                'id' => $variant->id,
                'size' => $variant->size,
                'price' => (float) $variant->price,
                'original_price' => (float) ($variant->original_price ?? 0),
            ];
        }

        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'image' => self::imageUrl($product->image),
            'price' => (float) $price,
            'original_price' => (float) ($product->original_price ?? 0),
            'is_featured' => (bool) ($product->is_featured ?? false),
            'menu_id' => $product->menu_id,
            'food_menu_id' => $product->food_menu_id ?? null,
            'catalog_source' => MenuCatalog::isSpecial($menu)
                ? 'special'
                : (MenuCatalog::isWholesale($menu) ? 'wholesale' : 'food'),
            'variants' => $variants,
            'complementary_product' => self::complementaryProduct($product),
        ];
    }

    public static function menu($menu): array
    {
        $products = collect($menu->product ?? ($menu->products ?? []));
        return [
            'id' => $menu->id,
            'name' => $menu->name,
            'slug' => $menu->slug ?? null,
            'type' => $menu->type ?? null,
            'is_special' => MenuCatalog::isSpecial($menu),
            'is_wholesale' => MenuCatalog::isWholesale($menu),
            'products' => $products->map(function ($product) use ($menu) {
                return self::product($product, $menu);
            })->values()->all(),
        ];
    }

    public static function menus($menus): array
    {
        return collect($menus)->map(function ($menu) {
            return self::menu($menu);
        })->values()->all();
    }
}

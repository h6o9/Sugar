<?php

namespace App\Support;

use App\Models\Menu;

class MenuCatalog
{
    public static function isSpecial($menu): bool
    {
        $type = strtolower((string) ($menu->type ?? ''));
        $slug = strtolower((string) ($menu->slug ?? ''));
        $name = (string) ($menu->name ?? '');
        return $type === 'special' || $slug === 'pappi-special' || stripos($name, 'Pappi Special') !== false;
    }

    public static function isWholesale($menu): bool
    {
        $type = strtolower((string) ($menu->type ?? ''));
        $slug = strtolower((string) ($menu->slug ?? ''));
        $name = (string) ($menu->name ?? '');
        return $type === 'wholesale' || $slug === 'dessert-wholesale' || stripos($name, 'Wholesale') !== false;
    }

    public static function hydrate($menus)
    {
        foreach ($menus as $menu) {
            $products = $menu->relationLoaded('products')
                ? $menu->products
                : $menu->products()->where('status', 1)->get();
            $products->load(['variants', 'complementaryProductSingle.complementary', 'category.getCategory']);
            foreach ($products as $prod) {
                $prod->default_price = $prod->resolvedDisplayPrice();
            }
            $menu->product = $products;
        }
        return $menus;
    }

    public static function forStorefront($excludeSpecial = false, $excludeWholesale = false)
    {
        $menus = Menu::with(['products' => function ($q) {
            $q->where('status', 1);
        }])->orderBy('id')->get();

        $menus = self::hydrate($menus);
        $menus = self::mirrorWholesaleIntoFood($menus);

        $menus = $menus->filter(function ($menu) use ($excludeSpecial, $excludeWholesale) {
            if ($excludeSpecial && self::isSpecial($menu)) {
                return false;
            }
            if ($excludeWholesale && self::isWholesale($menu)) {
                return $menu->product && $menu->product->count() > 0;
            }
            return true;
        })->sortBy(function ($menu) {
            if (self::isSpecial($menu)) {
                return 0;
            }
            if (self::isWholesale($menu)) {
                return 2;
            }
            return 1;
        })->values();

        return $menus->filter(function ($menu) {
            return $menu->product && $menu->product->count() > 0;
        })->values();
    }

    public static function forSpecial()
    {
        $menus = self::forStorefront(false, true)->filter(function ($menu) {
            return self::isSpecial($menu);
        })->values();

        if ($menus->isEmpty()) {
            $virtual = new Menu();
            $virtual->id = 0;
            $virtual->name = 'Pappi Special';
            $virtual->type = 'special';
            $virtual->slug = 'pappi-special';
            $virtual->product = collect();
            $menus = collect([$virtual]);
        }

        return $menus;
    }

    /**
     * Products assigned to Dessert Wholesale also appear on the matching food tab
     * (Cookie Dough, Waffles, Cakes, …). Pappi Special is never copied onto food tabs.
     */
    protected static function mirrorWholesaleIntoFood($menus)
    {
        $foodMenus = $menus->filter(function ($menu) {
            return !self::isSpecial($menu) && !self::isWholesale($menu);
        });
        $wholesaleMenus = $menus->filter(function ($menu) {
            return self::isWholesale($menu);
        });

        foreach ($wholesaleMenus as $wholesaleMenu) {
            $unmatched = collect();
            foreach ($wholesaleMenu->product as $prod) {
                $target = self::matchFoodMenu($foodMenus, $prod);
                if ($target) {
                    self::appendUniqueProduct($target, $prod);
                } else {
                    $unmatched->push($prod);
                }
            }
            $wholesaleMenu->product = $unmatched;
        }

        return $menus;
    }

    protected static function appendUniqueProduct($menu, $prod)
    {
        $products = $menu->product ?? collect();
        foreach ($products as $existing) {
            if ((int) $existing->id === (int) $prod->id) {
                return;
            }
        }
        $menu->product = $products->push($prod);
    }

    protected static function matchFoodMenu($foodMenus, $prod)
    {
        $foodId = (int) ($prod->food_menu_id ?? 0);
        if ($foodId > 0) {
            $byId = $foodMenus->first(function ($menu) use ($foodId) {
                return (int) $menu->id === $foodId;
            });
            if ($byId) {
                return $byId;
            }
        }

        $productName = self::norm($prod->name);
        if ($productName !== '') {
            $best = null;
            $bestLen = 0;
            foreach ($foodMenus as $menu) {
                $menuName = self::norm($menu->name);
                if ($menuName === '' || strlen($menuName) < 8) {
                    continue;
                }
                if (strpos($productName, $menuName) !== false && strlen($menuName) > $bestLen) {
                    $best = $menu;
                    $bestLen = strlen($menuName);
                }
            }
            if ($best) {
                return $best;
            }
        }

        return self::fallbackFoodMenu($foodMenus);
    }

    protected static function fallbackFoodMenu($foodMenus)
    {
        $hot = $foodMenus->first(function ($menu) {
            return self::norm($menu->name) === 'hot desserts';
        });
        if ($hot) {
            return $hot;
        }
        return $foodMenus->first();
    }

    protected static function norm($value)
    {
        $value = strtolower((string) $value);
        $value = preg_replace("/[^a-z0-9]+/", ' ', $value);
        return trim(preg_replace('/\s+/', ' ', $value));
    }
}

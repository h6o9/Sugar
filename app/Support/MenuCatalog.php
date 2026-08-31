<?php

namespace App\Support;

use App\Models\Menu;

class MenuCatalog
{
    public static function isSpecial($menu): bool
    {
        if (!$menu) {
            return false;
        }
        $type = strtolower((string) ($menu->type ?? ''));
        $slug = strtolower((string) ($menu->slug ?? ''));
        $name = strtolower((string) ($menu->name ?? ''));
        if ($type === 'special') {
            return true;
        }
        if (in_array($slug, ['pappi-special', 'papi-special', 'sugar-papi-special', 'sugar-pappi-special'], true)) {
            return true;
        }
        return strpos($name, 'pappi special') !== false
            || strpos($name, 'papi special') !== false
            || strpos($name, 'sugar papi special') !== false
            || strpos($name, 'sugar pappi special') !== false;
    }

    public static function matchesChannel($menu, string $channel): bool
    {
        $channel = \App\Support\AppCartContext::normalizeChannel($channel);
        if ($channel === 'special') {
            return self::isSpecial($menu);
        }
        if ($channel === 'wholesale') {
            return self::isWholesale($menu);
        }
        if ($channel === 'drive_in') {
            return !self::isSpecial($menu) && !self::isWholesale($menu);
        }
        return !self::isSpecial($menu);
    }

    public static function isWholesale($menu): bool
    {
        if (!$menu) {
            return false;
        }
        $type = strtolower((string) ($menu->type ?? ''));
        $slug = strtolower((string) ($menu->slug ?? ''));
        $name = strtolower((string) ($menu->name ?? ''));
        if ($type === 'wholesale') {
            return true;
        }
        if (in_array($slug, ['dessert-wholesale', 'desert-wholesale'], true)) {
            return true;
        }
        return strpos($name, 'wholesale') !== false
            || strpos($name, 'whole sale') !== false;
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
                return false;
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

    public static function forWholesale()
    {
        $menus = self::forStorefront(true, false)->filter(function ($menu) {
            return self::isWholesale($menu);
        })->values();

        if ($menus->isEmpty()) {
            $virtual = new Menu();
            $virtual->id = 0;
            $virtual->name = 'Dessert Wholesale';
            $virtual->type = 'wholesale';
            $virtual->slug = 'dessert-wholesale';
            $virtual->product = collect();
            $menus = collect([$virtual]);
        }

        return $menus;
    }

    /**
     * Dessert Wholesale keeps every item on its own tab (Home + Menu).
     * If a product also has food_menu_id, copy it onto that food tab as well.
     * Pappi Special is never copied onto food tabs.
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
            foreach ($wholesaleMenu->product as $prod) {
                $target = self::matchFoodMenu($foodMenus, $prod);
                if ($target) {
                    self::appendUniqueProduct($target, $prod);
                }
            }
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
            return $foodMenus->first(function ($menu) use ($foodId) {
                return (int) $menu->id === $foodId;
            });
        }

        return null;
    }
}

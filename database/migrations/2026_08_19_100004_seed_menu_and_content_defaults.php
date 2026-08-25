<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('menus')) {
            return;
        }

        $defaults = [
            ['name' => 'New Food Menu', 'slug' => 'new-food-menu', 'type' => 'food', 'icon' => 'ri-restaurant-line', 'sort_order' => 1],
            ['name' => 'Pappi Special', 'slug' => 'pappi-special', 'type' => 'special', 'icon' => 'ri-star-smile-line', 'sort_order' => 2],
            ['name' => 'Belgian Waffles', 'slug' => 'belgian-waffles', 'type' => 'food', 'icon' => 'ri-cake-3-line', 'sort_order' => 3],
            ['name' => 'Thickshakes', 'slug' => 'thickshakes', 'type' => 'food', 'icon' => 'ri-cup-line', 'sort_order' => 4],
            ['name' => 'Delicious Donuts', 'slug' => 'delicious-donuts', 'type' => 'food', 'icon' => 'ri-donut-chart-line', 'sort_order' => 5],
            ['name' => 'Crepes', 'slug' => 'crepes', 'type' => 'food', 'icon' => 'ri-blaze-line', 'sort_order' => 6],
            ['name' => "Papi's Pancakes", 'slug' => 'papis-pancakes', 'type' => 'food', 'icon' => 'ri-cake-2-line', 'sort_order' => 7],
            ['name' => 'Sundaes', 'slug' => 'sundaes', 'type' => 'food', 'icon' => 'ri-ice-cream-line', 'sort_order' => 8],
            ['name' => 'Mocktails', 'slug' => 'mocktails', 'type' => 'food', 'icon' => 'ri-goblet-line', 'sort_order' => 9],
            ['name' => 'Dessert Wholesale', 'slug' => 'dessert-wholesale', 'type' => 'wholesale', 'icon' => 'ri-box-3-line', 'sort_order' => 20],
        ];

        $existing = DB::table('menus')->get();
        $hasCube = $existing->contains(function ($menu) {
            return stripos($menu->name, 'cube') !== false;
        });

        if ($hasCube) {
            $defaults[] = ['name' => 'Cube Menu', 'slug' => 'cube-menu', 'type' => 'food', 'icon' => 'ri-layout-grid-line', 'sort_order' => 10];
        } elseif ($existing->isNotEmpty()) {
            // Preserve existing unnamed cube-style menus by tagging slug if name already matches
            foreach ($existing as $menu) {
                if (stripos($menu->name, 'cube') !== false && Schema::hasColumn('menus', 'slug')) {
                    DB::table('menus')->where('id', $menu->id)->update(['slug' => 'cube-menu', 'type' => 'food']);
                }
            }
        }

        foreach ($defaults as $row) {
            $found = $existing->first(function ($menu) use ($row) {
                return strcasecmp($menu->name, $row['name']) === 0;
            });
            if ($found) {
                $update = [];
                if (Schema::hasColumn('menus', 'slug')) {
                    $update['slug'] = $row['slug'];
                }
                if (Schema::hasColumn('menus', 'type')) {
                    $update['type'] = $row['type'];
                }
                if (Schema::hasColumn('menus', 'icon')) {
                    $update['icon'] = $row['icon'];
                }
                if (Schema::hasColumn('menus', 'sort_order')) {
                    $update['sort_order'] = $row['sort_order'];
                }
                if (!empty($update)) {
                    DB::table('menus')->where('id', $found->id)->update($update);
                }
                continue;
            }

            $insert = ['name' => $row['name'], 'created_at' => now(), 'updated_at' => now()];
            $insert['id'] = ((int) DB::table('menus')->max('id')) + 1;
            if (Schema::hasColumn('menus', 'slug')) {
                $insert['slug'] = $row['slug'];
            }
            if (Schema::hasColumn('menus', 'type')) {
                $insert['type'] = $row['type'];
            }
            if (Schema::hasColumn('menus', 'icon')) {
                $insert['icon'] = $row['icon'];
            }
            if (Schema::hasColumn('menus', 'sort_order')) {
                $insert['sort_order'] = $row['sort_order'];
            }
            DB::table('menus')->insert($insert);
        }

        if (Schema::hasTable('cibo_express') && Schema::hasColumn('cibo_express', 'page_key')) {
            $booking = DB::table('cibo_express')->where('page_key', 'private_booking')->first();
            if (!$booking) {
                $ciboId = ((int) DB::table('cibo_express')->max('id')) + 1;
                DB::table('cibo_express')->insert([
                    'id' => $ciboId,
                    'title' => 'Private Bookings / Large Orders',
                    'description' => 'Book Sugar Pappi for private events and large orders.',
                    'image' => 'public/img/private-bookings.jpg',
                    'status' => 1,
                    'page_key' => 'private_booking',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $wholesale = DB::table('cibo_express')->where('page_key', 'wholesale')->first();
            if (!$wholesale) {
                $ciboId = ((int) DB::table('cibo_express')->max('id')) + 1;
                DB::table('cibo_express')->insert([
                    'id' => $ciboId,
                    'title' => 'Dessert Wholesale',
                    'description' => 'Order desserts in bulk for delivery on Monday, Thursday and Saturday between 7:00 PM and 10:00 PM.',
                    'image' => null,
                    'status' => 1,
                    'page_key' => 'wholesale',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Non-destructive: keep existing menus.
    }
};

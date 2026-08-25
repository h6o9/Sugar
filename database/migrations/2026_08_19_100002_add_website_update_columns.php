<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'order_type')) {
                $table->string('order_type')->nullable()->after('status');
            }
            if (!Schema::hasColumn('orders', 'menu_type')) {
                $table->string('menu_type')->nullable()->default('food');
            }
            if (!Schema::hasColumn('orders', 'add_items_until')) {
                $table->timestamp('add_items_until')->nullable();
            }
            if (!Schema::hasColumn('orders', 'last_modified_at')) {
                $table->timestamp('last_modified_at')->nullable();
            }
            if (!Schema::hasColumn('orders', 'receipt_version')) {
                $table->unsignedInteger('receipt_version')->default(1);
            }
            if (!Schema::hasColumn('orders', 'is_scheduled')) {
                $table->boolean('is_scheduled')->default(false);
            }
            if (!Schema::hasColumn('orders', 'scheduled_at')) {
                $table->timestamp('scheduled_at')->nullable();
            }
            if (!Schema::hasColumn('orders', 'wholesale_delivery_date')) {
                $table->date('wholesale_delivery_date')->nullable();
            }
            if (!Schema::hasColumn('orders', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('orders', 'discount_label')) {
                $table->string('discount_label')->nullable();
            }
            if (!Schema::hasColumn('orders', 'paid_amount')) {
                $table->decimal('paid_amount', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('orders', 'balance_due')) {
                $table->decimal('balance_due', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('orders', 'notes')) {
                $table->text('notes')->nullable();
            }
            if (!Schema::hasColumn('orders', 'branch_id')) {
                $table->unsignedBigInteger('branch_id')->nullable();
            }
        });

        Schema::table('menus', function (Blueprint $table) {
            if (!Schema::hasColumn('menus', 'slug')) {
                $table->string('slug')->nullable();
            }
            if (!Schema::hasColumn('menus', 'type')) {
                $table->string('type')->default('food');
            }
            if (!Schema::hasColumn('menus', 'icon')) {
                $table->string('icon')->nullable();
            }
            if (!Schema::hasColumn('menus', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0);
            }
            if (!Schema::hasColumn('menus', 'is_visible')) {
                $table->boolean('is_visible')->default(true);
            }
        });

        Schema::table('branches', function (Blueprint $table) {
            if (!Schema::hasColumn('branches', 'is_orderable')) {
                $table->boolean('is_orderable')->default(false);
            }
            if (!Schema::hasColumn('branches', 'city_label')) {
                $table->string('city_label')->nullable();
            }
            if (!Schema::hasColumn('branches', 'display_on_landing')) {
                $table->boolean('display_on_landing')->default(true);
            }
        });

        if (Schema::hasTable('cibo_express') && !Schema::hasColumn('cibo_express', 'page_key')) {
            Schema::table('cibo_express', function (Blueprint $table) {
                $table->string('page_key')->nullable();
            });
        }

        if (Schema::hasTable('branches')) {
            DB::table('branches')
                ->where(function ($q) {
                    $q->where('name', 'like', '%Manchester%')
                        ->orWhere('location', 'like', '%Manchester%');
                })
                ->update([
                    'is_orderable' => 1,
                    'city_label' => 'Manchester City Centre',
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $columns = [
                'order_type', 'menu_type', 'add_items_until', 'last_modified_at',
                'receipt_version', 'is_scheduled', 'scheduled_at', 'wholesale_delivery_date',
                'discount_amount', 'discount_label', 'paid_amount', 'balance_due', 'notes',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('menus', function (Blueprint $table) {
            foreach (['slug', 'type', 'icon', 'sort_order', 'is_visible'] as $column) {
                if (Schema::hasColumn('menus', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('branches', function (Blueprint $table) {
            foreach (['is_orderable', 'city_label', 'display_on_landing'] as $column) {
                if (Schema::hasColumn('branches', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

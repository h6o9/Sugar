<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFoodMenuIdToProductsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('products') || Schema::hasColumn('products', 'food_menu_id')) {
            return;
        }
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('food_menu_id')->nullable()->after('menu_id');
        });
    }

    public function down()
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'food_menu_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('food_menu_id');
            });
        }
    }
}

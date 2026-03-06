<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOriginalPriceToAddToCartItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
   public function up()
{
    Schema::table('add_to_cart_items', function (Blueprint $table) {
        $table->decimal('original_price', 10, 2)->nullable()->after('price');
    });
}

public function down()
{
    Schema::table('add_to_cart_items', function (Blueprint $table) {
        $table->dropColumn('original_price');
    });
}
}

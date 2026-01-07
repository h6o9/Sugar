<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPriceColumnsToAddToCartItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
   public function up()
{
    Schema::table('add_to_cart_items', function (Blueprint $table) {
        $table->decimal('subtotal', 10, 2)->default(0)->after('price');
        $table->decimal('tax_amount', 10, 2)->default(0)->after('subtotal');
        $table->decimal('estimated_total', 10, 2)->default(0)->after('tax_amount');
    });
}

public function down()
{
    Schema::table('add_to_cart_items', function (Blueprint $table) {
        $table->dropColumn(['subtotal', 'tax_amount', 'estimated_total']);
    });
}

}

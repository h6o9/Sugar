<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductComplementaryIdToOrderItemsTable extends Migration
{
    public function up()
    {
        Schema::table('order_items', function (Blueprint $table) {

            $table->unsignedBigInteger('product_complementary_id')->nullable()->after('product_id');

            $table->foreign('product_complementary_id')
                ->references('id')
                ->on('products')
                ->onDelete('set null'); // agar product delete ho jaye to null ho jaye
        });
    }

    public function down()
    {
        Schema::table('order_items', function (Blueprint $table) {

            $table->dropForeign(['product_complementary_id']);
            $table->dropColumn('product_complementary_id');
        });
    }
}
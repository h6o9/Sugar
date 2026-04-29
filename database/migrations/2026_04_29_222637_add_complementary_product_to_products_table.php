<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddComplementaryProductToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('complementary_product')->nullable()->after('menu_id');
            
            // Add foreign key constraint
            $table->foreign('complementary_product')
                  ->references('id')
                  ->on('products')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
                  
            // Add index for better performance
            $table->index('complementary_product');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['complementary_product']);
            // Drop index
            $table->dropIndex(['complementary_product']);
            // Drop column
            $table->dropColumn('complementary_product');
        });
    }
}

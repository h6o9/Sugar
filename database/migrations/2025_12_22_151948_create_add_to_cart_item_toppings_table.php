<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('add_to_cart_item_toppings', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('add_to_cart_item_id');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('topping_id');
			$table->unsignedBigInteger('variant_id')->nullable();


            $table->timestamps();

            // Foreign key
            $table->foreign('add_to_cart_item_id')
                ->references('id')
                ->on('add_to_cart_items')
                ->onDelete('cascade');

				  // Optional FKs
				$table->foreign('variant_id')->references('id')->on('product_variants');
				$table->foreign('topping_id')->references('id')->on('toppings');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('add_to_cart_item_toppings');
    }
};

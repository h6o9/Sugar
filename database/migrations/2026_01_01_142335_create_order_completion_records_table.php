<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('order_completion_records', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('order_id');     // Completed order ID
            $table->string('order_code')->nullable();  // Order code / number
            $table->string('reward_type')->default('order_completion'); 
            $table->integer('points');                  // Awarded points

            $table->timestamps();

            // Optional: foreign key
            // $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_completion_records');
    }
};

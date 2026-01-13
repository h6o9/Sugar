<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reward_histories', function (Blueprint $table) {
            $table->id();
            $table->string('reward_type');        // e.g., 'bonus', 'referral'
            $table->integer('points');            // reward points
			$table->string('reward_title'); // e.g., 'earned', 'redeemed'
            $table->unsignedBigInteger('user_id'); // user reference
            $table->text('description')->nullable(); // optional description

            // New nullable columns
            $table->string('order_code')->nullable();   // optional order code
            $table->string('referral_code')->nullable(); // optional referral code

            $table->timestamps();

            // Foreign key (optional)
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('reward_histories');
    }
};

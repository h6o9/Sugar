<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_links', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement();
            $table->unsignedBigInteger('user_id');
            $table->string('referral_code', 50);
            $table->string('install_url', 255);
            $table->integer('clicks')->default(0);
            $table->integer('installs')->default(0);
            $table->timestamps();

            // Optional but recommended
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique('referral_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_links');
    }
};

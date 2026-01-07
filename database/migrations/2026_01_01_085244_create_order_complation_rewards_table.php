<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('order_complation_rewards', function (Blueprint $table) {
            $table->id();             // Auto-increment primary key
            $table->integer('points'); // Points value
            $table->timestamps();      // created_at, updated_at (optional)
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_complation_rewards');
    }
};

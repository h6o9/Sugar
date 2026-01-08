<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id(); // bigint unsigned auto increment (Primary)

            $table->unsignedBigInteger('user_id')->nullable()->index();

            $table->string('title', 255)->nullable();

            $table->text('description'); // NOT NULL

            $table->string('seenByUser', 255)->nullable()->default('0');

            $table->timestamps(); // created_at & updated_at

			$table->foreign('user_id')
          ->references('id')
          ->on('users')
          ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};

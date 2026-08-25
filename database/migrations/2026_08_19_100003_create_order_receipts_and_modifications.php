<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('order_receipts')) {
            Schema::create('order_receipts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id');
                $table->unsignedInteger('version')->default(1);
                $table->string('receipt_number')->nullable();
                $table->string('status')->default('active');
                $table->longText('snapshot')->nullable();
                $table->timestamp('superseded_at')->nullable();
                $table->timestamps();

                $table->index(['order_id', 'status']);
            });
        }

        if (!Schema::hasTable('order_modifications')) {
            Schema::create('order_modifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('action');
                $table->longText('payload')->nullable();
                $table->decimal('previous_total', 10, 2)->nullable();
                $table->decimal('new_total', 10, 2)->nullable();
                $table->unsignedInteger('receipt_version')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('order_payments')) {
            Schema::create('order_payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id');
                $table->string('type')->default('original');
                $table->string('method')->nullable();
                $table->decimal('amount', 10, 2)->default(0);
                $table->string('status')->default('recorded');
                $table->string('stripe_session_id')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_payments');
        Schema::dropIfExists('order_modifications');
        Schema::dropIfExists('order_receipts');
    }
};

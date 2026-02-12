<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBulkFeatureDetailsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('bulk_feature_details', function (Blueprint $table) {
            $table->id();

            $table->enum('action', ['increase', 'decrease']);
            $table->enum('method', ['percentage', 'fixed amount']);
            $table->decimal('amount', 10, 2);

            $table->boolean('status')->default(1);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('bulk_feature_details');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lost_item_id');
            $table->unsignedBigInteger('found_item_id');
            $table->float('similarity_score')->nullable();
            $table->enum('status', ['available', 'pending', 'approved', 'rejected', 'dismissed'])->default('available');
            $table->timestamps();

            $table->foreign('lost_item_id')->references('id')->on('items')->onDelete('cascade');
            $table->foreign('found_item_id')->references('id')->on('items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};

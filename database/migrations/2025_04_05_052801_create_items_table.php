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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name',255)->nullable(false);
            $table->text('description')->nullable();
            $table->string('image',255)->nullable();
            $table->json('image_embeddings')->nullable();
            $table->enum('type', ['lost', 'found'])->nullable(false);
            $table->enum('status', ['active', 'resolved'])->default('active');
            $table->unsignedBigInteger('category_id')->nullable(false);
            $table->unsignedBigInteger('color_id')->nullable(false);
            $table->unsignedBigInteger('location_id')->nullable(false);
            $table->unsignedBigInteger('student_id')->nullable(false);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('color_id')->references('id')->on('colours');
            $table->foreign('location_id')->references('id')->on('locations');
            $table->foreign('student_id')->references('id')->on('students');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};

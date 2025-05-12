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
        Schema::create('claims', function (Blueprint $table) {
            $table->id(); // claim_id - Primary Key, Auto-increment
            $table->unsignedBigInteger('match_id')->nullable(); // Foreign Key, Nullable
            $table->unsignedBigInteger('lost_item_id')->nullable(); // Foreign Key, Nullable
            $table->unsignedBigInteger('found_item_id'); // Foreign Key
            $table->unsignedBigInteger('student_id'); // Foreign Key
            $table->unsignedBigInteger('admin_id')->nullable(); // Foreign Key
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('student_justification'); // Not Null
            $table->text('admin_justification')->nullable();
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('match_id')->references('id')->on('matches')->onDelete('set null');
            $table->foreign('lost_item_id')->references('id')->on('items')->onDelete('set null');
            $table->foreign('found_item_id')->references('id')->on('items')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};

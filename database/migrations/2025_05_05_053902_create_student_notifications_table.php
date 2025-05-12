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
        Schema::create('student_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('body');
            $table->string('type')->nullable(); // Type of notification (e.g. claim_update)
            $table->text('data')->nullable(); // JSON data associated with the notification
            $table->string('status')->default('unread'); // read, unread
            $table->dateTime('read_at')->nullable(); // When the notification was read
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_notifications');
    }
};

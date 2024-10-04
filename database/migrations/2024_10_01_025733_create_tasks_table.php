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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('council_position_id')->nullable();
            $table->foreignId('approved_by_council_position_id')->nullable();
            $table->string('title')->nullable();
            $table->text('task_details')->nullable();
            $table->dateTime('due_date')->nullable();
            $table->dateTime('completed_at')->nullable(); // Add completed_at column
            $table->string('status')->default('To Do');
            $table->boolean('is_lock')->default(false);
            $table->boolean('is_done')->default(false);
            $table->dateTime('status_changed_at')->nullable();  // Track when the status changes
            $table->text('remarks')->nullable();  //
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};

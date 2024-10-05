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
        Schema::create('council_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('council_id')->nullable();
            $table->foreignId('user_id')->nullable();
            $table->string('position')->nullable();
            $table->boolean('is_login')->default(false);
            $table->boolean('grant_access')->default(false); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('council_positions');
    }
};

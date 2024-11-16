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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('council_id')->nullable();
            $table->foreignId('council_position_id')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->text('content')->nullable();
            $table->double('latitude', 15, 12); // Up to 12 decimal places, ideal for GPS precision
    $table->double('longitude', 15, 12); // Same here
    $table->double('radius', 15, 12)->nullable();
            $table->text('specified_location')->nullable();
            $table->text('map_location')->nullable();
            $table->text('place_id')->nullable();
            $table->timestamp('start_time');
            $table->timestamp('end_time');
            $table->boolean('is_active')->default(true);
            $table->boolean('restrict_event')->default(false);
            $table->unsignedInteger('max_capacity')->nullable(); // Optional
            $table->string('type')->nullable(); // Optional
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};

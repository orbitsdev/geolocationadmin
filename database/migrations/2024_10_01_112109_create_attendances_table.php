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
            Schema::create('attendances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('event_id')->nullable();
                $table->foreignId('council_position_id')->nullable();
                    $table->decimal('latitude', 10, 8);
                    $table->decimal('longitude', 11, 8);
                $table->string('status')->default('present');
                $table->timestamp('attendance_time')->nullable();
                $table->timestamp('check_in_time')->nullable(); // Optional
                $table->timestamp('check_out_time')->nullable(); // Optional
                $table->string('attendance_code')->nullable(); // Optional
                $table->string('device_id')->nullable();
                $table->string('device_name')->nullable();
                $table->text('selfie_image')->nullable();
                $table->boolean('attendance_allowed')->default(true);
                $table->text('notes')->nullable(); // Option
                $table->timestamps();
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('attendances');
        }
    };

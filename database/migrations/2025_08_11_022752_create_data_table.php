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
        Schema::create('data', function (Blueprint $table) {
            $table->id();
            $table->string('station_id', 12);
            $table->decimal('temp_air', 5, 2)->nullable();
            $table->decimal('humidity', 5, 2)->nullable();
            $table->decimal('wind_speed', 5, 2)->nullable();
            $table->decimal('wind_direction', 5, 2)->nullable();
            $table->decimal('rain_10min', 6, 2)->nullable();
            $table->timestamps();
            $table->foreign('station_id')
                ->references('id')
                ->on('stations')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data');
    }
};

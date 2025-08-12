<?php

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stations', function (Blueprint $table) {
            $table->string('id', 12)->primary()->unique();
            $table->foreignIdFor(User::class)->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->decimal('lat', 8, 5);
            $table->decimal('lon', 8, 5);
            $table->string('voivodeship')->default('wielkopolskie');
            $table->string('district')->default('poznanski');
            $table->string('photo')->nullable();
            $table->mediumText('description')->nullable();
            $table->boolean('temperature')->default(true);
            $table->boolean('humidity')->default(true);
            $table->boolean('wind')->default(true);
            $table->boolean('rain')->default(true);
            $table->boolean('active')->default(true);
            $table->boolean('public')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stations');
    }
};

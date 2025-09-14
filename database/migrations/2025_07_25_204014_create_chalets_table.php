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
        Schema::create('chalets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('location');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('price_per_night', 10, 2);
            $table->integer('max_guests')->default(1);
            $table->integer('bedrooms')->default(1);
            $table->integer('bathrooms')->default(1);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->json('availability_calendar')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['owner_id', 'is_active']);
            $table->index(['is_featured', 'is_active']);
            $table->index(['location']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chalets');
    }
};

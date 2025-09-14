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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chalet_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->integer('rating')->unsigned(); // 1-5 stars
            $table->text('comment')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->timestamps();

            // Indexes
            $table->index(['chalet_id', 'is_approved']);
            $table->index(['customer_id']);
            $table->index(['rating', 'is_approved']);

            // Ensure one review per booking
            $table->unique(['booking_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};

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
        Schema::create('booking_extras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->string('extra_name'); // e.g., "Late checkout", "Extra cleaning", etc.
            $table->decimal('extra_price', 8, 2);
            $table->integer('quantity')->default(1);
            $table->timestamps();

            // Index
            $table->index(['booking_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_extras');
    }
};

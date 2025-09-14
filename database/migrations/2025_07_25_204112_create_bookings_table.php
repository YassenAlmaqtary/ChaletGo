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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chalet_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->string('booking_number')->unique();
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->integer('guests_count')->default(1);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending');
            $table->text('special_requests')->nullable();
            $table->json('booking_details')->nullable(); // Extra details like contact info, etc.
            $table->timestamps();

            // Indexes for better performance
            $table->index(['chalet_id', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index(['check_in_date', 'check_out_date']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

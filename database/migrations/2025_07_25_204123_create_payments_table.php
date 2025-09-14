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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->string('payment_method'); // credit_card, bank_transfer, cash, etc.
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->string('transaction_id')->nullable();
            $table->json('payment_details')->nullable(); // Gateway response, etc.
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['booking_id', 'status']);
            $table->index(['status', 'paid_at']);
            $table->index(['transaction_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

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
        Schema::create('chalet_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chalet_id')->constrained()->onDelete('cascade');
            $table->string('image_path');
            $table->string('alt_text')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Indexes
            $table->index(['chalet_id', 'is_primary']);
            $table->index(['chalet_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chalet_images');
    }
};

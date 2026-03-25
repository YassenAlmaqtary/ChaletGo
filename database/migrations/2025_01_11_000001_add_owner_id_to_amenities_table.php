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
        if (!Schema::hasTable('amenities')) {
            // If the amenities table does not exist yet (migration order),
            // skip safely. The create_amenities_table migration should be
            // the source of truth for fresh installs.
            return;
        }

        if (Schema::hasColumn('amenities', 'owner_id')) {
            return;
        }

        Schema::table('amenities', function (Blueprint $table) {
            $table->foreignId('owner_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
            $table->index('owner_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('amenities')) {
            return;
        }

        if (!Schema::hasColumn('amenities', 'owner_id')) {
            return;
        }

        Schema::table('amenities', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
            $table->dropIndex(['owner_id']);
            $table->dropColumn('owner_id');
        });
    }
};


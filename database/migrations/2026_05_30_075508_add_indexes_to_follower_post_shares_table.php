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
        Schema::table('follower_post_shares', function (Blueprint $table) {
           // Adds a composite index for ultra-fast lookups and deletions
            $table->index(['post_id', 'shared_by_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('follower_post_shares', function (Blueprint $table) {
            //
            $table->dropIndex(['post_id', 'shared_by_id']);
        });
    }
};

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
        Schema::create('post_circle_shares', function (Blueprint $table) {
            $table->id();
               // Post reference
        $table->foreignId('post_id')
              ->constrained()
              ->onDelete('cascade');

        // Circle reference
        $table->foreignId('circle_id')
              ->constrained()
              ->onDelete('cascade');

        // User who shared the post
        $table->foreignId('shared_by')
              ->constrained('users')
              ->onDelete('cascade');

        $table->timestamps();

        // Prevent duplicate share
        $table->unique(['post_id', 'circle_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_circle_shares');
    }
};

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
        Schema::create('circle_members', function (Blueprint $table) {
            $table->id(); 
            // Circle reference
            $table->foreignId('circle_id')
                ->constrained()
                ->onDelete('cascade');

            // Member user
            $table->foreignId('member_id')
                ->constrained('users')
                ->onDelete('cascade');

            // Who added this member (optional)
            $table->foreignId('added_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->timestamps();

            // Prevent duplicate membership
            $table->unique(['circle_id', 'member_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('circle_members');
    }
};

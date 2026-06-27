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
        Schema::create('post_feedbacks', function (Blueprint $table) {
            $table->id();
               // Who gave the feedback
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
 
            // Which post it's about
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
 
            // Denormalized: whose score this feedback affects (the post's author
            // at the time feedback was given). Stored directly so we never need
            // a join through `posts` just to recompute a user's score.
            $table->foreignId('post_owner_id')->constrained('users')->cascadeOnDelete();
 
            // 1 = good ... 10 = bad. Only 1/4/6/8/10 are selectable from the UI,
            // but stored as a plain tinyint — no need for an enum at the DB level.
            $table->unsignedTinyInteger('rating');
 
            $table->timestamps();
 
            // One feedback per user per post, ever — but editable (UPDATE, not
            // a new row), enforced here so a race condition can never create two.
            $table->unique(['user_id', 'post_id']);
 
            // Speeds up "give me all feedback rows for this owner" when
            // recomputing their score.
            $table->index('post_owner_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_feedbacks');
    }
};

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
        Schema::table('users', function (Blueprint $table) {
            //
              // The user's current reputation score, 1.00 (good) - 10.00 (bad).
            // Defaults to 1.00 (good) so brand-new users get full reach until
            // they've actually accumulated real feedback.
            $table->decimal('owner_score', 4, 2)->default(1.00)->after('email');
 
            // How many feedback ratings have ever contributed to owner_score.
            // Used both to compute the running average and to gate the
            // "minimum 5 ratings before throttling applies" grace period.
            $table->unsignedInteger('owner_score_count')->default(0)->after('owner_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
             $table->dropColumn(['owner_score', 'owner_score_count']);
        });
    }
};

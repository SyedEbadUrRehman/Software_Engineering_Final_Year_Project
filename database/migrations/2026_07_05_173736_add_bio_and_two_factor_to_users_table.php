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
               $table->text('bio')->nullable()->after('name');
 
            // UI-only toggle for now — no actual 2FA enforcement is wired
            // up yet (no OTP flow, no recovery codes). This just persists
            // the switch state so it doesn't reset on refresh once the
            // real flow is built later.
            $table->boolean('two_factor_enabled')->default(false)->after('bio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
            
            $table->dropColumn(['bio', 'two_factor_enabled']);
        });
    }
};

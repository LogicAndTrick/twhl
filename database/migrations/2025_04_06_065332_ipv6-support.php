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
            $table->string('last_access_ip', 40)->nullable()->change();
        });
        Schema::table('api_keys', function (Blueprint $table) {
            $table->string('ip', 40)->nullable()->change();
        });
        Schema::table('bans', function (Blueprint $table) {
            $table->string('ip', 40)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBrokenLinkFlagToVaultItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vault_items', function (Blueprint $table) {
            // Whether or not the download link is broken
            $table->boolean('link_broken')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vault_items', function (Blueprint $table) {
            $table->dropColumn('link_broken');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWikiRevisionCreditsTable extends Migration
{
    public function up()
    {
        Schema::create('wiki_revision_credits', function (Blueprint $table)
        {
            $table->increments('id');
            $table->char('type');
            $table->unsignedInteger('revision_id');
            $table->string('description');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('name')->nullable();
            $table->string('url')->nullable();
            $table->string('wayback_url', 512)->nullable();

            $table->foreign('revision_id')->references('id')->on('wiki_revisions');
            $table->foreign('user_id')->references('id')->on('users');

			$table->index(['revision_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('wiki_revision_credits');
    }
}

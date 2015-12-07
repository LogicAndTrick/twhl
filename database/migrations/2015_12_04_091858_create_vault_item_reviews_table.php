<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVaultItemReviewsTable extends Migration
{
    public function up()
    {
        Schema::create('vault_item_reviews', function(Blueprint $table)
        {
            $table->increments('id');

            $table->unsignedInteger('item_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('comment_id')->nullable();

            $table->text('content_text');
            $table->text('content_html');

            $table->decimal('score_architecture');
            $table->decimal('score_texturing');
            $table->decimal('score_ambience');
            $table->decimal('score_lighting');
            $table->decimal('score_gameplay');

            $table->integer('stat_comments');
            $table->boolean('flag_locked');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('item_id')->references('id')->on('vault_items');
            $table->foreign('comment_id')->references('id')->on('comments');
        });
    }

    public function down()
    {
        Schema::drop('vault_item_reviews');
    }
}

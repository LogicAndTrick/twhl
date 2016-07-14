<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UserSubscriptions extends Migration
{
    public function up()
    {
        Schema::create('user_subscriptions', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->char('article_type', 2);
            $table->unsignedInteger('article_id');
            $table->boolean('send_email');
            $table->boolean('send_push_notification');

            $table->index(['user_id', 'article_type', 'article_id']);
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::drop('user_subscriptions');
    }
}

<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApiKeysTable extends Migration
{
    public function up()
    {
        Schema::create('api_keys', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('key', 100);
            $table->string('app', 255);
            $table->string('ip', 15);

            $table->timestamps();

            $table->index(['user_id']);
            $table->unique(['key']);
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::drop('api_keys');
    }
}

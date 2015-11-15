<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bans', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('ip', 15)->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('reason', 255);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->index(['ip', 'ends_at']);
            $table->index(['user_id', 'ends_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('bans');
    }
}

<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMotmsTable extends Migration
{
    public function up()
    {
        Schema::create('motms', function(Blueprint $table)
        {
            $table->increments('id');

            $table->unsignedInteger('item_id')->nullable();

            $table->unsignedInteger('year');
            $table->unsignedInteger('month');

            $table->timestamps();

            $table->foreign('item_id')->references('id')->on('vault_items');
        });
    }

    public function down()
    {
        Schema::drop('motms');
    }
}

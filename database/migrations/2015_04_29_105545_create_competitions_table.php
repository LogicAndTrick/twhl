<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompetitionsTable extends Migration {

	public function up()
	{
		Schema::create('competitions', function(Blueprint $table)
		{
			$table->increments('id');
            $table->unsignedInteger('status_id');
            $table->unsignedInteger('type_id');
            $table->unsignedInteger('judge_type_id');
            $table->string('name');
            $table->text('brief_text');
            $table->text('brief_html');
            $table->string('brief_attachment');
            $table->date('open_date');
            $table->date('close_date');
            $table->date('voting_close_date');
            $table->text('outro_text');
            $table->text('outro_html');
			$table->timestamps();
            $table->softDeletes();

            $table->foreign('status_id')->references('id')->on('competition_statuses');
            $table->foreign('type_id')->references('id')->on('competition_types');
            $table->foreign('judge_type_id')->references('id')->on('competition_judge_types');
		});
	}

	public function down()
	{
		Schema::drop('competitions');
	}

}

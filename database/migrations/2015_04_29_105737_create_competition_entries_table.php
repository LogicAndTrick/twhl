<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompetitionEntriesTable extends Migration {

	public function up()
	{
		Schema::create('competition_entries', function(Blueprint $table)
		{
			$table->increments('id');
            $table->unsignedInteger('competition_id');
            $table->unsignedInteger('user_id');
            $table->string('title');
            $table->text('content_text');
            $table->text('content_html');
            $table->boolean('is_hosted_externally');
            $table->string('file_location', 512);
			$table->timestamps();

            $table->foreign('competition_id')->references('id')->on('competitions');
            $table->foreign('user_id')->references('id')->on('users');
		});
	}

	public function down()
	{
		Schema::drop('competition_entries');
	}

}

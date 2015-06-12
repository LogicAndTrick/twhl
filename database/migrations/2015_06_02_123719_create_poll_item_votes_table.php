<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePollItemVotesTable extends Migration {

	public function up()
	{
		Schema::create('poll_item_votes', function(Blueprint $table)
		{
			$table->increments('id');
            $table->unsignedInteger('poll_id');
            $table->unsignedInteger('item_id');
            $table->unsignedInteger('user_id');

            $table->foreign('poll_id')->references('id')->on('polls');
            $table->foreign('item_id')->references('id')->on('poll_items');
            $table->foreign('user_id')->references('id')->on('users');
		});

        DB::unprepared("
            CREATE PROCEDURE update_poll_statistics(pid INT)
            BEGIN
                -- Update vote count
                UPDATE poll_items pi
                SET pi.stat_votes = (SELECT COUNT(*) FROM poll_item_votes WHERE item_id = pi.id)
                WHERE poll_id = pid;
            END;");

        DB::unprepared("
            CREATE TRIGGER poll_item_votes_update_statistics_on_insert AFTER INSERT ON poll_item_votes
            FOR EACH ROW BEGIN
                CALL update_poll_statistics(NEW.poll_id);
            END;");
	}

	public function down()
	{
        DB::unprepared("DROP TRIGGER IF EXISTS poll_item_votes_update_statistics_on_insert");
        DB::unprepared("DROP PROCEDURE IF EXISTS update_poll_statistics");
		Schema::drop('poll_item_votes');
	}

}

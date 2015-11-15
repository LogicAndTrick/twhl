<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJournalsTable extends Migration {

	public function up()
	{
		Schema::create('journals', function(Blueprint $table)
		{
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->text('content_text');
            $table->text('content_html');
            $table->integer('stat_comments');
            $table->boolean('flag_locked');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users');
		});

        DB:unprepared("
            CREATE PROCEDURE update_user_journal_statistics(uid INT)
            BEGIN
                UPDATE users
                SET stat_journals = (SELECT COUNT(*) FROM journals WHERE user_id = uid AND deleted_at IS NULL)
                WHERE id = uid;
            END;
        ");

        DB::unprepared("
            CREATE TRIGGER journals_update_statistics_on_insert AFTER INSERT ON journals
            FOR EACH ROW BEGIN
                CALL update_user_journal_statistics(NEW.user_id);
            END;");

        DB::unprepared("
            CREATE TRIGGER journals_update_statistics_on_update AFTER UPDATE ON journals
            FOR EACH ROW BEGIN
                CALL update_user_journal_statistics(NEW.user_id);

                IF NEW.user_id != OLD.user_id THEN
                    CALL update_user_journal_statistics(OLD.user_id);
                END IF;
            END;");
	}

	public function down()
	{
        DB::unprepared("DROP TRIGGER IF EXISTS journals_update_statistics_on_update");
        DB::unprepared("DROP TRIGGER IF EXISTS journals_update_statistics_on_insert");
        DB::unprepared("DROP PROCEDURE IF EXISTS update_user_journal_statistics");
		Schema::drop('journals');
	}

}

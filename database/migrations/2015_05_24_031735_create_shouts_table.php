<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShoutsTable extends Migration {

	public function up()
	{
		Schema::create('shouts', function(Blueprint $table)
		{
			$table->increments('id');
            $table->unsignedInteger('user_id');
            $table->text('content');
			$table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
		});

        DB:unprepared("
            CREATE PROCEDURE update_user_shout_statistics(uid INT)
            BEGIN
                UPDATE users
                SET stat_shouts = (SELECT COUNT(*) FROM shouts WHERE user_id = uid AND deleted_at IS NULL)
                WHERE id = uid;
            END;
        ");

        DB::unprepared("
            CREATE TRIGGER shouts_update_statistics_on_insert AFTER INSERT ON shouts
            FOR EACH ROW BEGIN
                CALL update_user_shout_statistics(NEW.user_id);
            END;");

        DB::unprepared("
            CREATE TRIGGER shouts_update_statistics_on_update AFTER UPDATE ON shouts
            FOR EACH ROW BEGIN
                CALL update_user_shout_statistics(NEW.user_id);

                IF NEW.user_id != OLD.user_id THEN
                    CALL update_user_shout_statistics(OLD.user_id);
                END IF;
            END;");
	}

	public function down()
	{
        DB::unprepared("DROP TRIGGER IF EXISTS shouts_update_statistics_on_update");
        DB::unprepared("DROP TRIGGER IF EXISTS shouts_update_statistics_on_insert");
        DB::unprepared("DROP PROCEDURE IF EXISTS update_user_shout_statistics");
		Schema::drop('shouts');
	}

}

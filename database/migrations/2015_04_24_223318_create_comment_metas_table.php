<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommentMetasTable extends Migration {

	public function up()
	{
		Schema::create('comment_metas', function(Blueprint $table)
		{
            $table->increments('id');
            $table->unsignedInteger('comment_id');
            $table->char('key', 1);
            $table->string('value');

            $table->foreign('comment_id')->references('id')->on('comments');
            $table->index(['key', 'value']);
		});

        DB::unprepared("
            CREATE PROCEDURE update_comment_statistics(atype CHAR(1), aid INT, uid INT)
            BEGIN

                UPDATE users
                SET stat_comments = (SELECT COUNT(*) FROM comments WHERE user_id = uid AND deleted_at IS NULL)
                WHERE id = uid;

                CASE atype
                    WHEN 'n' THEN
                        BEGIN
                            -- Todo: news
                        END;
                    WHEN 'j' THEN
                        BEGIN
                            -- Todo: journals
                        END;
                    WHEN 'v' THEN
                        UPDATE vault_items SET
                            stat_ratings = (SELECT COUNT(*) FROM comment_metas as m LEFT JOIN comments as c ON m.comment_id = c.id
                                            WHERE article_type = atype AND article_id = aid AND deleted_at IS NULL AND m.key = 'r'),
                            stat_comments = (SELECT COUNT(*) FROM comments WHERE article_type = atype AND article_id = aid AND deleted_at IS NULL),
                            stat_average_rating = (SELECT IFNULL(AVG(value), 0) FROM comment_metas as m LEFT JOIN comments as c ON m.comment_id = c.id
                                                   WHERE article_type = atype AND article_id = aid AND deleted_at IS NULL AND m.key = 'r')
                        WHERE
                            id = aid AND deleted_at IS NULL;
                    WHEN 'm' THEN
                        BEGIN
                            -- Todo: motm
                        END;
                    ELSE
                        BEGIN
                            -- Do nothing...
                        END;
                END CASE;
            END;");
	}

	public function down()
	{
        DB::unprepared('DROP PROCEDURE IF EXISTS update_comment_statistics');
		Schema::drop('comment_metas');
	}

}

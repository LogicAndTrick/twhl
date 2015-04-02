<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForumPostsTable extends Migration {

	public function up()
	{
		Schema::create('forum_posts', function(Blueprint $table)
		{
            $table->increments('id');
            $table->unsignedInteger('forum_id');
            $table->unsignedInteger('thread_id');
            $table->unsignedInteger('user_id');
            $table->text('content_text');
            $table->text('content_html');
			$table->timestamps();
            $table->softDeletes();

            $table->foreign('forum_id')->references('id')->on('forums');
            $table->foreign('thread_id')->references('id')->on('forum_threads');
            $table->foreign('user_id')->references('id')->on('users');

            $table->index('forum_id');
            $table->index('thread_id');
            $table->index('user_id');
            $table->index('created_at');
		});

        DB::unprepared("
            CREATE PROCEDURE update_thread_statistics(tid INT)
            BEGIN
                -- Update last post & post count
                UPDATE forum_threads SET
                    last_post_id = (SELECT id from forum_posts WHERE thread_id = tid AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 1),
                    stat_posts = (SELECT COUNT(*) FROM forum_posts WHERE thread_id = tid AND deleted_at IS NULL)
                WHERE id = tid;
            END;");

        DB::unprepared("
            CREATE PROCEDURE update_forum_statistics(fid INT)
            BEGIN
                UPDATE forums
                SET last_post_id = (SELECT id from forum_posts WHERE forum_id = fid AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 1),
                stat_posts = (SELECT COUNT(*) FROM forum_posts WHERE forum_id = fid AND deleted_at IS NULL)
                WHERE id = fid;
            END;");

        DB::unprepared("
            CREATE TRIGGER forum_posts_update_statistics_on_insert AFTER INSERT ON forum_posts
            FOR EACH ROW BEGIN
                CALL update_thread_statistics(NEW.thread_id);
                CALL update_forum_statistics(NEW.forum_id);
            END;");

        DB::unprepared("
            CREATE TRIGGER forum_posts_update_statistics_on_update AFTER UPDATE ON forum_posts
            FOR EACH ROW BEGIN
                CALL update_thread_statistics(NEW.thread_id);
                CALL update_forum_statistics(NEW.forum_id);

                IF NEW.thread_id != OLD.thread_id THEN
                    CALL update_thread_statistics(OLD.thread_id);
                END IF;

                IF NEW.forum_id != OLD.forum_id THEN
                    CALL update_forum_statistics(NEW.forum_id);
                END IF;
            END;");
	}

	public function down()
	{
        DB::unprepared("DROP TRIGGER IF EXISTS forum_posts_update_statistics_on_update");
        DB::unprepared("DROP TRIGGER IF EXISTS forum_posts_update_statistics_on_insert");
        DB::unprepared("DROP PROCEDURE IF EXISTS update_thread_statistics");
        DB::unprepared("DROP PROCEDURE IF EXISTS update_forum_statistics");
		Schema::drop('forum_posts');
	}

}

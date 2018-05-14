<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWikiRevisionsTable extends Migration {

	public function up()
	{
		Schema::create('wiki_revisions', function(Blueprint $table)
		{
			$table->increments('id');
            $table->unsignedInteger('object_id');
            $table->unsignedInteger('user_id');
            $table->boolean('is_active');
            $table->string('slug', 200);
            $table->string('title', 200);
            $table->text('content_text');
            $table->text('content_html');
            $table->string('message', 500);
			$table->timestamps();
            $table->softDeletes();

            $table->foreign('object_id')->references('id')->on('wiki_objects');
            $table->foreign('user_id')->references('id')->on('users');

            $table->index(['deleted_at', 'is_active', 'slug']);
		});

        DB::unprepared("ALTER TABLE wiki_revisions ADD FULLTEXT wiki_revisions_content_text_fulltext (title, content_text);");

        DB::unprepared("
            CREATE PROCEDURE update_wiki_object(oid INT)
            BEGIN
                DECLARE rid INT;
                DECLARE del TIMESTAMP;

                UPDATE wiki_revisions SET is_active = 0 WHERE object_id = oid;

                -- Check if the object is deleted
                SELECT deleted_at INTO del
                FROM wiki_objects WHERE id = oid
                LIMIT 1;

                IF del IS NULL THEN
                    -- Get the current revision id
                    SELECT id INTO rid
                    FROM wiki_revisions WHERE object_id = oid AND deleted_at IS NULL
                    ORDER BY created_at DESC LIMIT 1;
                    
                    IF rid IS NULL THEN
                        -- All revisions are deleted
                        UPDATE wiki_objects SET
                            current_revision_id = 0,
                            deleted_at = NOW()
                        WHERE id = oid;
                    ELSE
                        -- Update current revision
                        UPDATE wiki_objects SET current_revision_id = rid WHERE id = oid;
                        UPDATE wiki_revisions SET is_active = 1 WHERE id = rid;
                    END IF;
                END IF;
            END;");

        DB::unprepared("
            CREATE PROCEDURE update_user_wiki_statistics(uid INT)
            BEGIN
                UPDATE users
                SET stat_wiki_edits = (SELECT COUNT(*) FROM wiki_revisions WHERE user_id = uid AND deleted_at IS NULL)
                WHERE id = uid;
            END;
        ");

        DB::unprepared("
            CREATE TRIGGER wiki_revisions_update_statistics_on_insert AFTER INSERT ON wiki_revisions
            FOR EACH ROW BEGIN
                CALL update_user_wiki_statistics(NEW.user_id);
            END;");

        DB::unprepared("
            CREATE TRIGGER wiki_revisions_update_statistics_on_update AFTER UPDATE ON wiki_revisions
            FOR EACH ROW BEGIN
                CALL update_user_wiki_statistics(NEW.user_id);

                IF NEW.user_id != OLD.user_id THEN
                    CALL update_user_wiki_statistics(OLD.user_id);
                END IF;
            END;");
	}

	public function down()
	{
        DB::unprepared("DROP TRIGGER IF EXISTS wiki_revisions_update_statistics_on_update");
        DB::unprepared("DROP TRIGGER IF EXISTS wiki_revisions_update_statistics_on_insert");
        DB::unprepared("DROP PROCEDURE IF EXISTS update_user_wiki_statistics");
        DB::unprepared("DROP PROCEDURE IF EXISTS update_wiki_object");
		Schema::drop('wiki_revisions');
	}

}

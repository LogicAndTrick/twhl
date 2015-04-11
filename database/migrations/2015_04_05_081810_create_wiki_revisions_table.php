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
		});

        DB::unprepared("
            CREATE PROCEDURE update_wiki_object(oid INT)
            BEGIN
                DECLARE rid INT;
                DECLARE deleted_at TIMESTAMP;

                UPDATE wiki_revisions SET is_active = 0 WHERE object_id = oid;

                -- Get the current revision id
                SELECT id, deleted_at
                INTO rid, deleted_at
                FROM wiki_revisions WHERE object_id = oid AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 1;


                -- Update current revision
                UPDATE wiki_objects SET current_revision_id = rid WHERE id = oid;

                IF deleted_at is null THEN
                    -- Set the active revision
                    UPDATE wiki_revisions SET is_active = 1 WHERE id = rid;
                ELSE
                    -- The object has been deleted, there's no active revision
                    UPDATE wiki_objects SET current_revision_id = rid WHERE id = oid;
                END IF;

            END;");
	}

	public function down()
	{
        DB::unprepared("DROP PROCEDURE IF EXISTS update_wiki_object");
		Schema::drop('wiki_revisions');
	}

}

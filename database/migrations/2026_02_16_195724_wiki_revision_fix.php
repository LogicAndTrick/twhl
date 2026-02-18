<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // See https://github.com/LogicAndTrick/twhl/issues/133

        
        DB::unprepared("DROP procedure IF EXISTS update_wiki_object;");
        DB::unprepared("
            CREATE PROCEDURE update_wiki_object(oid INT)
            BEGIN
                DECLARE rid INT;
                DECLARE del TIMESTAMP;
                
                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK; -- On failure, roll back...
                    SET @disable_wiki_revisions_update_statistics_on_update = NULL; -- ...enable stats update trigger...
                    RESIGNAL; -- ... and signal failure, before exiting.
                END;
                START TRANSACTION;
                    -- Disable stats update trigger
                    SET @disable_wiki_revisions_update_statistics_on_update = 1;

                    UPDATE wiki_revisions
                    SET is_active = 0
                    WHERE object_id = oid AND is_active = 1;

                    -- Check if the object is deleted
                    SELECT deleted_at INTO del
                    FROM wiki_objects
                    WHERE id = oid
                    LIMIT 1;

                    IF del IS NULL THEN
                        -- Get the current revision id
                        SELECT id INTO rid
                        FROM wiki_revisions
                        WHERE object_id = oid AND deleted_at IS NULL
                        ORDER BY created_at DESC, id DESC
                        LIMIT 1;

                        IF rid IS NULL THEN
                            -- All revisions are deleted
                            UPDATE wiki_objects
                            SET current_revision_id = 0, deleted_at = NOW()
                            WHERE id = oid;
                        ELSE
                            -- Update current revision
                            UPDATE wiki_objects SET current_revision_id = rid WHERE id = oid;
                            UPDATE wiki_revisions SET is_active = 1 WHERE id = rid;
                        END IF;
                    END IF;
                COMMIT;
                -- Enable stats update trigger
                SET @disable_wiki_revisions_update_statistics_on_update = NULL;
            END;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP procedure IF EXISTS update_wiki_object;");
        DB::unprepared("
            CREATE PROCEDURE update_wiki_object(oid INT)
            BEGIN
                DECLARE rid INT;
                DECLARE del TIMESTAMP;

                -- disable stats update trigger
                SET @disable_wiki_revisions_update_statistics_on_update = 1;
                
                UPDATE wiki_revisions
                SET is_active = 0
                WHERE object_id = oid AND is_active = 1;

                -- Check if the object is deleted
                SELECT deleted_at INTO del
                FROM wiki_objects
                WHERE id = oid
                LIMIT 1;

                IF del IS NULL THEN
                    -- Get the current revision id
                    SELECT id INTO rid
                    FROM wiki_revisions
                    WHERE object_id = oid AND deleted_at IS NULL
                    ORDER BY created_at DESC, id DESC
                    LIMIT 1;
                    
                    IF rid IS NULL THEN
                        -- All revisions are deleted
                        UPDATE wiki_objects
                        SET current_revision_id = 0, deleted_at = NOW()
                        WHERE id = oid;
                    ELSE
                        -- Update current revision
                        UPDATE wiki_objects SET current_revision_id = rid WHERE id = oid;
                        UPDATE wiki_revisions SET is_active = 1 WHERE id = rid;
                    END IF;
                END IF;
                
                -- enable stats update trigger
                SET @disable_wiki_revisions_update_statistics_on_update = NULL;
            END;");
    }
};

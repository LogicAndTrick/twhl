<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS wiki_revisions_update_statistics_on_update;");
        DB::unprepared("
            CREATE TRIGGER wiki_revisions_update_statistics_on_update AFTER UPDATE ON wiki_revisions
            FOR EACH ROW BEGIN
                IF COALESCE(@disable_wiki_revisions_update_statistics_on_update, 0) != 1 THEN
                    CALL update_user_wiki_statistics(NEW.user_id);
    
                    IF NEW.user_id != OLD.user_id THEN
                        CALL update_user_wiki_statistics(OLD.user_id);
                    END IF;
                END IF;
            END;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS wiki_revisions_update_statistics_on_update;");
        DB::unprepared("
            CREATE TRIGGER wiki_revisions_update_statistics_on_update AFTER UPDATE ON wiki_revisions
            FOR EACH ROW BEGIN
                IF @disable_wiki_revisions_update_statistics_on_update != 1 THEN
                    CALL update_user_wiki_statistics(NEW.user_id);
    
                    IF NEW.user_id != OLD.user_id THEN
                        CALL update_user_wiki_statistics(OLD.user_id);
                    END IF;
                END IF;
            END;");
    }
};

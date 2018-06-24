<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CommentArticlesView extends Migration
{
    public function up()
    {
        DB::unprepared("
            CREATE VIEW comment_articles
            AS
                SELECT 'n' AS article_type, n.id AS article_id, n.user_id, n.title, n.stat_comments, n.created_at, n.updated_at
                FROM news n
                WHERE n.deleted_at IS NULL
            UNION
                SELECT 'j', j.id, j.user_id, j.title, j.stat_comments, j.created_at, j.updated_at
                FROM journals j
                WHERE j.deleted_at IS NULL
            UNION
                SELECT 'v', vi.id, vi.user_id, vi.name, vi.stat_comments, vi.created_at, vi.updated_at
                FROM vault_items vi
                WHERE vi.deleted_at IS NULL
            UNION
                SELECT 'p', p.id, null, p.title, p.stat_comments, p.created_at, p.updated_at
                FROM polls p
                WHERE p.deleted_at IS NULL
            UNION
                SELECT 'w', o.id, null, r.title, o.stat_comments, o.created_at, o.updated_at
                FROM wiki_objects o
                INNER JOIN wiki_revisions r ON r.object_id = o.id AND r.is_active = 1
                WHERE o.deleted_at IS NULL
        ");
    }

    public function down()
    {
        DB::unprepared("DROP VIEW IF EXISTS comment_articles");
    }
}

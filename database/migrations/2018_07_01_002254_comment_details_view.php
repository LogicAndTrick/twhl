<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CommentDetailsView extends Migration
{

    public function up()
    {
        DB::unprepared("
            create view comment_details
            as
            select
            	c.id, c.article_type, c.article_id, c.user_id, c.content_text, c.content_html, c.created_at, c.updated_at, c.deleted_at,
                COALESCE(n.user_id, j.user_id, v.user_id) as article_user_id,
                COALESCE(n.title, j.title, v.name, p.title, r.title, '') as article_title,
                COALESCE(n.stat_comments, j.stat_comments, v.stat_comments, p.stat_comments, o.stat_comments, 0) as article_stat_comments,
                COALESCE(n.flag_locked, j.flag_locked, p.flag_locked, o.flag_locked, 0) as article_flag_locked,
                COALESCE(n.created_at, j.created_at, v.created_at, p.created_at, o.created_at) as article_created_at,
                COALESCE(n.updated_at, j.updated_at, v.updated_at, p.updated_at, o.updated_at) as article_updated_at
            from comments c
            left join news n on c.article_type = 'n' and n.id = c.article_id
            left join journals j on c.article_type = 'j' and j.id = c.article_id
            left join vault_items v on c.article_type = 'v' and v.id = c.article_id
            left join polls p on c.article_type = 'p' and p.id = c.article_id
            left join wiki_objects o on c.article_type = 'w' and o.id = c.article_id
            left join wiki_revisions r on o.current_revision_id = r.id
            where c.deleted_at is null
            and COALESCE(n.deleted_at, j.deleted_at, v.deleted_at, p.deleted_at, o.deleted_at) is null
        ");
    }

    public function down()
    {
        DB::unprepared("DROP VIEW IF EXISTS comment_details");
    }
}

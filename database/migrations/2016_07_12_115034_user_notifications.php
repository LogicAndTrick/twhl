<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UserNotifications extends Migration
{
    public function up()
    {
        Schema::create('user_notifications', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->char('article_type', 2);
            $table->unsignedInteger('article_id');
            $table->unsignedInteger('post_id');
            $table->boolean('is_unread');
            $table->boolean('is_processed');

            $table->timestamps();

            $table->index(['user_id', 'article_type', 'article_id', 'is_unread'], 'user_notifications_user_type_id_unread_index');
            $table->foreign('user_id')->references('id')->on('users');
        });
        DB::unprepared("
            CREATE VIEW user_notification_details AS
            SELECT
                UN.id, UN.user_id, UN.article_type, UN.article_id, UN.post_id, UN.is_unread, UN.created_at, UN.updated_at,
                (CASE UN.article_type
                    WHEN 'wo' THEN WOWR.title
                    WHEN 'wr' THEN WR.title
                    WHEN 'ft' THEN FT.title
                    WHEN 'vc' THEN VC.name
                    WHEN 'vi' THEN VI.name
                    WHEN 'ns' THEN NS.title
                    WHEN 'jn' THEN JN.title
                    WHEN 'po' THEN PO.title
                END) AS title
            FROM user_notifications UN
            LEFT JOIN wiki_objects WO ON UN.article_type = 'wo' AND UN.article_id = WO.id
              LEFT JOIN wiki_revisions WOWR ON WO.current_revision_id = WOWR.id
            LEFT JOIN wiki_revisions WR ON UN.article_type = 'wr' AND UN.article_id = WR.id
            LEFT JOIN forum_threads ft ON UN.article_type = 'ft' AND UN.article_id = FT.id
            LEFT JOIN vault_categories VC ON UN.article_type = 'vc' AND UN.article_id = VC.id
            LEFT JOIN vault_items VI ON UN.article_type = 'vi' AND UN.article_id = VI.id
            LEFT JOIN news NS ON UN.article_type = 'ns' AND UN.article_id = NS.id
            LEFT JOIN journals JN ON UN.article_type = 'jn' AND UN.article_id = JN.id
            LEFT JOIN polls PO ON UN.article_type = 'po' AND UN.article_id = PO.id
            ;");
    }

    public function down()
    {
        DB::unprepared("DROP VIEW IF EXISTS user_notification_details");
        Schema::drop('user_notifications');
    }
}

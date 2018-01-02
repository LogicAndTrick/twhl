<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UserNotificationsItemId extends Migration
{
    public function up()
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->boolean('is_own_article');
        });
        Schema::table('user_notifications', function (Blueprint $table) {
            $table->unsignedInteger('stat_hits');
        });

        DB::unprepared("
            ALTER VIEW user_subscription_details AS
            SELECT
                US.id, US.user_id, US.article_type, US.article_id, US.send_email, US.send_push_notification, US.is_own_article,
                (CASE US.article_type
                    WHEN 'wo' THEN WOWR.title
                    WHEN 'wr' THEN WOWR.title
                    WHEN 'ft' THEN FT.title
                    WHEN 'vc' THEN VC.name
                    WHEN 'vi' THEN VI.name
                    WHEN 'ns' THEN NS.title
                    WHEN 'jn' THEN JN.title
                    WHEN 'po' THEN PO.title
                END) AS title
            FROM user_subscriptions US
            LEFT JOIN wiki_objects WO ON US.article_type IN ('wo', 'wr') AND US.article_id = WO.id
              LEFT JOIN wiki_revisions WOWR ON WO.current_revision_id = WOWR.id
            LEFT JOIN forum_threads FT ON US.article_type = 'ft' AND US.article_id = FT.id
            LEFT JOIN vault_categories VC ON US.article_type = 'vc' AND US.article_id = VC.id
            LEFT JOIN vault_items VI ON US.article_type = 'vi' AND US.article_id = VI.id
            LEFT JOIN news NS ON US.article_type = 'ns' AND US.article_id = NS.id
            LEFT JOIN journals JN ON US.article_type = 'jn' AND US.article_id = JN.id
            LEFT JOIN polls PO ON US.article_type = 'po' AND US.article_id = PO.id
            ;");

        DB::unprepared("
            ALTER VIEW user_notification_details AS
            SELECT
                UN.id, UN.user_id, UN.article_type, UN.article_id, UN.post_id, UN.stat_hits, UN.is_unread, UN.created_at, UN.updated_at,
                (CASE UN.article_type
                    WHEN 'wo' THEN WOWR.title
                    WHEN 'wr' THEN WOWR.title
                    WHEN 'ft' THEN FT.title
                    WHEN 'vc' THEN VC.name
                    WHEN 'vi' THEN VI.name
                    WHEN 'ns' THEN NS.title
                    WHEN 'jn' THEN JN.title
                    WHEN 'po' THEN PO.title
                END) AS title
            FROM user_notifications UN
            LEFT JOIN wiki_objects WO ON UN.article_type IN ('wo', 'wr') AND UN.article_id = WO.id
              LEFT JOIN wiki_revisions WOWR ON WO.current_revision_id = WOWR.id
            LEFT JOIN forum_threads FT ON UN.article_type = 'ft' AND UN.article_id = FT.id
            LEFT JOIN vault_categories VC ON UN.article_type = 'vc' AND UN.article_id = VC.id
            LEFT JOIN vault_items VI ON UN.article_type = 'vi' AND UN.article_id = VI.id
            LEFT JOIN news NS ON UN.article_type = 'ns' AND UN.article_id = NS.id
            LEFT JOIN journals JN ON UN.article_type = 'jn' AND UN.article_id = JN.id
            LEFT JOIN polls PO ON UN.article_type = 'po' AND UN.article_id = PO.id;
        ");
    }

    public function down()
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->dropColumn('is_own_article');
        });
        Schema::table('user_notifications', function (Blueprint $table) {
            $table->dropColumn('stat_hits');
        });
    }
}

<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UserSubscriptions extends Migration
{
    public function up()
    {
        Schema::create('user_subscriptions', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->char('article_type', 2);
            $table->unsignedInteger('article_id');
            $table->boolean('send_email');
            $table->boolean('send_push_notification');

            $table->index(['user_id', 'article_type', 'article_id']);
            $table->foreign('user_id')->references('id')->on('users');
        });
        DB::unprepared("
            CREATE VIEW user_subscription_details AS
            SELECT
                US.id, US.user_id, US.article_type, US.article_id, US.send_email, US.send_push_notification,
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
    }

    public function down()
    {
        DB::unprepared("DROP VIEW IF EXISTS user_subscription_details");
        Schema::drop('user_subscriptions');
    }
}

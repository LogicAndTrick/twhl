<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class WikiRevisionsTitleSlugIndex extends Migration
{
    public function up()
    {
        Schema::table('wiki_revisions', function (Blueprint $table) {
            $table->index(['title', 'slug', 'is_active', 'deleted_at', 'id', 'object_id'], 'wiki_revisions_title_slug');
        });
    }

    public function down()
    {
        Schema::table('wiki_revisions', function (Blueprint $table) {
            $table->dropIndex('wiki_revisions_title_slug');
        });
    }
}

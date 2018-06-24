<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CommentsDeletedAtIndex extends Migration
{
    public function up()
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->index(['deleted_at', 'user_id', 'created_at'], 'comments_deleted_at');
        });
    }

    public function down()
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex('comments_deleted_at');
        });
    }
}

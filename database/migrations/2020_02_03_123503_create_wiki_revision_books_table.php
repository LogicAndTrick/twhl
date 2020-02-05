<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWikiRevisionBooksTable extends Migration
{
    public function up()
    {
        Schema::create('wiki_revision_books', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('revision_id');

            $table->string('book_name');
            $table->string('chapter_name');
            $table->integer('chapter_number');
            $table->integer('page_number');

            $table->foreign('revision_id')->references('id')->on('wiki_revisions');
            $table->index([ 'book_name', 'chapter_name' ]);
            $table->index([ 'book_name', 'chapter_number' ]);
        });
    }

    public function down()
    {
        Schema::dropIfExists('wiki_revision_books');
    }
}

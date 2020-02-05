<?php

namespace App\Models\Wiki;

use Illuminate\Database\Eloquent\Model;

class WikiRevisionBook extends Model
{
    protected $table = 'wiki_revision_books';
    protected $fillable = ['revision_id', 'book_name', 'chapter_name', 'chapter_number', 'page_number'];
    public $visible = ['id', 'revision_id', 'book_name', 'chapter_name', 'chapter_number', 'page_number', 'wiki_revision'];
    public $timestamps = false;

    public function wiki_revision()
    {
        return $this->belongsTo('App\Models\Wiki\WikiRevision', 'revision_id');
    }
}

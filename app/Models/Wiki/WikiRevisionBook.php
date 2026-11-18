<?php

namespace App\Models\Wiki;

use App\Models\ModelWithExtraTypeInfo;

class WikiRevisionBook extends ModelWithExtraTypeInfo
{
    protected $table = 'wiki_revision_books';
    protected $fillable = ['revision_id', 'book_name', 'chapter_name', 'chapter_number', 'page_number'];
    public $visible = ['id', 'revision_id', 'book_name', 'chapter_name', 'chapter_number', 'page_number', 'wiki_revision'];
    protected $casts = [
        'chapter_number' => 'integer',
        'page_number' => 'integer',
    ];
    public $timestamps = false;

    public function wiki_revision()
    {
        return $this->belongsTo('App\Models\Wiki\WikiRevision', 'revision_id');
    }
}

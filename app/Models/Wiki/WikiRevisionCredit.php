<?php

namespace App\Models\Wiki;

use App\Models\ModelWithExtraTypeInfo;

class WikiRevisionCredit extends ModelWithExtraTypeInfo
{
    const CREDIT = 'c';
    const ARCHIVE = 'a';
    const FULL = 'f';

    protected $table = 'wiki_revision_credits';
    protected $fillable = ['type', 'revision_id', 'description', 'user_id', 'name', 'url', 'wayback_url'];
    public $visible = ['id', 'type', 'revision_id', 'description', 'user_id', 'name', 'url', 'wayback_url', 'user'];
    protected $nullable = [
        'name',
        'url',
        'user_id',
        'wayback_url',
    ];
    public $timestamps = false;

    public function wiki_revision()
    {
        return $this->belongsTo('App\Models\Wiki\WikiRevision', 'revision_id');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\Accounts\User');
    }
}

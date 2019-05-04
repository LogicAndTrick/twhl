<?php namespace App\Models\Wiki;

use Illuminate\Database\Eloquent\Model;

class WikiRevisionMeta extends Model {

    const CATEGORY = 'c';
    const LINK = 'l';
    const IMAGE_WIDTH = 'w';
    const IMAGE_HEIGHT = 'h';
    const FILE_SIZE = 's';
    const UPLOAD_ID = 'u';

	//
    protected $table = 'wiki_revision_metas';
    protected $fillable = ['revision_id', 'key', 'value'];
    public $visible = ['id', 'revision_id','key', 'value'];
    public $timestamps = false;

    public function wiki_revision()
    {
        return $this->belongsTo('App\Models\Wiki\WikiRevision', 'revision_id');
    }
}

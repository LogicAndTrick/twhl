<?php namespace App\Models\Wiki;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WikiRevision extends Model {

	//
    protected $table = 'wiki_revisions';
    protected $fillable = ['object_id', 'user_id', 'slug', 'title', 'content_text', 'content_html', 'message'];
    public $visible = ['id', 'object_id', 'user_id', 'is_active', 'slug', 'title', 'content_text', 'content_html', 'message', 'created_at', 'wiki_object', 'user', 'wiki_revision_metas', 'wiki_revision_books', 'wiki_revision_credits'];

    public $appends = [ 'escaped_slug' ];

    use SoftDeletes;

    public function wiki_object()
    {
        return $this->belongsTo('App\Models\Wiki\WikiObject', 'object_id');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\Accounts\User');
    }

    public function wiki_revision_metas()
    {
        return $this->hasMany('App\Models\Wiki\WikiRevisionMeta', 'revision_id');
    }

    public function wiki_revision_books()
    {
        return $this->hasMany('App\Models\Wiki\WikiRevisionBook', 'revision_id');
    }

    public function wiki_revision_credits()
    {
        return $this->hasMany('App\Models\Wiki\WikiRevisionCredit', 'revision_id');
    }

    public function hasCategories() {
        return $this->getMeta(WikiRevisionMeta::CATEGORY, true) !== null;
    }

    public function getCategories() {
        return $this->getMeta(WikiRevisionMeta::CATEGORY);
    }

    public function getUpload() {
        $id = $this->getMeta(WikiRevisionMeta::UPLOAD_ID, true);
        return $id ? WikiUpload::find($id) : null;
    }

    public function getFileSize() {
        return $this->getMeta(WikiRevisionMeta::FILE_SIZE, true);
    }

    public function getImageWidth() {
        return $this->getMeta(WikiRevisionMeta::IMAGE_WIDTH, true);
    }

    public function getImageHeight() {
        return $this->getMeta(WikiRevisionMeta::IMAGE_HEIGHT, true);
    }

    public function getEmbedSlug() {
        if (substr($this->slug, 0, 7) == 'upload:') return substr($this->slug, 7);
        else return $this->slug;
    }

    public function getEscapedSlugAttribute() {
        return rawurlencode($this->slug);
    }

    private function getMeta($type, $first = false) {
        $r = [];
        foreach ($this->wiki_revision_metas as $meta) {
            if ($meta->key != $type) continue;
            if ($first) return $meta->value;
            else $r[] = $meta->value;
        }
        if ($first) return null;
        return $r;
    }

    public function getNiceTitle($object = null) {
        if (!$object) $object = $this->wiki_object;
        $type_id = $object->type_id;
        if ($type_id == 2) return 'Upload: '.$this->title;
        if ($type_id == 3) return 'Category: '.explode(':',$this->title)[1];
        return $this->title;
    }

    public static function CreateSlug($text) {
        $text = str_ireplace(' ', '_', $text);
        $text = preg_replace('/[^-$_.+!*\'"(),:;<>^{}|~0-9a-z[\]]/si', '', $text);
        return $text;
    }
}

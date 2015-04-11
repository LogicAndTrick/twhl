<?php namespace App\Models\Wiki;

use Illuminate\Database\Eloquent\Model;

class WikiRevision extends Model {

	//
    protected $table = 'wiki_revisions';
    protected $fillable = ['object_id', 'user_id', 'slug', 'title', 'content_text', 'content_html', 'message'];
    protected $visible = ['object_id', 'user_id', 'slug', 'title', 'content_text', 'message'];

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

    public function hasCategories() {
        return $this->getMeta(WikiRevisionMeta::CATEGORY, true) !== null;
    }

    public function getCategories() {
        return $this->getMeta(WikiRevisionMeta::CATEGORY);
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

    public static function CreateSlug($text) {
        $text = str_ireplace(' ', '_', $text);
        $text = preg_replace('%[^a-z0-9-_()\'\\.:]%si', '', $text);
        return $text;
    }

}

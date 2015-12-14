<?php namespace App\Models\Wiki;

use Illuminate\Database\Eloquent\Model;

class WikiObject extends Model {

	//
    protected $table = 'wiki_objects';
    protected $fillable = ['type_id', 'permission_id'];

    public function current_revision()
    {
        return $this->belongsTo('App\Models\Wiki\WikiRevision', 'current_revision_id');
    }

    public function commentsIsLocked() {
        return $this->flag_locked;
    }

    public function commentsCanAddMeta($meta) {
        return true;
    }

}

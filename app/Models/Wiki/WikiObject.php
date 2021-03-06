<?php namespace App\Models\Wiki;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WikiObject extends Model {

	//
    protected $table = 'wiki_objects';
    protected $fillable = ['type_id', 'permission_id'];
    public $visible = ['id','type_id','current_revision_id', 'permission_id','stat_comments', 'flag_locked','created_at','updated_at','current_revision','permission'];

    use SoftDeletes;

    public function current_revision()
    {
        return $this->belongsTo('App\Models\Wiki\WikiRevision', 'current_revision_id');
    }

    public function permission()
    {
        return $this->belongsTo('App\Models\Accounts\Permission', 'permission_id');
    }

    public function commentsIsLocked() {
        return $this->flag_locked;
    }

    public function commentsCanAddMeta($meta) {
        return true;
    }

    public function canEdit() {
        if (!$this->permission_id) return true;
        return permission('WikiCreate') && permission($this->permission->name);
    }

    public function canDelete() {
        return permission('WikiAdmin');
    }

    public function isProtected() {
        return !!$this->permission_id;
    }
}

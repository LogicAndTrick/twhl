<?php

namespace App\Models\Vault;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Auth;

class VaultItemReview extends Model
{
    use SoftDeletes;

    public $table = 'vault_item_reviews';
    public $fillable = [
        'item_id', 'user_id', 'comment_id',
        'content_text', 'content_html',
        'score_architecture', 'score_texturing', 'score_ambience', 'score_lighting', 'score_gameplay',
        'stat_comments', 'flag_locked'
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\Accounts\User');
    }

    public function vault_item()
    {
        return $this->belongsTo('App\Models\Vault\VaultItem', 'item_id');
    }

    public function comment()
    {
        return $this->belongsTo('App\Models\Comments\Comment');
    }

    public function getRating() {
        return (
            $this->score_architecture +
            $this->score_texturing +
            $this->score_ambience +
            $this->score_lighting +
            $this->score_gameplay
        ) / 5;
    }

    public function getStarRating() {
        $r = ceil($this->getRating() / 2);
        return $r ? $r : 1;
    }

    /**
     * Returns true if this review is editable by the current user.
     * @return bool
     */
    public function isEditable()
    {
        $user = Auth::user();
        return $user && ($user->id == $this->user_id || permission('VaultAdmin'));
    }
}

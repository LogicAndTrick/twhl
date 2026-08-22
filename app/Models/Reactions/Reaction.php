<?php

namespace App\Models\Reactions;

use App\Models\Comments\Comment;
use App\Models\Forums\ForumPost;
use App\Models\Journal;
use App\Models\News;
use App\Models\Vault\VaultItem;
use Illuminate\Database\Eloquent\Model;

class Reaction extends Model
{
    const NEWS = 'n';
    const JOURNAL = 'j';
    const FORUM_POST = 'p';
    const COMMENT = 'c';
    const VAULT_ITEM = 'v';

    protected $table = 'reactions';
    protected $fillable = ['id', 'entity_type', 'entity_id', 'user_id', 'reaction_type_id'];
    protected $visible = ['id', 'entity_type', 'entity_id', 'user_id', 'reaction_type_id', 'user'];
    public $timestamps = false;

    protected $with = ['user:id,name,avatar_custom,avatar_file'];

    public function user()
    {
        return $this->belongsTo(\App\Models\Accounts\User::class, 'user_id', 'id');
    }

    public function getPermissionToReact(): string | bool
    {
        switch ($this->entity_type) {
            case Reaction::NEWS:
                return 'NewsComment';
            case Reaction::JOURNAL:
                return 'JournalComment';
            case Reaction::FORUM_POST:
                return 'ForumCreate';
            case Reaction::COMMENT:
                return true;
            case Reaction::VAULT_ITEM:
                return 'VaultComment';
            default:
                return 'MISSING';
        }
    }

    public function getEntity()
    {
        switch ($this->entity_type) {
            case Reaction::NEWS:
                return News::query()->find($this->entity_id);
            case Reaction::JOURNAL:
                return Journal::query()->find($this->entity_id);
            case Reaction::FORUM_POST:
                $fp = ForumPost::query()->find($this->entity_id);
                if (!$fp) return null;

                $ft = $fp->thread;
                if (!$ft) return null;

                $ff = $ft->forum;
                if (!$ff) return null;

                return $fp;
            case Reaction::COMMENT:
                $com = Comment::query()->find($this->entity_id);
                if (!$com) return null;
                
                $art = $com->getArticle();
                if (!$art) return null;

                return $com;
            case Reaction::VAULT_ITEM:
                return VaultItem::query()->find($this->entity_id);
            default:
                return null;
        }
    }
}

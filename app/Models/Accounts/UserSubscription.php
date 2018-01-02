<?php namespace App\Models\Accounts;

use Illuminate\Database\Eloquent\Model;
use DB;

class UserSubscription extends Model {

    const WIKI_OBJECT = 'wo';
    const WIKI_REVISION = 'wr';
    const FORUM_THREAD = 'ft';
    const VAULT_CATEGORY = 'vc';
    const VAULT_ITEM = 'vi';
    const NEWS = 'ns';
    const JOURNAL = 'jn';
    const POLL = 'po';

    public static function getSubscription($user, $article_type, $article_id, $clear = false)
    {
        if (!$user || !$user->id) return null;

        $ty = $article_type;
        $sub = UserSubscription::whereUserId($user->id)
                ->whereArticleType($ty)
                ->whereArticleId($article_id)
                ->first();
        if ($sub && $clear) {
            DB::statement('CALL clear_user_notifications(?, ?, ?);', [$user->id, $ty, $article_id]);
        }
        return $sub;
    }

	protected $table = 'user_subscriptions';
	protected $fillable = [ 'user_id', 'article_type', 'article_id', 'send_email', 'send_push_notification', 'is_own_article' ];
    public $visible = [ ];
    public $timestamps = false;

    protected $appends = ['type_description','link'];
    public function getTypeDescriptionAttribute() {
        switch ($this->article_type) {
            case UserSubscription::WIKI_OBJECT: return 'Wiki Comments';
            case UserSubscription::WIKI_REVISION: return 'Wiki Revisions';
            case UserSubscription::FORUM_THREAD: return 'Forum Thread';
            case UserSubscription::VAULT_CATEGORY: return 'Vault Category';
            case UserSubscription::VAULT_ITEM: return 'Vault Item';
            case UserSubscription::NEWS: return 'News Article';
            case UserSubscription::JOURNAL: return 'Journal';
            case UserSubscription::POLL: return 'Poll';
            default: return 'Unknown';
        }
    }
    public function getLinkAttribute() {
        $id = $this->article_id;
        switch ($this->article_type) {
            case UserSubscription::WIKI_OBJECT:
            case UserSubscription::WIKI_REVISION: return act('wiki', 'view', $id);
            case UserSubscription::FORUM_THREAD: return act('thread', 'view', $id).'?page=last';
            case UserSubscription::VAULT_CATEGORY: return act('vault', 'index').'?cats='.$id;
            case UserSubscription::VAULT_ITEM: return act('vault', 'view', $id);
            case UserSubscription::NEWS: return act('news', 'view', $id);
            case UserSubscription::JOURNAL: return act('journal', 'view', $id);
            case UserSubscription::POLL: return act('poll', 'view', $id);
            default: return 'Unknown';
        }
    }

    public function user()
    {
        return $this->belongsTo('App\Models\Accounts\User');
    }

    public function wiki_revision()
    {
        return $this->belongsTo('App\Models\Wiki\WikiRevision', 'article_id', 'id');
    }

    public function forum_thread()
    {
        return $this->belongsTo('App\Models\Forums\ForumThread', 'article_id', 'id');
    }
}

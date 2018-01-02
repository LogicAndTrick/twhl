<?php namespace App\Models\Accounts;

use App\Models\Comments\Comment;
use Illuminate\Database\Eloquent\Model;
use DB;

class UserNotification extends Model {

    const WIKI_OBJECT = 'wo';
    const WIKI_REVISION = 'wr';
    const FORUM_THREAD = 'ft';
    const VAULT_CATEGORY = 'vc';
    const VAULT_ITEM = 'vi';
    const NEWS = 'ns';
    const JOURNAL = 'jn';
    const POLL = 'po';

	protected $table = 'user_notifications';
	protected $fillable = [ 'user_id', 'article_type', 'article_id', 'is_unread', 'is_processed', 'stat_hits' ];
    public $visible = [ ];
    protected $appends = ['type_description', 'link'];

    public function getTypeDescriptionAttribute() {
        switch ($this->article_type) {
            case UserSubscription::WIKI_OBJECT: return 'Wiki Comments';
            case UserSubscription::WIKI_REVISION: return 'Wiki Page';
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
        $link = '';
        $bookmark = '';

        $id = $this->article_id;
        $post_id = $this->post_id;

        switch ($this->article_type) {
            case UserSubscription::WIKI_OBJECT:
            case UserSubscription::WIKI_REVISION:
                $link = act('wiki', 'view', $id);
                break;
            case UserSubscription::FORUM_THREAD:
                $link = act('thread', 'view', $id).'?page=last';
                break;
            case UserSubscription::VAULT_CATEGORY:
                $link = act('vault', 'index').'?cats='.$id;
                break;
            case UserSubscription::VAULT_ITEM:
                $link = act('vault', 'view', $id);
                break;
            case UserSubscription::NEWS:
                $link = act('news', 'view', $id);
                break;
            case UserSubscription::JOURNAL:
                $link = act('journal', 'view', $id);
                break;
            case UserSubscription::POLL:
                $link = act('poll', 'view', $id);
                break;
        }

        if ($post_id <= 0) return $link;

        switch ($this->article_type) {
            case UserSubscription::WIKI_OBJECT:
            case UserSubscription::VAULT_ITEM:
            case UserSubscription::NEWS:
            case UserSubscription::JOURNAL:
            case UserSubscription::POLL:
                $bookmark = "#comment-$post_id";
                break;
            case UserSubscription::FORUM_THREAD:
                $link = act('thread', 'locate-post', $post_id);
                break;
        }

        return $link . $bookmark;
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

    public static function GetTypeFromCommentType($comment_type)
    {
        switch ($comment_type) {
            case Comment::NEWS: return UserNotification::NEWS;
            case Comment::JOURNAL: return UserNotification::JOURNAL;
            case Comment::VAULT: return UserNotification::VAULT_ITEM;
            case Comment::POLL: return UserNotification::POLL;
            case Comment::WIKI: return UserNotification::WIKI_OBJECT;
            default: return null;
        }
    }

    /**
     * Add a notification to all subscribers
     *
     * @param $notifying_user_id int The user who created the post. They will not be notified.
     * @param $article_type string The article type
     * @param $article_id int The article id
     * @param $post_id int The post id
     */
    public static function AddNotification($notifying_user_id, $article_type, $article_id, $post_id)
    {
        $type = $article_type;
        $id = $article_id;
        $user_id = $notifying_user_id;

        DB::statement('
            UPDATE user_notifications
            SET stat_hits = stat_hits + 1
            WHERE user_id != ?
            AND article_id = ?
            AND article_type = ?
            AND is_unread = 1
        ', [ $user_id, $id, $type ]);

        DB::statement('
            INSERT INTO user_notifications (user_id, article_type, article_id, post_id, stat_hits, is_unread, is_processed, created_at, updated_at)
            SELECT US.user_id, US.article_type, US.article_id, ?, 1, 1, 0, UTC_TIMESTAMP(), UTC_TIMESTAMP()
            FROM user_subscriptions US
            WHERE US.article_type = ? AND US.article_id = ? AND US.user_id != ?
            AND (
                SELECT COUNT(*)
                FROM user_notifications UN
                WHERE UN.user_id = US.user_id
                AND UN.article_id = US.article_id
                AND UN.article_type = US.article_type
                AND UN.is_unread = 1
            ) = 0
        ', [ $post_id, $type, $id, $user_id ]);
    }
}

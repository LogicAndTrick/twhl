<?php namespace App\Models\Vault;

use App\Models\Comments\CommentMeta;
use App\Models\Messages\Message;
use App\Models\Messages\MessageThread;
use App\Models\Messages\MessageThreadUser;
use App\Models\Messages\MessageUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Auth;

class VaultItem extends Model {

    use SoftDeletes;

    public $table = 'vault_items';
    public $fillable = [
        'user_id', 'engine_id', 'game_id', 'category_id', 'type_id', 'license_id',
        'name', 'content_text', 'content_html',
        'is_hosted_externally', 'file_location', 'file_size',
        'flag_notify', 'flag_ratings',
        'stat_views', 'stat_downloads', 'stat_ratings', 'stat_comments', 'stat_average_rating'
    ];
    public $visible = [
        'id', 'user_id', 'engine_id', 'game_id', 'category_id', 'type_id', 'license_id',
        'name', 'content_text', 'content_html',
        'is_hosted_externally', 'file_location', 'file_size',
        'flag_notify', 'flag_ratings',
        'stat_views', 'stat_downloads', 'stat_ratings', 'stat_comments', 'stat_average_rating'
    ];
    protected $dates = ['created_at', 'updated_at'];

    public function user()
    {
        return $this->belongsTo('App\Models\Accounts\User');
    }

    public function engine()
    {
        return $this->belongsTo('App\Models\Engine');
    }

    public function game()
    {
        return $this->belongsTo('App\Models\Game');
    }

    public function license()
    {
        return $this->belongsTo('App\Models\License');
    }

    public function vault_category()
    {
        return $this->belongsTo('App\Models\Vault\VaultCategory', 'category_id');
    }

    public function vault_type()
    {
        return $this->belongsTo('App\Models\Vault\VaultType', 'type_id');
    }

    public function vault_item_includes()
    {
        return $this->hasMany('App\Models\Vault\VaultItemInclude', 'item_id');
    }

    public function vault_includes()
    {
        return $this->belongsToMany('App\Models\Vault\VaultInclude', 'vault_item_includes', 'item_id', 'include_id');
    }

    public function vault_screenshots()
    {
        return $this->hasMany('App\Models\Vault\VaultScreenshot', 'item_id');
    }

    public function vault_item_reviews()
    {
        return $this->hasMany('App\Models\Vault\VaultItemReview', 'item_id');
    }

    public function motms()
    {
        return $this->hasMany('App\Models\Vault\Motm', 'item_id');
    }

    public function hasPrimaryScreenshot()
    {
        return $this->getPrimaryScreenshot() != null;
    }

    public function getPrimaryScreenshot()
    {
        $pri = array_first($this->vault_screenshots, function($x, $i) {
            return $x->is_primary > 0;
        });
        if (!$pri && $this->vault_screenshots->count() > 0) $pri = $this->vault_screenshots[0];
        return $pri;

    }

    public function getThumbnailAsset()
    {
        $ps = $this->getPrimaryScreenshot();
        return $ps == null
            ? 'images/no-screenshot-320.png'
            : 'uploads/vault/' . $this->getPrimaryScreenshot()->image_small;
    }

    public function getMediumAsset()
    {
        $ps = $this->getPrimaryScreenshot();
        return $ps == null
            ? 'images/no-screenshot-640.png'
            : 'uploads/vault/' . $this->getPrimaryScreenshot()->image_medium;
    }

    public function getDownloadUrl()
    {
        if ($this->is_hosted_externally) return $this->file_location;
        else return asset('uploads/vault/items/'.$this->file_location);
    }

    public function getServerFilePath()
    {
        if ($this->is_hosted_externally) return '';
        else return public_path('uploads/vault/items/'.$this->file_location);
    }

    public function getRatingStars()
    {
        $score = $this->stat_average_rating;
        $rounded = ceil($score * 2) / 2; // Round up to closest 0.5

        $full = floor($rounded);
        $half = $rounded - $full > 0;
        $empty = 5 - ceil($rounded);

        $stars = [];

        for ($i = 0; $i < $full; $i++) $stars[] = 'full';
        if ($half) $stars[] = 'half';
        for ($i = 0; $i < $empty; $i++) $stars[] = 'empty';

        return $stars;
    }

    public function reviewsAllowed()
    {
        if (!$this->flag_ratings) return false; // Can't review if you can't rate
        if ($this->category_id != 2) return false; // Only completed stuff can be reviewed
        if ($this->type_id != 1 && $this->type_id != 4) return false; // Only maps and mods can be reviewed
        return true;
    }

    public function hasReviews()
    {
        return $this->vault_item_reviews->count();
    }

    /**
     * Returns true if this item is editable by the current user.
     * @return bool
     */
    public function isEditable()
    {
        $user = Auth::user();
        return $user && ($user->id == $this->user_id || permission('VaultAdmin'));
    }

    /**
     * Returns true if this item can be reviewed
     * @return bool
     */
    public function canReview()
    {
        if (!$this->reviewsAllowed()) return false;
        $user = Auth::user();
        return $user && ($user->id != $this->user_id && permission('VaultCreate'));
    }

    public function commentsIsLocked() {
        return false;
    }

    public function commentsCanAddMeta($meta) {
        if (!Auth::check() || Auth::user()->id == $this->user_id) return false;

        switch ($meta) {
            case CommentMeta::RATING:
                return $this->flag_ratings;
        }
        return true;
    }

    public function onCommentCreated($comment) {

        if (!$this->flag_notify || $comment->user_id == $this->user_id) return;

        /*
         * Send a PM to the item owner
         * TODO: Make a more robust notifications engine and move this logic into there
         */

        $users = [$this->user_id];
        $url = act('vault', 'view', $this->id);
        $post_text = "This is an automated notification of a new comment that's been posted on: [{$url}|{$this->name}]. Don't reply to the comment here, unless you want nobody else to see the response. You should go to the vault page and post a comment there.\n\n---\n\n{$comment->content_text}";

        // Start a new thread
        if (array_search($comment->user_id, $users) === false) $users[] = Auth::user()->id;

        // Make the thread
        $thread = MessageThread::Create([
            'user_id' => $comment->user_id,
            'subject' => 'Comment on Vault item: ' . $this->name
        ]);

        // Assign all the users to the thread
        foreach ($users as $user) MessageThreadUser::Create([ 'thread_id' => $thread->id, 'user_id' => $user ]);

        // Make the message
        $message = Message::Create([
            'user_id' => $comment->user_id,
            'thread_id' => $thread->id,
            'content_text' => $post_text,
            'content_html' => app('bbcode')->Parse($post_text)
        ]);

        // Send the message to all the users
        foreach ($users as $user) {
            MessageUser::Create([
                'thread_id' => $thread->id,
                'message_id' => $message->id,
                'user_id' => $user,
                'is_unread' => $user != $comment->user_id
            ]);
        }

        $thread->update([ 'last_message_id' => $message->id ]);
    }
}

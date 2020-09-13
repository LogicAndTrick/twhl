<?php namespace App\Models\Accounts;

use App\Models\Comments\Comment;
use App\Models\Messages\MessageUser;
use App\Models\Wiki\WikiRevision;
use Carbon\Carbon;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract {

	use Notifiable, Authenticatable, CanResetPassword, SoftDeletes;

    const DEFINITELY_OBLITERATE_THIS_USER = '79aaca79215e23a4e0c99cdaf96aeb9aa111becadb88b23b00e2b96f32c9ed90';
    const DEFINITELY_REMOVE_THIS_USER = '6675a05fc096806fa52ed720baea105acb2cbf30732f5ee0a967f89d7a72dcf5';

	protected $table = 'users';
	protected $fillable = [
        'name', 'email', 'password', 'legacy_password',
        'show_email', 'timezone',
        'avatar_custom', 'avatar_file',
        'title_custom', 'title_text',
        'info_name', 'info_website', 'info_occupation', 'info_interests', 'info_location', 'info_languages', 'info_steam_profile', 'info_birthday', 'info_biography_text', 'info_biography_html',
        'skill_map', 'skill_model', 'skill_code', 'skill_music', 'skill_voice', 'skill_animate', 'skill_texture'
    ];
	protected $hidden = ['password', 'remember_token'];
    public $visible = [ 'id', 'name', 'avatar_custom', 'avatar_file', 'title_custom', 'title_text', 'avatar_full', 'avatar_small', 'avatar_inline', 'last_login_time', 'last_access_time' ];
    public $dates = ['deleted_at', 'last_login_time', 'last_access_time'];
    protected $attributes = [ 'avatar_file' => 'user_noavatar1.png' ];

    protected $appends = ['avatar_full', 'avatar_small', 'avatar_inline', 'info_birthday_formatted'];

    public function posts()
    {
        return $this->hasMany('App\Models\Forums\ForumPost');
    }

    public function name_histories()
    {
        return $this->hasMany('App\Models\Accounts\UserNameHistory');
    }

    public function api_keys()
    {
        return $this->hasMany('App\Models\Accounts\ApiKey');
    }

    public function getPreviousAliases()
    {
        $ret = [];
        foreach ($this->name_histories->sortByDesc(function($x) { return $x->created_at; }) as $nh) {
            if ($this->name != $nh->name && array_search($nh->name, $ret) === false) $ret[] = $nh->name;
        }
        return $ret;
    }

    public function unreadPrivateMessageCount()
    {
        return MessageUser::where('user_id', '=', $this->id)
                    ->where('is_unread', '=', true)
                    ->count();

    }

    public function unreadNotificationCount()
    {
        return UserNotification::whereUserId($this->id)->whereIsUnread(true)->sum('stat_hits');
    }

    public function deleteAvatar() {
        if ($this->avatar_custom) {
            if (is_file(public_path('uploads/avatars/full/'.$this->avatar_file)))
                unlink(public_path('uploads/avatars/full/'.$this->avatar_file));
            if (is_file(public_path('uploads/avatars/small/'.$this->avatar_file)))
                unlink(public_path('uploads/avatars/small/'.$this->avatar_file));
            if (is_file(public_path('uploads/avatars/inline/'.$this->avatar_file)))
                unlink(public_path('uploads/avatars/inline/'.$this->avatar_file));
        }
        $this->avatar_custom = false;
        $this->avatar_file = 'user_noavatar1.png';
    }

    public function getAvatarUrl($type = 'full')
    {
        $f = '/avatars/' . $type . '/' . $this->avatar_file;
        $l = $this->avatar_custom ? 'uploads' : 'images';
        return asset($l.$f);
    }

    public function getAvatarFullAttribute() { return $this->getAvatarUrl('full'); }
    public function getAvatarSmallAttribute() { return $this->getAvatarUrl('small'); }
    public function getAvatarInlineAttribute() { return $this->getAvatarUrl('inline'); }

    public function getInfoBirthdayFormattedAttribute() {
        $val = $this->info_birthday;
        if ($val == 0) return '';
        $d = $val % 100;
        $m = ($val - $d) / 100;
        return str_pad(strval($d), 2, '0', STR_PAD_LEFT) . '/' . str_pad(strval($m), 2, '0', STR_PAD_LEFT);
    }

    public function setInfoBirthdayFormattedAttribute($value) {
        if (preg_match('%(0[1-9]|[12][0-9]|3[01])/(0[1-9]|1[012])%m', $value, $regs)) {
        	$this->info_birthday = intval($regs[2] . $regs[1]);
        } else {
            $this->info_birthday = 0;
        }
    }

    public function permissions()
    {
        return $this->belongsToMany('App\Models\Accounts\Permission', 'user_permissions');
    }

    public function hasPermission($name)
    {
        return count($this->permissions->filter(function($p) use ($name) {
            return $p->name == $name;
        })) > 0;
    }

    public function hasSkills() {
        return $this->skill_map
            || $this->skill_model
            || $this->skill_code
            || $this->skill_music
            || $this->skill_voice
            || $this->skill_animate
            || $this->skill_texture;
    }

    public function getSkills() {
        $skills = [];
        if ($this->skill_map) $skills[] = 'Mapping';
        if ($this->skill_model) $skills[] = 'Modelling';
        if ($this->skill_code) $skills[] = 'Programming';
        if ($this->skill_music) $skills[] = 'Music/sound effects';
        if ($this->skill_voice) $skills[] = 'Voice acting';
        if ($this->skill_animate) $skills[] = 'Model animation';
        if ($this->skill_texture) $skills[] = 'Texture creation';
        return $skills;
    }

    /**
     * Remove this user, which will delete MOST content they have ever posted, and anonymise the rest.
     * @param $confirmation string If this value doesn't equal User::DEFINITELY_REMOVE_THIS_USER, the user will not be removed
     * @return bool True if the user is removed, false otherwise
     */
    public function remove($confirmation)
    {
        if ($confirmation != User::DEFINITELY_REMOVE_THIS_USER) return false;

        $deleted = Carbon::now();
        $id = $this->id;

        // Not deleted:
        // competition entries, news, wiki revisions, competition votes, competition judges, poll votes

        $soft_delete_tables = [
            'comments',
            'forum_posts',
            'forum_threads',
            'journals',
            'vault_items'
        ];
        $tables = [
            'message_users',
            'message_thread_users',
            'messages',
            'message_threads',
            'shouts',
            'user_permissions',
        ];

        // Clean up PM threads
        DB::statement("delete u from message_users u where u.message_id IN ( select m.id from messages m where m.user_id = ? )", [$id]);
        DB::statement("delete u from message_users u where u.thread_id IN ( select t.id from message_threads t where t.user_id = ? )", [$id]);
        DB::statement("delete u from message_thread_users u where u.thread_id IN ( select m.id from message_threads m where m.user_id = ? )", [$id]);
        DB::statement("delete m from messages m where m.thread_id IN ( select t.id from message_threads t where t.user_id = ? )", [$id]);

        foreach ($soft_delete_tables as $t) {
            DB::statement("UPDATE $t SET deleted_at = ? WHERE user_id = ? and deleted_at is null", [$deleted, $id]);
        }
        foreach ($tables as $t) {
            DB::statement("DELETE FROM $t WHERE user_id = ?", [$id]);
        }

        DB::statement("
            UPDATE users SET
            name = ?, email = ?, password = 'removed',
            legacy_password = '', remember_token = '',
            last_access_page = '', last_access_ip = '',
            timezone = 0, show_email = 0,
            avatar_custom = 0, avatar_file = 'user_noavatar1.png',
            title_custom = 0, title_text = '',
            info_name = '', info_website = '', info_occupation = '', info_interests = '', info_location = '', info_languages = '', info_steam_profile = '', info_birthday = 0, info_biography_text = '', info_biography_html = '',
            skill_map = 0, skill_model = 0, skill_code = 0, skill_music = 0, skill_voice = 0, skill_animate = 0, skill_texture = 0,
            stat_forum_posts = 0, stat_shouts = 0, stat_vault_items = 0, stat_journals = 0, stat_comments = 0
            WHERE id = ?
        ", [ 'User#'.$id, $id.'@removed', $id ]);

        // Fix any statistics that were messed up by deleting the comments
        $comments = Comment::onlyTrashed()->whereUserId($id)->get();
        foreach ($comments as $comment) {
            DB::statement('CALL update_comment_statistics(?, ?, ?);', [$comment->article_type, $comment->article_id, $comment->user_id]);
        }

        return true;
    }

    /**
     * Obliterate this user, which will delete ALL content they have ever posted.
     * It will also PERMANENTLY ban them by IP address.
     * @param $confirmation string If this value doesn't equal User::DEFINITELY_OBLITERATE_THIS_USER, the user will not be obliterated
     * @return bool True if the user is obliterated, false otherwise
     */
    public function obliterate($confirmation)
    {
        if ($confirmation != User::DEFINITELY_OBLITERATE_THIS_USER) return false;

        $deleted = Carbon::now();
        $id = $this->id;

        $soft_delete_tables = [
            'comments',
            'competition_entries',
            'forum_posts',
            'forum_threads',
            'journals',
            'news',
            'vault_items',
            'wiki_revisions',
        ];
        $tables = [
            'competition_entry_votes',
            'competition_judges',
            'message_users',
            'message_thread_users',
            'messages',
            'message_threads',
            'poll_item_votes',
            'shouts',
            'user_permissions',
        ];

        // Clean up PM threads
        DB::statement("delete u from message_users u where u.message_id IN ( select m.id from messages m where m.user_id = ? )", [$id]);
        DB::statement("delete u from message_users u where u.thread_id IN ( select t.id from message_threads t where t.user_id = ? )", [$id]);
        DB::statement("delete u from message_thread_users u where u.thread_id IN ( select m.id from message_threads m where m.user_id = ? )", [$id]);
        DB::statement("delete m from messages m where m.thread_id IN ( select t.id from message_threads t where t.user_id = ? )", [$id]);

        foreach ($soft_delete_tables as $t) {
            DB::statement("UPDATE $t SET deleted_at = ? WHERE user_id = ?", [$deleted, $id]);
        }
        foreach ($tables as $t) {
            DB::statement("DELETE FROM $t WHERE user_id = ?", [$id]);
        }
        DB::statement('UPDATE users SET deleted_at = ? WHERE id = ?', [$deleted, $id]);

        Ban::create([
            'user_id' => $id,
            'ip' => $this->last_access_ip ? $this->last_access_ip : null,
            'ends_at' => null,
            'reason' => 'You have been banned for spamming.'
        ]);

        // Fix any statistics that were messed up by deleting the comments
        $comments = Comment::onlyTrashed()->whereUserId($id)->get();
        foreach ($comments as $comment) {
            DB::statement('CALL update_comment_statistics(?, ?, ?);', [$comment->article_type, $comment->article_id, $comment->user_id]);
        }

        // Repair any wiki articles that the user edited
        $revisions = WikiRevision::onlyTrashed()->whereUserId($id)->get();
        foreach ($revisions as $revision) {
            DB::statement('CALL update_wiki_object(?);', [$revision->object_id]);
        }

        return true;
    }
}

<?php namespace App\Models\Accounts;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract {

	use Authenticatable, CanResetPassword, SoftDeletes;

	protected $table = 'users';
	protected $fillable = [
        'name', 'email', 'password',
        'show_email', 'timezone',
        'avatar_custom', 'avatar_file',
        'title_custom', 'title_text',
        'info_name', 'info_website', 'info_occupation', 'info_interests', 'info_location', 'info_languages', 'info_steam_profile', 'info_birthday', 'info_biography_text', 'info_biography_html',
        'skill_map', 'skill_model', 'skill_code', 'skill_music', 'skill_voice', 'skill_animate', 'skill_texture'
    ];
	protected $hidden = ['password', 'remember_token'];
    protected $visible = [ 'id', 'name', 'avatar_custom', 'avatar_file', 'title_custom', 'title_text', 'avatar_full', 'avatar_small', 'avatar_inline' ];
    protected $dates = ['deleted_at'];
    protected $attributes = [ 'avatar_file' => 'user_noavatar.png' ];

    protected $appends = ['avatar_full', 'avatar_small', 'avatar_inline', 'info_birthday_formatted'];

    public function posts()
    {
        return $this->hasMany('App\Models\Forums\ForumPost');
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
        $this->avatar_file = 'user_noavatar.png';
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
}

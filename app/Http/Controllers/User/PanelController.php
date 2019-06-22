<?php namespace App\Http\Controllers\User;

use App\Helpers\Image;
use App\Http\Controllers\Controller;
use App\Models\Accounts\ApiKey;
use App\Models\Accounts\Ban;
use App\Models\Accounts\User;
use App\Models\Accounts\UserNameHistory;
use App\Models\Accounts\UserNotificationDetails;
use App\Models\Accounts\UserPermission;
use App\Models\Accounts\UserSubscription;
use App\Models\Accounts\UserSubscriptionDetails;
use Carbon\Carbon;
use Request;
use Input;
use Auth;
use DB;
use Validator;
use Hash;

class PanelController extends Controller {

	public function __construct() {
        $this->permission(['index', 'editAvatar', 'editProfile', 'editSettings', 'editPassword'], true);
        $this->permission(['editName', 'editBans', 'addBan', 'deleteBan', 'editPermissions', 'addPermission', 'deletePermission'], 'Admin');
        $this->permission(['obliterate', 'remove'], 'ObliterateAdmin');
	}

    private static function GetUser($id) {
        if (!permission('Admin') || !$id) $id = Auth::user()->id;
        return User::findOrFail($id);
    }

	public function getIndex($id = 0) {
        $user = PanelController::GetUser($id);
        return view('user/panel/index', [
            'user' => $user
        ]);
	}

    public function getEditProfile($id = 0) {
        $user = PanelController::GetUser($id);
        return view('user/panel/edit-profile', [
            'user' => $user
        ]);
    }

    public function postEditProfile() {
        $id = Request::input('id');
        $user = PanelController::GetUser($id);
        $this->validate(Request::instance(), [
            'title_text' => 'required_with:title_custom',
            'info_website' => 'url',
            'info_birthday_formatted' => 'date_format:d/m',
            'info_biography_text' => 'max:10000'
        ]);

        $steam_user = '';
        if (preg_match('%^(?:.*/)?(.*)$%m', Request::input('info_steam_profile'), $regs)) {
            $steam_user = $regs[1];
        }

        $user->info_birthday_formatted = Request::input('info_birthday_formatted');

        $user->update([
            'title_custom' => !!Request::input('title_custom'),
            'title_text' => Request::input('title_text'),

            'skill_map' => !!Request::input('skill_map'),
            'skill_model' => !!Request::input('skill_model'),
            'skill_code' => !!Request::input('skill_code'),
            'skill_music' => !!Request::input('skill_music'),
            'skill_voice' => !!Request::input('skill_voice'),
            'skill_animate' => !!Request::input('skill_animate'),
            'skill_texture' => !!Request::input('skill_texture'),

            'info_name' => Request::input('info_name'),
            'info_website' => Request::input('info_website'),
            'info_occupation' => Request::input('info_occupation'),
            'info_interests' => Request::input('info_interests'),
            'info_location' => Request::input('info_location'),
            'info_languages' => Request::input('info_languages'),
            'info_steam_profile' => $steam_user,

            'info_biography_text' => Request::input('info_biography_text'),
            'info_biography_html' => app('bbcode')->Parse(Request::input('info_biography_text'))
        ]);

        return redirect('panel/index/'.$id);
    }

    public function getEditSettings($id = 0) {
        $user = PanelController::GetUser($id);

        // Generate time zones - we don't care too much about full zone support
        $zones = [];
        $now = new Carbon(null, 'UTC');
        for ($i = -12; $i <= 12; $i++) {
            $offset = $now->copy()->addHours($i);
            $zones[$i] = 'UTC'.($i >= 0 ? '+' : '').$i.' - '.$offset->format('H:i');
        }

        return view('user/panel/edit-settings', [
            'user' => $user,
            'zones' => $zones
        ]);
    }

    public function postEditSettings() {
        $id = Request::input('id');
        $user = PanelController::GetUser($id);
        $this->validate(Request::instance(), [
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'timezone' => 'required|between:-12,12'
        ]);
        $user->update([
            'email' => Request::input('email'),
            'show_email' => !!Request::input('show_email'),
            'timezone' => Request::input('timezone')
        ]);
        return redirect('panel/index/'.$id);
    }

    public function getEditPassword($id = 0) {
        $user = PanelController::GetUser($id);
        return view('user/panel/edit-password', [
            'user' => $user,
            'need_original' => $user->id == Auth::user()->id || !permission('Admin')
        ]);
    }

    public function postEditPassword() {
        $id = Request::input('id');
        $user = PanelController::GetUser($id);

        Validator::extend('matches_original', function($attribute, $value, $parameters) use ($user) {
            return Hash::check($value, $user->password);
        });

        $rules = [ 'password' => 'required|confirmed|min:6' ];
        // Admin users can reset another user's password without the original
        if ($user->id == Auth::user()->id || !permission('Admin')) $rules['original_password'] = 'required|matches_original';

        $this->validate(Request::instance(), $rules, [
            'matches_original' => 'This doesn\'t match your current password.'
        ]);

        $user->update([
            'password' => bcrypt(Request::input('password')),
            'legacy_password' => ''
        ]);
        return redirect('panel/index/'.$id);
    }

    public function getEditAvatar($id = 0) {
        $user = PanelController::GetUser($id);
        return view('user/panel/edit-avatar', [
            'user' => $user,
            'avatar_groups' => PanelController::$preset_avatars
        ]);
    }

    public function postEditAvatar() {
        $id = Request::input('id');
        $user = PanelController::GetUser($id);

        Validator::extend('valid_extension', function($attribute, $value, $parameters) {
            return in_array(strtolower($value->getClientOriginalExtension()), $parameters);
        });
        Validator::extend('image_size', function($attr, $value, $parameters) {
            $max = count($parameters) > 0 ? intval($parameters[0]) : 3000;
            $info = getimagesize($value->getPathName());
            return $info[0] <= $max && $info[1] <= $max;
        });

        $this->validate(Request::instance(), [
            'type' => 'in:upload,preset',
            'upload' => 'required_if:type,upload|valid_extension:jpeg,jpg,png|image_size:1000',
            'preset' => 'required_if:type,preset'
        ], [
            'valid_extension' => 'Only the following file formats are allowed: jpg, png',
            'image_size' => 'The image can\'t be larger than 1000px square. It will be resized to 100px anyway, please upload a smaller image!'
        ]);

        if (Request::input('type') == 'upload') {
            $upload = Request::file('upload');
            $slug = str_pad(strval(rand(0, 99999)), 5, '0', STR_PAD_LEFT);
            $uid = str_pad(strval($user->id), 5, '0', STR_PAD_LEFT);
            $name = $uid . '_' . $slug . '.' . strtolower($upload->getClientOriginalExtension());

            $user->deleteAvatar();

            $temp_dir = public_path('uploads/avatars/temp');
            $temp_name = $user->id . '_temp.' . strtolower($upload->getClientOriginalExtension());
            $upload->move($temp_dir, $temp_name);
            Image::MakeThumbnails($temp_dir . '/' . $temp_name, Image::$avatar_image_sizes, public_path('uploads/avatars/'), $name, true);
            unlink($temp_dir . '/' . $temp_name);

            $user->update([ 'avatar_custom' => true, 'avatar_file' => $name ]);
        } else {
            $preset = Request::input('preset');
            // Make sure it's in our list before doing anything
            foreach (PanelController::$preset_avatars as $group => $avatars) {
                if (array_search($preset, $avatars) !== false) {
                    $user->deleteAvatar();
                    $user->update([ 'avatar_custom' => false, 'avatar_file' => $preset ]);
                    break;
                }
            }
        }
        return redirect('panel/index/'.$id);
    }

    public function getEditKeys($id = 0) {
        $user = PanelController::GetUser($id);
        return view('user/panel/edit-keys', [
            'user' => $user
        ]);
    }

    public function postAddKey() {
        $id = Request::input('id');
        $user = PanelController::GetUser($id);

        $this->validate(Request::instance(), [
            'app' => 'required|max:255'
        ]);

        $key = ApiKey::create([
            'user_id' => $user->id,
            'key' => ApiKey::GenerateKey($id),
            'app' => Request::input('app'),
            'ip' => Request::ip(),
        ]);

        return redirect('panel/edit-keys/'.$id);
    }

    public function postDeleteKey() {
        $id = Request::input('id');
        $key = ApiKey::findOrFail($id);
        $user = PanelController::GetUser($key->user_id);
        $key->delete();
        return redirect('panel/edit-keys/'.$key->user_id);
    }

    public function getNotifications($id = 0) {
        $user = PanelController::GetUser($id);
        $notifications = UserNotificationDetails::whereUserId($user->id)->whereIsUnread(true)->get();
        $subscriptions = UserSubscriptionDetails::whereUserId($user->id)->whereIsOwnArticle(0)->get();
        return view('user/panel/notifications', [
            'user' => $user,
            'notifications' => $notifications,
            'subscriptions' => $subscriptions
        ]);
    }

    public function getClearNotifications($id = 0) {
        $user = PanelController::GetUser($id);
        DB::statement("UPDATE user_notifications SET is_unread = 0 WHERE user_id = ? AND is_unread = 1", [ $user->id ]);
        return redirect('panel/notifications/'.$id);
    }

    public function getDeleteSubscription($id) {
	    $sub = UserSubscription::findOrFail($id);
        $user = PanelController::GetUser($sub->user_id);
        $sub->delete();
        return redirect('panel/notifications/'.$user->id);
    }

    public function getEditName($id = 0) {
        $user = PanelController::GetUser($id);
        return view('user/panel/edit-name', [
            'user' => $user
        ]);
    }

    public function postEditName() {
        $id = Request::input('id');
        $user = PanelController::GetUser($id);

        $this->validate(Request::instance(), [
            'new_name' => 'required|max:255|unique:users,name,'.$user->id
        ]);

        $orig_name = $user->name;
        $user->update([ 'name' => Request::input('new_name') ]);
        UserNameHistory::create([
            'user_id' => $user->id,
            'name' => $orig_name
        ]);
        return redirect('panel/index/'.$id);
    }

    public function getEditBans($id = 0) {
        $user = PanelController::GetUser($id);
        $bans = Ban::whereUserId($id)->get();
        return view('user/panel/edit-bans', [
            'user' => $user,
            'bans' => $bans
        ]);
    }

    public function postAddBan() {
        $id = Request::input('id');
        $user = PanelController::GetUser($id);

        $this->validate(Request::instance(), [
            'reason' => 'required|max:255',
            'duration' => 'required|integer',
            'unit' => 'required|integer'
        ]);

        $hours = intval(Request::input('duration')) * intval(Request::input('unit'));
        $ban = Ban::create([
            'user_id' => $user->id,
            'ip' => (Request::input('ip_ban') && $user->last_access_ip ? $user->last_access_ip : null),
            'ends_at' => $hours < 0 ? null : Carbon::now()->addHours($hours),
            'reason' => Request::input('reason')
        ]);

        return redirect('panel/edit-bans/'.$id);
    }

    public function postDeleteBan() {
        $id = Request::input('id');
        $ban = Ban::findOrFail($id);
        $ban->delete();
        return redirect('panel/edit-bans/'.$ban->user_id);
    }

    public function getEditPermissions($id = 0) {
        $user = PanelController::GetUser($id);
        $permissions = UserPermission::with(['permission'])->whereUserId($id)->get();
        return view('user/panel/edit-permissions', [
            'user' => $user,
            'permissions' => $permissions
        ]);
    }

    public function postAddPermission() {
        $id = Request::input('id');
        $user = PanelController::GetUser($id);

        $this->validate(Request::instance(), [
            'permission_id' => 'required|integer'
        ]);

        $perm = UserPermission::create([
            'user_id' => $user->id,
            'permission_id' => Request::input('permission_id')
        ]);

        return redirect('panel/edit-permissions/'.$id);
    }

    public function postDeletePermission() {
        $id = Request::input('id');
        $perm = UserPermission::findOrFail($id);
        $perm->delete();
        return redirect('panel/edit-permissions/'.$perm->user_id);
    }

    public function getObliterate($id) {
        $user = PanelController::GetUser($id);
        return view('user/panel/obliterate', [
            'user' => $user
        ]);
    }

    public function postObliterate() {
        $id = Request::input('id');
        $user = PanelController::GetUser($id);

        $this->validate(Request::instance(), [
            'sure' => 'required|confirmed'
        ], [
            'required' => 'You must check both boxes if you want to obliterate this user.',
            'confirmed' => 'You must check both boxes if you want to obliterate this user.'
        ]);

        set_time_limit(500);
        $user->obliterate(User::DEFINITELY_OBLITERATE_THIS_USER);

        return redirect('/');
    }

    public function getRemove($id) {
        $user = PanelController::GetUser($id);
        return view('user/panel/remove', [
            'user' => $user
        ]);
    }

    public function postRemove() {
        $id = Request::input('id');
        $user = PanelController::GetUser($id);

        $this->validate(Request::instance(), [
            'sure' => 'required|confirmed'
        ], [
            'required' => 'You must check both boxes if you want to remove this user.',
            'confirmed' => 'You must check both boxes if you want to remove this user.'
        ]);

        set_time_limit(500);
        $user->remove(User::DEFINITELY_REMOVE_THIS_USER);

        return redirect('/user/view/' . $id);
    }

    private static $preset_avatars = [
        'Lambda Logo' => [
            'user_noavatar1.png',
            'user_noavatar2.png',
            'user_noavatar_alt1.png',
            'user_noavatar_alt2.png',
        ],
        'Half-Life' => [
            'hl_blueshift.jpg',
            'hl_opforce.jpg',
            'hl_tentacle.jpg',
            'hl_vortigaunt.jpg',
            'hl2_alyx.jpg',
            'hl2_combine1.jpg',
            'hl2_combine2.jpg',
            'hl2_freeman.jpg',
            'hl2_gman.jpg',
            'hl2_headcrab.jpg',
            'hl2_hunters.jpg',
            'hl2_metrocop.jpg',
        ],
        'Counter-Strike' => [
            'counterstrike1.jpg',
            'counterstrike_go.jpg',
            'counterstrike_s.jpg',
        ],
        'DOTA' => [
            'dota2_bloodseeker.jpg',
            'dota2_drowranger.jpg',
            'dota2_lina.jpg',
            'dota2_morphling.jpg',
        ],
        'Portal' => [
            'portal2_atlas.jpg',
            'portal2_chell.jpg',
            'portal2_cube.jpg',
            'portal2_glados.jpg',
            'portal2_pbody.jpg',
            'portal2_turrets.jpg',
            'portal2_wheatley.jpg',
        ],
        'Team Fortress' => [
            'tf2_demoman.jpg',
            'tf2_engineer.jpg',
            'tf2_heavy.jpg',
            'tf2_medic.jpg',
            'tf2_pyro.jpg',
            'tf2_scout.jpg',
            'tf2_sniper.jpg',
            'tf2_soldier.jpg',
            'tf2_spy.jpg',
        ],
        'Left 4 Dead' => [
            'l4d_bill.jpg',
            'l4d_francis.jpg',
            'l4d_louis.jpg',
            'l4d_zoey.jpg',
        ],
        'Day of Defeat' => [
            'dayofdefeat1.jpg',
            'dayofdefeat2.jpg',
        ],
        'Classic TWHL (Final Fantasy)' => [
            'classic_002.jpg',
            'classic_003.jpg',
            'classic_004.jpg',
            'classic_005.jpg',
            'classic_006.jpg',
            'classic_007.jpg',
            'classic_008.jpg',
            'classic_009.jpg',
            'classic_010.jpg',
            'classic_011.jpg',
            'classic_012.jpg',
            'classic_013.jpg',
            'classic_014.jpg',
            'classic_015.jpg',
            'classic_016.jpg',
            'classic_017.jpg',
            'classic_018.jpg',
            'classic_019.jpg',
            'classic_020.jpg',
            'classic_021.jpg',
            'classic_022.jpg',
            'classic_023.jpg',
            'classic_024.jpg',
            'classic_025.jpg',
            'classic_026.jpg',
        ],
        'Classic TWHL (Quake)' => [
            'classic_028.jpg',
            'classic_029.jpg',
            'classic_030.jpg',
        ],
        'Classic TWHL (Raichu)' => [
            'classic_027.jpg',
        ],
    ];
}

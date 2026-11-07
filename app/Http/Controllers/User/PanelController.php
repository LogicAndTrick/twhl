<?php

namespace App\Http\Controllers\User;

use App\Helpers\Image;
use App\Helpers\TypedRouting;
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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

#[TypedRouting]
class PanelController extends Controller
{
    public function __construct()
    {
        $this->permission([
            'index',
            'editAvatar',
            'editProfile',
            'editSettings',
            'editPassword',
            'editKeys',
            'addKey',
            'deleteKey',
            'notifications',
            'clearNotifications',
            'deleteSubscription'
        ], true);
        $this->permission(['editName', 'editBans', 'addBan', 'deleteBan', 'editPermissions', 'addPermission', 'deletePermission'], 'Admin');
        $this->permission(['obliterate', 'remove'], 'ObliterateAdmin');
    }

    private static function GetUser(int|null $id)
    {
        if (!Auth::check()) return abort(404);
        if (!permission('Admin') || !$id) $id = Auth::user()->id;
        return User::findOrFail($id);
    }

    public function getIndex(int|null $id = null)
    {
        $user = PanelController::GetUser($id);
        return view('user/panel/index', [
            'user' => $user
        ]);
    }

    public function getEditProfile(int|null $id = null)
    {
        $user = PanelController::GetUser($id);
        return view('user/panel/edit-profile', [
            'user' => $user
        ]);
    }

    public function postEditProfile(Request $request)
    {
        $id = $request->integer('id');
        $user = PanelController::GetUser($id);
        $this->validate($request, [
            'title_text' => ['required_with:title_custom'],
            'info_website' => ['url'],
            'info_birthday_formatted' => ['date_format:d/m'],
            'info_biography_text' => ['max:10000']
        ]);

        $steam_user = '';
        if (preg_match('%^(?:.*/)?(.*)$%m', $request->input('info_steam_profile'), $regs)) {
            $steam_user = $regs[1];
        }

        $user->info_birthday_formatted = $request->input('info_birthday_formatted');

        $user->update([
            'title_custom' => $request->boolean('title_custom'),
            'title_text' => $request->input('title_text'),

            'skill_map' => $request->boolean('skill_map'),
            'skill_model' => $request->boolean('skill_model'),
            'skill_code' => $request->boolean('skill_code'),
            'skill_music' => $request->boolean('skill_music'),
            'skill_voice' => $request->boolean('skill_voice'),
            'skill_animate' => $request->boolean('skill_animate'),
            'skill_texture' => $request->boolean('skill_texture'),

            'info_name' => $request->input('info_name'),
            'info_pronouns' => $request->input('info_pronouns'),
            'info_website' => $request->input('info_website'),
            'info_occupation' => $request->input('info_occupation'),
            'info_interests' => $request->input('info_interests'),
            'info_location' => $request->input('info_location'),
            'info_languages' => $request->input('info_languages'),
            'info_steam_profile' => $steam_user,

            'info_biography_text' => $request->input('info_biography_text'),
            'info_biography_html' => bbcode($request->input('info_biography_text'))
        ]);

        return redirect('panel/index/' . $id);
    }

    public function getEditSettings(int|null $id = null)
    {
        $user = PanelController::GetUser($id);

        // Generate time zones - we don't care too much about full zone support
        $zones = [];
        $now = new Carbon(null, 'UTC');
        for ($i = -12; $i <= 12; $i++) {
            $offset = $now->copy()->addHours($i);
            $zones[$i] = 'UTC' . ($i >= 0 ? '+' : '') . $i . ' - ' . $offset->format('H:i');
        }

        return view('user/panel/edit-settings', [
            'user' => $user,
            'zones' => $zones
        ]);
    }

    public function postEditSettings(Request $request)
    {
        $id = $request->integer('id');
        $user = PanelController::GetUser($id);
        $this->validate($request, [
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'timezone' => ['required', 'between:-12,12']
        ]);
        $user->update([
            'email' => $request->input('email'),
            'show_email' => $request->boolean('show_email'),
            'timezone' => $request->input('timezone')
        ]);
        return redirect('panel/index/' . $id);
    }

    public function getEditPassword(int|null $id = null)
    {
        $user = PanelController::GetUser($id);
        return view('user/panel/edit-password', [
            'user' => $user,
            'need_original' => $user->id == Auth::user()->id || !permission('Admin')
        ]);
    }

    public function postEditPassword(Request $request)
    {
        $id = $request->integer('id');
        $user = PanelController::GetUser($id);

        Validator::extend('matches_original', function ($attribute, $value, $parameters) use ($user) {
            return Hash::check($value, $user->password);
        });

        $rules = ['password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()]];
        // Admin users can reset another user's password without the original
        if ($user->id == Auth::user()->id || !permission('Admin')) $rules['original_password'] = ['required', 'matches_original'];

        $this->validate($request, $rules, [
            'matches_original' => 'This doesn\'t match your current password.'
        ]);

        $user->update([
            'password' => Hash::make($request->input('password')),
            'legacy_password' => ''
        ]);
        return redirect('panel/index/' . $id);
    }

    public function getEditAvatar(int|null $id = null)
    {
        $user = PanelController::GetUser($id);
        return view('user/panel/edit-avatar', [
            'user' => $user,
            'avatar_groups' => PanelController::$preset_avatars
        ]);
    }

    public function postEditAvatar(Request $request)
    {
        $id = $request->integer('id');
        $user = PanelController::GetUser($id);

        $this->validate($request, [
            'type' => ['in:upload,preset'],
            'upload' => ['required_if:type,upload', 'mimetypes:image/gif,image/jpeg,image/png', 'dimensions:max_width=1000,max_height=1000'],
            'preset' => ['required_if:type,preset']
        ], [
            'mimetypes' => 'Only the following file formats are allowed: gif, jpg, png',
            'dimensions' => 'The image can\'t be larger than 1000px square. It will be resized to 100px anyway, please upload a smaller image!'
        ]);

        if ($request->input('type') == 'upload') {
            $upload = $request->file('upload');
            $slug = str_pad(strval(rand(0, 99999)), 5, '0', STR_PAD_LEFT);
            $uid = str_pad(strval($user->id), 5, '0', STR_PAD_LEFT);
            $name = $uid . '_' . $slug;

            $user->deleteAvatar();

            $temp_dir = storage_path('app/uploads/avatars/temp');
            $temp_name = $user->id . '_temp';
            $upload->move($temp_dir, $temp_name);
            $thumbs = Image::MakeThumbnails($temp_dir . '/' . $temp_name, Image::$avatar_image_sizes, public_path('uploads/avatars/'), $name, true);
            unlink($temp_dir . '/' . $temp_name);

            $user->update(['avatar_custom' => true, 'avatar_file' => pathinfo($thumbs[0], PATHINFO_BASENAME)]);
        } else {
            $preset = $request->input('preset');
            // Make sure it's in our list before doing anything
            foreach (PanelController::$preset_avatars as $group => $avatars) {
                if (array_search($preset, $avatars) !== false) {
                    $user->deleteAvatar();
                    $user->update(['avatar_custom' => false, 'avatar_file' => $preset]);
                    break;
                }
            }
        }
        return redirect('panel/index/' . $id);
    }

    public function getEditKeys(int|null $id = null)
    {
        $user = PanelController::GetUser($id);
        return view('user/panel/edit-keys', [
            'user' => $user
        ]);
    }

    public function postAddKey(Request $request)
    {
        $id = $request->integer('id');
        $user = PanelController::GetUser($id);

        $this->validate($request, [
            'app' => ['required', 'max:255']
        ]);

        $key = ApiKey::create([
            'user_id' => $user->id,
            'key' => ApiKey::GenerateKey($user->id),
            'app' => $request->input('app'),
            'ip' => $request->ip(),
        ]);

        return redirect('panel/edit-keys/' . $user->id);
    }

    public function postDeleteKey(Request $request)
    {
        $id = $request->integer('id');
        $key = ApiKey::findOrFail($id);
        $user = PanelController::GetUser($key->user_id);
        if ($user->id !== $key->user_id) abort(404);
        $key->delete();
        return redirect('panel/edit-keys/' . $user->id);
    }

    public function getNotifications(int|null $id = null)
    {
        $user = PanelController::GetUser($id);
        $notifications = UserNotificationDetails::whereUserId($user->id)->whereIsUnread(true)->get();
        $subscriptions = UserSubscriptionDetails::whereUserId($user->id)->whereIsOwnArticle(0)->get();
        return view('user/panel/notifications', [
            'user' => $user,
            'notifications' => $notifications,
            'subscriptions' => $subscriptions
        ]);
    }

    public function postClearNotifications(Request $request)
    {
        $id = $request->integer('id');
        $user = PanelController::GetUser($id);
        DB::statement("UPDATE user_notifications SET is_unread = 0 WHERE user_id = ? AND is_unread = 1", [$user->id]);
        return redirect('panel/notifications/' . $id);
    }

    public function postDeleteSubscription(Request $request)
    {
        $id = $request->integer('id');
        $sub = UserSubscription::findOrFail($id);
        $user = PanelController::GetUser($sub->user_id);
        if ($user->id !== $sub->user_id) abort(404);
        $sub->delete();
        return redirect('panel/notifications/' . $user->id);
    }

    // ADMIN ACTIONS

    public function getEditName(int $id)
    {
        $user = PanelController::GetUser($id);
        return view('user/panel/edit-name', [
            'user' => $user
        ]);
    }

    public function postEditName(Request $request)
    {
        $id = $request->integer('id');
        $user = PanelController::GetUser($id);

        $this->validate($request, [
            'new_name' => [
                'required',
                'max:64',
                Rule::unique('users', 'name')->ignore($user->id),
                Rule::notRegex('/^\s|\s$/u'), // no leading or trailing whitespace
                Rule::notRegex('/[\p{C}\p{So}]|[\x{FE0F}\x{20E3}\x{1F3FB}-\x{1F3FF}]/u'), // no control characters, no emojis
                Rule::notRegex('/@/'), // no @ symbol
            ],
        ]);

        $orig_name = $user->name;
        $user->update(['name' => $request->input('new_name')]);
        UserNameHistory::create([
            'user_id' => $user->id,
            'name' => $orig_name
        ]);
        return redirect('panel/index/' . $id);
    }

    public function getEditBans(int $id)
    {
        $user = PanelController::GetUser($id);
        $bans = Ban::whereUserId($user->id)->get();
        return view('user/panel/edit-bans', [
            'user' => $user,
            'bans' => $bans
        ]);
    }

    public function postAddBan(Request $request)
    {
        $id = $request->integer('id');
        $user = PanelController::GetUser($id);

        $this->validate($request, [
            'reason' => ['required', 'max:255'],
            'duration' => ['required', 'integer'],
            'unit' => ['required', 'integer']
        ]);

        $hours = intval($request->input('duration')) * intval($request->input('unit'));
        $ban = Ban::create([
            'user_id' => $user->id,
            'ip' => ($request->input('ip_ban') && $user->last_access_ip ? $user->last_access_ip : null),
            'ends_at' => $hours < 0 ? null : Carbon::now()->addHours($hours),
            'reason' => $request->input('reason')
        ]);

        return redirect('panel/edit-bans/' . $id);
    }

    public function postDeleteBan(Request $request)
    {
        $id = $request->integer('id');
        $ban = Ban::findOrFail($id);
        $ban->delete();
        return redirect('panel/edit-bans/' . $ban->user_id);
    }

    public function getEditPermissions(int $id)
    {
        $user = PanelController::GetUser($id);
        $permissions = UserPermission::with(['permission'])->whereUserId($user->id)->get();
        return view('user/panel/edit-permissions', [
            'user' => $user,
            'permissions' => $permissions
        ]);
    }

    public function postAddPermission(Request $request)
    {
        $id = $request->integer('id');
        $user = PanelController::GetUser($id);

        $this->validate($request, [
            'permission_id' => ['required', 'integer']
        ]);

        $perm = UserPermission::create([
            'user_id' => $user->id,
            'permission_id' => $request->integer('permission_id')
        ]);

        return redirect('panel/edit-permissions/' . $id);
    }

    public function postDeletePermission(Request $request)
    {
        $id = $request->integer('id');
        $perm = UserPermission::findOrFail($id);
        $perm->delete();
        return redirect('panel/edit-permissions/' . $perm->user_id);
    }

    // OBLITERATE / REMOVE

    public function getObliterate(int $id)
    {
        $user = PanelController::GetUser($id);
        return view('user/panel/obliterate', [
            'user' => $user
        ]);
    }

    public function postObliterate(Request $request)
    {
        $id = $request->integer('id');
        $user = PanelController::GetUser($id);

        $this->validate($request, [
            'sure' => ['required', 'confirmed']
        ], [
            'required' => 'You must check both boxes if you want to obliterate this user.',
            'confirmed' => 'You must check both boxes if you want to obliterate this user.'
        ]);

        set_time_limit(500);
        $user->obliterate(User::DEFINITELY_OBLITERATE_THIS_USER);

        return redirect('/');
    }

    public function getRemove(int $id)
    {
        $user = PanelController::GetUser($id);
        return view('user/panel/remove', [
            'user' => $user
        ]);
    }

    public function postRemove(Request $request)
    {
        $id = $request->integer('id');
        $user = PanelController::GetUser($id);

        $this->validate($request, [
            'sure' => ['required', 'confirmed']
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

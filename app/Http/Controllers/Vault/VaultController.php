<?php namespace App\Http\Controllers\Vault;

use App\Helpers\Image;
use App\Http\Controllers\Controller;
use App\Models\Forums\Forum;
use App\Models\Forums\ForumThread;
use App\Models\Vault\VaultInclude;
use App\Models\Vault\VaultItem;
use App\Models\Vault\VaultItemInclude;
use App\Models\Vault\VaultScreenshot;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Validator;
use Request;
use Input;
use Auth;

class VaultController extends Controller {

	public function __construct() {
        $this->permission(['create', 'edit', 'delete', 'createscreenshot', 'savescreenshotorder', 'deletescreenshot', 'editscreenshots'], 'VaultCreate');
        $this->permission(['restore'], 'VaultAdmin');
	}

	public function getIndex() {
        $item_query = VaultItem::with(['vault_screenshots', 'user', 'game']);

        $search = trim(Request::get('search'));
        if ($search) $item_query = $item_query->where('name', 'like', "%$search%");

        $games = array_filter(explode('-', Request::get('games')), function($x) { return is_numeric($x); });
        if (count($games) > 0) $item_query = $item_query->whereIn('game_id', $games);

        $cats = array_filter(explode('-', Request::get('cats')), function($x) { return is_numeric($x); });
        if (count($cats) > 0) $item_query = $item_query->whereIn('category_id', $cats);

        $incs = array_filter(explode('-', Request::get('incs')), function($x) { return is_numeric($x); });
        if (count($incs) > 0) {
            $qs = implode(', ', array_map(function() { return '?'; }, $incs));
            $incs[] = count($incs);
            $item_query = $item_query->whereRaw("(select count(*) from vault_item_includes as i where i.item_id = vault_items.id and i.include_id in ($qs)) >= ?", $incs);
        }

        $rating = Request::get('rate');
        if (is_numeric($rating)) $item_query = $item_query->where('stat_average_rating', '>=', $rating);

        $sort = Request::get('sort');
        $allowed_sort = ['date', 'rating', 'num_ratings', 'num_views', 'num_downloads'];
        $mapped_sort = ['created_at', 'stat_average_rating', 'stat_ratings', 'stat_views', 'stat_downloads'];
        $search_sort = array_search($sort, $allowed_sort);
        if (!$search_sort) $search_sort = 0;
        $item_query = $item_query->orderBy($mapped_sort[$search_sort], Request::get('asc') == 'true' ? 'asc' : 'desc');

        $items = $item_query->paginate(32);
        return view('vault/list', [
            'items' => $items->appends(Request::except('page'))
        ]);
	}

    public function getView($id) {
        $item = VaultItem::with(['user', 'game', 'license', 'vault_screenshots', 'vault_includes', 'vault_category', 'vault_type'])->findOrFail($id);
        return view('vault/view', [
            'item' => $item
        ]);
    }

    // Create / edit

    public function getCreate() {
        $includes = VaultInclude::all();
        return view('vault/create', [
            'includes' => $includes
        ]);
    }

    private function makeScreenshot($item, $screen) {
        // We need the id to save the files, so create the db object first
        $shot = VaultScreenshot::Create([
            'item_id' => $item->id,
            'is_primary' => count($item->vault_screenshots) == 0,
            'image_thumb' => '',
            'image_small' => '',
            'image_medium' => '',
            'image_large' => '',
            'image_full' => '',
            'image_size' => 0,
            'order_index' => count($item->vault_screenshots)
        ]);

        // Save the screenshot at various sizes
        $temp_dir = public_path('uploads/vault/temp');
        $temp_name = $shot->id . '_temp.' . $screen->getClientOriginalExtension();
        $screen->move($temp_dir, $temp_name);
        $thumbs = Image::MakeThumbnails(
            $temp_dir . '/' . $temp_name, Image::$vault_image_sizes,
            public_path('uploads/vault/'), $shot->id . '.' . $screen->getClientOriginalExtension()
        );
        unlink($temp_dir . '/' . $temp_name);

        // Update the shot object
        $shot->update([
            'image_thumb' => $thumbs[0] ? $thumbs[0] : $thumbs[4],
            'image_small' => $thumbs[1] ? $thumbs[1] : $thumbs[4],
            'image_medium' => $thumbs[2] ? $thumbs[2] : $thumbs[4],
            'image_large' => $thumbs[3] ? $thumbs[3] : $thumbs[4],
            'image_full' => $thumbs[4],
            'image_size' => filesize(public_path('uploads/vault/'.$thumbs[4]))
        ]);

        return $shot;
    }

    public function postCreate() {
        $func = function($attribute, $value, $parameters) {
            return in_array($value->getClientOriginalExtension(), $parameters);
        };
        Validator::extend('valid_extension_file', $func);
        Validator::extend('valid_extension_screen', $func);
        $this->validate(Request::instance(), [
            'engine_id' => 'required',
            'game_id' => 'required',
            'category_id' => 'required',
            'type_id' => 'required',
            // 'license_id' => 'required', // Default to license 1 = none
            'item_name' => 'required|max:120',
            'screen' => 'max:2048|valid_extension_screen:jpeg,jpg,png',
            'content_text' => 'required|max:10000',

            '__upload_method' => 'required|in:file,link',
            'link' => 'required_if:__upload_method,link|max:512',
            'file' => 'required_if:__upload_method,file|max:16384|valid_extension_file:zip,rar,7z'
        ], [
            'valid_extension_file' => 'Only the following file formats are allowed: zip, rar, 7z',
            'valid_extension_screen' => 'Only the following file formats are allowed: jpg, png',
        ]);

        $uploaded = Request::input('__upload_method') == 'file';
        $location = Request::input('link');
        if (!$location) $location = '';
        $size = -1;

        $item = VaultItem::Create([
            'user_id' => Auth::user()->id,
            'engine_id' => Request::input('engine_id'),
            'game_id' => Request::input('game_id'),
            'category_id' => Request::input('category_id'),
            'type_id' => Request::input('type_id'),
            'license_id' => Request::input('license_id') || 1,
            'name' => Request::input('item_name'),

            'content_text' => Request::input('content_text'),
            'content_html' => app('bbcode')->Parse(Request::input('content_text')),

            'is_hosted_externally' => !$uploaded,
            'file_location' => $location,
            'file_size' => $size,

            'flag_notify' => !!Request::input('flag_notify'),
            'flag_ratings' => !!Request::input('flag_ratings'),

            'stat_views' => 0,
            'stat_downloads' => 0,
            'stat_ratings' => 0,
            'stat_comments' => 0,
            'stat_average_rating' => 0
        ]);

        // Set included files
        $includes = Request::input('__includes');
        if (is_array($includes)) {
            $incs = [];
            foreach ($includes as $i) {
                $incs[] = new VaultItemInclude(['include_id' => $i]);
            }
            $item->vault_item_includes()->saveMany($incs);
        }

        // Upload the map file
        if ($uploaded) {
            $file = Request::file('file');

            $dir = public_path('uploads/vault/items');
            $name = 'twhl-vault-' . $item->id . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $name);

            $file_name = $dir . '/' . $name;
            $size = filesize($file_name);

            $item->file_location = $name;
            $item->file_size = $size;
            $item->save();
        }

        // Upload the screenshot
        $screen = Request::file('screen');
        if ($screen) {
            $this->makeScreenshot($item, $screen);
        }

        return redirect('vault/index');
    }

    public function getEdit($id) {
        $item = VaultItem::with(['vault_screenshots', 'vault_includes'])->findOrFail($id);
        return view('vault/edit', [

        ]);
    }

    public function postEdit() {

    }

    public function postCreateScreenshot() {
        $id = Request::input('id');
        $item = VaultItem::findOrFail($id);
        if (!$item->isEditable()) abort(422);

        Validator::extend('valid_extension', function($attribute, $value, $parameters) {
            return in_array($value->getClientOriginalExtension(), $parameters);
        });
        $this->validate(Request::instance(), [
            'file' => 'required|max:2048|valid_extension:jpeg,jpg,png'
        ], [
            'valid_extension' => 'Only the following file formats are allowed: jpg, png',
        ]);

        $file = Request::file('file');
        $screen = $this->makeScreenshot($item, $file);
        return response()->json($screen);
    }

    public function postSaveScreenshotOrder($id) {
        $item = VaultItem::findOrFail($id);
        if (!$item->isEditable()) abort(422);

        $ids = Request::input('ids');
        $screenshots = VaultScreenshot::where('item_id', '=', $id)->whereIn('id', $ids)->get();
        if (count($screenshots) != count($item->vault_screenshots))  abort(422);
        foreach ($screenshots as $shot) {
            $shot->order_index = array_search($shot->id, $ids);
            $shot->is_primary = $shot->order_index == 0;
            $shot->save();
        }
        return response()->json(['success' => true]);
    }

    public function postDeleteScreenshot() {
        $shot = VaultScreenshot::findOrFail(Request::input('id'));
        $item = VaultItem::findOrFail($shot->item_id);
        if (!$item->isEditable()) abort(422);

        $shot->delete();
        return response()->json(['success' => true]);
    }

    public function getEditScreenshots($id) {
        $item = VaultItem::findOrFail($id);
        return view('vault/edit-screenshots', [
            'item' => $item
        ]);
    }

    // Administrative Tasks

    public function getDelete($id) {
        return view('vault/delete', [

        ]);
    }

    public function postDelete() {

    }

    public function getRestore($id) {
        return view('vault/restore', [

        ]);
    }

    public function postRestore() {

    }
}

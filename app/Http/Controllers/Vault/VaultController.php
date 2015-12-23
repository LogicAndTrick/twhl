<?php namespace App\Http\Controllers\Vault;

use App\Helpers\Image;
use App\Http\Controllers\Controller;
use App\Models\Comments\Comment;
use App\Models\Vault\VaultInclude;
use App\Models\Vault\VaultItem;
use App\Models\Vault\VaultItemInclude;
use App\Models\Vault\VaultItemReview;
use App\Models\Vault\VaultScreenshot;
use Illuminate\Support\Facades\Validator;
use Request;
use Input;
use Auth;
use DB;

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

        $types = array_filter(explode('-', Request::get('types')), function($x) { return is_numeric($x); });
        if (count($types) > 0) $item_query = $item_query->whereIn('type_id', $types);

        $incs = array_filter(explode('-', Request::get('incs')), function($x) { return is_numeric($x); });
        if (count($incs) > 0) {
            $qs = implode(', ', array_map(function() { return '?'; }, $incs));
            $incs[] = count($incs);
            $item_query = $item_query->whereRaw("(select count(*) from vault_item_includes as i where i.item_id = vault_items.id and i.include_id in ($qs)) >= ?", $incs);
        }

        $rating = Request::get('rate');
        if (is_numeric($rating)) $item_query = $item_query->where('stat_average_rating', '>=', $rating);

        $users = array_filter(explode('-', Request::get('users')), function($x) { return is_numeric($x); });
        if (count($users) > 0) $item_query = $item_query->whereIn('user_id', $users);

        $sort = Request::get('sort');
        $allowed_sort = ['date', 'update', 'rating', 'num_ratings', 'num_views', 'num_downloads'];
        $mapped_sort = ['created_at', 'updated_at', 'stat_average_rating', 'stat_ratings', 'stat_views', 'stat_downloads'];
        $search_sort = array_search($sort, $allowed_sort);
        if (!$search_sort) $search_sort = 0;
        $item_query = $item_query->orderBy($mapped_sort[$search_sort], Request::get('asc') == 'true' ? 'asc' : 'desc');

        $items = $item_query->paginate(24);
        return view('vault/list', [
            'items' => $items->appends(Request::except('page')),
            'filtering' => strlen($search) > 0 || count($games) > 0 || count($cats) > 0 || count($types) > 0 || count($incs) > 0 || is_numeric($rating) || count($users) > 0 || $search_sort > 0,
            'fluid' => true
        ]);
	}

    public function getView($id) {
        $item = VaultItem::with(['user', 'game', 'engine', 'license', 'vault_item_reviews', 'vault_screenshots', 'vault_includes', 'vault_category', 'vault_type'])->findOrFail($id);
        $item->timestamps = false;
        $item->stat_views++;
        $item->save();

        $review = null;
        if (Auth::user()) $review = $item->vault_item_reviews->where('user_id', Auth::user()->id)->first();

        $comments = Comment::with(['comment_metas', 'user'])->whereArticleType(Comment::VAULT)->whereArticleId($id)->get();
        return view('vault/view', [
            'item' => $item,
            'comments' => $comments,
            'user_review' => $review
        ]);
    }

    public function getDownload($id) {
        $item = VaultItem::findOrFail($id);
        $item->timestamps = false;
        $item->stat_downloads++;
        $item->save();
        return redirect($item->getDownloadUrl());
    }

    public function getEmbed($id) {
        $item = VaultItem::with(['vault_screenshots', 'user'])->findOrFail($id);

        return response()->json($item);
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
        $temp_name = $shot->id . '_temp.' . strtolower($screen->getClientOriginalExtension());
        $screen->move($temp_dir, $temp_name);
        $thumbs = Image::MakeThumbnails(
            $temp_dir . '/' . $temp_name, Image::$vault_image_sizes,
            public_path('uploads/vault/'), $shot->id . '.' . strtolower($screen->getClientOriginalExtension()), true
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
            return in_array(strtolower($value->getClientOriginalExtension()), $parameters);
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
            'license_id' => Request::input('license_id') ? Request::input('license_id') : 1,
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
            $name = 'twhl-vault-' . $item->id . '.' . strtolower($file->getClientOriginalExtension());
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

        return redirect('vault/view/'.$item->id);
    }

    public function getEdit($id) {
        $item = VaultItem::with(['vault_screenshots', 'vault_includes'])->findOrFail($id);
        if (!$item->isEditable()) abort(404);
        $includes = VaultInclude::all();

        $type_id = Request::old('type_id');
        if (!$type_id) $type_id = $item->type_id;
        $content = Request::old('content_text');
        if (!$content) $content = $item->content_text;
        $method = Request::old('__upload_method');
        if (!$method) $method = $item->is_hosted_externally ? 'link' : 'file';
        $included = Request::old('__includes');
        if (!is_array($included)) $included = $item->vault_includes->map(function($x) { return $x->id; })->toArray();
        $location = Request::old($method);
        if (!$location && $method == 'link') $location = $item->file_location;


        return view('vault/edit', [
            'item' => $item,
            'includes' => $includes,
            'type_id' => $type_id,
            '__upload_method' => $method,
            '__includes' => $included,
            'location' => $location,
            'content' => $content
        ]);
    }

    public function postEdit() {
        $item = VaultItem::with(['vault_screenshots', 'vault_includes'])->findOrFail(Request::input('id'));
        if (!$item->isEditable()) abort(404);

        Validator::extend('valid_extension', function($attribute, $value, $parameters) {
            return in_array(strtolower($value->getClientOriginalExtension()), $parameters);
        });
        $this->validate(Request::instance(), [
            'engine_id' => 'required',
            'game_id' => 'required',
            'category_id' => 'required',
            'type_id' => 'required',
            // 'license_id' => 'required', // Default to license 1 = none
            'item_name' => 'required|max:120',
            'content_text' => 'required|max:10000',

            '__upload_method' => 'required|in:file,link',
            'link' => 'required_if:__upload_method,link|max:512',
            'file' => 'required_if:__upload_method,file|max:16384|valid_extension:zip,rar,7z'
        ], [
            'valid_extension' => 'Only the following file formats are allowed: zip, rar, 7z'
        ]);

        $uploaded = Request::input('__upload_method') == 'file';
        $location = Request::input('link');
        if (!$location) $location = '';
        $size = -1;

        // Upload the map file
        if ($uploaded) {
            $file = Request::file('file');

            $dir = public_path('uploads/vault/items');
            $name = 'twhl-vault-' . $item->id . '.' . strtolower($file->getClientOriginalExtension());
            $file->move($dir, $name);

            $file_name = $dir . '/' . $name;
            $size = filesize($file_name);

            $item->file_location = $name;
            $item->file_size = $size;
            $item->save();
        } else {
            $item->file_location = $location;
            $item->file_size = $size;
        }

        $item->engine_id = Request::input('engine_id');
        $item->game_id = Request::input('game_id');
        $item->category_id = Request::input('category_id');
        $item->type_id = Request::input('type_id');
        $item->license_id = Request::input('license_id') ? Request::input('license_id') : 1;
        $item->name = Request::input('item_name');

        $item->content_text = Request::input('content_text');
        $item->content_html = app('bbcode')->Parse(Request::input('content_text'));

        $item->is_hosted_externally = !$uploaded;

        $item->flag_notify = !!Request::input('flag_notify');
        $item->flag_ratings = !!Request::input('flag_ratings');

        $item->save();

        // Set included files
        DB::statement('delete from vault_item_includes where item_id = ?', [$item->id]);
        $includes = Request::input('__includes');
        if (is_array($includes)) {
            $incs = [];
            foreach ($includes as $i) {
                $incs[] = new VaultItemInclude(['include_id' => $i]);
            }
            $item->vault_item_includes()->saveMany($incs);
        }

        return redirect('vault/view/'.$item->id);
    }

    public function postCreateScreenshot() {
        $id = Request::input('id');
        $item = VaultItem::findOrFail($id);
        if (!$item->isEditable()) abort(422);

        Validator::extend('valid_extension', function($attribute, $value, $parameters) {
            return in_array(strtolower($value->getClientOriginalExtension()), $parameters);
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
        if ($shot->is_primary) {
            $screenshots = VaultScreenshot::where('item_id', '=', $shot->item_id)->get();
            $idx = 0;
            foreach ($screenshots as $shot) {
                $shot->order_index = $idx++;
                $shot->is_primary = $shot->order_index == 0;
                $shot->save();
            }
        }
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
        $item = VaultItem::findOrFail($id);
        if (!$item->isEditable()) abort(404);

        return view('vault/delete', [
            'item' => $item
        ]);
    }

    public function postDelete() {
        $item = VaultItem::findOrFail(Request::input('id'));
        if (!$item->isEditable()) abort(404);
        $item->delete();
        return redirect('vault/index');
    }

    public function getRestore($id) {
        $item = VaultItem::onlyTrashed()->findOrFail($id);
        return view('vault/restore', [
            'item' => $item
        ]);
    }

    public function postRestore() {
        $item = VaultItem::onlyTrashed()->findOrFail(Request::input('id'));
        if (!$item->isEditable()) abort(404);
        $item->restore();
        return redirect('vault/view/'.$item->id);
    }
}

<?php

namespace App\Http\Controllers\Vault;

use App\Helpers\Image;
use App\Helpers\TypedRouting;
use App\Http\Controllers\Controller;
use App\Models\Accounts\UserSubscription;
use App\Models\Comments\Comment;
use App\Models\Vault\VaultInclude;
use App\Models\Vault\VaultItem;
use App\Models\Vault\VaultItemInclude;
use App\Models\Vault\VaultScreenshot;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

#[TypedRouting]
class VaultController extends Controller
{

    public function __construct()
    {
        $this->anonymous(['index', 'view', 'download', 'embed']);
        $this->permission(['create', 'edit', 'delete', 'createScreenshot', 'saveScreenshotOrder', 'deleteScreenshot', 'editScreenshots'], 'VaultCreate');
        $this->permission(['restore'], 'VaultAdmin');
    }

    public function getIndex(Request $request)
    {
        $item_query = VaultItem::with(['vault_screenshots', 'user', 'game']);

        $search = trim($request->input('search'));
        if ($search) $item_query = $item_query->where('name', 'like', "%$search%");

        $games = array_filter(explode('-', $request->input('games')), function ($x) {
            return is_numeric($x);
        });
        if (count($games) > 0) $item_query = $item_query->whereIn('game_id', $games);

        $cats = array_filter(explode('-', $request->input('cats')), function ($x) {
            return is_numeric($x);
        });
        if (count($cats) > 0) $item_query = $item_query->whereIn('category_id', $cats);

        $types = array_filter(explode('-', $request->input('types')), function ($x) {
            return is_numeric($x);
        });
        if (count($types) > 0) $item_query = $item_query->whereIn('type_id', $types);

        $incs = array_filter(explode('-', $request->input('incs')), function ($x) {
            return is_numeric($x);
        });
        if (count($incs) > 0) {
            $qs = implode(', ', array_map(function () {
                return '?';
            }, $incs));
            $incs[] = count($incs);
            $item_query = $item_query->whereRaw("(select count(*) from vault_item_includes as i where i.item_id = vault_items.id and i.include_id in ($qs)) >= ?", $incs);
        }

        $rating = $request->input('rate');
        if (is_numeric($rating)) $item_query = $item_query->where('stat_average_rating', '>=', $rating);

        $users = array_filter(explode('-', $request->input('users')), function ($x) {
            return is_numeric($x);
        });
        if (count($users) > 0) $item_query = $item_query->whereIn('user_id', $users);

        $sort = $request->input('sort');
        $allowed_sort = ['date', 'update', 'rating', 'num_ratings', 'num_views', 'num_downloads'];
        $mapped_sort = ['created_at', 'updated_at', 'stat_average_rating', 'stat_ratings', 'stat_views', 'stat_downloads'];
        $search_sort = array_search($sort, $allowed_sort);
        if (!$search_sort) $search_sort = 0;
        $item_query = $item_query->orderBy($mapped_sort[$search_sort], $request->input('asc') == 'true' ? 'asc' : 'desc');

        $items = $item_query->paginate(24);
        return view('vault/list', [
            'items' => $items->appends($request->except('page')),
            'filtering' => strlen($search) > 0 || count($games) > 0 || count($cats) > 0 || count($types) > 0 || count($incs) > 0 || is_numeric($rating) || count($users) > 0 || $search_sort > 0
        ]);
    }

    public function getView(int $id)
    {
        $item = VaultItem::with(['user', 'game', 'engine', 'license', 'vault_item_reviews', 'vault_screenshots', 'vault_includes', 'vault_category', 'vault_type', 'motms'])->findOrFail($id);
        VaultItem::withoutTimestamps(fn() => $item->increment('stat_views'));

        $review = null;
        if (Auth::user()) $review = $item->vault_item_reviews->where('user_id', Auth::user()->id)->first();

        $comments = Comment::with(['comment_metas', 'user'])->whereArticleType(Comment::VAULT)->whereArticleId($id)->get();
        return view('vault/view', [
            'item' => $item,
            'comments' => $comments,
            'subscription' => Comment::getSubscription(Auth::user(), Comment::VAULT, $id, true),
            'user_review' => $review
        ]);
    }

    public function getDownload(int $id)
    {
        $item = VaultItem::findOrFail($id);
        VaultItem::withoutTimestamps(fn() => $item->increment('stat_downloads'));
        return redirect($item->getDownloadUrl());
    }

    public function getEmbed(int $id)
    {
        $item = VaultItem::with(['vault_screenshots', 'user'])->findOrFail($id);
        return response()->json($item);
    }

    // Create / edit

    public function getCreate()
    {
        $includes = VaultInclude::all();
        return view('vault/create', [
            'includes' => $includes
        ]);
    }

    private function makeScreenshot(VaultItem $item, UploadedFile $screen)
    {
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
        $temp_dir = storage_path('app/uploads/vault/temp');
        if (!is_dir($temp_dir)) mkdir($temp_dir, 0755, true);
        $temp_name = $shot->id . '_temp.' . strtolower($screen->getClientOriginalExtension());
        $screen->move($temp_dir, $temp_name);
        $thumbs = Image::MakeThumbnails(
            $temp_dir . '/' . $temp_name,
            Image::$vault_image_sizes,
            public_path('uploads/vault/'),
            strval($shot->id),
            true
        );
        unlink($temp_dir . '/' . $temp_name);

        // Update the shot object
        $shot->update([
            'image_thumb' => $thumbs[0] ? $thumbs[0] : $thumbs[4],
            'image_small' => $thumbs[1] ? $thumbs[1] : $thumbs[4],
            'image_medium' => $thumbs[2] ? $thumbs[2] : $thumbs[4],
            'image_large' => $thumbs[3] ? $thumbs[3] : $thumbs[4],
            'image_full' => $thumbs[4],
            'image_size' => filesize(public_path('uploads/vault/' . $thumbs[4]))
        ]);

        return $shot;
    }

    public function postCreate(Request $request)
    {
        $this->validate($request->instance(), [
            'engine_id' => ['required', 'integer'],
            'game_id' => ['required', 'integer'],
            'category_id' => ['required', 'integer'],
            'type_id' => ['required', 'integer'],
            // 'license_id' => 'required', // Default to license 1 = none
            'item_name' => ['required', 'max:120'],
            'screen' => [
                'max:2048',
                'extensions:jpeg,jpg,png',
                'mimetypes:image/jpeg,image/png',
                Rule::dimensions()->maxWidth(3000)->maxHeight(3000)
            ],
            'content_text' => ['required', 'max:10000'],
            '__upload_method' => [
                'required',
                Rule::in(['file', 'link'])
            ],
            'link' => [
                Rule::requiredIf($request->input('__upload_method') == 'link'),
                'max:512',
                Rule::url()->protocols(['http', 'https'])
            ],
            'file' => [
                Rule::requiredIf($request->input('__upload_method') == 'file'),
                'max:65536',
                'extensions:zip,rar,7z',
                'mimetypes:application/zip,application/x-zip-compressed,application/x-rar-compressed,application/vnd.rar,application/x-7z-compressed'
            ]
        ], [
            'file.extensions' => 'Only the following file formats are allowed: zip, rar, 7z',
            'screen.extensions' => 'Only the following file formats are allowed: jpg, png',
            'dimensions' => 'The image cannot have a width or height of more than 3000 pixels'
        ]);

        $uploaded = $request->input('__upload_method') == 'file';
        $location = $request->input('link');
        if (!$location) $location = '';
        $size = -1;

        $item = VaultItem::Create([
            'user_id' => Auth::user()->id,
            'engine_id' => $request->integer('engine_id'),
            'game_id' => $request->integer('game_id'),
            'category_id' => $request->integer('category_id'),
            'type_id' => $request->integer('type_id'),
            'license_id' => $request->integer('license_id') ?: 1,
            'name' => $request->input('item_name'),

            'content_text' => $request->input('content_text'),
            'content_html' => bbcode($request->input('content_text')),

            'is_hosted_externally' => !$uploaded,
            'file_location' => $location,
            'file_size' => $size,

            'flag_notify' => !!$request->input('flag_notify'),
            'flag_ratings' => !!$request->input('flag_ratings'),

            'stat_views' => 0,
            'stat_downloads' => 0,
            'stat_ratings' => 0,
            'stat_comments' => 0,
            'stat_average_rating' => 0,

            'version' => 1
        ]);

        // Set included files
        $includes = $request->input('__includes');
        if (is_array($includes)) {
            $includes = array_unique($includes);
            $incs = [];
            foreach ($includes as $i) {
                $incs[] = new VaultItemInclude(['include_id' => $i]);
            }
            $item->vault_item_includes()->saveMany($incs);
        }

        // Upload the map file
        if ($uploaded) {
            $file = $request->file('file');

            $name = 'twhl-vault-' . $item->id . '.' . strtolower($file->getClientOriginalExtension());
            Storage::cloud()->putFileAs('uploads/vault/items', $file, $name, 'public');

            $item->file_location = $name;
            $item->file_size = $file->getSize();
            $item->save();
        }

        // Upload the screenshot
        $screen = $request->file('screen');
        if ($screen) {
            $this->makeScreenshot($item, $screen);
        }

        // Subscribe to the item
        $sub = UserSubscription::Create([
            'user_id' => Auth::user()->id,
            'article_type' => UserSubscription::VAULT_ITEM,
            'article_id' => $item->id,
            'send_email' => true,
            'send_push_notification' => false,
            'is_own_article' => true
        ]);

        return redirect('vault/view/' . $item->id);
    }

    public function getEdit(Request $request, int $id)
    {
        $item = VaultItem::with(['vault_screenshots', 'vault_includes', 'vault_category', 'license'])->findOrFail($id);
        if (!$item->isEditable()) abort(404);
        $includes = VaultInclude::all();

        $type_id = $request->old('type_id');
        if (!$type_id) $type_id = $item->type_id;
        $content = $request->old('content_text');
        if (!$content) $content = $item->content_text;
        $method = $request->old('__upload_method');
        if (!$method) $method = $item->is_hosted_externally ? 'link' : 'file';
        $included = $request->old('__includes');
        if (!is_array($included)) $included = $item->vault_includes->map(function ($x) {
            return $x->id;
        })->toArray();
        $location = $request->old($method);
        if (!$location && $method == 'link') $location = $item->file_location;

        return view('vault/edit', [
            'item' => $item,
            'includes' => $includes,
            'type_id' => $type_id,
            '__upload_method' => $method,
            '__includes' => $included,
            'location' => $location,
            'content' => $content,
            'has_upload' => !$item->is_hosted_externally
        ]);
    }

    public function postEdit(Request $request)
    {
        $item = VaultItem::with(['vault_screenshots', 'vault_includes'])->findOrFail($request->input('id'));
        if (!$item->isEditable()) abort(404);

        $this->validate($request->instance(), [
            'engine_id' => ['required', 'integer'],
            'game_id' => ['required', 'integer'],
            'category_id' => ['required', 'integer'],
            'type_id' => ['required', 'integer'],
            // 'license_id' => 'required', // Default to license 1 = none
            'item_name' => ['required', 'max:120'],
            'content_text' => ['required', 'max:10000'],
            '__upload_method' => [
                'required',
                'in:file,link'
            ],
            'link' => [
                Rule::requiredIf($request->input('__upload_method') == 'link'),
                'max:512',
                Rule::url()->protocols(['http', 'https'])
            ],
            'link_broken' => [
                Rule::prohibitedIf($request->input('__upload_method') != 'link')
            ],
            'file' => [
                'max:65536',
                'extensions:zip,rar,7z',
                'mimetypes:application/zip,application/x-zip-compressed,application/x-rar-compressed,application/vnd.rar,application/x-7z-compressed'
            ]
        ], [
            'file.extensions' => 'Only the following file formats are allowed: zip, rar, 7z'
        ]);

        $uploaded = $request->input('__upload_method') == 'file';
        $location = $request->input('link');
        if (!$location) $location = '';
        $size = -1;

        // Don't update the timestamps if it's not the author making the change
        if ($item->user_id != Auth::user()->id) {
            $item->timestamps = false;
        }

        // Upload the map file
        if ($uploaded) {
            $file = $request->file('file');

            if ($file) {
                $name = 'twhl-vault-' . $item->id . '.' . strtolower($file->getClientOriginalExtension());
                Storage::cloud()->putFileAs('uploads/vault/items', $file, $name, 'public');

                $item->file_location = $name;
                $item->file_size = $file->getSize();
                $item->save();
            }
        } else {
            $item->file_location = $location;
            $item->file_size = $size;
        }

        $item->engine_id = $request->integer('engine_id');
        $item->game_id = $request->integer('game_id');
        $item->category_id = $request->integer('category_id');
        $item->type_id = $request->integer('type_id');
        $item->license_id = $request->integer('license_id') ?: 1;
        $item->name = $request->input('item_name');

        $item->content_text = $request->input('content_text');
        $item->content_html = bbcode($request->input('content_text'));

        $item->is_hosted_externally = !$uploaded;
        $item->link_broken = !!$request->input('link_broken');

        $item->flag_notify = !!$request->input('flag_notify');
        $item->flag_ratings = !!$request->input('flag_ratings');
        $item->version++;

        $item->save();

        // Set included files
        DB::statement('delete from vault_item_includes where item_id = ?', [$item->id]);
        $includes = $request->input('__includes');
        if (is_array($includes)) {
            $includes = array_unique($includes);
            $incs = [];
            foreach ($includes as $i) {
                $incs[] = new VaultItemInclude(['include_id' => $i]);
            }
            $item->vault_item_includes()->saveMany($incs);
        }

        return redirect('vault/view/' . $item->id);
    }

    public function postCreateScreenshot(Request $request)
    {
        $id = $request->integer('id');
        $item = VaultItem::findOrFail($id);
        if (!$item->isEditable()) abort(422);

        Validator::extend('image_limit', function ($attr, $value, $parameters) use ($item) {
            return $item->vault_screenshots->count() < 25;
        });
        $this->validate($request->instance(), [
            'file' => [
                'required',
                'max:2048',
                'extensions:jpeg,jpg,png',
                'mimetypes:image/jpeg,image/png',
                Rule::dimensions()->maxWidth(3000)->maxHeight(3000),
                'image_limit'
            ]
        ], [
            'extensions' => 'Only the following file formats are allowed: jpg, png',
            'dimensions' => 'The image cannot have a width or height of more than 3000 pixels',
            'image_limit' => 'You can\'t add more than 25 screenshots to a vault item'
        ]);

        $file = $request->file('file');
        $screen = $this->makeScreenshot($item, $file);
        return response()->json($screen);
    }

    public function postSaveScreenshotOrder(Request $request, int $id)
    {
        $item = VaultItem::findOrFail($id);
        if (!$item->isEditable()) abort(422);

        $ids = $request->input('ids');
        $screenshots = VaultScreenshot::where('item_id', '=', $id)->whereIn('id', $ids)->get();
        if (count($screenshots) != count($item->vault_screenshots))  abort(422);
        foreach ($screenshots as $shot) {
            $shot->order_index = array_search($shot->id, $ids);
            $shot->is_primary = $shot->order_index == 0;
            $shot->save();
        }
        return response()->json(['success' => true]);
    }

    public function postDeleteScreenshot(Request $request)
    {
        $shot = VaultScreenshot::findOrFail($request->integer('id'));
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

    public function getEditScreenshots(int $id)
    {
        $item = VaultItem::findOrFail($id);
        return view('vault/edit-screenshots', [
            'item' => $item
        ]);
    }

    // Administrative Tasks

    public function getDelete(int $id)
    {
        $item = VaultItem::findOrFail($id);
        if (!$item->isEditable()) abort(404);

        return view('vault/delete', [
            'item' => $item
        ]);
    }

    public function postDelete(Request $request)
    {
        $item = VaultItem::findOrFail($request->integer('id'));
        if (!$item->isEditable()) abort(404);
        $item->delete();
        return redirect('vault/index');
    }

    public function getRestore(int $id)
    {
        $item = VaultItem::onlyTrashed()->findOrFail($id);
        return view('vault/restore', [
            'item' => $item
        ]);
    }

    public function postRestore(Request $request)
    {
        $item = VaultItem::onlyTrashed()->findOrFail($request->integer('id'));
        if (!$item->isEditable()) abort(404);
        $item->restore();
        return redirect('vault/view/' . $item->id);
    }
}

<?php namespace App\Http\Controllers\Wiki;

use App\Events\WikiRevisionCreated;
use App\Events\WikiTitleRenamed;
use App\Helpers\BBCode\ParseResult;
use App\Http\Controllers\Controller;
use App\Models\Accounts\UserSubscription;
use App\Models\Comments\Comment;
use App\Models\Wiki\WikiObject;
use App\Models\Wiki\WikiRevision;
use App\Models\Wiki\WikiRevisionBook;
use App\Models\Wiki\WikiRevisionMeta;
use App\Models\Wiki\WikiType;
use App\Models\Wiki\WikiUpload;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WikiController extends Controller {

	public function __construct()
	{
        $this->permission(['create', 'createupload'], 'WikiCreate');
        $this->permission(['edit', 'editupload', 'revert', 'revertupload'], 'WikiCreate');
        $this->permission(['delete', 'deleteupload'], 'WikiAdmin');
	}

	public function getIndex()
	{
        return $this->getPage('Home');
	}

    // Listings

    public function getPages() {
        $revisions = WikiRevision::with(['wiki_object'])->where('is_active', '=', 1)->whereNull('wiki_revisions.deleted_at')
            ->leftJoin('wiki_objects as o', 'o.id', '=', 'wiki_revisions.object_id')
            ->where('o.type_id', '=', WikiType::PAGE)
            ->orderBy('title')
            ->paginate(50);
        return view('wiki/list/pages', [
            'uploads' => false,
            'revisions' => $revisions
        ]);
    }

    public function getUploads() {
        $revisions = WikiRevision::with(['wiki_object'])->where('is_active', '=', 1)
            ->leftJoin('wiki_objects as o', 'o.id', '=', 'wiki_revisions.object_id')
            ->where('o.type_id', '=', WikiType::UPLOAD)
            ->orderBy('title')
            ->paginate(50);
        return view('wiki/list/uploads', [
            'uploads' => true,
            'revisions' => $revisions
        ]);
    }

    public function getCategories() {
        $sql = 'from wiki_revision_metas as m inner join wiki_revisions as r on m.revision_id = r.id where r.is_active = ? and m.key = ?';
        $param = [ true, WikiRevisionMeta::CATEGORY ];
        $count = DB::select("select COUNT(distinct m.value) as count $sql", $param)[0]->count;

        $page = intval(Request::get('page')) ?: 1;
        $offset = ($page - 1) * 50;

        $cats = DB::select("select distinct m.value as value $sql order by r.title limit 50 offset $offset", $param);
        $categories = new LengthAwarePaginator($cats, $count, 50, $page, [ 'path' => Paginator::resolveCurrentPath() ]);

        foreach ($cats as $c) {
            $c->title = str_replace('_', ' ', $c->value);
        }

        return view('wiki/list/categories', [
            'categories' => $categories
        ]);
    }

    public function getHistory($page) {
        $rev = WikiRevision::where('is_active', '=', 1)->where('slug', '=', $page)->firstOrFail();
        $revisions = WikiRevision::with(['user'])->where('object_id', '=', $rev->object_id)->orderBy('created_at', 'desc')->paginate(50);
        return view('wiki/list/revisions', [
            'revision' => $rev,
            'latest_revision' => $rev,
            'object' => $rev->wiki_object,
            'history' => $revisions,
            'next_id' => count($revisions) > 1 ? $revisions[1]->id : 0
        ]);
    }

    // Page viewing

    // Page types: 1; Article; 2: Category, 3: Upload

    public function getView($id) {
        $obj = WikiObject::findOrFail($id);
        $rev = WikiRevision::where('is_active', '=', 1)->where('object_id', '=', $id)->firstOrFail();
        return redirect('wiki/page/'.$rev->escaped_slug);
    }

    public function getViewRevision($id) {
        $rev = WikiRevision::findOrFail($id);
        return redirect('wiki/page/'.$rev->escaped_slug);
    }

    public function getPage($page, $revision = 0) {
        $rev = null;
        if (!$revision) {
            $rev = WikiRevision::with(['wiki_revision_metas', 'wiki_revision_books', 'wiki_revision_credits', 'wiki_revision_credits.user', 'wiki_object', 'user'])->where('is_active', '=', 1)->where('slug', '=', $page)->first();
        } else {
            $rev = WikiRevision::with(['wiki_revision_metas', 'wiki_revision_books', 'wiki_revision_credits', 'wiki_revision_credits.user', 'wiki_object', 'user'])->findOrFail($revision);
        }

        // A category will always have the list of pages at the bottom, even if the page doesn't exist
        $cat_name = null;
        $cat_pages = null;
        $subcats = null;
        if (substr($page, 0, 9) == 'category:') {
            $cat_name = substr($page, 9);

            $all_cats = explode('+', $cat_name);

            $cat_names = array_filter($all_cats, function ($c) { return !Str::startsWith($c, '!'); });
            $notcat_names = array_map(function ($c) { return substr($c, 1); }, array_filter($all_cats, function ($c) { return Str::startsWith($c, '!'); }));

            $cat_num = count($cat_names);

            $cats_escaped = implode(',', array_map(function($x) {
                return DB::connection()->getPdo()->quote($x);
            }, $cat_names));
            $notcats_escaped = implode(',', array_map(function($x) {
                return DB::connection()->getPdo()->quote($x);
            }, $notcat_names));

            $key = DB::connection()->getPdo()->quote(WikiRevisionMeta::CATEGORY);

            $catpage_filter = "select m.revision_id from wiki_revision_metas as m where m.key = $key \n";
            if (strlen($cats_escaped) > 0) $catpage_filter .= "and m.value in ({$cats_escaped}) \n";
            if (strlen($notcats_escaped) > 0) $catpage_filter .= "and m.revision_id not in (select m2.revision_id from wiki_revision_metas as m2 where m2.key = 'c' and m2.value in ({$notcats_escaped})) \n";
            $catpage_filter .= "group by m.revision_id \n";
            if (strlen($cats_escaped) > 0) $catpage_filter .= "having count(*) = $cat_num \n";

            $cat_pages = WikiRevision::whereIsActive(true)
                ->join('wiki_objects as o', 'o.id', '=', 'wiki_revisions.object_id')
                ->whereRaw("wiki_revisions.id in ($catpage_filter)")
                ->where('o.type_id', '=', WikiType::PAGE)
                ->select('wiki_revisions.*')
                ->orderBy('wiki_revisions.title')
                ->paginate(250);

            $subcats = DB::select("
                    select `value` as name, count(*) as num
                    from wiki_revision_metas as mm
                    where mm.`key` = ?
                    " . (strlen($cats_escaped) > 0 ? "and `value` not in ({$cats_escaped})" : '') . "
                    and mm.revision_id in (
                        select r.id from `wiki_revisions` as r
                        inner join wiki_objects o on r.object_id = o.id
                        where r.`is_active` = 1
                        and o.type_id = " . WikiType::PAGE . "
                        and r.id in ($catpage_filter) and r.deleted_at is null
                    )
                    group by `value`
                ", [ WikiRevisionMeta::CATEGORY, WikiRevisionMeta::CATEGORY, $cat_num ]);
        }

        $upload = null;
        if ($rev && $rev->wiki_object->type_id == WikiType::UPLOAD) {
            $upload = $rev->getUpload();
        }

        $books = [];
        if ($rev && $rev->wiki_revision_books->count() > 0) {
            $names = $rev->wiki_revision_books->map(function ($c) { return $c->book_name; })->unique()->toArray();
            $books = DB::table('wiki_revision_books as wrb')
                ->join('wiki_revisions as wr', 'wr.id', '=', 'revision_id')
                ->whereIn('book_name', $names)
                ->where('wr.is_active', '=', 1)
                ->whereNull('wr.deleted_at')
                ->select(['wrb.*', 'wr.slug', 'wr.title'])
                ->get();
        }

        $comments = [];
        $sub = null;
        $obj_sub = null;
        if ($rev) {
            $comments = Comment::with(['comment_metas', 'user'])->whereArticleType(Comment::WIKI)->whereArticleId($rev->object_id)->get();
            $sub = Comment::getSubscription(Auth::user(), Comment::WIKI, $rev->object_id, true);
            $obj_sub = UserSubscription::getSubscription(Auth::user(), UserSubscription::WIKI_REVISION, $rev->object_id, true);
        }

        $latest_rev = $rev;
        if ($rev && $rev->wiki_object && $rev->wiki_object->current_revision_id != $rev->id) {
            $latest_rev = $rev->wiki_object->current_revision;
            if (!$latest_rev) $latest_rev = $rev;
        }

        return view('wiki/view/object', [
            'slug' => $page,
            'slug_title' => str_replace('_', ' ', $page),
            'object' => $rev ? $rev->wiki_object : null,
            'revision' => $rev,
            'latest_revision' => $latest_rev,
            'obj_subscription' => $obj_sub,
            'cat_name' => $cat_name,
            'cat_pages' => $cat_pages,
            'subcats' => $subcats,
            'upload' => $upload,
            'comments' => $comments,
            'subscription' => $sub,
            'books' => $books
        ]);
    }

    public function getSubscribe($id)
    {
        $sub = UserSubscription::getSubscription(Auth::user(), UserSubscription::WIKI_REVISION, $id);
        if (!$sub) {
            $sub = UserSubscription::Create([
                'user_id' => Auth::user()->id,
                'article_type' => UserSubscription::WIKI_REVISION,
                'article_id' => intval($id, 10),
                'send_email' => true,
                'send_push_notification' => false
            ]);
        }
        return redirect('wiki/view/'.$id);
    }

    public function getUnsubscribe($id)
    {
        $sub = UserSubscription::getSubscription(Auth::user(), UserSubscription::WIKI_REVISION, $id);
        if ($sub) {
            $sub->delete();
        }
        return redirect('wiki/view/'.$id);
    }

    public function getBookInfo()
    {
        $book = Request::input('book');
        $books = DB::table('wiki_revision_books as wrb')
            ->join('wiki_revisions as wr', 'wr.id', '=', 'revision_id')
            ->where('book_name', '=', $book)
            ->where('wr.is_active', '=', 1)
            ->whereNull('wr.deleted_at')
            ->select(['wrb.*', 'wr.slug', 'wr.title'])
            ->get();
        return response()->json($books);
    }

    public function getEmbedInfo($id)
    {
        $upload = null;
        $rev = null;
        if (substr($id, 0, 4) == 'rev:') {
            $rev = WikiRevision::with(['wiki_revision_metas'])->where('id', '=', substr($id, 4))->first();
        } else if (substr($id, 0, 3) == 'id:') {
            $upload = WikiUpload::where('id', '=', substr($id, 3))->first();
            $rev = $upload->revision;
        }

        if (!$rev) {
            $upload = null;
            $rev = WikiRevision::with(['wiki_revision_metas'])->where('is_active', '=', 1)->where('slug', '=', 'upload:'.$id)->first();
        }

        if ($rev && !$upload) $upload = $rev->getUpload();

        $res = [
            'exists' => !!$rev && !!$upload,
            'revision' => $rev,
            'upload' => $upload,
            'meta' => $rev ? $rev->wiki_revision_metas : null
        ];
        return response()->json($res);
    }

    public function getEmbed($id)
    {
        $upload = null;
        $rev = null;
        if (substr($id, 0, 4) == 'rev:') {
            $rev = WikiRevision::with(['wiki_revision_metas'])->where('id', '=', substr($id, 4))->first();
        } else if (substr($id, 0, 3) == 'id:') {
            $upload = WikiUpload::where('id', '=', substr($id, 3))->first();
            $rev = $upload->revision;
        }

        if (!$rev) {
            $upload = null;
            $rev = WikiRevision::with(['wiki_revision_metas'])->where('is_active', '=', 1)->where('slug', '=', 'upload:'.$id)->first();
        }

        if ($rev && !$upload) $upload = $rev->getUpload();

        if (!$rev || !$upload) return response()->download(public_path('images/image-not-found.png'));

        return response()->download($upload->getServerFileName(), $rev->title . '.' . $upload->extension);
    }

    // Create/Edit/Delete

    /**
     * Creates a revision for the given object from the request data
     * @param $object
     * @param null $existing_revision
     * @return WikiRevision
     */
    public static function createRevision($object, $existing_revision = null) {
        /**
         * @var $parse_result ParseResult
         */
        $parse_result = app('bbcode')->ParseResult(Request::input('content_text'));

        // The title can only change for standard/upload pages
        $title = Request::input('title');
        $slug = WikiRevision::CreateSlug(Request::input('title'));
        if ($object->type_id == WikiType::UPLOAD) {
            $slug = 'upload:'.$slug;
        }
        if ($object->type_id != WikiType::PAGE && $object->type_id != WikiType::UPLOAD) {
            if ($existing_revision) {
                $title = $existing_revision->title;
                $slug = $existing_revision->slug;
            }
        }

        // Create the revision
        /**
         * @var $revision WikiRevision
         */
        $revision = WikiRevision::Create([
            'object_id' => $object->id,
            'user_id' => Auth::user()->id,
            'slug' => $slug,
            'title' => $title,
            'content_text' => Request::input('content_text'),
            'content_html' => $parse_result->text,
            'message' => Request::input('message') ?: ''
        ]);

        // Parse meta from the content
        $meta = [];
        foreach ($parse_result->meta as $c => $v) {
            if ($c == 'WikiLink') {
                foreach ($v as $val) $meta[] = new WikiRevisionMeta([ 'key' => WikiRevisionMeta::LINK, 'value' => $val ]);
            } else if ($c == 'WikiUpload') {
                foreach ($v as $val) $meta[] = new WikiRevisionMeta([ 'key' => WikiRevisionMeta::LINK, 'value' => 'upload:' . $val ]);
            } else if ($c == 'WikiCategory') {
                foreach ($v as $val) $meta[] = new WikiRevisionMeta([ 'key' => WikiRevisionMeta::CATEGORY, 'value' => str_replace(' ', '_', $val) ]);
            }
        }

        if ($object->type_id == WikiType::UPLOAD) {
            // Check if we need to upload a new file
            $file = Request::file('file');

            if ($file) {
                $upload = WikiUpload::Create([
                    'object_id' => $object->id,
                    'revision_id' => $revision->id,
                    'extension' => strtolower($file->getClientOriginalExtension())
                ]);

                $dir = public_path($upload->getRelativeDirectoryName());
                $name = $upload->getFileName();
                $file->move($dir, $name);
            } else {
                $upload = $existing_revision->getUpload();
                $upload->update([
                    'revision_id' => $revision->id
                ]);
            }

            $file_name = $upload->getServerFileName();
            $info = getimagesize($file_name);
            $size = filesize($file_name);

            $revision->wiki_revision_metas()->saveMany([
                new WikiRevisionMeta(['key' => WikiRevisionMeta::UPLOAD_ID, 'value' => $upload->id]),
                new WikiRevisionMeta(['key' => WikiRevisionMeta::FILE_SIZE, 'value' => $size]),
                new WikiRevisionMeta(['key' => WikiRevisionMeta::IMAGE_WIDTH, 'value' => $info ? $info[0] : 0]),
                new WikiRevisionMeta(['key' => WikiRevisionMeta::IMAGE_HEIGHT, 'value' => $info ? $info[1] : 0]),
            ]);
        }

        // Save meta & update the object
        $revision->wiki_revision_metas()->saveMany($meta);
        $revision->wiki_revision_books()->saveMany($parse_result->GetMeta('WikiBook'));
        $revision->wiki_revision_credits()->saveMany($parse_result->GetMeta('WikiCredit'));
        DB::statement('CALL update_wiki_object(?);', [$object->id]);
        $object->touch();

        if ($existing_revision && $revision->title != $existing_revision->title) {
            event(new WikiTitleRenamed($existing_revision->title, $object, $revision));
        }

        event(new WikiRevisionCreated($revision));

        return $revision;
    }

    public function getCreate($page = null) {
        return view('wiki/edit/create', [
            'slug' => $page,
            'slug_title' => str_replace('_', ' ', $page)
        ]);
    }

    public function postCreate() {
        Validator::extend('unique_wiki_slug', function($attribute, $value, $parameters) {
            $s = WikiRevision::CreateSlug($value);
            $rev = WikiRevision::where('is_active', '=', 1)->where('slug', '=', $s)->first();
            return $rev == null;
        });
        Validator::extend('valid_categories', function($attribute, $value, $parameters) {
            return !preg_match('/\[cat:[^\r\n\]]*[^a-z0-9- _\'\r\n\]][^\r\n\]]*\]/i', $value);
        });
        Validator::extend('category_name_must_exist', function($attribute, $value, $parameters) {
            if (substr($value, 0, 9) != 'category:') return true;
            $cat_name = WikiRevision::CreateSlug(substr($value, 9));
            $meta = WikiRevisionMeta::where('key', '=', WikiRevisionMeta::CATEGORY)->where('value', '=', $cat_name)->first();
            return $meta !== null;
        });
        Validator::extend('invalid_title', function($attribute, $value, $parameters) {
            return substr($value, 0, 7) != 'upload:';
        });
        $this->validate(Request::instance(), [
            'title' => 'required|max:200|unique_wiki_slug|category_name_must_exist|invalid_title',
            'content_text' => 'required|max:65536|valid_categories',
            'message' => 'max:200'
        ], [
            'unique_wiki_slug' => 'The URL of this page is not unique, change the title to create a URL that doesn\'t already exist.',
            'valid_categories' => 'Category names must only contain letters, numbers, and spaces. Example: [cat:Name]',
            'invalid_title' => "A page title cannot start with ':upload'.",
            'category_name_must_exist' => 'This category name doesn\'t exist. Apply this category to at least one object before creating the category page.'
        ]);
        $type = WikiType::PAGE;
        if (substr(Request::input('title'), 0, 9) == 'category:') $type = WikiType::CATEGORY;
        $object = WikiObject::Create([ 'type_id' => $type ]);
        $revision = $this->createRevision($object);
        return redirect('wiki/page/'.$revision->escaped_slug);
    }

    public function getEdit($page) {
        $rev = WikiRevision::with(['wiki_object'])->where('is_active', '=', 1)->where('slug', '=', $page)->first();

        if (!$rev->wiki_object->canEdit()) return abort(404);

        return view('wiki/edit/page', [
            'revision' => $rev,
            'latest_revision' => $rev
        ]);
    }

    public function postEdit() {
        $id = intval(Request::input('id'));
        $rev = WikiRevision::findOrFail($id);
        $obj = WikiObject::findOrFail($rev->object_id);

        if (!$obj->canEdit()) return abort(404);

        Validator::extend('unique_wiki_slug', function($attribute, $value, $parameters) use ($obj) {
            if ($obj->type_id == WikiType::UPLOAD) {
                $value = 'upload:'.$value;
            }
            $s = WikiRevision::CreateSlug($value);
            $rev = WikiRevision::where('is_active', '=', 1)->where('slug', '=', $s)->where('object_id', '!=', $obj->id)->first();
            return $rev == null;
        });
        Validator::extend('must_change', function($attribute, $value, $parameters) use ($rev, $obj) {
            return trim($rev->content_text) != trim(Request::input('content_text'))
                || trim($rev->title) != trim(Request::input('title'))
                || ($obj->type_id == WikiType::UPLOAD && Request::file('file')
                || (permission('WikiAdmin') && $obj->permission_id != Request::input('permission_id')));
        });
        Validator::extend('valid_categories', function($attribute, $value, $parameters) {
            return !preg_match('/\[cat:[^\r\n\]]*[^a-z0-9- _\'\r\n\]][^\r\n\]]*\]/i', $value);
        });
        Validator::extend('invalid_title', function($attribute, $value, $parameters) use ($obj, $rev) {
            return ($obj->type_id != WikiType::PAGE) ||
                   (substr($value, 0, 9) != 'category:' && substr($value, 0, 7) != 'upload:');
        });
        Validator::extend('valid_extension', function($attribute, $value, $parameters) {
            return in_array(strtolower($value->getClientOriginalExtension()), $parameters);
        });
        $max_size = 1024*4;
        $allowed_extensions = 'jpeg,jpg,png,gif,mp3,mp4';
        if (permission('Admin')) {
            $max_size = 1024*64;
            $allowed_extensions .= ',zip,rar,exe,msi';
        }
        $rules = [
            'file' => "max:$max_size|valid_extension:$allowed_extensions",
            'content_text' => 'required|max:65536|must_change|valid_categories',
            'message' => 'max:200'
        ];
        if ($obj->type_id == WikiType::PAGE || $obj->type_id == WikiType::UPLOAD) {
            $rules['title'] = 'required|max:200|unique_wiki_slug|invalid_title';
        }
        $this->validate(Request::instance(), $rules, [
            'must_change' => 'At least one field must be changed to apply an edit.',
            'unique_wiki_slug' => 'The URL of this page is not unique, change the title to create a URL that doesn\'t already exist.',
            'valid_categories' => 'Category names must only contain letters, numbers, and spaces. Example: [cat:Name]',
            'invalid_title' => "A page title cannot start with ':category' or ':upload'.",
            'valid_extension' => 'Only the following file formats are allowed: jpg, png, gif'
        ]);
        $revision = $this->createRevision($obj, $rev);

        if(permission('WikiAdmin')) {
            $obj->permission_id = Request::input('permission_id');
            $obj->save();
        }

        return redirect('wiki/page/'.$revision->escaped_slug);
    }

    public function getRevert($id) {
        $rev = WikiRevision::with(['wiki_object', 'user'])->where('is_active', '=', false)->findOrFail($id);
        $obj = $rev->wiki_object;

        if (!$obj->canEdit()) return abort(404);

        return view('wiki/edit/revert', [
            'object' => $obj,
            'revision' => $rev,
            'latest_revision' => $rev
        ]);
    }

    public function postRevert() {
        $id = intval(Request::input('id'));
        $rev = WikiRevision::with(['wiki_object', 'user'])->where('is_active', '=', false)->findOrFail($id);
        $obj = $rev->wiki_object;

        if (!$obj->canEdit()) return abort(404);

        $this->validate(Request::instance(), [
            'reason' => 'max:200'
        ]);

        $current_rev = WikiRevision::where('is_active', '=', 1)->where('object_id', '=', $obj->id)->first();
        $same_slug = WikiRevision::where('is_active', '=', 1)->where('slug', '=', $rev->slug)->where('object_id', '!=', $obj->id)->first();

        // Copy the old revision and apply it over the top of the current revision
        $parse_result = app('bbcode')->ParseResult($rev->content_text);
        $revision = WikiRevision::Create([
            'object_id' => $obj->id,
            'user_id' => Auth::user()->id,
            'slug' => $same_slug ? $current_rev->slug : $rev->slug,
            'title' => $same_slug ? $current_rev->title : $rev->title,
            'content_text' => $rev->content_text,
            'content_html' => $parse_result->text,
            'message' => 'Reverting to revision #' . $rev->id .
                         ($rev->message ? " ({$rev->message})" : '') .
                         (Request::input('reason') ? ' - ' . Request::input('reason') : '')
        ]);
        $meta = [];
        foreach ($parse_result->meta as $c => $v) {
            if ($c == 'WikiLink') {
                foreach ($v as $val) $meta[] = new WikiRevisionMeta(['key' => WikiRevisionMeta::LINK, 'value' => $val]);
            } else if ($c == 'WikiUpload') {
                foreach ($v as $val) $meta[] = new WikiRevisionMeta(['key' => WikiRevisionMeta::LINK, 'value' => 'upload:' . $val]);
            } else if ($c == 'WikiCategory') {
                foreach ($v as $val) $meta[] = new WikiRevisionMeta(['key' => WikiRevisionMeta::CATEGORY, 'value' => $val]);
            }
        }
        foreach ($rev->wiki_revision_metas as $m) {
            if ($m->key == WikiRevisionMeta::CATEGORY || $m->key == WikiRevisionMeta::LINK) continue;
            else $meta[] = new WikiRevisionMeta(['key' => $m->key, 'value' => $m->value]);
        }
        $revision->wiki_revision_metas()->saveMany($meta);
        $revision->wiki_revision_books()->saveMany($parse_result->GetMeta('WikiBook'));
        $revision->wiki_revision_credits()->saveMany($parse_result->GetMeta('WikiCredit'));
        DB::statement('CALL update_wiki_object(?);', [$obj->id]);
        return redirect('wiki/page/'.$revision->escaped_slug);
    }

    public function getDelete($id)
    {
        $obj = WikiObject::with(['current_revision'])->findOrFail($id);
        if (!$obj->canDelete()) abort(404);

        return view('wiki/edit/delete', [
            'object' => $obj,
            'revision' => $obj->current_revision,
            'latest_revision' => $obj->current_revision
        ]);
    }

    public function postDelete()
    {
        $id = intval(Request::input('id'));
        $obj = WikiObject::findOrFail($id);
        if (!$obj->canDelete()) abort(404);

        $obj->delete();
        DB::statement('CALL update_wiki_object(?);', [$obj->id]);
        return redirect('wiki');
    }

    // Uploads

    public function getCreateUpload($name = null) {
        return view('wiki/edit/create-upload', [
            'slug' => $name,
            'slug_title' => str_replace('_', ' ', $name)
        ]);
    }

    public function postCreateUpload()
    {
        Validator::extend('unique_wiki_slug', function($attribute, $value, $parameters) {
            $s = WikiRevision::CreateSlug('upload:'.$value);
            $rev = WikiRevision::where('is_active', '=', 1)->where('slug', '=', $s)->first();
            return $rev == null;
        });
        Validator::extend('valid_categories', function($attribute, $value, $parameters) {
            return !preg_match('/\[cat:[^\r\n\]]*[^a-z0-9- _\'\r\n\]][^\r\n\]]*\]/i', $value);
        });
        Validator::extend('valid_extension', function($attribute, $value, $parameters) {
            return in_array(strtolower($value->getClientOriginalExtension()), $parameters);
        });

        $max_size = 1024*4;
        $allowed_extensions = 'jpeg,jpg,png,gif,mp3,mp4';
        if (permission('Admin')) {
            $max_size = 1024*64;
            $allowed_extensions .= ',zip,rar,exe,msi';
        }

        $this->validate(Request::instance(), [
            'title' => 'required|max:200|unique_wiki_slug',
            'file' => "required|max:{$max_size}|valid_extension:{$allowed_extensions}",
            'content_text' => 'required|max:65536|valid_categories',
            'message' => 'max:200'
        ], [
            'unique_wiki_slug' => 'The URL of this page is not unique, change the title to create a URL that doesn\'t already exist.',
            'valid_categories' => 'Category names must only contain letters, numbers, and spaces. Example: [cat:Name]',
            'valid_extension' => 'Only the following file formats are allowed: ' . $allowed_extensions
        ]);
        $type = WikiType::UPLOAD;
        $object = WikiObject::Create([ 'type_id' => $type ]);
        $revision = $this->createRevision($object);
        return redirect('wiki/page/'.$revision->escaped_slug);
    }
}

<?php namespace App\Http\Controllers\Wiki;

use App\Http\Controllers\Controller;
use App\Models\Wiki\WikiObject;
use App\Models\Wiki\WikiRevision;
use App\Models\Wiki\WikiRevisionMeta;
use App\Models\Wiki\WikiType;
use Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Request;
use Validator;
use DB;

class WikiController extends Controller {

	public function __construct()
	{
        $this->permission(['create', 'createupload'], 'WikiCreate');
        $this->permission(['edit', 'editupload', 'revert', 'revertupload'], 'WikiEdit');
        $this->permission(['delete', 'deleteupload'], 'WikiDelete');
	}

	public function getIndex()
	{
        return $this->getPage('Home');
	}

    // Listings

    public function getPages() {
        $revisions = WikiRevision::where('is_active', '=', 1)
            ->leftJoin('wiki_objects as o', 'o.id', '=', 'wiki_revisions.object_id')
            ->where('o.type_id', '=', WikiType::PAGE)
            ->orderBy('title')
            ->paginate();
        return view('wiki/list/pages', [
            'revisions' => $revisions
        ]);
    }

    public function getCategories() {
        $sql = 'from wiki_revision_metas as m inner join wiki_revisions as r on m.revision_id = r.id where r.is_active = ? and m.key = ?';
        $param = [ true, WikiRevisionMeta::CATEGORY ];
        $count = DB::select("select COUNT(distinct m.value) as count $sql", $param)[0]->count;

        $page = intval(Request::get('page')) ?: 1;
        $offset = ($page - 1) * 50;

        $cats = DB::select("select distinct m.value as value $sql limit 50 offset $offset", $param);
        $categories = new LengthAwarePaginator($cats, $count, 50, $page, [ 'path' => Paginator::resolveCurrentPath() ]);

        return view('wiki/list/categories', [
            'categories' => $categories
        ]);
    }

    // Page viewing

    // Page types: 1; Article; 2: Category, 3: Image

    public function getPage($page, $revision = 0) {
        $rev = null;
        if (!$revision) {
            $rev = WikiRevision::with(['wiki_revision_metas', 'wiki_object', 'user'])->where('is_active', '=', 1)->where('slug', '=', $page)->first();
        } else {
            $rev = WikiRevision::with(['wiki_revision_metas', 'wiki_object', 'user'])->findOrFail($revision);
        }

        // A category will always have the list of pages at the bottom, even if the page doesn't exist
        $cat_name = null;
        $cat_pages = null;
        if (substr($page, 0, 9) == 'category:') {
            $cat_name = substr($page, 9);
            $cat_pages = DB::table('wiki_revision_metas as m')
                ->join('wiki_revisions as r', 'm.revision_id', '=', 'r.id')
                ->select(['r.*'])
                ->where('r.is_active', '=', true)
                ->where('m.value', '=', $cat_name)
                ->where('m.key', '=', WikiRevisionMeta::CATEGORY)
                ->orderBy('r.title')
                ->paginate(50);
        }

        return view('wiki/view/object', [
            'slug' => $page,
            'slug_title' => str_replace('_', ' ', $page),
            'object' => $rev ? $rev->wiki_object : null,
            'revision' => $rev,
            'cat_name' => $cat_name,
            'cat_pages' => $cat_pages
        ]);
    }

    public function getHistory($page) {
        $rev = WikiRevision::where('is_active', '=', 1)->where('slug', '=', $page)->firstOrFail();
        $revisions = WikiRevision::with(['user'])->where('object_id', '=', $rev->object_id)->orderBy('created_at', 'desc')->paginate(50);
        return view('wiki/list/revisions', [
            'revision' => $rev,
            'object' => $rev->wiki_object,
            'history' => $revisions,
            'next_id' => count($revisions) > 1 ? $revisions[1]->id : 0
        ]);
    }

    // Create/Edit/Delete

    /**
     * Creates a revision for the given object from the request data
     * @param $object
     * @return WikiRevision
     */
    private function createRevision($object) {
        $parse_result = app('bbcode')->ParseResult(Request::input('content_text'));
        $title = Request::input('title');
        $slug = WikiRevision::CreateSlug(Request::input('title'));
        if (substr($title, 0, 10) == 'Category: ') {
            $title = 'Category: ' . substr($title, 10);
            $slug = 'category:' . substr($title, 10);
        }
        $revision = WikiRevision::Create([
            'object_id' => $object->id,
            'user_id' => Auth::user()->id,
            'slug' => $slug,
            'title' => $title,
            'content_text' => Request::input('content_text'),
            'content_html' => $parse_result->text,
            'message' => Request::input('message') ?: ''
        ]);
        $meta = [];
        foreach ($parse_result->meta as $c => $v) {
            if ($c == 'WikiLink') {
                foreach ($v as $val) $meta[] = new WikiRevisionMeta(['key' => WikiRevisionMeta::LINK, 'value' => $val]);
            } else if ($c == 'WikiImage') {
                foreach ($v as $val) $meta[] = new WikiRevisionMeta(['key' => WikiRevisionMeta::LINK, 'value' => 'image:' . $val]);
            } else if ($c == 'WikiCategory') {
                foreach ($v as $val) $meta[] = new WikiRevisionMeta(['key' => WikiRevisionMeta::CATEGORY, 'value' => $val]);
            }
        }
        $revision->wiki_revision_metas()->saveMany($meta);
        DB::statement('CALL update_wiki_object(?);', [$object->id]);
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
            return !preg_match('/\[cat:[^\r\n\]]*[^a-z0-9\r\n\]][^\r\n\]]*\]/i', $value);
        });
        Validator::extend('category_name_must_exist', function($attribute, $value, $parameters) {
            if (substr($value, 0, 9) != 'category:') return true;
            $cat_name = substr($value, 9);
            $meta = WikiRevisionMeta::whereKey(WikiRevisionMeta::CATEGORY)->whereValue($cat_name)->first();
            return $meta !== null;
        });
        $this->validate(Request::instance(), [
            'title' => 'required|max:200|unique_wiki_slug|category_name_must_exist',
            'content_text' => 'required|max:10000|valid_categories',
            'message' => 'max:255'
        ], [
            'unique_wiki_slug' => 'The URL of this page is not unique, change the title to create a URL that doesn\'t already exist.',
            'valid_categories' => 'Category names must only contain letters and numbers. Example: [cat:Name]',
            'category_name_must_exist' => 'This category name doesn\'t exist. Apply this category to at least one object before creating the category page.'
        ]);
        $type = WikiType::PAGE;
        if (substr(Request::input('title'), 0, 9) == 'category:') $type == WikiType::CATEGORY;
        $object = WikiObject::Create([ 'type_id' => $type ]);
        $revision = $this->createRevision($object);
        return redirect('wiki/page/'.$revision->slug);
    }

    public function getEdit($page) {
        $rev = WikiRevision::where('is_active', '=', 1)->where('slug', '=', $page)->first();
        return view('wiki/edit/page', [
            'revision' => $rev
        ]);
    }

    public function postEdit() {
        $id = intval(Request::input('id'));
        $rev = WikiRevision::findOrFail($id);
        $obj = WikiObject::findOrFail($rev->object_id);
        Validator::extend('unique_wiki_slug', function($attribute, $value, $parameters) use ($obj) {
            $s = WikiRevision::CreateSlug($value);
            $rev = WikiRevision::where('is_active', '=', 1)->where('slug', '=', $s)->where('object_id', '!=', $obj->id)->first();
            return $rev == null;
        });
        Validator::extend('must_change', function($attribute, $value, $parameters) use ($rev) {
            return trim($rev->content_text) != trim(Request::input('content_text'))
                || trim($rev->title) != trim(Request::input('title'));
        });
        Validator::extend('valid_categories', function($attribute, $value, $parameters) {
            return !preg_match('/\[cat:[^\r\n\]]*[^a-z0-9\r\n\]][^\r\n\]]*\]/i', $value);
        });
        Validator::extend('cannot_change_category_title', function($attribute, $value, $parameters) use ($obj, $rev) {
            return $obj->type_id != WikiType::CATEGORY || $rev->{$attribute} == $value;
        });
        $this->validate(Request::instance(), [
            'title' => 'required|max:200|unique_wiki_slug|cannot_change_category_title',
            'content_text' => 'required|max:10000|must_change|valid_categories',
            'message' => 'max:255'
        ], [
            'must_change' => 'At least one field (title or content) must be changed to apply an edit.',
            'unique_wiki_slug' => 'The URL of this page is not unique, change the title to create a URL that doesn\'t already exist.',
            'valid_categories' => 'Category names must only contain letters and numbers. Example: [cat:Name]',
            'cannot_change_category_title' => 'The title of a category page cannot be changed.'
        ]);
        $revision = $this->createRevision($obj);
        return redirect('wiki/page/'.$revision->slug);
    }

    public function getRevert($id) {

    }

    public function getEmbed($id)
    {
        return response()->download(public_path('images/'.$id), $id, array(), 'inline');
    }

}

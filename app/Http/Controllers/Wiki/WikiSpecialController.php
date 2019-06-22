<?php namespace App\Http\Controllers\Wiki;

use App\Http\Controllers\Controller;
use App\Models\Accounts\User;
use App\Models\Wiki\WikiRevision;
use App\Models\Wiki\WikiRevisionMeta;
use App\Models\Wiki\WikiType;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;

class WikiSpecialController extends Controller {

	public function __construct()
	{

	}

	public function getIndex()
	{
	    return view('wiki/special/home');
	}

    // Maintenance

    public function getMaintenanceCategories() {
        $pages = DB::select("
               select wr.*
               from wiki_revisions wr
               inner join wiki_objects wo on wr.object_id = wo.id
               where wr.is_active = 1
               and wo.type_id = ?
               and not exists (select * from wiki_revision_metas wrm where wrm.revision_id = wr.id and wrm.key = 'c')
               and wr.deleted_at is null
               and wo.deleted_at is null
               limit 50
           ", [ WikiType::PAGE ]);
        $uploads = DB::select("
               select wr.*
               from wiki_revisions wr
               inner join wiki_objects wo on wr.object_id = wo.id
               where wr.is_active = 1
               and wo.type_id = ?
               and not exists (select * from wiki_revision_metas wrm where wrm.revision_id = wr.id and wrm.key = 'c')
               and wr.deleted_at is null
               and wo.deleted_at is null
               limit 50
           ", [ WikiType::UPLOAD ]);
        return view('wiki/special/page', [
            'title' => 'Pages with no categories',
            'sections' => [
                [ 'title' => 'Uncategorised pages', 'data' => $pages, 'type' => 'revisions' ],
                [ 'title' => 'Uncategorised uploads', 'data' => $uploads, 'type' => 'revisions' ],
            ]
        ]);
    }

    public function getMaintenanceLinks() {
        $missing_pages = DB::select("
   	        select wr.*, wrm.value as missing_link
   	        from wiki_revision_metas wrm
   	        inner join wiki_revisions wr on wrm.revision_id = wr.id
   	        left join wiki_revisions lwr on lwr.is_active = 1 and lwr.deleted_at is null and lwr.title = wrm.value
   	        where wr.is_active = 1 and wr.deleted_at is null
            and wrm.value not like 'category:%' and wrm.value not like 'upload:%'
   	        and wrm.key = ? and lwr.id is null
   	        order by wr.title
            limit 200
   	    ", [ WikiRevisionMeta::LINK ]);
        return view('wiki/special/page', [
            'title' => 'Missing links',
            'sections' => [
                [ 'title' => 'Links to missing pages', 'data' => $missing_pages, 'type' => 'revisions', 'missing_link' => true ]
            ]
        ]);
    }

    public function getMaintenanceUploads() {
        $missing_uploads = DB::select("
   	        select wr.*, wrm.value as missing_link
   	        from wiki_revision_metas wrm
   	        inner join wiki_revisions wr on wrm.revision_id = wr.id
   	        left join wiki_revisions lwr on lwr.is_active = 1 and lwr.deleted_at is null and lwr.title = substring(wrm.value, 8)
   	        where wr.is_active = 1 and wr.deleted_at is null
            and wrm.value like 'upload:%'
   	        and wrm.key = ? and lwr.id is null
            order by wr.title
            limit 200
   	    ", [ WikiRevisionMeta::LINK ]);
        return view('wiki/special/page', [
            'title' => 'Missing files',
            'sections' => [
                [ 'title' => 'Links to missing files', 'data' => $missing_uploads, 'type' => 'revisions', 'missing_link' => true ]
            ]
        ]);
    }

    public function getMaintenanceContent() {
        $pages = DB::select("
           select wr.*
           from wiki_revisions wr
           inner join wiki_objects wo on wr.object_id = wo.id
           where wr.is_active = 1
           and wr.deleted_at is null
           and wo.deleted_at is null
           and wr.content_text like '%twhl.info/wiki/%'
           limit 50
       ");
        return view('wiki/special/page', [
            'title' => 'Missing files',
            'sections' => [
                [ 'title' => 'Long-style links to internal pages', 'data' => $pages, 'type' => 'revisions' ]
            ]
        ]);
    }

    // Reports

    public function getReportChanges() {
        $revisions = WikiRevision::with(['user'])
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();
        return view('wiki/special/page', [
            'title' => 'Recent changes',
            'sections' => [
                [ 'title' => 'Recent changes', 'data' => $revisions, 'type' => 'revisions', 'link_to_revision' => true, 'message' => true ]
            ]
        ]);
    }

    public function getReportLinks() {

	    $most_linked = DB::select("
	        select wr.*, c as link_count
	        from (
	            select wrm.value, count(*) as c
	            from wiki_revision_metas wrm
	            inner join wiki_revisions wr on wrm.revision_id = wr.id
	            inner join wiki_objects wo on wr.object_id = wo.id
	            where wr.deleted_at is null and wr.is_active = 1
	            and wrm.key = 'l' and wr.slug not like 'category:%' and wo.type_id = ?
	            group by wrm.value
	        ) counts
	        inner join wiki_revisions wr on wr.title = counts.value
	        where wr.is_active = 1 and wr.deleted_at is null
	        order by c desc
	        limit 20	        
	    ", [ WikiType::PAGE ]);
        return view('wiki/special/page', [
            'title' => 'Link statistics',
            'sections' => [
                [ 'title' => 'Most linked pages', 'data' => $most_linked, 'type' => 'links' ]
            ]
        ]);
    }

    public function getReportPages() {
        $most_revisions = DB::select('
            select r.id, r.object_id, r.slug, r.title, count(revs.id) as num_revisions
            from wiki_revisions r
            inner join wiki_objects wo on r.object_id = wo.id and wo.deleted_at is null
            inner join wiki_revisions revs on revs.object_id = wo.id and revs.deleted_at is null
            where r.deleted_at is null
            and r.is_active = 1
            and wo.type_id = ?
            group by r.id
            order by num_revisions desc, r.created_at desc
            limit 20
        ', [ WikiType::PAGE ]);
        $least_revisions = DB::select('
            select r.id, r.object_id, r.slug, r.title, count(revs.id) as num_revisions
            from wiki_revisions r
            inner join wiki_objects wo on r.object_id = wo.id and wo.deleted_at is null
            inner join wiki_revisions revs on revs.object_id = wo.id and revs.deleted_at is null
            where r.deleted_at is null
            and r.is_active = 1
            and wo.type_id = ?
            group by r.id
            order by num_revisions asc, r.created_at asc
            limit 20
        ', [ WikiType::PAGE ]);
        $longest_revisions = DB::select('
            select r.id, r.object_id, r.slug, r.title, length(r.content_text) as content_length
            from wiki_revisions r
            inner join wiki_objects wo on r.object_id = wo.id and wo.deleted_at is null
            where r.deleted_at is null
            and r.is_active = 1
            and wo.type_id = ?
            order by length(r.content_text) desc
            limit 20
        ', [ WikiType::PAGE ]);
        $shortest_revisions = DB::select('
            select r.id, r.object_id, r.slug, r.title, length(r.content_text) as content_length
            from wiki_revisions r
            inner join wiki_objects wo on r.object_id = wo.id and wo.deleted_at is null
            where r.deleted_at is null
            and r.is_active = 1
            and wo.type_id = ?
            order by length(r.content_text) asc
            limit 20
        ', [ WikiType::PAGE ]);
        return view('wiki/special/page', [
            'title' => 'Page statistics',
            'sections' => [
                [ 'title' => 'Most revisions', 'data' => $most_revisions, 'type' => 'revisions', 'num_revisions' => true ],
                [ 'title' => 'Least revisions', 'data' => $least_revisions, 'type' => 'revisions', 'num_revisions' => true ],
                [ 'title' => 'Longest pages', 'data' => $longest_revisions, 'type' => 'revisions', 'content_length' => true ],
                [ 'title' => 'Shortest pages', 'data' => $shortest_revisions, 'type' => 'revisions', 'content_length' => true ],
            ]
        ]);
    }

    public function getReportUsers() {
	    $editors = User::where('stat_wiki_edits', '>', 0)->orderBy('stat_wiki_edits', 'desc')->limit(50)->get();
        return view('wiki/special/page', [
            'title' => 'User statistics',
            'sections' => [
                [ 'title' => 'Users with most edits', 'data' => $editors, 'type' => 'users', 'stat_wiki_edits' => true ]
            ]
        ]);
    }

    // Queries

    public function getQueryLinks() {
	    $title = Input::get('title');
	    $links_to = DB::select("
	        select distinct wr.title
	        from wiki_revision_metas wrm
	        inner join wiki_revisions wr on wrm.revision_id = wr.id
	        where wr.is_active = 1 and wr.deleted_at is null
	        and wrm.key = ? and wrm.value = ?
            order by wr.title
	    ", [ WikiRevisionMeta::LINK, $title ]);
	    $links_from = DB::select("
	        select distinct wrm.value as title, (case when lr.id is not null or wrm.value like 'category:%' then 1 else 0 end) as page_exists
	        from wiki_revisions wr
	        inner join wiki_revision_metas wrm on wrm.revision_id = wr.id
	        left join wiki_revisions lr on lr.title = wrm.value and lr.is_active = 1 and lr.deleted_at is null
	        where wr.is_active = 1 and wr.deleted_at is null
	        and wrm.key = ? and wr.title = ?
            order by wrm.value
	    ", [ WikiRevisionMeta::LINK, $title ]);

	    return view('wiki/special/query-links', [
	        'title' => $title,
            'links_to' => $links_to,
            'links_from' => $links_from
        ]);
    }

    public function getQuerySearch() {
	    $search = Input::get('search');
	    $pages = WikiRevision::with([])
                ->where('is_active', '=', 1)
                ->whereRaw("content_text like concat('%', ?, '%')", [ $search ])
                ->orderBy('title')
                ->limit(100)
                ->get();

	    return view('wiki/special/query-search', [
	        'search' => $search,
            'pages' => $pages
        ]);
    }
}

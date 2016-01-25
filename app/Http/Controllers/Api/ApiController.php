<?php

namespace App\Http\Controllers\Api;
 
use App\Http\Controllers\Controller;
use App\Models\Accounts\Permission;
use App\Models\Accounts\User;
use App\Models\Competitions\CompetitionJudgeType;
use App\Models\Competitions\CompetitionRestrictionGroup;
use App\Models\Competitions\CompetitionStatus;
use App\Models\Competitions\CompetitionType;
use App\Models\Engine;
use App\Models\Forums\Forum;
use App\Models\Game;
use App\Models\License;
use App\Models\Vault\VaultCategory;
use App\Models\Vault\VaultInclude;
use App\Models\Vault\VaultScreenshot;
use App\Models\Vault\VaultType;
use App\Models\Wiki\WikiRevision;
use App\Models\Wiki\WikiRevisionMeta;
use Input;

class ApiController extends Controller {

    public function __construct()
    {
        $this->permission(['format'], true);
        $this->permission(['permissions'], 'Admin');
    }

    private function filter($query, $filter_cols, $sort_cols = []) {

        $ids = Input::get('id');
        if ($ids) {
            $ids = array_filter(array_map(function($x) { return intval($x); }, explode(',', $ids)), function($x) { return $x > 0; });
            if ($ids) $query = $query-> whereIn('id', $ids);
        }

        if (!is_array($filter_cols)) $filter_cols = [$filter_cols];
        if ($sort_cols && !is_array($sort_cols)) $sort_cols = [$sort_cols];
        if (!$sort_cols || !is_array($sort_cols) || count($sort_cols) == 0) $sort_cols = $filter_cols;

        foreach ($sort_cols as $v) {
            $query = $query->orderBy($v);
        }

        $filter = Input::get('filter');
        if (!$filter) return $query;
        $filter .= '%';
        $args = [];
        $sql = '1 != 1';
        for ($i = 0; $i < count($filter_cols); $i++) {
            $col = $filter_cols[$i];
            $sql .= " or $col like ?";
            $args[] = $filter;
        }
        return $query->whereRaw("($sql)", $args);
    }

    private function toArray($query) {

        $page = intval(Input::get('page'));
        if (!$page) $page = 1;

        $count = Input::get('count');
        if (!$count) $count = 10;

        $plain = Input::get('plain') !== null;
        if (Input::get('all') !== null) {
            $plain = true;
            $count = 100;
            $page = 1;
        }

        $total = $query->getQuery()->getCountForPagination();

        $pages = ceil($total / $count);
        if ($page == 'last' || $page > $pages) $page = $pages;
        else if ($page < 1) $page = 1;

        $items = $query->skip(($page - 1) * $count)->take($count)->get();

        if ($plain) return $items->values()->toArray();
        return [
            'items' => $items->values()->toArray(),
            'total' => $total,
            'pages' => $pages,
            'page' => $page
        ];
    }

    private function call($q, $filter_cols, $sort_cols = []) {
        $filtered = $this->filter($q, $filter_cols, $sort_cols);
        $array = $this->toArray($filtered);
        return response()->json($array);
    }

    // Standard

    public function getEngines()
    {
        return $this->call(Engine::where('id', '>', 0), 'name', 'orderindex');
    }

    public function getGames()
    {
        return $this->call(Game::where('id', '>', 0), 'name', 'orderindex');
    }

    public function getLicenses()
    {
        return $this->call(License::where('id', '>', 0), 'name', 'orderindex');
    }

    // Account

    public function getUsers()
    {
        return $this->call(User::where('id', '>', 0), 'name');
    }

    // Forum

    public function getPermissions()
    {
        return $this->call(Permission::where('id', '>', 0), 'name');
    }

    public function getForums()
    {
        return $this->call(Forum::where('id', '>', 0), 'name');
    }

    // Wiki

    public function getWikiRevisions()
    {
        $q = WikiRevision::where('id', '>', 0);
        if (Input::get('active') !== null) $q = $q->where('is_active', '=', 1);
        return $this->call($q, 'title');
    }

    public function getWikiRevisionMetas($id)
    {
        return $this->call(WikiRevisionMeta::where('revision_id', '=', $id), 'id', 'key');
    }

    // Vault

    public function getVaultCategories()
    {
        return $this->call(VaultCategory::where('id', '>', 0), 'name', 'orderindex');
    }

    public function getVaultTypes()
    {
        return $this->call(VaultType::where('id', '>', 0), 'name', 'orderindex');
    }

    public function getVaultIncludes()
    {
        return $this->call(VaultInclude::where('id', '>', 0), 'name', 'orderindex');
    }

    public function getVaultScreenshots($id)
    {
        return $this->call(VaultScreenshot::where('item_id', '=', $id), 'id', 'order_index');
    }

    // Competitions

    public function getCompetitionGroups()
    {
        return $this->call(CompetitionRestrictionGroup::where('id', '>', 0), 'title', 'title');
    }

    public function getCompetitionStatuses()
    {
        return $this->call(CompetitionStatus::where('id', '>', 0), 'name', 'id');
    }

    public function getCompetitionTypes()
    {
        return $this->call(CompetitionType::where('id', '>', 0), 'name', 'id');
    }

    public function getCompetitionJudgeTypes()
    {
        return $this->call(CompetitionJudgeType::where('id', '>', 0), 'name', 'id');
    }

    // Non-standard

    public function postFormat() {
        $field = Input::get('field') ?: 'text';
        $text = Input::input($field) ?: '';
        return app('bbcode')->Parse($text);
    }
}

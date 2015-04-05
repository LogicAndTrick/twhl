<?php

namespace App\Http\Controllers\Api;
 
use App\Http\Controllers\Controller;
use App\Models\Accounts\Permission;
use App\Models\Accounts\User;
use App\Models\Forums\Forum;
use Input;

class ApiController extends Controller {

    public function __construct()
    {
        $this->permission(['format'], true);
        $this->permission(['permissions'], 'Admin');
    }

    private function filter($query, $filter_cols) {

        $ids = Input::get('id');
        if ($ids) {
            $ids = array_filter(array_map(function($x) { return intval($x); }, explode(',', $ids)), function($x) { return $x > 0; });
            if ($ids) $query = $query-> whereIn('id', $ids);
        }

        if (!is_array($filter_cols)) $filter_cols = [$filter_cols];

        foreach ($filter_cols as $v) {
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

        if ($plain) return $items->toArray();
        return [
            'items' => $items->toArray(),
            'total' => $total,
            'pages' => $pages,
            'page' => $page
        ];
    }

    private function call($q, $filter_cols) {
        $filtered = $this->filter($q, $filter_cols);
        $array = $this->toArray($filtered);
        return response()->json($array);
    }

    public function getPermissions()
    {
        return $this->call(Permission::where('id', '>', 0), 'name');
    }

    public function getForums()
    {
        return $this->call(Forum::where('id', '>', 0), 'name');
    }

    public function getUsers()
    {
        return $this->call(User::where('id', '>', 0), 'name');
    }

    public function postFormat() {
        $field = Input::get('field') ?: 'text';
        $text = Input::input($field) ?: '';
        return app('bbcode')->Parse($text);
    }
}

?> 
<?php namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;

abstract class Controller extends BaseController {

	use ValidatesRequests;

    protected function permission($action, $permission) {
        $per = $permission === true ? 'LoggedIn' : $permission;

        $actions = [];

        if (!is_array($action)) $action = [$action];
        foreach ($action as $a) {
            $act = strtoupper(substr($a, 0, 1)) . substr($a, 1);
            $actions[] = $a;
            $actions[] = "get$act";
            $actions[] = "post$act";
        }

        $this->middleware('permission:'.$per, ['only' => $actions]);
    }
}

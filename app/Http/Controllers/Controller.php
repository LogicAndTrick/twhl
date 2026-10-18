<?php namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;

abstract class Controller extends BaseController {

	use ValidatesRequests;

    private array $defined_permissions = [];

    public function hasPermissionDefined(string $action) {
        return isset($this->defined_permissions[$action])
            && is_array($this->defined_permissions[$action])
            && count($this->defined_permissions[$action]) > 0;
    }

    protected function anonymous(string|array $action) {
        if (!is_array($action)) $action = [$action];
        foreach ($action as $a) {
            $act = strtoupper(substr($a, 0, 1)) . substr($a, 1);

            if (!isset($this->defined_permissions[$a])) $this->defined_permissions[$a] = [];
            $this->defined_permissions["get$act"][] = '*';
            $this->defined_permissions["post$act"][] = '*';
        }
    }

    protected function permission(string|array $action, string $permission) {
        $per = $permission === true ? 'LoggedIn' : $permission;

        $actions = [];

        if (!is_array($action)) $action = [$action];
        foreach ($action as $a) {
            $act = strtoupper(substr($a, 0, 1)) . substr($a, 1);
            $actions[] = $a;
            $actions[] = "get$act";
            $actions[] = "post$act";

            if (!isset($this->defined_permissions[$a])) $this->defined_permissions[$a] = [];
            $this->defined_permissions["get$act"][] = $permission;
            $this->defined_permissions["post$act"][] = $permission;
        }

        $this->middleware('permission:'.$per, ['only' => $actions]);
    }

    protected function missingMethod($parameters = []) {
        abort(404);
    }
}

<?php namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesCommands;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Route;

abstract class Controller extends BaseController {

	use DispatchesCommands, ValidatesRequests;

    private $auth = null;

    protected function permission($action, $permission) {
        if ($this->auth === null) {
            $this->beforeFilter('@validateAuth');
            $this->auth = array();
        }
        if (!is_array($action)) $action = [$action];
        foreach ($action as $a) {
            $act = strtolower($a);
            $this->auth[$act] = $permission;
            $this->auth["get$act"] = $permission;
            $this->auth["post$act"] = $permission;
        }
    }

    protected function loggedIn($action) {
        if ($this->auth === null) {
            $this->beforeFilter('@validateAuth');
            $this->auth = array();
        }
        if (!is_array($action)) $action = [$action];
        foreach ($action as $a) {
            $act = $a;
            $this->auth[$act] = true;
            $this->auth["get$act"] = true;
            $this->auth["post$act"] = true;
        }
    }

    public function validateAuth(Route $route, Request $request) {
        $name = strtolower(explode('@', $route->getActionName())[1]);
        if ($this->auth === null || !array_key_exists($name, $this->auth)) return;
        $permission = $this->auth[$name];
        $user = $request->user();
        if ($permission === true && !$user) abort(404);
        if (is_string($permission) && (!$user || !$user->hasPermission($permission))) abort(404);
    }

}

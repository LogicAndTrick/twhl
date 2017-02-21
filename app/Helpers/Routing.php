<?php namespace App\Helpers;

use Illuminate\Routing\Router;
use ReflectionClass;
use ReflectionMethod;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

/**
 * Class Routing
 * Because Laravel devs decided this useful thing isn't useful...
 * @package App\Helpers
 */
class Routing {


    /*
     * Borrowed from: Illuminate\Routing\Router @5.2
     */

    public static function controllers(array $controllers)
    {
        foreach ($controllers as $uri => $controller) {
            Routing::controller($uri, $controller);
        }
    }

    public static function controller($uri, $controller, $names = [])
    {
        /**
         * @var Router
         */
        $router = app('router');

        $prepended = $controller;

        // First, we will check to see if a controller prefix has been registered in
        // the route group. If it has, we will need to prefix it before trying to
        // reflect into the class instance and pull out the method for routing.
        if ($router->hasGroupStack()) {
            $prepended = Routing::prependGroupUses($router, $controller);
        }


        $routable = Routing::getRoutable($prepended, $uri);

        // When a controller is routed using this method, we use Reflection to parse
        // out all of the routable methods for the controller, then register each
        // route explicitly for the developers, so reverse routing is possible.
        foreach ($routable as $method => $routes) {
            foreach ($routes as $route) {
                Routing::registerInspected($router, $route, $controller, $method, $names);
            }
        }

        Routing::addFallthroughRoute($router, $controller, $uri);
    }

    /**
     * Prepend the last group uses onto the use clause.
     *
     * @param $router Router
     * @param  string  $uses
     * @return string
     */
    protected static function prependGroupUses($router, $uses)
    {
        $groupStack = $router->getGroupStack();
        $group = end($groupStack);
        return isset($group['namespace']) && strpos($uses, '\\') !== 0 ? $group['namespace'].'\\'.$uses : $uses;
    }

    /**
     * @param $router Router
     * @param $route
     * @param $controller
     * @param $method
     * @param $names
     */
    private static function registerInspected($router, $route, $controller, $method, &$names)
    {
        $action = ['uses' => $controller.'@'.$method];

        // If a given controller method has been named, we will assign the name to the
        // controller action array, which provides for a short-cut to method naming
        // so you don't have to define an individual route for these controllers.
        $action['as'] = Arr::get($names, $method);

        $router->{$route['verb']}($route['uri'], $action);
    }

    /**
     * @param $router Router
     * @param $controller
     * @param $uri
     */
    private static function addFallthroughRoute($router, $controller, $uri)
    {
        $missing = $router->any($uri.'/{_missing}', $controller.'@missingMethod');

        $missing->where('_missing', '(.*)');
    }

    /*
     * Borrowed from: Illuminate\Routing\ControllerInspector @5.2
     */

    /**
     * An array of HTTP verbs.
     *
     * @var array
     */
    private static $verbs = [
        'any', 'get', 'post', 'put', 'patch',
        'delete', 'head', 'options',
    ];

    /**
     * Get the routable methods for a controller.
     *
     * @param  string  $controller
     * @param  string  $prefix
     * @return array
     */
    private static function getRoutable($controller, $prefix)
    {
        $routable = [];

        $reflection = new ReflectionClass($controller);

        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

        // To get the routable methods, we will simply spin through all methods on the
        // controller instance checking to see if it belongs to the given class and
        // is a publicly routable method. If so, we will add it to this listings.
        foreach ($methods as $method) {
            if (Routing::isRoutable($method)) {
                $data = Routing::getMethodData($method, $prefix);

                $routable[$method->name][] = $data;

                // If the routable method is an index method, we will create a special index
                // route which is simply the prefix and the verb and does not contain any
                // the wildcard place-holders that each "typical" routes would contain.
                if ($data['plain'] == $prefix.'/index') {
                    $routable[$method->name][] = Routing::getIndexData($data, $prefix);
                }
            }
        }

        return $routable;
    }

    /**
     * Determine if the given controller method is routable.
     *
     * @param  \ReflectionMethod  $method
     * @return bool
     */
    private static function isRoutable(ReflectionMethod $method)
    {
        if ($method->class == 'Illuminate\Routing\Controller') {
            return false;
        }

        return Str::startsWith($method->name, Routing::$verbs);
    }

    /**
     * Get the method data for a given method.
     *
     * @param  \ReflectionMethod  $method
     * @param  string  $prefix
     * @return array
     */
    private static function getMethodData(ReflectionMethod $method, $prefix)
    {
        $verb = Routing::getVerb($name = $method->name);

        $uri = Routing::addUriWildcards($plain = Routing::getPlainUri($name, $prefix));

        return compact('verb', 'plain', 'uri');
    }

    /**
     * Get the routable data for an index method.
     *
     * @param  array   $data
     * @param  string  $prefix
     * @return array
     */
    private static function getIndexData($data, $prefix)
    {
        return ['verb' => $data['verb'], 'plain' => $prefix, 'uri' => $prefix];
    }

    /**
     * Extract the verb from a controller action.
     *
     * @param  string  $name
     * @return string
     */
    private static function getVerb($name)
    {
        return head(explode('_', Str::snake($name)));
    }

    /**
     * Determine the URI from the given method name.
     *
     * @param  string  $name
     * @param  string  $prefix
     * @return string
     */
    private static function getPlainUri($name, $prefix)
    {
        return $prefix.'/'.implode('-', array_slice(explode('_', Str::snake($name)), 1));
    }

    /**
     * Add wildcards to the given URI.
     *
     * @param  string  $uri
     * @return string
     */
    private static function addUriWildcards($uri)
    {
        return $uri.'/{one?}/{two?}/{three?}/{four?}/{five?}';
    }
}

<?php

namespace App\Helpers;

use ErrorException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionIntersectionType;
use ReflectionParameter;
use ReflectionUnionType;
use Illuminate\Routing\Router;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

/**
 * Class Routing
 * Because Laravel devs decided this useful thing isn't useful...
 * @package App\Helpers
 */
class Routing
{
    /*
     * Borrowed from: Illuminate\Routing\Router @5.2
     */

    public static function controllers(array $controllers)
    {
        foreach ($controllers as $uri => $controller) {
            Routing::controller($uri, $controller);
        }
    }

    public static function controller(string $uri, string $controller, array $names = [])
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
    }

    /**
     * Prepend the last group uses onto the use clause.
     */
    protected static function prependGroupUses(Router $router, string $uses): string
    {
        $groupStack = $router->getGroupStack();
        $group = end($groupStack);
        return isset($group['namespace']) && strpos($uses, '\\') !== 0 ? $group['namespace'] . '\\' . $uses : $uses;
    }

    /**
     * Register the controller method with the router
     */
    private static function registerInspected(Router $router, array $route, string $controller, string $method, array &$names): void
    {
        $action = ['uses' => $controller . '@' . $method];

        // If a given controller method has been named, we will assign the name to the
        // controller action array, which provides for a short-cut to method naming
        // so you don't have to define an individual route for these controllers.
        $action['as'] = Arr::get($names, $method);

        /** @var \Illuminate\Routing\Route */
        $rt = $router->{$route['verb']}($route['uri'], $action);
        if ($route['typed']) {
            foreach ($route['parameters'] as $par) {
                Routing::bindParameter($rt, $par);
            }
        }
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
        'any',
        'get',
        'post',
        'put',
        'patch',
        'delete',
        'head',
        'options',
    ];

    /**
     * Get the routable methods for a controller.
     *
     * @param  string  $controller
     * @param  string  $prefix
     * @return array
     */
    private static function getRoutable(string $controller, string $prefix)
    {
        $routable = [];

        $reflection = new ReflectionClass($controller);

        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

        $typed = count($reflection->getAttributes(TypedRouting::class)) > 0;

        /** @var \App\Http\Controllers\Controller|null */
        $instance = $typed ? $reflection->newInstance() : null;

        // To get the routable methods, we will simply spin through all methods on the
        // controller instance checking to see if it belongs to the given class and
        // is a publicly routable method. If so, we will add it to this listings.
        foreach ($methods as $method) {
            if (Routing::isRoutable($method)) {
                $data = Routing::getMethodData($reflection, $method, $prefix);

                $routable[$method->name][] = $data;

                if ($instance && !$instance->hasPermissionDefined($method->name)) {
                    throw new ErrorException("Controller method {$controller} @ {$method->name} has no permissions defined.");
                }

                // If the routable method is an index method, we will create a special index
                // route which is simply the prefix and the verb and does not contain any
                // the wildcard place-holders that each "typical" routes would contain.
                if ($data['plain'] == $prefix . '/index') {
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
     */
    private static function getMethodData(ReflectionClass $class, ReflectionMethod $method, string $prefix): array
    {
        $params = collect($method->getParameters())
            ->filter(Routing::isBindableParameter(...))
            ->map(function (ReflectionParameter $value) {
                return [
                    'index' => $value->getPosition(),
                    'name' => $value->getName(),
                    'optional' => $value->isOptional(),
                    'type' => $value->getType()?->getName(),
                ];
            })
            ->toArray();

        $typed = count($class->getAttributes(TypedRouting::class)) > 0;
        $name = $method->name;
        $verb = Routing::getVerb($name);
        $plain = Routing::getPlainUri($name, $prefix);
        $uri = $typed ? Routing::addUriParameters($plain, $params) : Routing::addUriWildcards($plain);
        return [
            'name' => $name,
            'verb' => $verb,
            'plain' => $plain,
            'uri' => $uri,
            'prefix' => $prefix,
            'method' => $method,
            'parameters' => $params,
            'typed' => $typed
        ];
    }

    /**
     * Check if a parameter is bindable to the route (i.e. must be a primitive)
     */
    private static function isBindableParameter(ReflectionParameter $param)
    {
        $type = $param->getType();
        if (!$type || $type instanceof ReflectionUnionType) return true;
        if ($type instanceof ReflectionIntersectionType) return false;

        $type_name = $type->getName();
        return $type_name == 'int' || $type_name == 'string';
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
        return [
            'name' => 'index',
            'verb' => $data['verb'],
            'plain' => $prefix,
            'uri' => $prefix,
            'prefix' => $prefix,
            'method' => $data['verb'] . 'Index',
            'parameters' => [],
            'typed' => false
        ];
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
        return $prefix . '/' . implode('-', array_slice(explode('_', Str::snake($name)), 1));
    }

    /**
     * Add wildcards to the given URI.
     */
    private static function addUriWildcards(string $uri): string
    {
        return $uri . '/{one?}/{two?}/{three?}/{four?}/{five?}';
    }

    /**
     * Add typed parameters to the URI
     */
    private static function addUriParameters(string $uri, array $params): string
    {
        $ret = $uri;
        foreach ($params as $par) {
            $ret .= '/{' . $par['name'] . ($par['optional'] ? '?' : '') . '}';
        }
        return $ret;
    }

    private static function bindParameter(\illuminate\routing\Route $route, array $par): void
    {
        $name = $par['name'];
        $type = $par['type'];
        switch ($type) {
            case 'int':
                $route->whereNumber($name);
                break;
            case 'string':
                // allow anything
                break;
            default:
                throw new ErrorException("Unsupported typed parameter: {$type}");
        }
    }
}

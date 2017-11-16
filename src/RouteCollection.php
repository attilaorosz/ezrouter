<?php
/**
 * Created by PhpStorm.
 * User: Attila
 * Date: 2017. 11. 16.
 * Time: 20:17
 */

namespace EzRouter;


class RouteCollection
{

    private $routes = [];

    public $globalParams = [];

    public $multiParams = [];

    /**
     * RouteCollection constructor.
     * @param array $routes
     */
    public function __construct(array $routes = [])
    {
        if ($routes !== [])
            $this->routes = $routes;
    }

    /**
     * @param array $params
     * @param callable $callback
     */
    public function group(array $params, callable $callback)
    {
        $oldGlobals = $this->globalParams;
        $this->globalParams = $this->mergeParams($oldGlobals, $params);
        $callback($this);
        $this->globalParams = $oldGlobals;
    }

    /**
     * @param $oldParams
     * @param $newParams
     * @return array
     */
    private function mergeParams($oldParams, $newParams)
    {
        $merged = array_merge_recursive($oldParams, $newParams);
        foreach ($merged as $k => &$v) {
            if (is_array($v) && !array_search($k, $this->multiParams)) {
                $v = $newParams[$k];
            }
        }

        return $merged;
    }

    /**
     * Add a route to the routes list
     *
     * @param string $requestMethod
     * @param string $route
     * @param $handler
     * @param string|array $params
     * @return $this RouteCollection
     */
    private function addRoute(string $requestMethod, string $route, $handler, array $params = [])
    {
        $r = new Route();

        if (!is_string($handler) && !is_callable($handler)) {
            throw new \InvalidArgumentException('Executable must be either a class and method name separated by a @ character or a callable!');
        }

        if (is_string($handler)) {
            if (!strpos($handler, '@')) {
                throw new \InvalidArgumentException('Controller should have a class and method name separated by a @ character!');
            }

            list($class, $method) = explode('@', $handler);

            $r->action = [$class, $method];
        } else {
            $r->action = $handler;
        }

        $r->method = $requestMethod;
        $r->uri = $route;

        $params = array_merge_recursive($params, $this->globalParams);
        foreach ($params as $k => $v) {
            $r->{$k} = $v;
        }

        if (!isset($this->routes[$requestMethod])) {
            $this->routes[$requestMethod] = [];
        }

        $this->routes[$requestMethod][] = $r;

        return $this;
    }

    /**
     * Get routes list
     * Pass request method to limit the routes to specific request method
     *
     * @param null|string $requestMethod
     * @return array
     */
    public function getRoutes(string $requestMethod = null)
    {
        if (null === $requestMethod) {
            return call_user_func_array('array_merge', $this->routes);
        }

        return $this->routes[$requestMethod];
    }

    /**
     * Register a GET method route
     *
     * @param string $route
     * @param $handler
     * @param array|string $params
     * @return RouteCollection
     */
    public function get($route, $handler, $params = [])
    {
        return $this->addRoute('GET', $route, $handler, $params);
    }

    /**
     * Register a POST method route
     *
     * @param string $route
     * @param $handler
     * @param array|string $params
     * @return RouteCollection
     */
    public function post($route, $handler, $params = [])
    {
        return $this->addRoute('POST', $route, $handler, $params);
    }

    /**
     * Register a PATCH method route
     *
     * @param string $route
     * @param $handler
     * @param array|string $params
     * @return RouteCollection
     */
    public function patch($route, $handler, $params = [])
    {
        return $this->addRoute('PATCH', $route, $handler, $params);
    }

    /**
     * Register a PUT method route
     *
     * @param string $route
     * @param $handler
     * @param array|string $params
     * @return RouteCollection
     */
    public function put($route, $handler, $params = [])
    {
        return $this->addRoute('PUT', $route, $handler, $params);
    }

    /**
     * Register a DELETE method route
     *
     * @param string $route
     * @param $handler
     * @param array|string $params
     * @return RouteCollection
     */
    public function delete($route, $handler, $params = [])
    {
        return $this->addRoute('DELETE', $route, $handler, $params);
    }

}
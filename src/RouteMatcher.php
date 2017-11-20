<?php

namespace EzRouter;


class RouteMatcher
{
    public $routes;

    public function __construct(RouteCollection $routes)
    {
        $this->routes = $routes;
    }

    /**
     * Find matching route to uri and method
     *
     * @param string $method
     * @param string $uri
     * @return Route|null
     */
    public function matchByRequest($method, $uri): ?Route
    {
        $segment_count = substr_count('/', $uri);

        foreach ($this->routes->getRoutes($method) as $route) {

            if ($segment_count === substr_count('/', $route->uri)) {

                $tester = '/^' . preg_replace('/[\/]/', '\\/',
                        preg_replace('/([{][a-zA-Z0-9_-]+[}])/', '([a-zA-Z0-9_-]+)', $route->uri, -1), -1) . '$/';

                $params = [];

                if (preg_match_all($tester, $uri, $matches)) {

                    preg_match_all('/[{][a-zA-Z0-9_-]+[}]/', $route->uri, $keys);

                    for ($i = 1, $iMax = count($matches); $i < $iMax; $i++) {
                        $params[substr($keys[0][$i - 1], 1, -1)] = $matches[$i][0];
                    }

                    $route->params = $params;

                    return $route;
                }
            }
        }

        return null;
    }

    /**
     * Find matching route to param
     *
     * @param string $param
     * @param string $val
     * @return Route|null
     */
    public function matchByParam(string $param, string $val): ?Route
    {
        foreach ($this->routes->getRoutes() as $route) {
            if (property_exists($route, $param) && $route->{$param} === $val) {
                return $route;
            }
        }

        return null;

    }

    /**
     * @param $param
     * @param $val
     * @param array $urlParams
     * @return string
     * @throws \Exception
     */
    public function buildUrlByParam($param, $val, array $urlParams = array()): string
    {
        /**
         * @var Route
         */
        $route = $this->matchByParam($param, $val);

        if ($route === null) {
            throw new \Exception("Route with parameter (" . $param . " = " . $val . ") not found");
        }

        if (count($urlParams) > 0) {

            $url = preg_replace_callback('/([{][a-zA-Z0-9]+[}])/', function () use (&$urlParams) {
                return array_shift($urlParams);
            }, $route->uri);
        } else {
            $url = $route->uri;
        }

        return $url;
    }
}
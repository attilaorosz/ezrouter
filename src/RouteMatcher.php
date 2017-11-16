<?php
/**
 * Created by PhpStorm.
 * User: Attila
 * Date: 2017. 11. 16.
 * Time: 20:18
 */

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
     * @internal param ServerRequestInterface $request
     */
    public function matchByRequest($method, $uri) : Route
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
                        $extras[substr($keys[0][$i - 1], 1, -1)] = $matches[$i][0];
                    }

                    $route->params = $params;

                    return $route;
                }
            }
        }

        return null;
    }

    /**
     * Find matching route to name
     *
     * @param string $name
     * @return null|Route
     */
    public function matchByName(string $name): Route
    {
        foreach ($this->routes->getRoutes() as $route) {
            if ($route->name !== "" && $route->name === $name) {
                return $route;
            }
        }

        return null;

    }

    /**
     * @param $name
     * @param array $params
     * @return string
     * @throws \Exception
     */
    public function buildUrlByName($name, array $params = array()) : string
    {
        /**
         * @var Route
         */
        $route = $this->matchByName($name);

        if ($route === null) {
            throw new \Exception("Route " . $name . " not found");
        }

        if (count($params) > 0) {

            $url = preg_replace_callback('/([{][a-zA-Z0-9]+[}])/', function () use (&$params) {
                return array_shift($params);
            }, $route->uri);
        } else {
            $url = $route->uri;
        }

        return $url;
    }
}
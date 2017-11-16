<?php

namespace EzRouter;


class Route
{

    public $uri;
    public $method;
    public $action;
    public $params = [];

    /**
     * Check whether route has specified property
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key)
    {
        return $this->{$key} !== null;
    }

    /**
     * Parse controller string and save controller class and controller action to route properties
     *
     * @param string $raw
     * @throws \Error
     */
    public function setController(string $raw)
    {
        if (strpos('@', $raw) === 0) {
            throw new \Error('Give a valid controller and action!');
        }

        $controllerParams = explode('@', $raw);
        $this->controller = $controllerParams[0];
        $this->action = $controllerParams[1];
    }

}
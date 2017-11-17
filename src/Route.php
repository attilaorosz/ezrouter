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

}
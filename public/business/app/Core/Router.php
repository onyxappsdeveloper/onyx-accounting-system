<?php
/**
 * ONYX Accounting System - Router Class
 * Handles routing and dispatching requests to controllers
 */

namespace App\Core;

class Router
{
    private $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => [],
        'PATCH' => []
    ];
    private $middleware = [];
    private $parameters = [];

    /**
     * Register GET route
     */
    public function get($path, $controller, $action = 'index')
    {
        $this->routes['GET'][$path] = [
            'controller' => $controller,
            'action' => $action
        ];
        return $this;
    }

    /**
     * Register POST route
     */
    public function post($path, $controller, $action = 'store')
    {
        $this->routes['POST'][$path] = [
            'controller' => $controller,
            'action' => $action
        ];
        return $this;
    }

    /**
     * Register PUT route
     */
    public function put($path, $controller, $action = 'update')
    {
        $this->routes['PUT'][$path] = [
            'controller' => $controller,
            'action' => $action
        ];
        return $this;
    }

    /**
     * Register DELETE route
     */
    public function delete($path, $controller, $action = 'destroy')
    {
        $this->routes['DELETE'][$path] = [
            'controller' => $controller,
            'action' => $action
        ];
        return $this;
    }

    /**
     * Register PATCH route
     */
    public function patch($path, $controller, $action = 'update')
    {
        $this->routes['PATCH'][$path] = [
            'controller' => $controller,
            'action' => $action
        ];
        return $this;
    }

    /**
     * Dispatch request to appropriate controller and action
     */
    public function dispatch($method, $uri)
    {
        if (!isset($this->routes[$method])) {
            throw new \Exception('Method not allowed', 405);
        }

        foreach ($this->routes[$method] as $route => $config) {
            if ($this->matchRoute($route, $uri)) {
                return $this->executeRoute($config, $uri, $route);
            }
        }

        throw new \Exception('Route not found', 404);
    }

    /**
     * Match route pattern with URI
     */
    private function matchRoute($pattern, $uri)
    {
        $pattern = preg_replace('/\{id\}/', '(?P<id>[0-9]+)', $pattern);
        $pattern = preg_replace('/\{slug\}/', '(?P<slug>[a-z0-9-]+)', $pattern);
        $pattern = preg_replace('/\{[a-z]+\}/', '(?P<[\\w]+>[a-z0-9-]+)', $pattern);
        $pattern = "^{$pattern}$";

        return preg_match("#{$pattern}#i", $uri, $this->parameters) === 1;
    }

    /**
     * Execute route
     */
    private function executeRoute($config, $uri, $route)
    {
        $controllerClass = "App\\Modules\\" . $config['controller'] . "\\Controllers\\" . $config['controller'] . "Controller";
        $action = $config['action'];

        if (!class_exists($controllerClass)) {
            throw new \Exception("Controller not found: $controllerClass", 404);
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $action)) {
            throw new \Exception("Action not found: $action in $controllerClass", 404);
        }

        $params = array_filter($this->parameters, 'is_string', ARRAY_FILTER_USE_KEY);
        
        return call_user_func_array([$controller, $action], $params);
    }

    /**
     * Get matched parameters
     */
    public function getParameters()
    {
        return array_filter($this->parameters, 'is_string', ARRAY_FILTER_USE_KEY);
    }
}

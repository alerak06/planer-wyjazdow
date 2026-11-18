<?php

declare(strict_types=1);

namespace Triptrav\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, array $action): void
    {
        $this->routes['GET'][$path] = $action;
    }

    public function post(string $path, array $action): void
    {
        $this->routes['POST'][$path] = $action;
    }


    public function dispatch(): void
{
    $method = $_SERVER['REQUEST_METHOD'];
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);


    foreach($this->routes[$method] ?? [] as $path => $action)
    {

        if($path === $uri)
        {
            [$controller, $actionName] = $action;

            $controller = new $controller();

            $controller->$actionName();

            return;
        }


        $pattern = preg_replace(
            '#\{[a-zA-Z_]+\}#',
            '([^/]+)',
            $path
        );


        if(preg_match("#^".$pattern."$#", $uri, $matches))
        {

            array_shift($matches);


            [$controller, $actionName] = $action;

            $controller = new $controller();


            $controller->$actionName(...$matches);

            return;
        }

    }


    http_response_code(404);

    echo "404 - Nie znaleziono strony";
}
}
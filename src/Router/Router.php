<?php
namespace App\Router;

use App\Exception\RouteNotFoundException;

class Router
{
    /** @var array<string, array{controller: string, action: string, options: array<string, mixed>}> */
    private array $routes = [];
    
    public function __construct()
    {
    }

    /**
     * @param array<string, mixed> $options
     */
    public function add(string $path, string $controller, string $action, array $options = []): void
    {
        $this->routes[$path] = [
            'controller' => $controller,
            'action' => $action,
            'options' => array_merge([
                'methods' => ['GET'],
                'auth' => false
            ], $options)
        ];
    }

    /**
     * @return array{controller: string, action: string, params: array<string, string>}
     * @throws RouteNotFoundException
     */
    public function match(string $url, string $method): array
    {
        $url = trim($url, '/');
        
        foreach ($this->routes as $path => $route) {
            if ($this->matchRoute($path, $url) && 
                in_array($method, $route['options']['methods'])) {
                return array_merge($route, ['params' => $this->extractParams($path, $url)]);
            }
        }
        
        throw new RouteNotFoundException("No route found for the given URL and method.");
    }

    private function matchRoute(string $path, string $url): bool
    {
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $path);
        return (bool) preg_match("#^$pattern$#", $url);
    }

    /**
     * @return array<string, string>
     */
    private function extractParams(string $path, string $url): array
    {
        $params = [];
        $pathParts = explode('/', $path);
        $urlParts = explode('/', $url);

        foreach ($pathParts as $index => $part) {
            if (preg_match('/\{([^}]+)\}/', $part, $matches)) {
                $params[$matches[1]] = $urlParts[$index];
            }
        }

        return $params;
    }
}

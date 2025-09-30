<?php

class Router
{
    protected $routes = array();

    public function add($method, $pattern, $callback)
    {
        $this->routes[] = array(
            'method' => strtoupper($method),
            'pattern' => $pattern,
            'callback' => $callback
        );
    }

    public function dispatch()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = strtoupper($_SERVER['REQUEST_METHOD']);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = '@^' . preg_replace('@\{([a-zA-Z0-9_]+)\}@', '(?P<$1>[^/]+)', $route['pattern']) . '$@';

            if (preg_match($pattern, $uri, $matches)) {
                $params = array();
                foreach ($matches as $key => $value) {
                    if (!is_int($key)) {
                        $params[$key] = rawurldecode($value);
                    }
                }
                return call_user_func($route['callback'], $params);
            }
        }

        header("HTTP/1.0 404 Not Found");
        echo 'Страница не найдена';
    }
}

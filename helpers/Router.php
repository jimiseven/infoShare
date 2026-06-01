<?php
declare(strict_types=1);

class Router
{
    private array $routes = [];

    public function add(string $name, string $method, callable $handler, array $middleware = [], bool $csrf = false): void
    {
        $this->routes[$name] = [
            'method' => strtoupper($method),
            'handler' => $handler,
            'middleware' => $middleware,
            'csrf' => $csrf,
        ];
    }

    public function dispatch(string $name, string $method): void
    {
        if (!isset($this->routes[$name])) {
            $this->error(404, 'Ruta no encontrada');
            return;
        }

        $route = $this->routes[$name];
        $requestMethod = strtoupper($method);
        if ($route['method'] !== 'ANY' && $route['method'] !== $requestMethod) {
            $this->error(405, 'Metodo no permitido');
            return;
        }

        foreach ($route['middleware'] as $mw) {
            $mw();
        }

        if ($route['csrf'] && $requestMethod === 'POST') {
            Csrf::validateOrFail($_POST['_token'] ?? '');
        }

        $handler = $route['handler'];
        try {
            $handler();
        } catch (Throwable $e) {
            $this->error(500, 'Error interno');
        }
    }

    private function error(int $code, string $message): void
    {
        http_response_code($code);
        $file = __DIR__ . '/../views/errors/' . $code . '.php';
        if (file_exists($file)) {
            $errorMessage = $message;
            include $file;
            return;
        }
        echo $message;
    }
}

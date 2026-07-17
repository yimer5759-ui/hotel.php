<?php
/**
 * Front-Controller Router
 *
 * Parses: GET /segment1/segment2/...
 * Maps to: {Segment1}Controller -> method{Segment2 | index}()
 *
 * Examples:
 *   /                   → PublicController::index()
 *   /auth/login         → AuthController::login()
 *   /admin/dashboard    → AdminController::dashboard()
 *   /admin/rooms/add    → RoomController::add()
 *   /admin/rooms/edit/5 → RoomController::edit(5)
 */

class Router
{
    private array $routes = [];

    public function get(string $pattern, string $controller, string $method): void
    {
        $this->routes[] = ['GET',  $pattern, $controller, $method];
    }

    public function post(string $pattern, string $controller, string $method): void
    {
        $this->routes[] = ['POST', $pattern, $controller, $method];
    }

    public function any(string $pattern, string $controller, string $method): void
    {
        $this->routes[] = ['ANY',  $pattern, $controller, $method];
    }

    public function dispatch(): void
    {
        $uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $base   = parse_url(APP_URL, PHP_URL_PATH);
        $uri    = '/' . ltrim(substr($uri, strlen($base)), '/');
        $method = $_SERVER['REQUEST_METHOD'];

        foreach ($this->routes as [$verb, $pattern, $controller, $action]) {
            $regex  = '#^' . preg_replace('/\{[^}]+\}/', '([^/]+)', $pattern) . '$#';
            if (!preg_match($regex, $uri, $matches)) continue;
            if ($verb !== 'ANY' && $verb !== $method) continue;

            array_shift($matches);   // remove full match

            if (!class_exists($controller)) {
                $this->abort(500, "Controller {$controller} not found.");
            }

            $obj = new $controller();
            if (!method_exists($obj, $action)) {
                $this->abort(500, "Method {$action} not found in {$controller}.");
            }

            call_user_func_array([$obj, $action], $matches);
            return;
        }

        $this->abort(404, "Page Not Found");
    }

    private function abort(int $code, string $message): never
    {
        http_response_code($code);
        // Try to render a nice error page
        $viewFile = VIEWS_PATH . '/errors/' . $code . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            echo "<h1>{$code}</h1><p>{$message}</p><a href='" . APP_URL . "'>Go Home</a>";
        }
        exit;
    }
}

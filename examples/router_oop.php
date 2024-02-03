<?php

session_start();

include __DIR__ . "/../src/matrix.php";

use divengine\matrix;

// Simple router implementation


class Route
{
    public string $route;
    public string $controller;
    public bool $public;
    public Closure $match;
    public Closure $pass;
    public Closure $handler;
    public Closure $handlerPath;
    public Closure $listener;
    public string $caption;

    public function __construct(
        matrix $context,
        string $route,
        string $controller,
        bool $public = true,
        Closure $match = null,
        Closure $pass = null,
        Closure $handler = null,
        Closure $handlerPath = null,
        Closure $listener = null,
        string $caption = ''
    ) {
        $this->route = $route;
        $this->controller = $controller;
        $this->public = $public;
        $this->match = $match ?? fn ($row, $c, $m) => self::match($m->{"$row.route"});
        $this->pass = $pass ?? fn ($row, $c, $m) => ($context->{1.1} || $m->{"$row.match"}) && $m->{"$row.public"};
        $this->handler = $handler ?? fn ($row, $c, $m) => $m->{"$row.pass"} ? $m->{"$row.controller"} : 'login';
        $this->handlerPath = $handlerPath ?? fn ($r, $c, $m) => __DIR__ . "/handlers/{$m->{"$r.handler"}}.php";
        $this->listener = $listener ?? fn ($r, $c, matrix $m) => $m->{"$r.match"} ? (require($m->{"$r.handlerPath"}))() : null;
        $this->caption = $caption;
    }

    public static function match($route)
    {
        $uri = $_SERVER['REQUEST_URI'] ?? "";

        if ($route == "*")
            return true;

        $uri = str_replace(basename(__FILE__), "", $uri);
        return $route == $uri;
    }

    public static function isLogged()
    {
        return isset($_SESSION['logged']) && $_SESSION['logged'] == true;
    }
}

$context = new matrix([
    ["Variable",                      "Data"],
    ["is_logged", fn () => Route::isLogged()]
]);

$routes = [
    new Route($context, "/",      "home",  caption: "Home"),
    new Route($context, "/about", "about", caption: "About"),
    new Route($context, "/login", "login", caption: "Login"),
    new Route($context, "/admin", "admin", false, caption: "Admin"),
    new Route($context, "*",      "log"),
    new Route(
        context: $context,
        route: "*",
        controller: "404",
        match: fn ($r, $c, matrix $m) => array_reduce($m->vertical($c, 0, $r - 2), fn ($x, $i) => $x && !$i, true),
        caption: ""
    )
];

// print menu
echo "<p>" . array_reduce($routes, function ($x, $route) {
    $caption = $route->caption ?? '';
    $path = $route->route;

    if ($caption == "")
        return $x;

    return $x . "<a href='$path'>$caption</a> | ";
}, "") . "</p>";

$router = new matrix($routes);

echo "<script>console.log(" . $router->formatJSON() . ");</script>";

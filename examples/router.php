<?php

session_start();

include __DIR__ . "/../src/matrix.php";

use divengine\matrix;

// Simple router implementation
function isLogged()
{
    return isset($_SESSION['logged']) && $_SESSION['logged'] == true;
}

function matchRoute($route)
{
    $uri = $_SERVER['REQUEST_URI'] ?? "";

    if ($route == "*")
        return true;

    $uri = str_replace(basename(__FILE__), "", $uri);
    return $route == $uri;
}

$context = new matrix([
    ["Variable",               "Data"],
    ["is_logged", fn () => isLogged()]
]);

$F_MATCH = fn ($r, $c, $m) => matchRoute($m->{$r});
$F_PASS = fn ($r, $c, $m) => ($context->{1.1} || $m->{$r + .2}) && $m->{$r + .3};
$F_HANDLER = fn ($r, $c, $m) => $m->{$r + .4} ? $m->{$r + .1} : 'login';
$F_HANDLER_PATH = fn ($r, $c, $m) => __DIR__ . "/handlers/{$m->{$r + .5}}.php";
$F_LISTENER = fn ($r, $c, matrix $m) => $m->{$r + .3} ? (require($m->{$r + .6}))() : null;
$F_NOTHING_MATCH = fn($r, $c, matrix $m) => array_reduce($m->vertical($c, 1, $r - 2), fn ($x, $i) => $x && !$i, true);

$routes = [
    ["Route",  "Controller", "Public",          "Match", "Pass",   "Handler",  "Handler Path",  "Listener", 'caption' => ""],
    // -------------------------------------------------------------------------------------------------------------------------
    ["/",      "home",        true,            $F_MATCH, $F_PASS, $F_HANDLER, $F_HANDLER_PATH, $F_LISTENER, 'caption' => "Home"],
    ["/about", "about",       true,            $F_MATCH, $F_PASS, $F_HANDLER, $F_HANDLER_PATH, $F_LISTENER, 'caption' => "About"],
    ["/login", "login",       true,            $F_MATCH, $F_PASS, $F_HANDLER, $F_HANDLER_PATH, $F_LISTENER, 'caption' => "Login"],
    ["/admin", "admin",       false,           $F_MATCH, $F_PASS, $F_HANDLER, $F_HANDLER_PATH, $F_LISTENER, 'caption' => "Admin"],
    // -----------------------------------------------------------------------------------------------------
    ["*",      "log",         true,            $F_MATCH, $F_PASS, $F_HANDLER, $F_HANDLER_PATH, $F_LISTENER, 'caption' => ""],
    ["*",      "404",         true,    $F_NOTHING_MATCH, $F_PASS, $F_HANDLER, $F_HANDLER_PATH, $F_LISTENER, 'caption' => ""]
];

// print menu
echo "<p>". array_reduce($routes, function ($x, $route) {
    $caption = $route['caption'] ?? '';
    $path = $route[0];
    
    if ($caption == "")
        return $x;

    return $x . "<a href='$path'>$caption</a> | ";
}, "") . "</p>";

$router = new matrix($routes);

echo "<script>console.log(" . $router->formatJSON(true) . ");</script>";

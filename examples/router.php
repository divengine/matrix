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
$F_NOTHING_MATCH = fn ($r, $c, matrix $m) => array_reduce($m->vertical($c, 1, $r - 3), fn ($c, $i) => !$c && !$i, false);

$routes = new matrix([
    ["Route",  "Controller", "Public",          "Match", "Pass",   "Handler",  "Handler Path",  "Listener"],
    // -----------------------------------------------------------------------------------------------------
    ["/",      "home",        true,            $F_MATCH, $F_PASS, $F_HANDLER, $F_HANDLER_PATH, $F_LISTENER],
    ["/about", "about",       true,            $F_MATCH, $F_PASS, $F_HANDLER, $F_HANDLER_PATH, $F_LISTENER],
    ["/login", "login",       true,            $F_MATCH, $F_PASS, $F_HANDLER, $F_HANDLER_PATH, $F_LISTENER],
    ["/admin", "admin",       false,           $F_MATCH, $F_PASS, $F_HANDLER, $F_HANDLER_PATH, $F_LISTENER],
    // -----------------------------------------------------------------------------------------------------
    ["*",      "log",         true,            $F_MATCH, $F_PASS, $F_HANDLER, $F_HANDLER_PATH, $F_LISTENER],
    ["*",      "404",         true,    $F_NOTHING_MATCH, $F_PASS, $F_HANDLER, $F_HANDLER_PATH, $F_LISTENER]
]);

echo "<script>console.log(" . $routes->formatJSON(true) . ");</script>";

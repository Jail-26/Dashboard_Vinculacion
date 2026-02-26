<?php
// Initialize session and load environment variables if available
session_start();
// Attempt to load .env via composer autoload (if developer chose to use it)
if (file_exists(__DIR__ . '/load_env.php')) {
    require_once __DIR__ . '/load_env.php';
}

$maxRequests = 100;
$timeWindow = 900;

if (!isset($_SESSION['requests'])) {
    $_SESSION['requests'] = [];
}

$now = time();
$_SESSION['requests'] = array_filter($_SESSION['requests'], function($timestamp) use ($now, $timeWindow) {
    return ($now - $timestamp) < $timeWindow;
});

if (count($_SESSION['requests']) >= $maxRequests) {
    http_response_code(429);
    die(json_encode(["error" => "Demasiadas peticiones, intenta más tarde."]));
}

$_SESSION['requests'][] = $now;

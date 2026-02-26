<?php
// Load .env when present (optional, requires composer install)
$envFile = dirname(__DIR__) . '/.env';
$vendorAutoload = dirname(__DIR__) . '/vendor/autoload.php';

if (file_exists($vendorAutoload)) {
    require_once $vendorAutoload;
    if (file_exists($envFile)) {
        try {
            $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
            $dotenv->safeLoad();
        } catch (Exception $e) {
            error_log('Could not load .env: ' . $e->getMessage());
        }
    }
}

?>

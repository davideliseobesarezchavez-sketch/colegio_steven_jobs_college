<?php
// Minimal autoloader for this example package.
spl_autoload_register(function($class) {
    $prefixes = [
        'PHPMailer\\\\PHPMailer\\\\' => __DIR__ . '/phpmailer/src/',
        'Psr\\\\Log\\\\' => __DIR__ . '/psr/log/src/',
        'App\\\\' => __DIR__ . '/../../src/'
    ];
    foreach ($prefixes as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }
        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
    // fallback: try class name to file mapping
    $file = __DIR__ . '/phpmailer/src/' . str_replace('\\\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

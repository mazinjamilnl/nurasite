<?php
declare(strict_types=1);

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$requestPath = rawurldecode($requestPath);

if ($requestPath === '/' || strtolower($requestPath) === '/index.php') {
    require __DIR__ . '/index.php';
    return true;
}

if (preg_match('#^/(?:app|storage|vendor|\.git)(?:/|$)#i', $requestPath) === 1) {
    http_response_code(404);
    echo 'Not Found';
    return true;
}

if (str_starts_with(strtolower($requestPath), '/public/')) {
    $publicRoot = realpath(__DIR__ . DIRECTORY_SEPARATOR . 'public');
    $requestedFile = realpath(__DIR__ . str_replace('/', DIRECTORY_SEPARATOR, $requestPath));
    if ($publicRoot !== false && $requestedFile !== false
        && str_starts_with($requestedFile, $publicRoot . DIRECTORY_SEPARATOR)
        && is_file($requestedFile)) {
        return false;
    }
}

http_response_code(404);
echo 'Not Found';
return true;

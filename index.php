<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/app/Models/ContactRequest.php';
$page = (string)($_GET['page'] ?? 'home');
$controllers = [
    'home' => ['HomeController.php', 'HomeController'],
    'diensten' => ['ServicesController.php', 'ServicesController'],
    'over-mij' => ['AboutController.php', 'AboutController'],
    'contact' => ['ContactController.php', 'ContactController'],
];

if (!isset($controllers[$page])) {
    http_response_code(404);
    $page = 'home';
}

[$file, $class] = $controllers[$page];
require_once __DIR__ . '/app/Controllers/' . $file;
(new $class(...($page === 'contact' ? [new ContactRequest()] : [])))->handle();

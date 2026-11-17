<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/app/Models/ContactRequest.php';
require_once __DIR__ . '/app/Controllers/HomeController.php';

$controller = new HomeController(new ContactRequest());
$controller->handle();

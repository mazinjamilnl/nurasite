<?php
declare(strict_types=1);

require_once __DIR__ . '/app/Services/AppLogger.php';
AppLogger::initialize();

set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) return false;
    AppLogger::error('PHP runtime error', [
        'severity' => $severity,
        'message' => $message,
        'file' => $file,
        'line' => $line,
    ]);
    return false;
});

set_exception_handler(static function (Throwable $exception): void {
    AppLogger::exception($exception);
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=UTF-8');
    }
    echo 'Er is een onverwachte fout opgetreden. Bekijk het lokale applicatielogbestand.';
});

register_shutdown_function(static function (): void {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR], true)) {
        AppLogger::error('Fatal PHP error', [
            'severity' => $error['type'],
            'message' => $error['message'],
            'file' => $error['file'],
            'line' => $error['line'],
        ]);
    }
});

session_start();
require_once __DIR__ . '/app/Contracts/MailerInterface.php';
require_once __DIR__ . '/app/Services/SmtpMailer.php';
require_once __DIR__ . '/app/Services/TestMailer.php';
require_once __DIR__ . '/app/Models/ContactRequest.php';
require_once __DIR__ . '/app/Validation/ContactRequestValidator.php';
$mailConfigFile = __DIR__ . '/app/config.local.php';
$appConfig = require is_file($mailConfigFile) ? $mailConfigFile : __DIR__ . '/app/config.example.php';
$mailDriver = strtolower((string)($appConfig['mail']['driver'] ?? 'test'));
$mailer = $mailDriver === 'smtp'
    ? new SmtpMailer($appConfig['mail'] ?? [])
    : new TestMailer(__DIR__ . '/storage/test-mail.jsonl');
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
$currentPage = $page;
require_once __DIR__ . '/app/Controllers/' . $file;
$controller = match ($page) {
    'contact' => new ContactController(new ContactRequest($mailer), new ContactRequestValidator()),
    default => new $class(),
};
$controller->handle();

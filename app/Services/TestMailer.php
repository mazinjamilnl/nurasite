<?php
declare(strict_types=1);

/** Local mail strategy for testing without sending real email. */
final class TestMailer implements MailerInterface
{
    public function __construct(private string $logFile) {}

    public function send(array $input, ?array $attachment = null): array
    {
        $directory = dirname($this->logFile);
        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
            AppLogger::error('Test mail storage directory could not be created.');
            return ['status' => 'failed'];
        }

        $record = [
            'created_at' => gmdate(DATE_ATOM),
            'request' => $input,
            'attachment' => $attachment === null ? null : [
                'name' => $attachment['name'],
                'mime' => $attachment['mime'],
                'size' => is_file($attachment['path']) ? filesize($attachment['path']) : null,
            ],
        ];
        $json = json_encode($record, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false || file_put_contents($this->logFile, $json . PHP_EOL, FILE_APPEND | LOCK_EX) === false) {
            AppLogger::error('Test mail record could not be written.');
            return ['status' => 'failed'];
        }

        return ['status' => 'test_saved'];
    }
}

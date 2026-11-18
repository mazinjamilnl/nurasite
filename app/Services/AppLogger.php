<?php
declare(strict_types=1);

/** Writes diagnostic events outside the website's document root. */
final class AppLogger
{
    private static string $logFile;
    private static bool $writing = false;

    public static function initialize(): void
    {
        self::$logFile = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR
            . 'storage' . DIRECTORY_SEPARATOR . 'log.txt';

        $directory = dirname(self::$logFile);
        if (!is_dir($directory)) {
            @mkdir($directory, 0700, true);
        }
        if (is_dir($directory) && !is_file(self::$logFile)) {
            @touch(self::$logFile);
        }
    }

    public static function path(): string
    {
        return self::$logFile ?? (dirname(__DIR__, 2) . DIRECTORY_SEPARATOR
            . 'storage' . DIRECTORY_SEPARATOR . 'log.txt');
    }

    public static function error(string $message, array $context = []): void
    {
        self::write('ERROR', $message, $context);
    }

    public static function exception(Throwable $exception, string $label = 'Uncaught exception'): void
    {
        self::write('EXCEPTION', $label, [
            'type' => $exception::class,
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);
    }

    private static function write(string $level, string $message, array $context = []): void
    {
        if (self::$writing) return;
        self::$writing = true;

        try {
            $directory = dirname(self::path());
            if (!is_dir($directory) && !@mkdir($directory, 0700, true) && !is_dir($directory)) {
                error_log('[AppLogger] Could not create the private log directory.');
                return;
            }

            $lines = [
                str_repeat('-', 72),
                gmdate('Y-m-d H:i:s') . ' UTC  [' . $level . ']  ' . self::singleLine($message),
            ];
            foreach ($context as $key => $value) {
                $safeValue = preg_match('/password|secret|token|credential|authorization/i', (string)$key) === 1
                    ? '[REDACTED]'
                    : (is_scalar($value) || $value === null ? self::singleLine((string)$value) : '[omitted]');
                $lines[] = '  ' . ucfirst((string)$key) . ': ' . $safeValue;
            }

            if (@file_put_contents(self::path(), implode(PHP_EOL, $lines) . PHP_EOL, FILE_APPEND | LOCK_EX) === false) {
                error_log('[AppLogger] Could not write to the private log file.');
            }
        } catch (Throwable) {
            error_log('[AppLogger] An error occurred while writing the private log.');
        } finally {
            self::$writing = false;
        }
    }

    private static function singleLine(string $value): string
    {
        return trim((string)preg_replace('/[\r\n\t]+/', ' ', $value));
    }
}

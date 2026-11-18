<?php
declare(strict_types=1);

final class SmtpMailer implements MailerInterface
{
    public function __construct(private array $config) {}

    public function send(array $input, ?array $attachment = null): array
    {
        $host = trim((string)($this->config['host'] ?? ''));
        $port = (int)($this->config['port'] ?? 587);
        $username = trim((string)($this->config['username'] ?? ''));
        // Google displays app passwords in groups separated by spaces; SMTP
        // authentication expects the 16-character value without whitespace.
        $password = preg_replace('/\s+/', '', (string)($this->config['password'] ?? '')) ?? '';
        // Gmail requires the From address to match the authenticated account.
        // The visitor's form email is supplied separately as Reply-To below.
        $from = $username;
        $recipient = trim((string)($this->config['to_email'] ?? ''));
        $encryption = strtolower(trim((string)($this->config['encryption'] ?? 'tls')));

        if ($host === '' || $username === '' || $password === '' || !filter_var($from, FILTER_VALIDATE_EMAIL) || !filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            AppLogger::error('SMTP settings are incomplete or contain an invalid email address.');
            return ['status' => 'not_configured'];
        }
        if ($encryption !== 'tls' || $port !== 587) {
            AppLogger::error('SMTP encryption or port is unsupported.', ['port' => $port, 'encryption' => $encryption]);
            return ['status' => 'not_configured'];
        }

        $subject = 'Website aanvraag — ' . str_replace(["\r", "\n"], '', $input['service']);
        $body = "Naam: {$input['name']}\r\nE-mail: {$input['email']}\r\nDienst: {$input['service']}\r\n\r\nBericht:\r\n{$input['message']}";
        $headers = [
            'From: Website contact <' . $from . '>',
            'To: ' . $recipient,
            'Reply-To: ' . $input['email'],
            'Subject: =?UTF-8?B?' . base64_encode($subject) . '?=',
            'MIME-Version: 1.0',
        ];

        if ($attachment === null) {
            $headers[] = 'Content-Type: text/plain; charset=UTF-8';
            $headers[] = 'Content-Transfer-Encoding: base64';
            $message = implode("\r\n", $headers) . "\r\n\r\n" . chunk_split(base64_encode($body), 76, "\r\n");
        } else {
            $boundary = '=_Website_' . bin2hex(random_bytes(18));
            $headers[] = 'Content-Type: multipart/mixed; boundary="' . $boundary . '"';
            $message = implode("\r\n", $headers) . "\r\n\r\n"
                . "--{$boundary}\r\n"
                . "Content-Type: text/plain; charset=UTF-8\r\n"
                . "Content-Transfer-Encoding: base64\r\n\r\n"
                . chunk_split(base64_encode($body), 76, "\r\n")
                . "--{$boundary}\r\n"
                . 'Content-Type: ' . $attachment['mime'] . '; name="' . $attachment['name'] . "\"\r\n"
                . "Content-Transfer-Encoding: base64\r\n"
                . 'Content-Disposition: attachment; filename="' . $attachment['name'] . "\"\r\n\r\n"
                . chunk_split(base64_encode((string)file_get_contents($attachment['path'])), 76, "\r\n")
                . "--{$boundary}--\r\n";
        }

        return $this->deliver($host, $port, $username, $password, $recipient, $from, $message);
    }

    private function deliver(string $host, int $port, string $username, string $password, string $recipient, string $from, string $message): array
    {
        $context = stream_context_create(['ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
            'peer_name' => $host,
        ]]);
        $socket = @stream_socket_client('tcp://' . $host . ':' . $port, $errorNumber, $errorText, 20, STREAM_CLIENT_CONNECT, $context);
        if (!is_resource($socket)) {
            AppLogger::error('SMTP connection failed', ['code' => $errorNumber, 'reason' => $errorText]);
            return ['status' => 'failed'];
        }
        stream_set_timeout($socket, 20);

        try {
            $this->expect($socket, [220]);
            $this->command($socket, 'EHLO localhost', [250]);
            $this->command($socket, 'STARTTLS', [220]);
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('SMTP TLS negotiation failed.');
            }
            $this->command($socket, 'EHLO localhost', [250]);
            $this->command($socket, 'AUTH LOGIN', [334]);
            $this->command($socket, base64_encode($username), [334]);
            $this->command($socket, base64_encode($password), [235]);
            $this->command($socket, 'MAIL FROM:<' . $from . '>', [250]);
            $this->command($socket, 'RCPT TO:<' . $recipient . '>', [250, 251]);
            $this->command($socket, 'DATA', [354]);

            $message = preg_replace('/(?m)^\./', '..', rtrim($message, "\r\n")) . "\r\n.\r\n";
            if (fwrite($socket, $message) === false) throw new RuntimeException('SMTP message write failed.');
            $this->expect($socket, [250]);
            $this->command($socket, 'QUIT', [221]);
            return ['status' => 'success'];
        } catch (Throwable $error) {
            AppLogger::exception($error, 'SMTP delivery failed');
            return ['status' => 'failed'];
        } finally {
            if (is_resource($socket)) fclose($socket);
        }
    }

    private function command($socket, string $command, array $expectedCodes): string
    {
        if (fwrite($socket, $command . "\r\n") === false) throw new RuntimeException('SMTP command write failed.');
        return $this->expect($socket, $expectedCodes);
    }

    private function expect($socket, array $expectedCodes): string
    {
        $response = '';
        do {
            $line = fgets($socket, 1024);
            if ($line === false) throw new RuntimeException('SMTP server closed the connection.');
            $response .= $line;
        } while (preg_match('/^\d{3}-/', $line) === 1);

        $code = (int)substr($line, 0, 3);
        if (!in_array($code, $expectedCodes, true)) {
            $safeResponse = preg_replace('/[\r\n]+/', ' ', trim($response));
            throw new RuntimeException('SMTP server returned: ' . $safeResponse);
        }
        return $response;
    }
}

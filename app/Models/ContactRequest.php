<?php
declare(strict_types=1);

final class ContactRequest
{
    public function send(array $input): array
    {
        $name = trim((string)($input['name'] ?? ''));
        $email = trim((string)($input['email'] ?? ''));
        $service = trim((string)($input['service'] ?? ''));
        $message = trim((string)($input['message'] ?? ''));
        $honeypot = trim((string)($input['website'] ?? ''));

        if ($honeypot !== '') return ['status' => 'success'];
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $service === '' || $message === '') {
            return ['status' => 'invalid'];
        }

        $recipient = getenv('CONTACT_EMAIL') ?: '';
        if ($recipient === '') return ['status' => 'not_configured'];

        $safeName = str_replace(["\r", "\n"], '', $name);
        $subject = 'Website aanvraag — ' . $service;
        $body = "Naam: {$safeName}\nE-mail: {$email}\nDienst: {$service}\n\nBericht:\n{$message}";
        $headers = ['From: Website contact <no-reply@' . ($_SERVER['SERVER_NAME'] ?? 'localhost') . '>', 'Reply-To: ' . $email, 'Content-Type: text/plain; charset=UTF-8'];

        return ['status' => mail($recipient, $subject, $body, implode("\r\n", $headers)) ? 'success' : 'failed'];
    }
}

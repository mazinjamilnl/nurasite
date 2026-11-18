<?php
declare(strict_types=1);

final class ContactRequest
{
    public function __construct(private MailerInterface $mailer) {}

    public function send(array $input, ?array $attachment = null): array
    {
        return $this->mailer->send($input, $attachment);
    }
}

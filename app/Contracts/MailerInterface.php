<?php
declare(strict_types=1);

/**
 * Contract for interchangeable mail delivery strategies.
 */
interface MailerInterface
{
    /** @return array{status: string} */
    public function send(array $input, ?array $attachment = null): array;
}

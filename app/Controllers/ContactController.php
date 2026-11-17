<?php
declare(strict_types=1);

final class ContactController
{
    public function __construct(private ContactRequest $contactRequest) {}

    public function handle(): void
    {
        $status = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $status = $this->contactRequest->send($_POST)['status'];
        }
        $pageTitle = 'Contact — Woorden verbinden';
        $view = __DIR__ . '/../Views/contact.php';
        require __DIR__ . '/../Views/layout.php';
    }
}

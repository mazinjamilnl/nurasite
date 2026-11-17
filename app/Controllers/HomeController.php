<?php
declare(strict_types=1);

final class HomeController
{
    public function __construct(private ContactRequest $contactRequest) {}

    public function handle(): void
    {
        $status = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->contactRequest->send($_POST);
            $status = $result['status'];
        }
        require __DIR__ . '/../Views/home.php';
    }
}

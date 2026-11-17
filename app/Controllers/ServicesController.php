<?php
declare(strict_types=1);

final class ServicesController
{
    public function handle(): void
    {
        $pageTitle = 'Diensten — Woorden verbinden';
        $view = __DIR__ . '/../Views/services.php';
        require __DIR__ . '/../Views/layout.php';
    }
}

<?php
declare(strict_types=1);

final class ServicesController
{
    public function handle(): void
    {
        $currentPage = 'diensten';
        $pageTitle = 'Diensten — Woorden verbinden';
        $view = __DIR__ . '/../Views/services.php';
        require __DIR__ . '/../Views/layout.php';
    }
}

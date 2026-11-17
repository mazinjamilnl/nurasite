<?php
declare(strict_types=1);

final class HomeController
{
    public function handle(): void
    {
        $pageTitle = 'Woorden verbinden — Vertaler & tolk';
        $view = __DIR__ . '/../Views/home.php';
        require __DIR__ . '/../Views/layout.php';
    }
}

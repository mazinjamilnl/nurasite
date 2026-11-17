<?php
declare(strict_types=1);

final class AboutController
{
    public function handle(): void
    {
        $pageTitle = 'Over mij — Woorden verbinden';
        $view = __DIR__ . '/../Views/about.php';
        require __DIR__ . '/../Views/layout.php';
    }
}

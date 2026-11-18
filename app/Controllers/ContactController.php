<?php
declare(strict_types=1);

final class ContactController
{
    public function __construct(
        private ContactRequest $contactRequest,
        private ContactRequestValidator $validator,
    ) {}

    public function handle(): void
    {
        $currentPage = 'contact';
        $status = null;
        $formErrors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $contentLength = (int)($_SERVER['CONTENT_LENGTH'] ?? 0);
            $postLimit = $this->iniSizeInBytes((string)ini_get('post_max_size'));
            if ($postLimit > 0 && $contentLength > $postLimit) {
                $status = 'file_too_large';
                $formErrors = ['attachment' => 'validation_attachment_too_large'];
            } else {
                $validation = $this->validator->validate($_POST, $_FILES);
                $formErrors = $validation['errors'] ?? [];
                $status = $validation['status'] === 'success'
                    ? (($validation['spam'] ?? false)
                        ? 'success'
                        : $this->contactRequest->send($validation['data'], $validation['attachment'])['status'])
                    : $validation['status'];
            }
        }
        $pageTitle = 'Contact — Woorden verbinden';
        $view = __DIR__ . '/../Views/contact.php';
        require __DIR__ . '/../Views/layout.php';
    }

    private function iniSizeInBytes(string $value): int
    {
        if (!preg_match('/^\s*(\d+)\s*([KMG]?)\s*B?\s*$/i', $value, $matches)) return 0;
        $size = (int)$matches[1];
        return $size * match (strtoupper($matches[2])) {
            'G' => 1024 ** 3,
            'M' => 1024 ** 2,
            'K' => 1024,
            default => 1,
        };
    }
}

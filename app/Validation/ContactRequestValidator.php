<?php
declare(strict_types=1);

final class ContactRequestValidator
{
    private const MAX_FILE_SIZE = 2 * 1024 * 1024;
    private const MAX_NAME_LENGTH = 120;
    private const MAX_MESSAGE_LENGTH = 5000;
    private const ALLOWED_SERVICES = ['Vertalen', 'Tolken', 'Tekst nakijken', 'Anders'];
    private const ALLOWED_FILES = [
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword', 'application/x-ole-storage'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
        'txt' => ['text/plain'],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
    ];

    public function validate(array $input, array $files = []): array
    {
        $name = $this->stringValue($input, 'name');
        $email = $this->stringValue($input, 'email');
        $service = $this->stringValue($input, 'service');
        $message = $this->stringValue($input, 'message');
        $honeypot = $this->stringValue($input, 'website');

        if ($honeypot !== '') return ['status' => 'success', 'spam' => true];
        $errors = [];
        if ($name === '') $errors['name'] = 'validation_name_required';
        elseif ($this->characterLength($name) > self::MAX_NAME_LENGTH) $errors['name'] = 'validation_name_too_long';
        if ($email === '') $errors['email'] = 'validation_email_required';
        elseif ($this->characterLength($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'validation_email_invalid';
        if ($service === '') $errors['service'] = 'validation_service_required';
        elseif (!in_array($service, self::ALLOWED_SERVICES, true)) $errors['service'] = 'validation_service_invalid';
        if ($message === '') $errors['message'] = 'validation_message_required';
        elseif ($this->characterLength($message) > self::MAX_MESSAGE_LENGTH) $errors['message'] = 'validation_message_too_long';
        if ($errors !== []) return ['status' => 'invalid', 'errors' => $errors];

        $attachment = $this->validateAttachment($files['attachment'] ?? null);
        if ($attachment['status'] !== 'success') {
            $attachment['errors'] = ['attachment' => $attachment['error']];
            unset($attachment['error']);
            return $attachment;
        }

        return [
            'status' => 'success',
            'spam' => false,
            'data' => ['name' => $name, 'email' => $email, 'service' => $service, 'message' => $message],
            'attachment' => $attachment['file'],
        ];
    }

    private function stringValue(array $input, string $key): string
    {
        $value = $input[$key] ?? '';
        return is_string($value) || is_numeric($value) ? trim((string)$value) : '';
    }

    private function characterLength(string $value): int
    {
        $count = preg_match_all('/./us', $value, $matches);
        return $count === false ? strlen($value) : $count;
    }

    private function validateAttachment(?array $upload): array
    {
        if ($upload === null || (int)($upload['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return ['status' => 'success', 'file' => null];
        }

        $error = (int)($upload['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
            return ['status' => 'file_too_large', 'error' => 'validation_attachment_too_large'];
        }
        if ($error !== UPLOAD_ERR_OK) return ['status' => 'file_invalid', 'error' => 'validation_attachment_invalid'];

        $path = (string)($upload['tmp_name'] ?? '');
        if ($path === '' || !is_uploaded_file($path)) return ['status' => 'file_invalid', 'error' => 'validation_attachment_invalid'];
        $size = (int)($upload['size'] ?? 0);
        if ($size <= 0) return ['status' => 'file_invalid', 'error' => 'validation_attachment_invalid'];
        if ($size > self::MAX_FILE_SIZE) return ['status' => 'file_too_large', 'error' => 'validation_attachment_too_large'];

        $originalName = (string)($upload['name'] ?? '');
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!isset(self::ALLOWED_FILES[$extension])) return ['status' => 'file_invalid', 'error' => 'validation_attachment_invalid'];

        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($path);
        if (!in_array($mime, self::ALLOWED_FILES[$extension], true)) return ['status' => 'file_invalid', 'error' => 'validation_attachment_invalid'];

        $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($originalName)) ?: 'bijlage.' . $extension;
        return ['status' => 'success', 'file' => ['path' => $path, 'name' => $safeName, 'mime' => $mime]];
    }
}

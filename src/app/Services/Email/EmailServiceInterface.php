<?php

namespace Chs\EmailService\app\Services\Email;

interface EmailServiceInterface
{
    public function sendTemplate(int|string $templateId, array|string $recipient, array $data = [], array $options = []): array;
}

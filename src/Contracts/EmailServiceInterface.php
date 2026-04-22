<?php

namespace Chs\EmailService\Contracts;

interface EmailServiceInterface
{
    public function sendTemplate(int|string $templateId, array|string $recipient, array $data = [], array $options = []): array;
}

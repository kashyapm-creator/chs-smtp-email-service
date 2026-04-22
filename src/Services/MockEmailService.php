<?php

namespace Chs\EmailService\Services;

use Chs\EmailService\Contracts\EmailServiceInterface;

use App\Services\Email\Concerns\ForcesBetaRecipient;
use Illuminate\Support\Facades\Log;

class MockEmailService implements EmailServiceInterface
{
    use ForcesBetaRecipient;

    public function sendTemplate(int|string $templateId, array|string $recipient, array $data = [], array $options = []): array
    {
        $recipients = $this->normalizeRecipients($recipient);
        $recipients = $this->applyBetaRecipientOverride($recipients, [
            'provider' => 'mock',
            'template_id' => $templateId,
        ]);

        Log::info('MockEmailService sendTemplate called', [
            'template_id' => $templateId,
            'recipients' => $recipients,
            'data' => $data,
            'options' => $options,
        ]);

        return [
            'success' => true,
            'provider' => 'mock',
            'status_code' => 200,
            'response' => null,
        ];
    }
}

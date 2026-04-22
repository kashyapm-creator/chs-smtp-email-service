<?php

namespace Chs\EmailService\Services\Email;

use Chs\EmailService\Services\Email\Concerns\ForcesBetaRecipient;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use function App\Services\Email\config;
use function App\Services\Email\report;

class Smtp2GoEmailService implements EmailServiceInterface
{
    use ForcesBetaRecipient;

    public function sendTemplate(int|string $templateId, array|string $recipient, array $data = [], array $options = []): array
    {
        $recipients = $this->normalizeRecipients($recipient);
        $recipients = $this->applyBetaRecipientOverride($recipients, [
            'provider' => 'smtp2go',
            'template_id' => $templateId,
        ]);

        $subject = $options['subject'] ?? '';
        $sender = $options['sender'] ?? 'noreply@movewithcitizen.com';

        $payload = [
            'sender' => $sender,
            'to' => $recipients['to'],
            'cc' => $recipients['cc'],
            'bcc' => $recipients['bcc'],
            'subject' => $subject,
            'template_id' => (string) $templateId,
            'version' => $options['version'] ?? 2,
            'template_data' => $data,
        ];

        if (!empty($templateId)) {
            $payload['template_id'] = (string) $templateId;
        } else {
            $payload['html_body'] = $data['html'];
            $payload['attachments'] = $data['attachments'];
        }

        Log::debug('Smtp2Go payload', $payload);

        try {
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Smtp2go-Api-Key' => config('services.smtp2go.api_key'),
            ])
                ->post('https://api.smtp2go.com/v3/email/send', $payload)
                ->throw();

            Log::debug('Smtp2Go response', $response->json());

            return [
                'success' => true,
                'provider' => 'smtp2go',
                'status_code' => $response->status(),
                'response' => $response->json(),
                'response_body' => $response->body(),
            ];
        } catch (RequestException $e) {
            $res = $e->response;
            report($e);

            return [
                'success' => false,
                'provider' => 'smtp2go',
                'status_code' => $res?->status(),
                'response' => $res?->json(),
            ];
        }
    }
}

<?php

namespace Chs\EmailService\Services\Email;

use Chs\EmailService\Services\Email\Concerns\ForcesBetaRecipient;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use function App\Services\Email\config;
use function App\Services\Email\report;

class SendPulseEmailService implements EmailServiceInterface
{
    use ForcesBetaRecipient;

    public function sendTemplate(int|string $templateId, array|string $recipient, array $data = [], array $options = []): array
    {
        $recipients = $this->normalizeRecipients($recipient);
        $recipients = $this->applyBetaRecipientOverride($recipients, [
            'provider' => 'sendpulse',
            'template_id' => $templateId,
        ]);

        $fromEmail = $options['from_email'] ?? 'utilities@citizenhomesolutions.com';
        $fromName = $options['from_name'] ?? 'Citizen';
        $subject = $options['subject'] ?? '';

        $tokenResponse = Http::asForm()->post('https://api.sendpulse.com/oauth/access_token', [
            'grant_type' => 'client_credentials',
            'client_id' => config('services.sendpulse.client_id'),
            'client_secret' => config('services.sendpulse.client_secret'),
        ]);

        if (! $tokenResponse->successful()) {
            Log::error('SendPulse auth failed', [
                'status' => $tokenResponse->status(),
                'body' => $tokenResponse->body(),
            ]);

            return [
                'success' => false,
                'provider' => 'sendpulse',
                'status_code' => $tokenResponse->status(),
                'response' => $tokenResponse->json(),
            ];
        }

        $accessToken = $tokenResponse->json('access_token');

        $emailPayload = [
            'from' => [
                'email' => $fromEmail,
                'name' => $fromName,
            ],
            'to' => array_map(fn ($email) => ['email' => $email], $recipients['to']),
            'subject' => $subject,
        ];

        if (!empty($templateId)) {
            $emailPayload['template'] = [
                'id' => (int) $templateId,
                'variables' => $data,
            ];
        } else {
            $emailPayload['html'] = $options['html'] ?? $data['html'] ?? '';
            if (!empty($options['attachments'] ?? $data['attachments'] ?? null)) {
                $emailPayload['attachments'] = $options['attachments'] ?? $data['attachments'];
            }
        }

        $payload = ['email' => $emailPayload];

        Log::debug('SendPulse payload', $payload);

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$accessToken}",
                'Content-Type' => 'application/json',
            ])
                ->post('https://api.sendpulse.com/smtp/emails', $payload)
                ->throw();

            Log::debug('SendPulse response', $response->json());

            return [
                'success' => true,
                'provider' => 'sendpulse',
                'status_code' => $response->status(),
                'response' => $response->json(),
                'response_body' => $response->body(),
            ];
        } catch (RequestException $e) {
            $res = $e->response;
            report($e);

            return [
                'success' => false,
                'provider' => 'sendpulse',
                'status_code' => $res?->status(),
                'response' => $res?->json(),
            ];
        }
    }
}

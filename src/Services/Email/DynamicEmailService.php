<?php

namespace Chs\EmailService\Services\Email;

use App\Models\EmailSentCount;
use App\Models\SmtpMailProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use function App\Services\Email\report;

class DynamicEmailService implements EmailServiceInterface
{
    public function __construct(
        protected SendPulseEmailService $sendPulse,
        protected Smtp2GoEmailService $smtp2go,
        protected MockEmailService $mock
    ) {
    }

    public function sendTemplate(int|string $templateId, array|string $recipient, array $data = [], array $options = []): array
    {
        $provider = $this->resolveProvider();
        $providerName = strtolower((string) ($provider?->provider_name ?? ''));

        if ($providerName === 'sendpulse') {
            return $this->sendPulse->sendTemplate($templateId, $recipient, $data, $options);
        }

        if ($providerName === 'smtp2go') {
            return $this->smtp2go->sendTemplate($templateId, $recipient, $data, $options);
        }

        Log::warning('Unknown mail provider; using mock', [
            'provider_name' => $provider?->provider_name,
            'template_id' => $templateId,
            'recipient' => $recipient,
            'data' => $data,
            'options' => $options,
            'provider' => $provider ?? null,
        ]);

        return $this->mock->sendTemplate($templateId, $recipient, $data, $options);
    }

    protected function resolveProvider(): ?SmtpMailProvider
    {
        try {
            $providers = SmtpMailProvider::all();
            if ($providers->isEmpty()) {
                return null;
            }

            $limit = EmailSentCount::query()->value('limit');
            $count = EmailSentCount::query()->value('email_count');

            if (! $limit || $limit <= 0) {
                return $providers->first();
            }

            $index = intval($count / $limit);
            $circularIndex = $index % $providers->count();

            return $providers[$circularIndex];
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }
}

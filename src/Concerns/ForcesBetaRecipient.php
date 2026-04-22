<?php

namespace Chs\EmailService\Concerns;

use Illuminate\Support\Facades\Log;

trait ForcesBetaRecipient
{
    protected function applyBetaRecipientOverride(array $recipients, array $context = []): array
    {
        if (config('quickbase.env') !== 'beta') {
            return $recipients;
        }

        $forcedTo = config('mail.force_to');

        $original = $recipients;

        $recipients['to'] = [$forcedTo];
        $recipients['cc'] = [];
        $recipients['bcc'] = [];

        Log::info('Beta recipient override applied', array_merge($context, [
            'forced_to' => $forcedTo,
            'original_recipients' => $original,
        ]));

        return $recipients;
    }

    protected function normalizeRecipients(array|string $recipient): array
    {
        if (is_string($recipient)) {
            return [
                'to' => [$recipient],
                'cc' => [],
                'bcc' => [],
            ];
        }

        return [
            'to' => array_values($recipient['to'] ?? []),
            'cc' => array_values($recipient['cc'] ?? []),
            'bcc' => array_values($recipient['bcc'] ?? []),
        ];
    }
}

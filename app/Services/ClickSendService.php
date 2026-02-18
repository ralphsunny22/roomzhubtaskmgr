<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ClickSendService
{
    protected string $username;
    protected string $apiKey;

    public function __construct()
    {
        $this->username = config('services.clicksend.username');
        $this->apiKey = config('services.clicksend.api_key');
    }

    public function sendEmail(array $data): array
    {
        $payload = [
            'to' => $this->formatRecipients($data['to']),
            'from' => [
                'email_address_id' => $data['from']['email_address_id'],
                'name' => $data['from']['name'] ?? null,
            ],
            'subject' => $data['subject'] ?? '',
            'body' => $data['body'],
        ];

        if (!empty($data['cc'])) {
            $payload['cc'] = $this->formatRecipients($data['cc']);
        }

        if (!empty($data['bcc'])) {
            $payload['bcc'] = $this->formatRecipients($data['bcc']);
        }

        if (!empty($data['attachments'])) {
            $payload['attachments'] = $this->formatAttachments($data['attachments']);
        }

        $response = Http::timeout(30)
            ->withBasicAuth($this->username, $this->apiKey)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post('https://rest.clicksend.com/v3/email/send', $payload);

        return [
            'success' => $response->successful(),
            'response' => $response->json(),
            'status' => $response->status(),
        ];
    }

    public function sendSms(array $messages): array
    {
        $payload = [
            'messages' => array_map(function ($msg) {
                return [
                    'source' => $msg['source'] ?? 'php',
                    'body'   => $msg['body'],
                    'to'     => $msg['to'],
                    'from'   => $msg['from'] ?? null, // optional sender ID
                ];
            }, $messages),
        ];

        $response = Http::timeout(30)
            ->withBasicAuth($this->username, $this->apiKey)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post('https://rest.clicksend.com/v3/sms/send', $payload);

        return [
            'success' => $response->successful(),
            'response' => $response->json(),
            'status' => $response->status(),
        ];
    }

    private function formatRecipients(array $recipients): array
    {
        return array_map(function ($recipient) {
            return [
                'email' => $recipient['email'],
                'name'  => $recipient['name'] ?? null,
            ];
        }, $recipients);
    }

    private function formatAttachments(array $attachments): array
    {
        return array_map(function ($attachment) {
            return [
                'content'     => base64_encode($attachment['content']),
                'type'        => $attachment['type'],
                'filename'    => $attachment['filename'],
                'disposition' => $attachment['disposition'] ?? 'attachment',
                'content_id'  => $attachment['content_id'] ?? null,
            ];
        }, $attachments);
    }
}

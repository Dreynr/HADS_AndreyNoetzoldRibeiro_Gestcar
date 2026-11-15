<?php

namespace App\Services;

use App\Contracts\WhatsAppSender;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Envio pela Z-API Omni (D-19). A API nova monta a URL com o id do canal e
 * autentica pela Secret Key no header Authorization, diferente da Z-API
 * antiga, que levava o token da instancia dentro da propria URL.
 *
 * E a unica classe do sistema que sabe que o provedor e a Z-API.
 */
class ZApiWhatsAppSender implements WhatsAppSender
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $channelId,
        private readonly string $secretKey,
    ) {
    }

    public function send(string $phone, string $message): void
    {
        $url = rtrim($this->baseUrl, '/') . "/channels/{$this->channelId}/messages";

        // O timeout curto e proposital: o WhatsApp fora do ar nao pode
        // segurar a tela do admin nem atrasar o e-mail (RNF10).
        $response = Http::withToken($this->secretKey)
            ->timeout(10)
            ->post($url, [
                'recipient' => [
                    'identifier' => $phone,
                ],
                'content' => [
                    'type' => 'TEXT',
                    'body' => [
                        'message' => $message,
                    ],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException(
                "Z-API respondeu {$response->status()}: {$response->body()}"
            );
        }
    }
}

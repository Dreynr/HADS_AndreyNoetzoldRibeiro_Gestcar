<?php

namespace App\Services;

use App\Contracts\WhatsAppSender;
use Illuminate\Support\Facades\Log;

/**
 * Implementacao usada no ambiente local e nos testes: escreve a mensagem no
 * log em vez de mandar para o celular de alguem. E o que permite testar o
 * fluxo inteiro sem gastar mensagem nem depender da Z-API estar no ar.
 */
class LogWhatsAppSender implements WhatsAppSender
{
    public function send(string $phone, string $message): void
    {
        Log::info('WhatsApp simulado', [
            'phone' => $phone,
            'message' => $message,
        ]);
    }
}

<?php

namespace App\Contracts;

/**
 * Contrato do envio de WhatsApp (D-05). O provedor fica isolado atras desta
 * interface: trocar a Z-API por outro servico e escrever uma classe nova,
 * sem mexer em nenhum dos lugares que chamam o envio.
 */
interface WhatsAppSender
{
    /**
     * @param  string  $phone  So digitos, no formato 55 + DDD + numero.
     *
     * @throws \RuntimeException quando o provedor recusa ou nao responde.
     */
    public function send(string $phone, string $message): void;
}

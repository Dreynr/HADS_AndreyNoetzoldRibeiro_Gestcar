<?php

namespace App\Providers;

use App\Contracts\WhatsAppSender;
use App\Services\LogWhatsAppSender;
use App\Services\ZApiWhatsAppSender;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Quem pede um WhatsAppSender recebe a Z-API quando ela esta ligada
        // e o gravador de log quando nao esta. E aqui, num lugar so, que o
        // sistema decide qual provedor usar (D-05).
        $this->app->bind(WhatsAppSender::class, function () {
            $zapi = config('services.zapi');

            // Ligado mas sem credencial completa no .env e erro de
            // configuracao: avisa no log e cai para o modo seguro, em vez de
            // estourar na cara do usuario na primeira mensagem.
            $incompleto = empty($zapi['base_url'])
                || empty($zapi['channel_id'])
                || empty($zapi['secret_key']);

            if (!$zapi['enabled'] || $incompleto) {
                if ($zapi['enabled'] && $incompleto) {
                    Log::warning('ZAPI_ENABLED esta ligado, mas faltam credenciais no .env. Usando o LogWhatsAppSender.');
                }

                return new LogWhatsAppSender();
            }

            return new ZApiWhatsAppSender(
                $zapi['base_url'],
                $zapi['channel_id'],
                $zapi['secret_key'],
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Limite proprio para o PEDIDO do codigo de recuperacao (RNF12).
        //
        // Ele precisa de chave separada: o `throttle:5,1` padrao do Laravel
        // conta por IP, sem olhar a rota, entao pedir o codigo gastaria o
        // mesmo saldo de tentativas de digitar o codigo. Aqui a conta e por
        // e-mail mais IP, que e o que se quer proteger — a caixa de entrada
        // de alguem.
        RateLimiter::for('password-request', function (Request $request) {
            $chave = strtolower((string) $request->input('email')) . '|' . $request->ip();

            return Limit::perMinute(5)->by($chave);
        });
    }
}

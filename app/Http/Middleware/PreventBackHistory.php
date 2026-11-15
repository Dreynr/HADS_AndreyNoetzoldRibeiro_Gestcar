<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Impede o navegador de guardar as telas de quem esta logado.
 *
 * Sem isto, depois de sair do sistema o botao "voltar" ainda mostra a ultima
 * pagina — com placas, valores e dados de cliente — porque o navegador a tem
 * em cache. A sessao ja acabou e o proximo clique leva ao login, mas o
 * conteudo ficou visivel na tela de um computador que pode ser de outra
 * pessoa.
 *
 * `no-store` e o que resolve: ele proibe guardar a resposta, enquanto
 * `no-cache` apenas obriga a revalidar — e a revalidacao nao acontece no
 * botao voltar.
 */
class PreventBackHistory
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // So nas telas de quem esta logado: pagina publica pode ser
        // guardada normalmente pelo navegador.
        if (auth()->check()) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        return $response;
    }
}

<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureRole::class,
        ]);

        // Telas de quem esta logado nao ficam no cache do navegador, para o
        // botao "voltar" nao mostrar dados depois do logout.
        $middleware->web(append: [
            \App\Http\Middleware\PreventBackHistory::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Pagina guardada pelo navegador leva junto um token CSRF da sessao
        // antiga, e o Laravel responde com a tela crua "419 Pagina expirada".
        // Acontece, por exemplo, ao sair, apertar voltar e tentar entrar de
        // novo. Em vez da tela de erro, devolve o formulario com um aviso.
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            $aviso = 'A pagina ficou aberta tempo demais e expirou. Tente de novo.';

            return redirect()->back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('status', $aviso)
                ->with('error', $aviso);
        });
    })->create();

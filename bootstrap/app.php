<?php

use App\Http\Middleware\LocaleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use \App\Http\Middleware\EncryptCookies;



return Application::configure(basePath: dirname(__DIR__))
  ->withRouting(
    web: __DIR__ . '/../routes/web.php',
    api: __DIR__ . '/../routes/api.php', // Asegúrate que esta línea existe
    commands: __DIR__ . '/../routes/console.php',
    health: '/up',
  )
  ->withMiddleware(function (Middleware $middleware) {
    //$middleware->web(LocaleMiddleware::class);

    // Configura los proxies confiables
    $middleware->trustProxies(
      at: [
        //'192.168.1.1',       // Ejemplo de IP de proxy confiable
        //'10.0.0.0/8',        // Ejemplo de rango de IPs
        '*'                    // Puedes usar '*' para confiar en todos
      ],
      headers: Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB
    );

    $middleware->validateCsrfTokens(except: [
      'api/factura-call-back',
      'api/nota-debito-call-back',
      'api/nota-credito-call-back',
      'api/tiquete-call-back',
      'api/mensaje-call-back',
      'api/factura-compra-call-back',
    ]);

    /*
    $middleware->alias([
      'prevent.duplicate' => \App\Http\Middleware\PreventDuplicateAuth::class,
    ]);

    $middleware->prependToGroup('web', \App\Http\Middleware\PreventDuplicateAuth::class);
    */

    /*
    $middleware->alias([
      'prevent.duplicate' => \App\Http\Middleware\PreventDuplicateLogin::class,
    ]);

    $middleware->prependToGroup('web', \App\Http\Middleware\PreventDuplicateLogin::class);

    $middleware->web(append: [
      //\App\Http\Middleware\EncryptCookies::class,
      \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
      \Illuminate\Session\Middleware\StartSession::class, // Asegurar que está antes de CheckSession
      \Illuminate\View\Middleware\ShareErrorsFromSession::class,
      //\App\Http\Middleware\VerifyCsrfToken::class,
      \Illuminate\Routing\Middleware\SubstituteBindings::class,
      \App\Http\Middleware\CheckSession::class, // Nuestro middleware al final
    ]);
    */
    /*
    $middleware->alias([
      'context' => \App\Http\Middleware\CheckContext::class,
    ]);
    */

    // Añade tu middleware personalizado al grupo 'web'
    //$middleware->appendToGroup('web', \App\Http\Middleware\CheckContext::class);

    //$middleware->trustProxies(at: '*'); // Confía en todos los proxies
    // Alternativamente, puedes especificar IPs de proxies confiables:
    // $middleware->trustProxies(['192.168.1.1', '192.168.1.2']);
  })
  ->withExceptions(function (Exceptions $exceptions) {
    //
  })->create();

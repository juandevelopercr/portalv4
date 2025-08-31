<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
  ->withRouting(
    web: __DIR__ . '/../routes/web.php',
    api: __DIR__ . '/../routes/api.php', // Asegúrate que esta línea existe
    commands: __DIR__ . '/../routes/console.php',
    health: '/up',
  )
  ->withMiddleware(function (Middleware $middleware) {


    // Middleware solo para rutas web
    $middleware->group('web', [
      \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
      \Illuminate\Session\Middleware\StartSession::class,
      \Illuminate\View\Middleware\ShareErrorsFromSession::class,
      \App\Http\Middleware\SetTenantDatabase::class, // <-- tu middleware aquí
      \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ]);

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
  })
  ->withExceptions(function (Exceptions $exceptions) {
    //
  })->create();

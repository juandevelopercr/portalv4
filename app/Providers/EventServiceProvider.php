<?php

namespace App\Providers;

use \App\Listeners\ClearSessionContext;
use App\Listeners\HandleUserLogin;
use App\Listeners\SetSessionVariables;
use App\Listeners\StoreSessionVariables;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
  /**
   * The event listener mappings for the application.
   *
   * @var array
   */
  protected $listen = [
    Login::class => [
      StoreSessionVariables::class,
    ],
    /*
    \Illuminate\Auth\Events\Logout::class => [
      ClearSessionContext::class,
    ],
    */
  ];

  /**
   * Register any events for your application.
   */
  public function boot(): void
  {
    parent::boot();
  }
}

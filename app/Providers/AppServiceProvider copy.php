<?php

namespace App\Providers;

use App\Listeners\StoreSessionVariables;
use App\Listeners\StoreSessionVariablesService;
use App\Services\ApiBCCR;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   */
  public function register(): void
  {
    $this->app->singleton(StoreSessionVariablesService::class, function ($app) {
      return new StoreSessionVariablesService(app(ApiBCCR::class));
    });
  }

  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {
    // Fijar el tamaño de las cadenas predeterminado a 191 caracteres
    Schema::defaultStringLength(191);

    if ($this->app->environment('production') || $this->app->environment('staging')) {
      URL::forceScheme('https');
    }

    // Implicitly grant "Super Admin" role all permissions
    // This works in the app by using gate-related functions like auth()->user->can() and @can()
    Gate::before(function ($user, $ability) {
      return $user->hasRole('SuperAdmin') ? true : null;
    });

    Vite::useStyleTagAttributes(function (?string $src, string $url, ?array $chunk, ?array $manifest) {
      if ($src !== null) {
        return [
          'class' => preg_match("/(resources\/assets\/vendor\/scss\/(rtl\/)?core)-?.*/i", $src) ? 'template-customizer-core-css' : (preg_match("/(resources\/assets\/vendor\/scss\/(rtl\/)?theme)-?.*/i", $src) ? 'template-customizer-theme-css' : '')
        ];
      }
      return [];
    });
  }
}

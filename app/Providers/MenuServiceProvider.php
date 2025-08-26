<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Routing\Route;

use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{
  /**
   * Register services.
   */
  public function register(): void
  {
    //
  }

  /**
   * Bootstrap services.
   */
  public function boot(): void
  {
    $verticalMenuJson = file_get_contents(base_path('resources/menu/verticalMenu.json'));
    $verticalMenuData = json_decode($verticalMenuJson);
    $horizontalMenuJson = file_get_contents(base_path('resources/menu/horizontalMenu.json'));
    $horizontalMenuData = json_decode($horizontalMenuJson);

    // Share all menuData to all the views
    $this->app->make('view')->share('menuData', [$verticalMenuData, $horizontalMenuData]);
  }

  function filterMenu($menu, $user)
  {
    $filtered = [];
    foreach ($menu as $item) {
      // Verificar permisos del ítem principal
      $hasMainPermissions = true;
      if (!empty($item['permissions'])) {
        $hasMainPermissions = $user->hasAnyPermission($item['permissions']);
      }

      // Filtrar subitems
      $hasVisibleSubitems = false;
      if (!empty($item['submenu'])) {
        $item['submenu'] = $this->filterMenu($item['submenu'], $user);
        $hasVisibleSubitems = !empty($item['submenu']);
      }

      // Mostrar ítem si cumple condiciones
      if ($hasMainPermissions || $hasVisibleSubitems) {
        $filtered[] = $item;
      }
    }
    return $filtered;
  }
}

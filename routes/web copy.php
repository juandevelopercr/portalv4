<?php

use App\Http\Controllers\Auth\DepartmentSelectionController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\authentications\RegisterBasic;
use App\Http\Controllers\billing\InvoiceController;
use App\Http\Controllers\customers\CustomerController;
use App\Http\Controllers\Home;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\products\ProductController;
use App\Http\Controllers\reports\ReportCasoController;
use App\Http\Controllers\rolesPersmissions\AccessPermission;
use App\Http\Controllers\rolesPersmissions\AccessRoles;
use App\Http\Controllers\Settings\SettingController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\SetTenantDatabase;
use Illuminate\Support\Facades\Route;


// locale
Route::get('/lang/{locale}', [LanguageController::class, 'swap']);
Route::get('/pages/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');

// authentication
Route::get('/auth/login-basic', [LoginBasic::class, 'index'])->name('auth-login-basic');
Route::get('/auth/register-basic', [RegisterBasic::class, 'index'])->name('auth-register-basic');

// Descargar factura electrónica mediante QR
Route::get('/download-invoice/{key}', [InvoiceController::class, 'downloadByKey'])
  ->name('invoice.download.public');

// Rutas protegidas por login + multi-tenancy
Route::group(['middleware' => ['auth:sanctum', 'verified', SetTenantDatabase::class]], function () {

  // Main Page Route
  Route::get('/', [Home::class, 'index'])->name('index');

  // CRUD USUARIOS
  Route::get('users', [UserController::class, 'index'])->name('users.index');

  // ROLES y PERMISOS
  Route::get('/app/access-roles', [AccessRoles::class, 'index'])->name('access-roles');
  Route::get('/app/access-permission', [AccessPermission::class, 'index'])->name('access-permission');

  // CRUD CUSTOMERS
  Route::get('customers', [CustomerController::class, 'customer'])->name('customers.index');
  Route::get('suppliers', [CustomerController::class, 'supplier'])->name('suppliers.index');

  // CRUD PRODUCTS
  Route::get('products', [ProductController::class, 'index'])->name('products.index');

  // Configuración de negocio
  Route::get('settings/business', [SettingController::class, 'index'])->name('settings-business');

  // Exportaciones y reportes
  Route::get('/descargar-exportacion-caso-pendientes/{filename}', [ReportCasoController::class, 'descargarExportacionCasoPendiente'])
    ->name('exportacion.caso.pendiente.descargar');
});

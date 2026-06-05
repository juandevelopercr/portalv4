<?php

namespace App\Http\Controllers\billing;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
  public function index()
  {
    return view('content.billing.invoices.index', []);
  }

  public function creditNote()
  {
    return view('content.billing.credit-note.index', []);
  }

  public function debitNote($params = [])
  {
    return view('content.billing.debit-note.index', compact('params'));
  }

  public function facturaCompra($params = [])
  {
    return view('content.billing.factura-compra.index', compact('params'));
  }

  public function reciboPago($params = [])
  {
    return view('content.billing.recibo-pago.index', compact('params'));
  }

  public function comprobante($params = [])
  {
    return view('content.billing.comprobante.index', compact('params'));
  }

  public function downloadByKey($key)
  {
    // Ruta pública (acceso vía QR). No hay usuario autenticado, por eso
    // buscamos la transacción iterando los tenants hasta encontrarla.
    $invoice = null;

    foreach (Tenant::all() as $tenant) {
      $connName = 'tenant_dl_' . $tenant->id;

      Config::set("database.connections.$connName", [
        'driver'    => 'mysql',
        'host'      => $tenant->db_host ?? '127.0.0.1',
        'port'      => $tenant->db_port ?? '3306',
        'database'  => $tenant->db_name,
        'username'  => $tenant->db_user,
        'password'  => $tenant->db_password,
        'charset'   => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
      ]);

      $found = DB::connection($connName)
        ->table('transactions')
        ->where('key', $key)
        ->whereNull('deleted_at')
        ->first();

      DB::purge($connName);

      if ($found) {
        // Reconectamos para que Helpers pueda usar el modelo correctamente
        DB::purge('tenant');
        Config::set('database.connections.tenant', [
          'driver'    => 'mysql',
          'host'      => $tenant->db_host ?? '127.0.0.1',
          'port'      => $tenant->db_port ?? '3306',
          'database'  => $tenant->db_name,
          'username'  => $tenant->db_user,
          'password'  => $tenant->db_password,
          'charset'   => 'utf8mb4',
          'collation' => 'utf8mb4_unicode_ci',
        ]);
        DB::setDefaultConnection('tenant');
        $invoice = Transaction::find($found->id);
        break;
      }
    }

    if (!$invoice) {
      abort(404, 'Factura no encontrada');
    }

    $filename = Helpers::generateComprobanteElectronicoPdf($invoice->id);

    $path = storage_path("app/public/invoices/$filename");

    if (!file_exists($path)) {
      abort(404, 'Archivo no encontrado');
    }

    return response()->download($path, $filename);
  }
}

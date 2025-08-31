<?php

namespace App\Http\Controllers\reports;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Livewire\Transactions\Export\TransactionExportFromView;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ReportProformaController extends Controller
{
  public function prepararExportacionProforma($key)
  {
    try {
      $params = Cache::pull($key);

      if (!is_array($params)) {
        abort(404, 'Clave de exportación inválida o expirada');
      }

      $invoiceId = $params['invoiceId'] ?? '';
      $type = $params['type'] ?? 'sencillo';

      ini_set('memory_limit', '-1');
      ini_set('max_execution_time', '360');

      $filename = Helpers::generateProformaPdf($invoiceId, $type);

      return response()->json([
        'filename' => $filename
      ]);
    } catch (Throwable $e) {
      Log::error("Error al preparar exportación de proforma: " . $e->getMessage());
      return response()->json(['error' => 'Error al generar el archivo'], 500);
    }
  }

  public function descargarExportacionProforma($filename)
  {
    $path = storage_path("app/public/proformas/$filename");

    if (!file_exists($path)) {
      abort(404, 'Archivo no encontrado');
    }

    return response()->download($path, $filename);
  }

  public function prepararExportacionRecibo($key)
  {
    return $this->prepararExportacionGenerica($key, 'recibo');
  }

  public function descargarExportacionRecibo($filename)
  {
    return $this->descargarExportacionGenerica($filename, 'recibos');
  }

  private function prepararExportacionGenerica($key, $tipo)
  {
    try {
      $params = Cache::pull($key);

      if (!is_array($params)) {
        abort(404, 'Clave de exportación inválida o expirada');
      }

      $invoiceId = $params['invoiceId'] ?? '';
      $type = $params['type'] ?? 'sencillo';

      ini_set('memory_limit', '-1');
      ini_set('max_execution_time', '360');

      $filename = $tipo === 'proforma'
        ? Helpers::generateProformaPdf($invoiceId, $type)
        : Helpers::generateReciboPdf($invoiceId, $type);

      return response()->json([
        'filename' => $filename
      ]);
    } catch (Throwable $e) {
      Log::error("Error al preparar exportación de $tipo: " . $e->getMessage());
      return response()->json(['error' => 'Error al generar el archivo'], 500);
    }
  }

  private function descargarExportacionGenerica($filename, $folder)
  {
    $path = storage_path("app/public/$folder/$filename");

    if (!file_exists($path)) {
      abort(404, 'Archivo no encontrado');
    }

    return response()->download($path, $filename);
  }

  public function prepararExportacionCalculoReciboGasto($key)
  {
    try {
      $params = Cache::pull($key);

      if (!is_array($params)) {
        abort(404, 'Clave de exportación inválida o expirada');
      }

      $invoiceId = $params['invoiceId'] ?? '';
      $ids = $params['ids'] ?? [];
      $ids_normal = $params['ids_normal'] ?? [];
      $ids_iva = $params['ids_iva'] ?? [];
      $ids_no_iva = $params['ids_no_iva'] ?? [];

      ini_set('memory_limit', '-1');
      ini_set('max_execution_time', '360');

      $filename = Helpers::generateReciboCalculoRegistroPdf($invoiceId, $ids, $ids_normal, $ids_iva, $ids_no_iva);

      return response()->json([
        'filename' => $filename
      ]);
    } catch (Throwable $e) {
      Log::error("Error al preparar exportación de recibo de gastos de calculo del registro: " . $e->getMessage());
      return response()->json(['error' => 'Error al generar el archivo'], 500);
    }
  }

  public function descargarExportacionCalculoReciboGasto($filename)
  {
    $path = storage_path("app/public/proformas/$filename");

    if (!file_exists($path)) {
      abort(404, 'Archivo no encontrado');
    }

    return response()->download($path, $filename);
  }

  public function prepararExportacionEstadoCuenta($key)
  {
    try {
      $params = Cache::pull($key);

      if (!is_array($params)) {
        abort(404, 'Clave de exportación inválida o expirada');
      }

      $transactionIds = $params['transactionsIds'] ?? [];

      ini_set('memory_limit', '-1');
      ini_set('max_execution_time', '360');

      $filename = Helpers::generateEstadoCuentaPdf($transactionIds);

      return response()->json([
        'filename' => $filename
      ]);
    } catch (Throwable $e) {
      Log::error("Error al preparar exportación de estado de cuenta: " . $e->getMessage());
      return response()->json(['error' => 'Error al generar el archivo'], 500);
    }
  }

  public function descargarExportacionEstadoCuenta($filename)
  {
    $path = storage_path("app/public/proformas/$filename");

    if (!file_exists($path)) {
      abort(404, 'Archivo no encontrado');
    }

    return response()->download($path, $filename);
  }
}

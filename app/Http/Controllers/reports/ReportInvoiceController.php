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

class ReportInvoiceController extends Controller
{
  public function prepararExportacionInvoice($key)
  {
    try {
      $params = Cache::pull($key);

      if (!is_array($params)) {
        abort(404, 'Clave de exportación inválida o expirada');
      }

      $invoiceId = $params['invoiceId'] ?? '';

      ini_set('memory_limit', '-1');
      ini_set('max_execution_time', '360');

      $filename = Helpers::generateComprobanteElectronicoPdf($invoiceId);

      return response()->json([
        'filename' => $filename
      ]);
    } catch (Throwable $e) {
      Log::error("Error al preparar exportación de factura electrónica: " . $e->getMessage());
      return response()->json(['error' => 'Error al generar el archivo'], 500);
    }
  }

  public function descargarExportacionInvoice($filename)
  {
    $path = storage_path("app/public/invoices/$filename");

    if (!file_exists($path)) {
      abort(404, 'Archivo no encontrado');
    }

    return response()->download($path, $filename);
  }
}

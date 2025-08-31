<?php

namespace App\Http\Controllers\reports;

use App\Http\Controllers\Controller;
use App\Livewire\Movimientos\Export\MovimientoExportFromView;
use App\Models\Movimiento;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ReportMovimientoController extends Controller
{
  public function prepararExportacion($key)
  {
    try {
      $params = Cache::pull($key);
      if (!is_array($params)) {
        abort(404, 'Clave inválida');
      }

      $search = $params['search'] ?? '';
      $filters = $params['filters'] ?? [];
      //$filterFecha = $params['filterFecha'] ?? '';
      //$filterCuentas = $params['filterCuentas'] ?? [];
      //$filters['filterFecha'] = $filterFecha;
      //$filters['filterCuentas'] = $filterCuentas;
      $selectedIds = $params['selectedIds'] ?? [];
      $defaultStatus = $params['defaultStatus'] ?? null;

      $query = Movimiento::search($search, $filters, $defaultStatus);
      if (!empty($selectedIds)) {
        $query->whereIn('movimientos.id', $selectedIds);
      }

      ini_set('memory_limit', '-1');
      ini_set('max_execution_time', '360');

      $filename = 'movimientos-' . now()->format('Ymd_His') . '.xlsx';
      $storagePath = "public/exports/$filename";
      Excel::store(new MovimientoExportFromView($query), $storagePath);

      //return Excel::download(
      //  new MovimientoExportFromView($query),
      //  'movimientos-' . now()->format('Ymd_His') . '.xlsx'
      //);

      return response()->json([
        'filename' => $filename
      ]);
    } catch (\Throwable $e) {
      Log::error("Error al preparar exportación: " . $e->getMessage());
      return response()->json(['error' => 'Error interno'], 500);
    }
  }

  public function descargarExportacion($filename)
  {
    $filePath = storage_path("app/public/exports/$filename");

    if (!file_exists($filePath)) {
      abort(404, 'Archivo no encontrado');
    }

    return response()->download($filePath, $filename);
  }
}

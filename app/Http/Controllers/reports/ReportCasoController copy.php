<?php

namespace App\Http\Controllers\reports;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Livewire\Casos\Export\CasoExportFromView;
use App\Livewire\Casos\Export\CasoSituacionExportFromView;
use App\Livewire\Transactions\Export\TransactionExportFromView;
use App\Models\Caso;
use App\Models\CasoSituacion;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ReportCasoController extends Controller
{
  public function prepararExportacionCasos($key)
  {
    try {
      $params = Cache::pull($key);
      if (!is_array($params)) {
        abort(404, 'Clave inválida');
      }

      $search = $params['search'] ?? '';
      $filters = $params['filters'] ?? [];
      $selectedIds = $params['selectedIds'] ?? [];

      $query = Caso::search($search, $filters);
      if (!empty($selectedIds)) {
        $query->whereIn('casos.id', $selectedIds);
      }

      ini_set('memory_limit', '-1');
      ini_set('max_execution_time', '360');

      $filename = 'casos-' . now()->format('Ymd_His') . '.xlsx';
      $storagePath = "public/casos/$filename";
      Excel::store(new CasoExportFromView($query), $storagePath);

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

  public function descargarExportacionCasos($filename)
  {
    $filePath = storage_path("app/public/casos/$filename");

    if (!file_exists($filePath)) {
      abort(404, 'Archivo no encontrado');
    }

    return response()->download($filePath, $filename);
  }

  public function prepararExportacionCasoPendiente($key)
  {
    try {
      $params = Cache::pull($key);

      if (!is_array($params)) {
        abort(404, 'Clave de exportación inválida o expirada');
      }

      $casoId = $params['casoId'] ?? '';

      ini_set('memory_limit', '-1');
      ini_set('max_execution_time', '360');

      $filename = Helpers::generateCasoPendientesPdf($casoId);

      return response()->json([
        'filename' => $filename
      ]);
    } catch (Throwable $e) {
      Log::error("Error al preparar exportación de proforma: " . $e->getMessage());
      return response()->json(['error' => 'Error al generar el archivo'], 500);
    }
  }

  public function descargarExportacionCasoPendiente($filename)
  {
    $path = storage_path("app/public/casos/$filename");

    if (!file_exists($path)) {
      abort(404, 'Archivo no encontrado');
    }

    return response()->download($path, $filename);
  }
}

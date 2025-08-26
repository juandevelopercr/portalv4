<?php

namespace App\Http\Controllers\reports;

use App\Http\Controllers\Controller;
use App\Livewire\Transactions\Export\TransactionExportFromView;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ReportTransactionController extends Controller
{
  public function prepararExportacionTransacciones($key)
  {
    try {
      $params = Cache::pull($key);

      if (!is_array($params)) {
        abort(404, 'Clave de exportación inválida o expirada');
      }

      $search = $params['search'] ?? '';
      $filters = $params['filters'] ?? [];
      $selectedIds = $params['selectedIds'] ?? [];
      $sortBy = $params['sortBy'] ?? 'transactions.transaction_date';
      $sortDir = $params['sortDir'] ?? 'DESC';
      $perPage = $params['perPage'] ?? 10;

      $query = Transaction::search($search, $filters)
        ->orderBy($sortBy, $sortDir)
        ->limit($perPage); // por ejemplo, limitar a 1000 registros
      //->paginate($perPage);

      if (!empty($selectedIds)) {
        $query->whereIn('transactions.id', $selectedIds);
      }

      $exportPath = storage_path('app/public/exports');
      if (!File::exists($exportPath)) {
        File::makeDirectory($exportPath, 0777, true);
      } else {
        foreach (File::files($exportPath) as $file) {
          $modified = Carbon::createFromTimestamp($file->getMTime());
          if ($modified->diffInMinutes(now()) >= 3) {
            File::delete($file->getPathname());
          }
        }
      }

      $filename = 'transactions-' . now()->format('Ymd_His') . '.xlsx';
      $relativePath = "exports/$filename";
      $storagePath = "public/$relativePath";

      ini_set('memory_limit', '-1');
      ini_set('max_execution_time', '360');

      Excel::store(new TransactionExportFromView($query), $storagePath);

      return response()->json(['filename' => $filename]);
    } catch (Throwable $e) {
      Log::error("Error al preparar exportación de transacciones: " . $e->getMessage());
      return response()->json(['error' => 'Error al generar el archivo'], 500);
    }
  }

  public function descargarExportacionTransacciones($filename)
  {
    $path = storage_path("app/public/exports/$filename");

    if (!file_exists($path)) {
      abort(404, 'Archivo no encontrado');
    }

    return response()->download($path, $filename);
  }
}

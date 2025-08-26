<?php

namespace App\Http\Controllers\billing;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
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
    // Buscar la factura por su key única
    $invoice = Transaction::where('key', $key)->firstOrFail();

    // Tu función existente para generar PDF
    $filename = Helpers::generateComprobanteElectronicoPdf($invoice->id);  // Reemplaza con tu función real

    $path = storage_path("app/public/invoices/$filename");

    if (!file_exists($path)) {
      abort(404, 'Archivo no encontrado');
    }

    return response()->download($path, $filename);
  }
}

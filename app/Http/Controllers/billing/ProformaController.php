<?php

namespace App\Http\Controllers\billing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProformaController extends Controller
{
  public function index()
  {
    return view('content.billing.proformas.index', []);
  }

  public function history()
  {
    return view('content.billing.history.index', []);
  }

  public function seguimiento()
  {
    return view('content.billing.seguimiento.index', []);
  }

  public function digitalCreditNote()
  {
    return view('content.billing.digital.credit-note', []);
  }

  public function digitalDebitNote()
  {
    return view('content.billing.digital.debit-note', []);
  }

  public function cuentasPorCobrar()
  {
    return view('content.billing.proformas.cuentas-por-cobrar', []);
  }

  public function cotizaciones()
  {
    return view('content.billing.cotizaciones.index', []);
  }

  public function buscador()
  {
    return view('content.billing.buscador.index', []);
  }
}

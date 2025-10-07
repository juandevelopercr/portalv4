<?php

namespace App\Http\Controllers\reports;

use App\Models\Contact;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FacturacionDetalladaReport;

class ReportFacturacionDetalladaController extends Controller
{
  public function index()
  {
    //$clients = Contact::all();
    return view('content.reports.facturacion-detallada');
  }
}

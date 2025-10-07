<?php

namespace App\Http\Controllers\reports;

use App\Models\Contact;
use Illuminate\Http\Request;
use App\Exports\ComisionReport;
use App\Exports\FacturacionReport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class ReportTripsController extends Controller
{
  public function index()
  {
    //$clients = Contact::all();
    return view('content.reports.trips');
  }
}

<?php

namespace App\Http\Controllers\reports;

use App\Models\Contact;
use Illuminate\Http\Request;
use App\Exports\ComisionReport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class ReportRegistroController extends Controller
{
  public function index()
  {
    return view('content.reports.registro');
  }
}

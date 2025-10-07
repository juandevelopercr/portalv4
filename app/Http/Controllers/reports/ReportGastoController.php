<?php

namespace App\Http\Controllers\reports;

use App\Models\Contact;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class ReportGastoController extends Controller
{
  public function index()
  {
    return view('content.reports.gastos');
  }
}

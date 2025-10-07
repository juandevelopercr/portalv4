<?php

namespace App\Http\Controllers\reports;

use App\Exports\CustomersReport;
use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CustomersReportController extends Controller
{
  public function index()
  {
    //$clients = Contact::all();
    return view('content.reports.customer');
  }
}

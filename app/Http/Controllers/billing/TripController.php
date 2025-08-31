<?php

namespace App\Http\Controllers\billing;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TripController extends Controller
{
  public function index()
  {
    return view('content.billing.trips.index', []);
  }
}

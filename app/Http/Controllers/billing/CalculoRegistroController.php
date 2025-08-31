<?php

namespace App\Http\Controllers\billing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CalculoRegistroController extends Controller
{
  public function index()
  {
    return view('content.billing.calculo-registro.index', []);
  }
}

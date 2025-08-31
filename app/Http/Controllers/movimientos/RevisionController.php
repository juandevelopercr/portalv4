<?php

namespace App\Http\Controllers\movimientos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RevisionController extends Controller
{
  public function index()
  {
    return view('content.revisiones.index', []);
  }
}

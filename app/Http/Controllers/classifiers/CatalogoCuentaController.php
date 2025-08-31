<?php

namespace App\Http\Controllers\classifiers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CatalogoCuentaController extends Controller
{
  public function index()
  {
    return view('content.classifiers.catalogo-cuentas.index', []);
  }
}

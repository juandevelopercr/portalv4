<?php

namespace App\Http\Controllers\classifiers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GrupoEmpresarialController extends Controller
{
  public function index()
  {
    return view('content.classifiers.grupos-empresariales.index', []);
  }
}

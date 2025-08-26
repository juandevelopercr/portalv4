<?php

namespace App\Http\Controllers\classifiers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClasificadorController extends Controller
{
  public function towns()
  {
    return view('content.classifiers.towns.index', []);
  }

  public function sellers()
  {
    return view('content.classifiers.sellers.index', []);
  }

  public function companies()
  {
    return view('content.classifiers.companies.index', []);
  }

  public function serviciosProveedores()
  {
    return view('content.classifiers.service-provider.index', []);
  }
}

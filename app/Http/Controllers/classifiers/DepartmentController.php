<?php

namespace App\Http\Controllers\classifiers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
  public function index()
  {
    return view('content.classifiers.departments.index', []);
  }
}

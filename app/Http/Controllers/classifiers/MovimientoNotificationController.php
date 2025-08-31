<?php

namespace App\Http\Controllers\classifiers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MovimientoNotificationController extends Controller
{
  public function index()
  {
    return view('content.movimiento-notifications.index', []);
  }
}

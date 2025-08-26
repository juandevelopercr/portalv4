<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class HomePage extends Controller
{
  public function index()
  {
    $user = User::selectRaw('count(id) as count, DATE_FORMAT(created_at, "%Y-%m") as month')
      ->groupBy('month')
      ->orderBy('month', 'ASC')
      ->get();

    $months = $user->pluck('month');
    $counts = $user->pluck('count');

    return view('content.pages.pages-home', compact('months', 'counts'));
  }
}

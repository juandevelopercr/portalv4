<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{

  public function index()
  {
    $user = User::selectRaw('count(id) as count, DATE_FORMAT(created_at, "%Y-%m") as month')
      ->groupBy('month')
      ->orderBy('month', 'ASC')
      ->get();

    $months = $user->pluck('month');
    $counts = $user->pluck('count');

    return view('content.users.index', compact('months', 'counts'));
  }
}

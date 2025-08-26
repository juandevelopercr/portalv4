<?php

namespace App\Http\Controllers\rolesPersmissions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AccessRoles extends Controller
{
  public function index()
  {
    return view('content.rolesPersmissions.access-roles');
  }
}

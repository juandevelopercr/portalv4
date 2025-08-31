<?php

namespace App\Http\Controllers\providerAndSellers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProviderAndSellerController extends Controller
{
  public function index()
  {
    return view('content.provider-and-seller.index', []);
  }
}

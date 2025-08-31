<?php

namespace App\Http\Controllers\customers;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
  public function customer()
  {
    $columns = [
      ['key' => 'id', 'label' => 'ID', 'searchable' => true],
      ['key' => 'name', 'label' => 'Nombre', 'searchable' => true],
      ['key' => 'email', 'label' => 'Email', 'searchable' => true],
    ];

    return view('content.customers.index', [
      'query' => Contact::query(), // Consulta base
      'columns' => $columns,
    ]);
  }

  public function supplier()
  {
    $columns = [
      ['key' => 'id', 'label' => 'ID', 'searchable' => true],
      ['key' => 'name', 'label' => 'Nombre', 'searchable' => true],
      ['key' => 'email', 'label' => 'Email', 'searchable' => true],
    ];

    return view('content.supplier.index', [
      'query' => Contact::query(), // Consulta base
      'columns' => $columns,
    ]);
  }
}

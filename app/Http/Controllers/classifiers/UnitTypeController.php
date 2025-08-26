<?php

namespace App\Http\Controllers\classifiers;

use App\Http\Controllers\Controller;
use App\Models\UnitType;
use Illuminate\Http\Request;

class UnitTypeController extends Controller
{
  public function index()
  {
    return view('content.classifiers.unit-types.index', []);
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function getUnitTypes(Request $request)
  {
    $columns = [
      1 => 'id',
      2 => 'code',
      3 => 'name',
      4 => 'active'
    ];

    $search = [];

    $totalData = UnitType::count();

    $totalFiltered = $totalData;

    $limit = $request->input('length');
    $start = $request->input('start');
    $order = $columns[$request->input('order.0.column')];
    $dir = $request->input('order.0.dir');

    if (empty($request->input('search.value'))) {
      $unittypes = UnitType::offset($start)
        ->limit($limit)
        ->orderBy($order, $dir)
        ->get();
    } else {
      $search = $request->input('search.value');

      $unittypes = UnitType::where('id', 'LIKE', "%{$search}%")
        ->orWhere('name', 'LIKE', "%{$search}%")
        ->orWhere('code', 'LIKE', "%{$search}%")
        ->offset($start)
        ->limit($limit)
        ->orderBy($order, $dir)
        ->get();

      $totalFiltered = UnitType::where('id', 'LIKE', "%{$search}%")
        ->orWhere('name', 'LIKE', "%{$search}%")
        ->orWhere('code', 'LIKE', "%{$search}%")
        ->count();
    }

    $data = [];

    if (!empty($unittypes)) {
      // providing a dummy id instead of database ids
      $ids = $start;


      foreach ($unittypes as $unittype) {
        $nestedData['id'] = $unittype->id;
        $nestedData['fake_id'] = ++$ids;
        $nestedData['name'] = $unittype->name;
        $nestedData['code'] = $unittype->code;
        $nestedData['active'] = $unittype->active;
        $data[] = $nestedData;
      }
    }

    return response()->json([
      'draw' => intval($request->input('draw')),
      'recordsTotal' => intval($totalData),
      'recordsFiltered' => intval($totalFiltered),
      'code' => 200,
      'data' => $data
    ]);
  }

  public function create()
  {
    return view('content.classifiers.unit-types.form');
  }

  public function store(Request $request)
  {
    $validatedData = $request->validate([
      'name' => 'required|string|max:255',
      'code' => 'required|string|max:255',
      'active' => 'nullable|boolean'
    ]);

    try {

      // Crear el usuario con la contraseña encriptada
      $unittype = UnitType::create([
        'name' => $validatedData['name'],
        'code' => $validatedData['code'],
        'active' => $validatedData['active']
      ]);

      // Responder con éxito
      return response()->json([
        'message' => __('The registry has been created successfully'),
        'type' => 'success'
      ]);
    } catch (\Exception $e) {
      dd($e);
      // Si ocurre un error, responder con mensaje de error
      return response()->json([
        'message' => __('An error has occurred. Failed to create the registry'),
        'type' => 'error'
      ]);
    }
  }


  public function show(UnitType $unit)
  {
    return view('content.classifiers.unit-types.show', compact('unit'));
  }

  public function edit(UnitType $unit)
  {
    return view('content.classifiers.unit-types.form', compact('unit'));
  }

  public function update(Request $request, UnitType $unit)
  {

    $validatedData = $request->validate([
      'name' => 'required|string|max:255',
      'code' => 'required|string|max:255',
      'active' => 'nullable|boolean'
    ]);

    try {
      // Actualizar los campos del usuario
      $unit->fill($validatedData);

      $result = $unit->save();

      // Responder con éxito
      return response()->json([
        'message' => __('The registry has been updated successfully'),
        'type' => 'success'
      ]);
    } catch (\Exception $e) {
      // Si ocurre un error, responde con mensaje de error
      return response()->json([
        'message' => __('An error has occurred. Failed to update the registry'),
        'type' => 'error'
      ]);
    }
  }


  public function destroy(UnitType $unit)
  {
    try {
      $unit->delete();
      // Si todo sale bien, redirigir con mensaje de éxito
      return response()->json([
        'message' => __('The registry has been deleted successfully'),
        'type' => 'success'
      ]);
    } catch (\Illuminate\Database\QueryException $e) {
      // Verificar si el error es por clave foránea
      if ($e->getCode() === '23000') { // Código de error SQL para restricciones de clave foránea
        return response()->json([
          'message' => 'Cannot delete register because it has related records',
          'type' => 'error'
        ]); // Código 400 para indicar error de solicitud
      }

      // Si es otro tipo de error, mostrar un mensaje genérico
      return response()->json([
        'message' => 'An error has occurred. Failed to delete the registry',
        'type' => 'error'
      ]); // Código 500 para indicar error del servidor
    }
  }
}

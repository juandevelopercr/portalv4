<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\State;
use App\Models\City;

class LocationController extends Controller
{
  public function getStates($country_id)
  {
    $states = State::where('country_id', $country_id)->get();
    return response()->json($states);
  }

  public function getCities($state_id)
  {
    $cities = City::where('state_id', $state_id)->get();
    return response()->json($cities);
  }
}

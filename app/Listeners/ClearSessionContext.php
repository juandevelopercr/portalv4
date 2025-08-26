<?php
// app/Listeners/ClearSessionContext.php
namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Session;

class ClearSessionContext
{
  public function handle(Logout $event)
  {
    Session::forget('context');
    Session::forget('pending_roles');
    Session::forget('pending_role');
    Session::forget('pending_departments');
  }
}

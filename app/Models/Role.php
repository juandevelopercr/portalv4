<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
  protected $connection = 'mysql'; // o 'master', según tu config
}

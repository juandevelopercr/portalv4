<?php

namespace App\Models;

use App\Models\Bank;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Role;

class UserRoleDepartmentBank extends Model
{
  protected $table = 'user_role_department_banks';

  protected $fillable = [
    'user_id',
    'role_id',
    'department_id',
    'bank_id'
  ];

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public function role(): BelongsTo
  {
    return $this->belongsTo(Role::class);
  }

  public function department(): BelongsTo
  {
    return $this->belongsTo(Department::class);
  }

  public function bank(): BelongsTo
  {
    return $this->belongsTo(Bank::class);
  }
}

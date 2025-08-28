<?php

namespace App\Models;

use App\Models\AreaPractica;
use App\Models\Contact;
use App\Models\Sector;
use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ContactContacto extends TenantModel
{
  protected $table = 'contacts_contactos';

  protected $fillable = [
    'contact_id',
    'name',
    'email',
    'telefono',
    'ext',
    'celular',
    'department_id'
  ];

  // Relaciones
  public function contact(): BelongsTo
  {
    return $this->belongsTo(Contact::class);
  }

  // Relaciones CORREGIDAS:
  public function department(): BelongsTo
  {
    return $this->belongsTo(Department::class, 'department_id');
  }

  public function scopeSearch($query, $value, $filters = [])
  {
    // Definir las columnas que quieres seleccionar
    $columns = [
      'contacts_contactos.id',
      'contacts_contactos.contact_id',
      'contacts_contactos.name',
      'contacts_contactos.email',
      'contacts_contactos.telefono',
      'contacts_contactos.ext',
      'contacts_contactos.celular',
      'contacts_contactos.department_id',
      'departments.name as department_name',
      'contacts_contactos.created_at'
    ];

    $query->select($columns)
      ->leftJoin('departments', 'department_id', '=', 'departments.id')
      ->where(function ($q) use ($value) {
        $q->where('contacts_contactos.name', 'like', "%{$value}%")
          ->orWhere('contacts_contactos.email', 'like', "%{$value}%")
          ->orWhere('contacts_contactos.telefono', 'like', "%{$value}%");
      });

    // Aplica filtros adicionales si están definidos
    if (!empty($filters['filter_department'])) {
      $query->where('departments.name', 'like', '%' . $filters['filter_department'] . '%');
    }

    if (!empty($filters['filter_name'])) {
      $query->where('contacts_contactos.name', 'like', '%' . $filters['filter_name'] . '%');
    }

    if (!empty($filters['filter_email'])) {
      $query->where('contacts_contactos.email', 'like', '%' . $filters['filter_email'] . '%');
    }

    if (!empty($filters['filter_phone'])) {
      $query->where('contacts_contactos.telefono', 'like', '%' . $filters['filter_phone'] . '%');
    }

    if (!empty($filters['filter_ext'])) {
      $query->where('contacts_contactos.ext', 'like', '%' . $filters['filter_ext'] . '%');
    }

    if (!empty($filters['filter_celular'])) {
      $query->where('contacts_contactos.celular', 'like', '%' . $filters['filter_celular'] . '%');
    }

    return $query;
  }

  public function getHtmlColumnAction(): string
  {
    $user = auth()->user();
    $iconSize = 'bx-md';

    $html = '<div class="d-flex align-items-center flex-nowrap">';

    // Editar
    if ($user->can('edit-clients')) {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2 text-primary"
                title="Editar"
                wire:click="edit({$this->id})"
                wire:loading.attr="disabled"
                wire:target="edit">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="edit"></i>
                <i class="bx bx-edit {$iconSize}" wire:loading.remove wire:target="edit"></i>
            </button>
        HTML;
    }

    // Eliminar
    if ($user->can('delete-clients')) {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2 text-danger"
                title="Eliminar"
                wire:click.prevent="confirmarAccion({$this->id}, 'delete',
                    '¿Está seguro que desea eliminar este registro?',
                    'Después de confirmar, el registro será eliminado',
                    'Sí, proceder')">
                <i class="bx bx-trash {$iconSize}"></i>
            </button>
        HTML;
    }

    $html .= '</div>';
    return $html;
  }

  public function getHtmlColumnArea()
  {
    $htmlColumn = '';
    if ($this->areaPractica) {
      $htmlColumn = $this->areaPractica->pluck('name')->join(', ');
    } else
      $htmlColumn = "<span class=\"text-gray-500\">-</span>";
    return $htmlColumn;
  }

  public function getHtmlColumnSector()
  {
    $htmlColumn = '';
    if ($this->sectores)
      $htmlColumn = $this->sectores->pluck('name')->join(', ');
    else
      $htmlColumn = "<span class=\"text-gray-500\">-</span>";
    return $htmlColumn;
  }
}

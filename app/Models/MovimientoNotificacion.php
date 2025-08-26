<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimientoNotificacion extends Model
{
  protected $table = 'movimientos_notificaciones';

  protected $fillable = [
    'nombre',
    'email',
    'copia',
    'activo',
    'enviar_rechazo',
    'enviar_aprobado',
  ];

  protected $casts = [
    'activo' => 'boolean',
    'enviar_rechazo' => 'boolean',
    'enviar_aprobado' => 'boolean',
  ];

  public function getHtmlColumnRechazo()
  {
    if ($this->enviar_rechazo)
      $output = '<i class="bx bx-check-circle text-success fs-4" title="Sí"></i>';
    else
      $output = '<i class="bx bx-x-circle text-danger fs-4" title="No"></i>';
    return $output;
  }

  public function getHtmlColumnAprobado()
  {
    if ($this->enviar_aprobado)
      $output = '<i class="bx bx-check-circle text-success fs-4" title="Sí"></i>';
    else
      $output = '<i class="bx bx-x-circle text-danger fs-4" title="No"></i>';
    return $output;
  }

  public function getHtmlColumnActivo()
  {
    if ($this->activo)
      $output = '<i class="bx bx-check-circle text-success fs-4" title="Sí"></i>';
    else
      $output = '<i class="bx bx-x-circle text-danger fs-4" title="No"></i>';
    return $output;
  }

  public function scopeSearch($query, $value, $filters = [])
  {
    // Definir las columnas que quieres seleccionar
    $columns = [
      'movimientos_notificaciones.id',
      'nombre',
      'email',
      'copia',
      'enviar_rechazo',
      'enviar_aprobado',
      'activo'
    ];

    $query->select($columns);

    // Aplica filtros adicionales si están definidos
    if (!empty($filters['filter_nombre'])) {
      $query->where('nombre', 'like', '%' . $filters['filter_nombre'] . '%');
    }

    if (!empty($filters['filter_email'])) {
      $query->where('email', 'like', '%' . $filters['filter_email'] . '%');
    }

    if (!empty($filters['filter_copia'])) {
      $query->where('copia', 'like', '%' . $filters['filter_copia'] . '%');
    }

    if (isset($filters['filter_enviar_rechazo']) && !is_null($filters['filter_enviar_rechazo'])  && $filters['filter_enviar_rechazo'] !== '') {
      $query->where('enviar_rechazo', '=', $filters['filter_enviar_rechazo']);
    }

    if (isset($filters['filter_enviar_aprobado']) && !is_null($filters['filter_enviar_aprobado'])  && $filters['filter_enviar_aprobado'] !== '') {
      $query->where('enviar_aprobado', '=', $filters['filter_enviar_aprobado']);
    }

    if (isset($filters['filter_activo']) && !is_null($filters['filter_activo'])  && $filters['filter_activo'] !== '') {
      $query->where('activo', '=', $filters['filter_activo']);
    }

    return $query;
  }


  public function getHtmlColumnAction(): string
  {
    $user = auth()->user();
    $iconSize = 'bx-md';

    $html = '<div class="d-flex align-items-center flex-nowrap">';

    // Editar
    if ($user->can('edit-movimiento')) {
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
    if ($user->can('delete-movimiento')) {
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
}

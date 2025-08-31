<?php

namespace App\Models;

use App\Models\ExonerationType;
use App\Models\Institution;
use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionLineTax extends TenantModel
{
  use HasFactory;

  protected $table = 'transactions_lines_taxes';

  protected $fillable = [
    'transaction_line_id',
    'tax_type_id',
    'tax_rate_id',
    'tax',
    'tax_type_other',
    'factor_calculo_tax',
    'count_unit_type',
    'percent',
    'proporcion',
    'volumen_unidad_consumo',
    'impuesto_unidad',
    'tax_amount',

    'exoneration_type_id',
    'exoneration_doc',
    'exoneration_doc_other',
    'exoneration_institution_id',
    'exoneration_institute_other',
    'exoneration_article',
    'exoneration_inciso',
    'exoneration_date',
    'exoneration_percent',
    'exoneration_tarifa_iva',
  ];

  // Relaciones
  public function transactionLine()
  {
    return $this->belongsTo(TransactionLine::class, 'transaction_line_id');
  }

  public function taxType()
  {
    return $this->belongsTo(TaxType::class, 'tax_type_id');
  }

  public function taxRate()
  {
    return $this->belongsTo(TaxRate::class, 'tax_rate_id');
  }

  public function exonerationType()
  {
    return $this->belongsTo(ExonerationType::class, 'exoneration_type_id');
  }

  public function exonerationInstitution()
  {
    return $this->belongsTo(Institution::class, 'exoneration_institution_id');
  }

  public function getHtmlColumnAction(): string
  {
    $user = auth()->user();
    $iconSize = 'bx-md';

    $html = '<div class="d-flex align-items-center flex-nowrap">';

    // Editar
    if ($user->can('edit-lineas-proformas')) {
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
    if ($user->can('delete-lineas-proformas')) {
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

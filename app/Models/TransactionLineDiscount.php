<?php

namespace App\Models;

use App\Models\DiscountType;
use App\Models\FactoryLevelTax;
use App\Models\TenantModel;
use App\Models\TransactionLine;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionLineDiscount extends TenantModel
{
  use HasFactory;

  protected $table = 'transactions_lines_discounts';

  protected $fillable = [
    'transaction_line_id',
    'discount_percent',
    'discount_amount',
    'discount_type_id',
    'discount_type_other',
    'nature_discount',
  ];

  // Relación con la línea de transacción
  public function transactionLine()
  {
    return $this->belongsTo(TransactionLine::class, 'transaction_line_id');
  }

  // Relación con el tipo de descuento
  public function discountType()
  {
    return $this->belongsTo(DiscountType::class, 'discount_type_id');
  }

  public function getHtmlColumnAction(): string
  {
    $user = auth()->user();
    $iconSize = 'bx-md';

    $html = '<div class="d-flex align-items-center flex-nowrap">';

    // Editar
    if ($user->can('edit-proformas')) {
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
    if ($user->can('delete-proformas')) {
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

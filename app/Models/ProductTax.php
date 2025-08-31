<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductTax extends TenantModel
{
  use HasFactory;

  protected $table = 'products_taxes';

  protected $fillable = [
    'product_id',
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
    'exoneration_type_id',
    'exoneration_doc',
    'exoneration_doc_other',
    'exoneration_article',
    'exoneration_inciso',
    'exoneration_institution_id',
    'exoneration_institute_other',
    'exoneration_date',
    'exoneration_percent',
  ];

  // Relaciones
  public function product()
  {
    return $this->belongsTo(Product::class, 'product_id');
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
    return $this->belongsTo(ExonerationType::class);
  }

  public function exonerationInstitute()
  {
    return $this->belongsTo(Institution::class, 'exoneration_institution_id', 'id');
  }

  public function scopeSearch($query, $value, $filters = [])
  {
    // Definir las columnas que quieres seleccionar
    $columns = [
      'products_taxes.id',
      'product_id',
      'tax_type_id',
      'tax_types.name as tax_type_name',
      'tax_types.code as tax_type_code',
      'tax_rate_id',
      'tax_rates.name as tax_rate_name',
      'tax_rates.code as tax_rate_code',
      'tax',
      'tax_type_other',
      'factor_calculo_tax',
      'count_unit_type',
      'products_taxes.percent',
      'proporcion',
      'volumen_unidad_consumo',
      'impuesto_unidad',
      'exoneration_type_id',
      'exoneration_doc',
      'exoneration_article',
      'exoneration_inciso',
      'exoneration_institution_id',
      'exoneration_doc_other',
      'exoneration_date',
      'exoneration_percent',
    ];

    $query->select($columns)
      ->join('tax_types', 'products_taxes.tax_type_id', '=', 'tax_types.id')
      ->join('tax_rates', 'products_taxes.tax_rate_id', '=', 'tax_rates.id')
      ->where(function ($q) use ($value) {
        $q->where('tax_types.name', 'like', "%{$value}%")
          ->orWhere('tax_types.code', 'like', "%{$value}%")
          ->orWhere('tax_rates.name', 'like', "%{$value}%")
          ->orWhere('tax_rates.code', 'like', "%{$value}%")
          ->orWhere('tax', 'like', "%{$value}%")
          ->orWhere('tax_type_other', 'like', "%{$value}%")
          ->orWhere('factor_calculo_tax', 'like', "%{$value}%")
          ->orWhere('count_unit_type', 'like', "%{$value}%")
          ->orWhere('products_taxes.percent', 'like', "%{$value}%")
          ->orWhere('proporcion', 'like', "%{$value}%")
          ->orWhere('volumen_unidad_consumo', 'like', "%{$value}%")
          ->orWhere('impuesto_unidad', 'like', "%{$value}%");
      });

    //dd($filters);

    // Aplica filtros adicionales si están definidos
    if (!empty($filters['filter_tax_type_name'])) {
      $query->where('tax_types.name', 'like', '%' . $filters['filter_tax_type_name'] . '%');
    }

    if (!empty($filters['filter_tax_rate_name'])) {
      $query->where('tax_rates.name', 'like', '%' . $filters['filter_tax_rate_name'] . '%');
    }

    if (!empty($filters['filter_tax'])) {
      $query->where('tax', 'like', '%' . $filters['filter_tax'] . '%');
    }

    if (!empty($filters['filter_tax_type_other'])) {
      $query->where('tax_type_other', 'like', '%' . $filters['filter_tax_type_other'] . '%');
    }

    if (!empty($filters['filter_factor_calculo_tax'])) {
      $query->where('factor_calculo_tax', 'like', '%' . $filters['filter_factor_calculo_tax'] . '%');
    }

    if (!empty($filters['filter_count_unit_type'])) {
      $query->where('count_unit_type', 'like', '%' . $filters['filter_count_unit_type'] . '%');
    }

    if (!empty($filters['filter_percent'])) {
      $query->where('products_taxes.percent', 'like', '%' . $filters['filter_percent'] . '%');
    }

    if (!empty($filters['filter_proporcion'])) {
      $query->where('proporcion', 'like', '%' . $filters['filter_proporcion'] . '%');
    }

    if (!empty($filters['filter_volumen_unidad_consumo'])) {
      $query->where('volumen_unidad_consumo', 'like', '%' . $filters['filter_volumen_unidad_consumo'] . '%');
    }

    if (!empty($filters['filter_impuesto_unidad'])) {
      $query->where('impuesto_unidad', 'like', '%' . $filters['filter_impuesto_unidad'] . '%');
    }

    return $query;
  }

  public function getHtmlColumnAction(): string
  {
    $user = auth()->user();
    $iconSize = 'bx-md';

    $html = '<div class="d-flex align-items-center flex-nowrap">';

    // Editar
    if ($user->can('edit-services')) {
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
    if ($user->can('delete-services')) {
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

<?php

namespace App\Models;

use App\Models\Business;
use App\Models\Canton;
use App\Models\Country;
use App\Models\District;
use App\Models\EconomicActivity;
use App\Models\IdentificationType;
use App\Models\InvoiceLayout;
use App\Models\Province;
use App\Models\SellingPriceGroup;
use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessLocation extends TenantModel
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'business_id',
    'location_parent_id',
    'code',
    'name',
    'commercial_name',
    'admin_invoice_layout_id',
    'pos_invoice_layout_id',
    'admin_quotation_layout_id',
    'pos_quotation_layout_id',
    'selling_price_group_id',
    'print_receipt_on_invoice',
    'receipt_printer_type',
    'printer_id',
    'phone_code',
    'phone',
    'email',
    'website',
    'environment',
    'identification_type_id',
    'identification',
    'country_id',
    'zip_code',
    'province_id',
    'canton_id',
    'district_id',
    'address',
    'other_signs',
    'certificate_pin',
    'api_user_hacienda',
    'api_password',
    'certificate_digital_file',
    'proveedor',
    'registrofiscal8707',
    'numero_sucursal',
    'numero_punto_venta',
    'active',
  ];

  // Relaciones
  public function business()
  {
    return $this->belongsTo(Business::class);
  }

  public function identificationType()
  {
    return $this->belongsTo(IdentificationType::class);
  }

  public function adminInvoiceLayout()
  {
    return $this->belongsTo(InvoiceLayout::class, 'admin_invoice_layout_id');
  }

  public function country()
  {
    return $this->belongsTo(Country::class, 'country_id');
  }

  public function province()
  {
    return $this->belongsTo(Province::class, 'province_id');
  }

  public function canton()
  {
    return $this->belongsTo(Canton::class, 'canton_id');
  }

  public function distrit()
  {
    return $this->belongsTo(District::class, 'district_id');
  }

  public function posInvoiceLayout()
  {
    return $this->belongsTo(InvoiceLayout::class, 'pos_invoice_layout_id');
  }

  public function sellingPriceGroup()
  {
    return $this->belongsTo(SellingPriceGroup::class);
  }

  public function economicActivities()
  {
    return $this->belongsToMany(EconomicActivity::class, 'business_locations_economic_activities', 'location_id', 'economic_activity_id');
  }

  public function scopeSearch($query, $value, $filters = [])
  {
    // Definir las columnas que quieres seleccionar
    $columns = [
      'business_locations.id',
      'business_id',
      'location_parent_id',
      'business_locations.name',
      'commercial_name',
      'admin_invoice_layout_id',
      'pos_invoice_layout_id',
      'admin_quotation_layout_id',
      'pos_quotation_layout_id',
      'selling_price_group_id',
      'print_receipt_on_invoice',
      'receipt_printer_type',
      'printer_id',
      'phone_code',
      'business_locations.code',
      'phone',
      'email',
      'website',
      'environment',
      'identification_type_id',
      'identification',
      'business_locations.country_id',
      'zip_code',
      'business_locations.province_id',
      'business_locations.canton_id',
      'business_locations.district_id',
      'address',
      'other_signs',
      'certificate_pin',
      'api_user_hacienda',
      'api_password',
      'certificate_digital_file',
      'proveedor',
      'registrofiscal8707',
      'business_locations.active',
      'numero_sucursal',
      'numero_punto_venta',
    ];

    $query->select($columns)
      ->leftJoin('identification_types', 'business_locations.identification_type_id', '=', 'identification_types.id')
      ->leftJoin('countries', 'business_locations.country_id', '=', 'countries.id')
      ->leftJoin('provinces', 'business_locations.province_id', '=', 'provinces.id')
      ->leftJoin('cantons', 'business_locations.canton_id', '=', 'cantons.id')
      ->leftJoin('districts', 'business_locations.district_id', '=', 'districts.id')
      ->where(function ($q) use ($value) {
        $q->where('business_locations.name', 'like', "%{$value}%")
          ->orWhere('business_locations.code', 'like', "%{$value}%")
          ->orWhere('business_locations.commercial_name', 'like', "%{$value}%")
          ->orWhere('business_locations.phone_code', 'like', "%{$value}%")
          ->orWhere('business_locations.email', 'like', "%{$value}%")
          ->orWhere('business_locations.phone', 'like', "%{$value}%")
          ->orWhere('business_locations.identification', 'like', "%{$value}%")
          ->orWhere('business_locations.proveedor', 'like', "%{$value}%")
          ->orWhere('business_locations.registrofiscal8707', 'like', "%{$value}%")
          ->orWhere('countries.name', 'like', "%{$value}%")
          ->orWhere('provinces.name', 'like', "%{$value}%")
          ->orWhere('cantons.name', 'like', "%{$value}%")
          ->orWhere('districts.name', 'like', "%{$value}%")
          ->orWhere('identification_types.name', 'like', "%{$value}%");
      });


    // Aplica filtros adicionales si están definidos
    if (!empty($filters['filter_name'])) {
      $query->where('business_locations.name', 'like', '%' . $filters['filter_name'] . '%');
    }

    if (!empty($filters['filter_commercial_name'])) {
      $query->where('commercial_name', 'like', '%' . $filters['filter_commercial_name'] . '%');
    }

    if (!empty($filters['filter_identification'])) {
      $query->where('identification', 'like', '%' . $filters['filter_identification'] . '%');
    }

    if (!empty($filters['filter_phone'])) {
      $query->where('phone', 'like', '%' . $filters['filter_phone'] . '%');
    }

    if (!empty($filters['filter_email'])) {
      $query->where('email', 'like', '%' . $filters['filter_email'] . '%');
    }

    if (!empty($filters['filter_active'])) {
      $query->where('active', 'like', '%' . $filters['filter_active'] . '%');
    }

    return $query;
  }

  public function getHtmlColumnAction(): string
  {
    $user = auth()->user();
    $iconSize = 'bx-md';

    $html = '<div class="d-flex align-items-center flex-nowrap">';

    // Editar
    if ($user->can('edit-settings')) {
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

    $html .= '</div>';
    return $html;
  }
}

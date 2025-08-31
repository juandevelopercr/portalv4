<?php

namespace App\Models;

use App\Models\Business;
use App\Models\BusinessCustomerCalculoRegistro;
use App\Models\Canton;
use App\Models\ConditionSale;
use App\Models\Country;
use App\Models\CustomerGroup;
use App\Models\District;
use App\Models\EconomicActivity;
use App\Models\IdentificationType;
use App\Models\Province;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Contact extends Model
{
  use HasFactory, SoftDeletes;

  const CUSTOMER = 'customer';
  const SUPPLIER = 'supplier';

  const FACTURA = 'FACTURA';
  const TIQUETE = 'TIQUETE';

  public $timestamps = true;

  protected $fillable = [
    'business_id',
    'type',
    'name',
    'commercial_name',
    'email',
    'email_cc',
    'code',
    'condition_sale_id',
    'identification_type_id',
    'identification',
    'economic_activity_id',
    'country_id',
    'province_id',
    'canton_id',
    'district_id',
    'other_signs',
    'address',
    'zip_code',
    'dob',
    'phone',
    'invoice_type',
    'aplicarImpuesto',
    'pay_term_number',
    'pay_term_type',
    'credit_limit',
    'balance',
    'total_rp',
    'total_rp_used',
    'total_rp_expired',
    'shipping_address',
    'customer_group_id',
    'is_default',
    'active',
    'created_by',
  ];

  public function business()
  {
    return $this->belongsTo(Business::class);
  }

  public function conditionSale()
  {
    return $this->belongsTo(ConditionSale::class, 'condition_sale_id');
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

  public function identificationType()
  {
    return $this->belongsTo(IdentificationType::class);
  }

  public function economicActivity()
  {
    return $this->belongsTo(EconomicActivity::class);
  }

  public function customerGroup()
  {
    return $this->belongsTo(CustomerGroup::class);
  }

  public function economicActivities()
  {
    return $this->belongsToMany(EconomicActivity::class, 'contacts_economic_activities', 'contact_id', 'economic_activity_id');
  }

  public function calculoRegistrosAsignados()
  {
    return $this->hasMany(BusinessCustomerCalculoRegistro::class);
  }

  public static function getCode()
  {
    $lastCode = Contact::max('code'); // Obtiene el último valor de 'code'
    $newCode = str_pad((int)$lastCode + 1, 4, '0', STR_PAD_LEFT); // Genera el nuevo código de 4 dígitos
    return $newCode;
  }

  public function scopeSearch($query, $value, $filters = [])
  {
    // Definir las columnas que quieres seleccionar
    $columns = [
      'contacts.id',
      'contacts.name',
      'contacts.commercial_name',
      'contacts.email',
      'contacts.phone',
      'contacts.code',
      'contacts.email_cc',
      'contacts.identification',
      'contacts.invoice_type',
      'contacts.aplicarImpuesto',
      'contacts.active',
      'contacts.created_at',
      'countries.name as country_name',
      'provinces.name as province_name',
      'cantons.name as canton_name',
      'districts.name as district_name',
      'customer_groups.name as customer_group_name',
      'condition_sales.name as condition_sale',
      'identification_types.name as identification_type'
    ];

    $query->select($columns)
      ->join('identification_types', 'contacts.identification_type_id', '=', 'identification_types.id')
      ->leftJoin('countries', 'contacts.country_id', '=', 'countries.id')
      ->leftJoin('provinces', 'contacts.province_id', '=', 'provinces.id')
      ->leftJoin('cantons', 'contacts.canton_id', '=', 'cantons.id')
      ->leftJoin('districts', 'contacts.district_id', '=', 'districts.id')
      ->leftJoin('customer_groups', 'contacts.customer_group_id', '=', 'customer_groups.id')
      ->leftJoin('condition_sales', 'contacts.condition_sale_id', '=', 'condition_sales.id')
      ->where(function ($q) use ($value) {
        $q->where('contacts.name', 'like', "%{$value}%")
          ->orWhere('contacts.commercial_name', 'like', "%{$value}%")
          ->orWhere('contacts.email', 'like', "%{$value}%")
          ->orWhere('contacts.phone', 'like', "%{$value}%")
          ->orWhere('contacts.code', 'like', "%{$value}%")
          ->orWhere('countries.name', 'like', "%{$value}%")
          ->orWhere('provinces.name', 'like', "%{$value}%")
          ->orWhere('cantons.name', 'like', "%{$value}%")
          ->orWhere('districts.name', 'like', "%{$value}%")
          ->orWhere('customer_groups.name', 'like', "%{$value}%")
          ->orWhere('condition_sales.name', 'like', "%{$value}%")
          ->orWhere('contacts.identification', 'like', "%{$value}%")
          ->orWhere('identification_types.name', 'like', "%{$value}%");
      });

    // Aplica filtros adicionales si están definidos
    if (!empty($filters['filter_name'])) {
      $query->where('contacts.name', 'like', '%' . $filters['filter_name'] . '%');
    }

    if (!empty($filters['filter_identification_type'])) {
      $query->where('identification_types.id', '=', $filters['filter_identification_type']);
    }

    if (!empty($filters['filter_identification'])) {
      $query->where('contacts.identification', 'like', '%' . $filters['filter_identification'] . '%');
    }

    if (!empty($filters['filter_phone'])) {
      $query->where('contacts.phone', 'like', '%' . $filters['filter_phone'] . '%');
    }

    if (!empty($filters['filter_condition_sale_name'])) {
      $query->where('contacts.condition_sale_id', '=', $filters['filter_condition_sale_name']);
    }

    if (!empty($filters['filter_email'])) {
      $query->where('contacts.email', 'like', '%' . $filters['filter_email'] . '%');
    }

    if (!empty($filters['filter_email_cc'])) {
      $query->where('contacts.email_cc', 'like', '%' . $filters['filter_email_cc'] . '%');
    }

    if (!empty($filters['filter_created_at'])) {
      $range = explode(' to ', $filters['filter_created_at']);

      if (count($range) === 2) {
        try {
          // Validar y convertir las fechas del rango
          $start = Carbon::createFromFormat('d-m-Y', $range[0])->format('Y-m-d');
          $end = Carbon::createFromFormat('d-m-Y', $range[1])->format('Y-m-d');

          // Aplicar filtro si ambas fechas son válidas
          $query->whereBetween('contacts.created_at', [$start, $end]);
        } catch (\Exception $e) {
          // Manejar el caso de fechas inválidas (opcional: log o ignorar)
        }
      } else {
        try {
          // Validar y convertir la fecha única
          $singleDate = Carbon::createFromFormat('d-m-Y', $filters['filter_created_at'])->format('Y-m-d');

          // Aplicar filtro si la fecha es válida
          $query->whereDate('contacts.created_at', $singleDate);
        } catch (\Exception $e) {
          // Manejar el caso de fecha inválida (opcional: log o ignorar)
        }
      }
    }

    if (isset($filters['filter_active']) && !is_null($filters['filter_active'])  && $filters['filter_active'] !== '') {
      $query->where('contacts.active', '=', $filters['filter_active']);
    }

    return $query;
  }

  public function getHtmlColumnActive()
  {
    if ($this->active) {
      $output = '<i class="bx bx-check-circle text-success fs-4" title="Activo"></i>';
    } else {
      $output = '<i class="bx bx-x-circle text-danger fs-4" title="Inactivo"></i>';
    }
    return $output;
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
}

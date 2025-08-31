<?php

namespace App\Models;

use App\Models\Business;
use App\Models\ProductHonorariosTimbre;
use App\Models\UnitType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class Product extends TenantModel
{
  use HasFactory;

  // Definir los campos que pueden ser llenados (Mass Assignable)
  protected $fillable = [
    'name',
    'business_id',
    'code',
    'type',
    'unit_type_id',
    'sub_unit_ids',
    'brand_id',
    'category_id',
    'caby_code',
    'price',
    'is_expense',
    'enable_quantity',
    'enable_stock',
    'alert_quantity',
    'secure_quantity',
    'sku',
    'barcode_type',
    'expiry_period',
    'expiry_period_type',
    'enable_sr_no',
    'weight',
    'image',
    'description',
    'warranty_id',
    'not_for_selling',
    'rp_cumul',
    'commission',
    'suppliers',
    'active',
  ];

  // Relación con otras tablas
  public function business()
  {
    return $this->belongsTo(Business::class);
  }
  /*
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    */

  public function unitType()
  {
    return $this->belongsTo(UnitType::class);
  }

  public function taxes()
  {
    return $this->hasMany(ProductTax::class);
  }

  // En tu modelo
  public function scopeSearch($query, $value, $filters = [])
  {
    // Definir las columnas que quieres seleccionar
    $columns = [
      'products.id',
      'products.name',
      'business_id',
      'type',
      'products.code',
      'unit_type_id',
      'unit_types.code as unit_type',
      'caby_code',
      'price',
      'enable_quantity',
      'sku',
      'image',
      'description',
      'products.active'
    ];
    //$masterUsers = DB::connection('mysql')->table('users')->select('id', 'name');

    $query->select($columns)
      ->join('business', 'products.business_id', '=', 'business.id')
      ->join('unit_types', 'products.unit_type_id', '=', 'unit_types.id')
      /*
      ->leftJoinSub($masterUsers, 'master_users', function ($join) {
        $join->on('products.created_by', '=', 'master_users.id');
      })
        */
      ->where(function ($q) use ($value) {
        $q->where('products.name', 'like', "%{$value}%")
          ->orWhere('sku', 'like', "%{$value}%")
          ->orWhere('caby_code', 'like', "%{$value}%")
          ->orWhere('unit_types.name', 'like', "%{$value}%")
          ->orWhere('description', 'like', "%{$value}%");
      });

    // Aplica filtros adicionales si están definidos
    if (!empty($filters['filter_code'])) {
      $query->where('products.code', 'like', '%' . $filters['filter_code'] . '%');
    }

    if (!empty($filters['filter_name'])) {
      $query->where('products.name', 'like', '%' . $filters['filter_name'] . '%');
    }

    if (!empty($filters['filter_caby_code'])) {
      $query->where('caby_code', 'like', '%' . $filters['filter_caby_code'] . '%');
    }

    if (!empty($filters['filter_unit_type'])) {
      $query->where('unit_types.code', 'like', '%' . $filters['filter_unit_type'] . '%');
    }

    if (isset($filters['filter_active']) && !is_null($filters['filter_active'])  && $filters['filter_active'] !== '') {
      $query->where('products.active', '=', $filters['filter_active']);
    }

    return $query;
  }

  /**
   * Generates product sku
   *
   * @param string $string
   *
   * @return generated sku (string)
   */
  public function generateProductSku($string)
  {
    $business_id = 1;
    $sku_prefix = Business::where('id', $business_id)->value('sku_prefix');

    return $sku_prefix . str_pad($string, 4, '0', STR_PAD_LEFT);
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

  public function getHtmlcolumnDepartment()
  {
    $htmlColumn = '';
    if ($this->departments->isNotEmpty())
      $htmlColumn = $this->departments->pluck('name')->join(', ');
    else
      $htmlColumn = "<span class=\"text-gray-500\">-</span>";
    return $htmlColumn;
  }

  public function getHtmlcolumnBank()
  {
    $htmlColumn = '';
    if ($this->banks->isNotEmpty())
      $htmlColumn = $this->banks->pluck('name')->join(', ');
    else
      $htmlColumn = "<span class=\"text-gray-500\">-</span>";
    return $htmlColumn;
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

    // Clonar
    if ($user->can('edit-services')) {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2 text-info"
                title="Clonar"
                wire:click.prevent="confirmarAccion({$this->id}, 'clonar',
                    '¿Está seguro que desea clonar la receta con nombre: {$this->name}?',
                    'Después de confirmar, la receta será clonada',
                    'Sí, proceder')">
                <i class="bx bx-copy {$iconSize}"></i>
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

  public function desgloseTimbreFormula($price, $quantity, $bank_id, $tipo, $currency, $changeType)
  {
    $monto = 0;
    $monto_con_descuento = 0;
    $summonto_sin_descuento = 0;
    $summonto_con_descuento = 0;
    $sum_item_con_descuento_seis_porciento = 0;
    $sum_item_sin_descuento_seis_porciento = 0;
    $datos = array();
    // Es fórmula si define el monto y por cada, además no tiene marcado cascada, grada, Fin Cascada/Grada, Escalonado, Fijo

    if ($tipo != 'GASTO') { // Timbre no importa el banco, Honorario por si acaso lo puse para que quede general, pero para los honoarios no hay calculo de formula
      // Consulta cuando el tipo no es 'GASTO'
      $honorario_timbres = ProductHonorariosTimbre::join('products_banks', 'products_banks.product_id', '=', 'product_honorarios_timbres.product_id')
        ->where('products_banks.bank_id', $bank_id)
        ->where('product_honorarios_timbres.product_id', $this->id)
        ->where('product_honorarios_timbres.tipo', $tipo)
        ->where('product_honorarios_timbres.base', '>', 0)
        ->where([
          'product_honorarios_timbres.tabla_abogado_inscripciones' => 0,
          'product_honorarios_timbres.tabla_abogado_traspasos' => 0,
          'product_honorarios_timbres.fijo' => 0,
          'product_honorarios_timbres.monto_manual' => 0,
        ])
        ->select('product_honorarios_timbres.*')
        ->get();
    } else {
      // Aqui antes no se filtraba por banco pero ahora en el form lo hize obligarotio
      // Consulta cuando el tipo es 'GASTO'
      $honorario_timbres = ProductHonorariosTimbre::join('products_banks', 'products_banks.product_id', '=', 'product_honorarios_timbres.product_id')
        ->where('products_banks.bank_id', $bank_id)
        ->where('product_honorarios_timbres.product_id', $this->id)
        ->where('product_honorarios_timbres.tipo', $tipo)
        ->where('product_honorarios_timbres.base', '>', 0)
        ->where([
          'product_honorarios_timbres.tabla_abogado_inscripciones' => 0,
          'product_honorarios_timbres.tabla_abogado_traspasos' => 0,
          'product_honorarios_timbres.fijo' => 0,
          'product_honorarios_timbres.monto_manual' => 0,
        ])
        ->select('product_honorarios_timbres.*')
        ->get();
    }

    // Los calculos siempre se hacen en colones
    $precio = $this->getMontoColones($currency, $price, $changeType);

    foreach ($honorario_timbres as $dato) {
      if ($dato->porciento == 1) {
        $monto = ($precio * $dato->base) / 100;
      } else
				if ($dato->porcada > 0)
        $monto = ($precio / $dato->porcada) * $dato->base;

      $monto = $currency != Currency::COLONES ? ($monto / $changeType) : $monto;
      $monto = $monto * $quantity;
      $summonto_sin_descuento += round($monto, 2);

      // El descuento se aplica al Enviar a pagar al Registro Nacional dicho caso  (aquí es donde se aplica el 6% a los timbres que así se configuraron)
      if ($tipo == 'GASTO' && $dato->descuento_timbre == true) {
        $sum_item_con_descuento_seis_porciento += round($monto, 2);
      } else
        $sum_item_sin_descuento_seis_porciento += round($monto, 2);

      $summonto_con_descuento += round($monto, 2);
      $monto_con_descuento = round($monto, 2);

      $datos[] = ['titulo' => $dato->description, 'monto_sin_descuento' => round($monto, 2), 'monto_con_descuento' => round($monto_con_descuento, 2)];
    }
    return [
      'monto_sin_descuento' => round($summonto_sin_descuento, 2),
      'monto_con_descuento' => round($summonto_con_descuento, 2),
      'sum_item_con_descuento_seis_porciento' => round($sum_item_con_descuento_seis_porciento, 2),
      'sum_item_sin_descuento_seis_porciento' => round($sum_item_sin_descuento_seis_porciento, 2),
      'datos' => $datos,
    ];
  }

  public function desgloseTablaAbogados($price, $quantity, $bank_id, $tipo, $currency, $changeType)
  {
    $monto = 0;
    $monto_con_descuento = 0;
    $summonto_sin_descuento = 0;
    $summonto_con_descuento = 0;
    $sum_item_con_descuento_seis_porciento = 0;
    $sum_item_sin_descuento_seis_porciento = 0;
    $datos = array();

    if ($tipo != 'GASTO') { // Timbre no importa el banco, Honorario por si acaso lo puse para que quede general, pero para los honoarios no hay calculo de formula
      // Cuando el tipo no es 'GASTO'
      $tabla_abogado = ProductHonorariosTimbre::join('products_banks', 'products_banks.product_id', '=', 'product_honorarios_timbres.product_id')
        ->where('products_banks.bank_id', $bank_id)
        ->where('product_honorarios_timbres.product_id', $this->id)
        ->where('product_honorarios_timbres.tipo', $tipo)
        ->where(function ($query) {
          $query->where('product_honorarios_timbres.tabla_abogado_inscripciones', 1)
            ->orWhere('product_honorarios_timbres.tabla_abogado_traspasos', 1);
        })
        ->select('product_honorarios_timbres.*')
        ->first();
    } else {
      //aqui antes no se filtraba por banco pero en el form lo hice obligatorio
      // Cuando el tipo es 'GASTO'
      $tabla_abogado = ProductHonorariosTimbre::join('products_banks', 'products_banks.product_id', '=', 'product_honorarios_timbres.product_id')
        ->where('products_banks.bank_id', $bank_id)
        ->where('product_honorarios_timbres.product_id', $this->id)
        ->where('product_honorarios_timbres.tipo', $tipo)
        ->where(function ($query) {
          $query->where('product_honorarios_timbres.tabla_abogado_inscripciones', 1)
            ->orWhere('product_honorarios_timbres.tabla_abogado_traspasos', 1);
        })
        ->select('product_honorarios_timbres.*')
        ->first();
    }

    if (!is_null($tabla_abogado)) {
      if ($tabla_abogado->tabla_abogado_inscripciones == 1)
        $tipo_tabla = 1; // para tomar los datos de las inscripciones
      else
        $tipo_tabla = 2; // para tomar los datos de los traspasos de vehículos

      $honorario_timbres = Timbre::where('tipo', $tipo_tabla)
        ->orderBy('orden', 'asc')
        ->get();

      $index = 0;
      $founded = false;
      // Los calculos siempre se hacen en colones
      $precio = $this->getMontoColones($currency, $price, $changeType);

      if (!empty($honorario_timbres)) {
        while ($founded == false && $index <= count($honorario_timbres)) {
          if ($precio <= $honorario_timbres[0]->base) {
            $monto = 0;
            $founded = true;
          } else
						if ($index + 1 < count($honorario_timbres)) {
            if ($precio >= $honorario_timbres[$index]->base && $precio <= $honorario_timbres[$index + 1]->base) {
              $monto = $honorario_timbres[$index + 1]->porcada;
              $founded = true;
            } else
							if ($precio >= $honorario_timbres[$index]->base && $honorario_timbres[$index + 1]->base == 0) {
              $monto = $honorario_timbres[$index + 1]->porcada;
              $founded = true;
            }
          }
          $index++;
        }
      }

      $monto = $currency != Currency::COLONES ? ($monto / $changeType) : $monto;
      $monto = $monto * $quantity;
      $monto_con_descuento = round($monto, 2);
      $summonto_sin_descuento = round($monto, 2);

      // El descuento se aplica al Enviar a pagar al Registro Nacional dicho caso  (aquí es donde se aplica el 6% a los timbres que así se configuraron)
      if ($tipo == 'GASTO' && $tabla_abogado->descuento_timbre == true) {
        $sum_item_con_descuento_seis_porciento += round($monto, 2);
      } else
        $sum_item_sin_descuento_seis_porciento += round($monto, 2);

      $summonto_con_descuento = round($monto, 2);
      $monto_con_descuento = round($monto, 2);

      $datos[] = ['titulo' => $tabla_abogado->description, 'monto_sin_descuento' => round($monto, 2), 'monto_con_descuento' => round($monto_con_descuento, 2)];
    }

    return [
      'monto_sin_descuento' => round($summonto_sin_descuento, 2),
      'monto_con_descuento' => round($summonto_con_descuento, 2),
      'sum_item_con_descuento_seis_porciento' => round($sum_item_con_descuento_seis_porciento, 2),
      'sum_item_sin_descuento_seis_porciento' => round($sum_item_sin_descuento_seis_porciento, 2),
      'datos' => $datos,
    ];
  }

  public function desgloseCalculosFijos($price, $quantity, $bank_id, $tipo, $currency, $changeType)
  {
    $monto = 0;
    $monto_con_descuento = 0;
    $summonto_sin_descuento = 0;
    $summonto_con_descuento = 0;
    $sum_item_con_descuento_seis_porciento = 0;
    $sum_item_sin_descuento_seis_porciento = 0;
    $datos = array();

    // Es fórmula si define el monto y por cada, además no tiene marcado cascada, grada, Fin Cascada/Grada, Escalonado, Fijo
    if ($tipo != 'GASTO') { // Timbre no importa el banco, Honorario por si acaso lo puse para que quede general, pero para los honorarios no hay calculo de formula
      // Para tipos diferentes de 'GASTO'
      $honorario_timbres = ProductHonorariosTimbre::join('products_banks', 'products_banks.product_id', '=', 'product_honorarios_timbres.product_id')
        ->where('products_banks.bank_id', $bank_id)
        ->where('product_honorarios_timbres.product_id', $this->id)
        ->where('product_honorarios_timbres.tipo', $tipo)
        ->where('product_honorarios_timbres.base', '>', 0)
        ->where([
          'product_honorarios_timbres.tabla_abogado_inscripciones' => 0,
          'product_honorarios_timbres.tabla_abogado_traspasos' => 0,
          'product_honorarios_timbres.fijo' => 1,
        ])
        ->select('product_honorarios_timbres.*')
        ->get();
    } else {
      //aqui antes no se filtraba por banco pero en el form lo hice requerido
      // Para 'GASTO' (sin importar banco)
      $honorario_timbres = ProductHonorariosTimbre::join('products_banks', 'products_banks.product_id', '=', 'product_honorarios_timbres.product_id')
        ->where('products_banks.bank_id', $bank_id)
        ->where('product_honorarios_timbres.product_id', $this->id)
        ->where('product_honorarios_timbres.tipo', $tipo)
        ->where('product_honorarios_timbres.base', '>', 0)
        ->where([
          'product_honorarios_timbres.tabla_abogado_inscripciones' => 0,
          'product_honorarios_timbres.tabla_abogado_traspasos' => 0,
          'product_honorarios_timbres.fijo' => 1,
        ])
        ->select('product_honorarios_timbres.*')
        ->get();
    }

    foreach ($honorario_timbres as $dato) {
      $monto = $dato->base;
      $monto = $currency != Currency::COLONES ? ($monto / $changeType) : $monto;
      $monto = $monto * $quantity;
      $summonto_sin_descuento += round($monto, 2);

      // El descuento se aplica al Enviar a pagar al Registro Nacional dicho caso  (aquí es donde se aplica el 6% a los timbres que así se configuraron)
      if ($tipo == 'GASTO' && $dato->descuento_timbre == true) {
        $sum_item_con_descuento_seis_porciento += round($monto, 2);
      } else
        $sum_item_sin_descuento_seis_porciento += round($monto, 2);

      $summonto_con_descuento += round($monto, 2);
      $monto_con_descuento = round($monto, 2);

      $datos[] = ['titulo' => $dato->description, 'monto_sin_descuento' => round($monto, 2), 'monto_con_descuento' => round($monto_con_descuento, 2)];
    }
    return [
      'monto_sin_descuento' => round($summonto_sin_descuento, 2),
      'monto_con_descuento' => round($summonto_con_descuento, 2),
      'sum_item_con_descuento_seis_porciento' => round($sum_item_con_descuento_seis_porciento, 2),
      'sum_item_sin_descuento_seis_porciento' => round($sum_item_sin_descuento_seis_porciento, 2),
      'datos' => $datos,
    ];
  }

  public function desgloseCalculaMontoManual($price, $quantity, $bank_id, $tipo, $currency, $changeType)
  {
    $monto = 0;
    $monto_con_descuento = 0;
    $summonto_sin_descuento = 0;
    $summonto_con_descuento = 0;
    $sum_item_con_descuento_seis_porciento = 0;
    $sum_item_sin_descuento_seis_porciento = 0;
    $datos = array();

    // Es fórmula si define el monto y por cada, además no tiene marcado cascada, grada, Fin Cascada/Grada, Escalonado, Fijo
    if ($tipo != 'GASTO') {
      // Query cuando $tipo no es 'GASTO' y considera el banco
      $honorario_timbres = ProductHonorariosTimbre::join('products_banks', 'products_banks.product_id', '=', 'product_honorarios_timbres.product_id')
        ->where('products_banks.bank_id', $bank_id)
        ->where('product_honorarios_timbres.product_id', $this->id)
        ->where('product_honorarios_timbres.tipo', $tipo)
        ->where([
          'product_honorarios_timbres.tabla_abogado_inscripciones' => 0,
          'product_honorarios_timbres.tabla_abogado_traspasos' => 0,
          'product_honorarios_timbres.fijo' => 0,
          'product_honorarios_timbres.monto_manual' => 1,
        ])
        ->select('product_honorarios_timbres.*')
        ->get();
    } else {
      // Aqui no se filtraba por banco pero en el formulario se puse requerido
      // Query cuando $tipo es 'GASTO' (no considera el banco)
      $honorario_timbres = ProductHonorariosTimbre::join('products_banks', 'products_banks.product_id', '=', 'product_honorarios_timbres.product_id')
        ->where('products_banks.bank_id', $bank_id)
        ->where('product_honorarios_timbres.product_id', $this->id)
        ->where('product_honorarios_timbres.tipo', $tipo)
        ->where([
          'product_honorarios_timbres.tabla_abogado_inscripciones' => 0,
          'product_honorarios_timbres.tabla_abogado_traspasos' => 0,
          'product_honorarios_timbres.fijo' => 0,
          'product_honorarios_timbres.monto_manual' => 1,
        ])
        ->select('product_honorarios_timbres.*')
        ->get();
    }

    // Los calculos siempre se hacen en colones
    $precio = $this->getMontoColones($currency, $price, $changeType);

    foreach ($honorario_timbres as $dato) {
      $monto = $precio;

      $monto = $currency != Currency::COLONES ? ($monto / $changeType) : $monto;

      $monto = $monto * $quantity;
      $summonto_sin_descuento += round($monto, 2);

      // El descuento se aplica al Enviar a pagar al Registro Nacional dicho caso  (aquí es donde se aplica el 6% a los timbres que así se configuraron)
      if ($tipo == 'GASTO' && $dato->descuento_timbre == true) {
        $sum_item_con_descuento_seis_porciento += round($monto, 2);
      } else
        $sum_item_sin_descuento_seis_porciento += round($monto, 2);

      $summonto_con_descuento += round($monto, 2);
      $monto_con_descuento = round($monto, 2);

      $datos[] = ['titulo' => $dato->description, 'monto_sin_descuento' => round($monto, 2), 'monto_con_descuento' => round($monto_con_descuento, 2)];
    }

    return [
      'monto_sin_descuento' => round($summonto_sin_descuento, 2),
      'monto_con_descuento' => round($summonto_con_descuento, 2),
      'sum_item_con_descuento_seis_porciento' => round($sum_item_con_descuento_seis_porciento, 2),
      'sum_item_sin_descuento_seis_porciento' => round($sum_item_sin_descuento_seis_porciento, 2),
      'datos' => $datos,
    ];
  }

  public function desgloseHonorarios($price, $quantity, $bank_id, $tipo, $currency, $changeType)
  {
    $honorarios = ProductHonorariosTimbre::join('products_banks', 'products_banks.product_id', '=', 'product_honorarios_timbres.product_id')
      ->where('products_banks.bank_id', $bank_id)
      ->where('product_honorarios_timbres.product_id', $this->id)
      ->where(function ($query) use ($tipo) {
        $query->where([
          'product_honorarios_timbres.tipo' => $tipo,
          'product_honorarios_timbres.fijo' => 1
        ])
          ->orWhere(function ($query) use ($tipo) {
            $query->where([
              'product_honorarios_timbres.tipo' => $tipo,
              'product_honorarios_timbres.fijo' => 0,
              'product_honorarios_timbres.porciento' => 1
            ]);
          })
          ->orWhere(function ($query) use ($tipo) {
            $query->where([
              'product_honorarios_timbres.tipo' => $tipo,
              'product_honorarios_timbres.fijo' => 0,
              'product_honorarios_timbres.monto_manual' => 0
            ]);
          });
      })
      ->select('product_honorarios_timbres.*')
      ->get();

    $monto = 0;
    $monto_con_descuento = 0;
    $summonto_sin_descuento = 0;
    $summonto_con_descuento = 0;
    $datos = array();

    // Los calculos siempre se hacen en colones
    $precio = $this->getMontoColones($currency, $price, $changeType);

    foreach ($honorarios as $honorario) {
      if ($honorario->fijo == 1 && $honorario->base >= 0) {
        $monto  = $honorario->base;
      } else
				if ($honorario->fijo == 0 && is_null($honorario->honorario_id) && $honorario->porciento == 1 && $honorario->base > 0) {
        $monto = ($precio * $honorario->base) / 100;
      } else
				if ($honorario->fijo == 0 && !is_null($honorario->honorario_id) && $honorario->honorario_id > 0 && $honorario->monto_manual == 0) {
        $monto = $this->desgloseCalculaHonorarioConTablaHonorarioBanco($price, $honorario, $bank_id, $currency, $changeType);
      }

      $monto = $currency != Currency::COLONES ? ($monto / $changeType) : $monto;
      $monto = $monto * $quantity;
      $summonto_sin_descuento += round($monto, 2);

      $summonto_con_descuento += round($monto, 2);
      $monto_con_descuento = round($monto, 2);

      $datos[] = ['titulo' => $honorario->description, 'monto_sin_descuento' => round($monto, 2), 'monto_con_descuento' => round($monto_con_descuento, 2)];
    }

    if (empty($honorarios))
      $datos[] = ['titulo' => '', 'monto_sin_descuento' => round(0, 2), 'monto_con_descuento' => round(0, 2)];

    return [
      'monto_sin_descuento' => round($summonto_sin_descuento, 2),
      'monto_con_descuento' => round($summonto_con_descuento, 2),
      'datos' => $datos,
    ];
  }

  function desgloseCalculaHonorarioConTablaHonorarioBanco($price, $honorario, $bank_id, $currency, $changeType)
  {
    $monto = 0;
    $honorarios_bancos = HonorarioReceta::join('honorarios_banks', 'honorarios_banks.honorario_id', '=', 'honorarios_recetas.honorario_id')
      ->where('honorarios_banks.bank_id', $bank_id)
      ->where('honorarios_recetas.honorario_id', $honorario->honorario_id)
      ->orderBy('honorarios_recetas.orden', 'asc')
      ->select('honorarios_recetas.*')
      ->get();

    $precio = $this->getMontoColones($currency, $price, $changeType);
    $i = 1;
    foreach ($honorarios_bancos as $honorarios_banco) {
      if ($i == 1)
        $formula = ($precio < $honorarios_banco->hasta) ? $precio : $honorarios_banco->hasta;
      else
			if ($i < count($honorarios_bancos))
        $formula = ($precio < $honorarios_banco->desde) ? $honorarios_banco->desde : (($precio < $honorarios_banco->hasta && $precio >= $honorarios_banco->desde) ? $precio : $honorarios_banco->hasta);
      else
        $formula = ($precio > $honorarios_banco->desde) ? $precio : $honorarios_banco->desde;

      $formula = round($formula, 2);
      $tracto_para_calculo = $formula - $honorarios_banco->desde;
      $tracto_para_calculo = round($tracto_para_calculo, 2);
      $monto_a_cobrar = $tracto_para_calculo * $honorarios_banco->porcentaje / 100;
      $monto += round($monto_a_cobrar, 2);
      $i++;
    }
    // Se retorna el monto en colones
    return round($monto, 2);
  }

  protected function getMontoColones($currency, $amount, $changeType)
  {
    $result = $currency != Currency::COLONES ? $amount * $changeType : $amount;
    return $result;
  }
}

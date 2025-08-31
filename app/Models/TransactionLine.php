<?php

namespace App\Models;

use App\Models\Currency;
use App\Models\Hacienda\ComprobanteElectronico\ImpuestoType\DatosImpuestoEspecificoAType;
use App\Models\HonorarioReceta;
use App\Models\Product;
use App\Models\ProductHonorariosTimbre;
use App\Models\TenantModel;
use App\Models\Transaction;
use App\Models\TransactionLineDiscount;
use App\Models\TransactionLineTax;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionLine extends TenantModel
{
  use HasFactory;

  protected $table = 'transactions_lines';

  protected $fillable = [
    'transaction_id',
    'product_id',
    'codigo',
    'codigocabys',
    'detail',
    'quantity',
    'price',
    'discount',
    'subtotal',
    'baseImponible',
    'tax',
    'impuestoAsumidoEmisorFabrica',
    'impuestoNeto',
    'total',
    'exoneration',

    'servGravados',
    'servExentos',
    'servExonerados',
    'servNoSujeto',

    'mercGravadas',
    'mercExentas',
    'mercExoneradas',
    'mercNoSujeta',

    'impuestoServGravados',
    'impuestoMercGravadas',
    'impuestoServExonerados',
    'impuestoMercExoneradas',

    'partida_arancelaria',
    'impuestosEspeciales',
    'hasRegaliaOrBonificacion',
    'hasImpuestoEspecifico'
  ];

  // Relaciones
  public function transaction()
  {
    return $this->belongsTo(Transaction::class, 'transaction_id');
  }

  // Productos
  public function product()
  {
    return $this->belongsTo(Product::class, 'product_id');
  }

  // Relación con TransactionLineTax (uno a muchos)
  public function taxes()
  {
    return $this->hasMany(TransactionLineTax::class, 'transaction_line_id');
  }

  // Relación con descuentos
  public function discounts()
  {
    return $this->hasMany(TransactionLineDiscount::class, 'transaction_line_id');
  }

  public function scopeSearch($query, $value, $filters = [])
  {
    // Definir las columnas que quieres seleccionar
    $columns = [
      'transactions_lines.id',
      'transaction_id',
      'product_id',
      'codigo',
      'codigocabys',
      'detail',
      'quantity',
      'transactions_lines.price',
      'discount',
      'tax',
      'exoneration',
      'subtotal',
      'total',
      'servGravados',
      'mercGravadas',
      'impuestoServGravados',
      'impuestoMercGravadas',
      'impuestoServExonerados',
      'impuestoMercExoneradas',
      'impuestoNeto',
      'servExentos',
      'mercExentas',
      'partida_arancelaria',

      'baseImponible',
      'impuestosEspeciales',
      'impuestoAsumidoEmisorFabrica',
      'hasRegaliaOrBonificacion',
      'hasImpuestoEspecifico',
      'servNoSujeto',
      'mercNoSujeta',

      'servExonerados',
      'mercExoneradas'
    ];

    $query->select($columns)
      ->join('products', 'transactions_lines.product_id', '=', 'products.id')
      ->where(function ($q) use ($value) {
        $q->where('codigocabys', 'like', "%{$value}%")
          ->orWhere('detail', 'like', "%{$value}%")
          ->orWhere('transactions_lines.price', 'like', "%{$value}%")
          ->orWhere('discount', 'like', "%{$value}%")
          ->orWhere('tax', 'like', "%{$value}%");
      });

    // Aplica filtros adicionales si están definidos
    if (!empty($filters['filter_codigocabys'])) {
      $query->where('codigocabys', 'like', '%' . $filters['filter_codigocabys'] . '%');
    }

    if (!empty($filters['filter_detail'])) {
      $query->where('detail', 'like', '%' . $filters['filter_detail'] . '%');
    }


    if (!empty($filters['filter_price'])) {
      $query->where('transactions_lines.price', 'like', '%' . $filters['filter_price'] . '%');
    }

    if (!empty($filters['filter_quantity'])) {
      $query->where('quantity', 'like', '%' . $filters['filter_quantity'] . '%');
    }

    if (!empty($filters['filter_discount'])) {
      $query->where('discount', 'like', '%' . $filters['filter_discount'] . '%');
    }

    if (!empty($filters['filter_subtotal'])) {
      $query->where('subtotal', 'like', '%' . $filters['filter_subtotal'] . '%');
    }

    if (!empty($filters['filter_tax'])) {
      $query->where('tax', 'like', '%' . $filters['filter_tax'] . '%');
    }

    if (!empty($filters['filter_exoneration'])) {
      $query->where('exoneration', 'like', '%' . $filters['filter_exoneration'] . '%');
    }

    if (!empty($filters['filter_total'])) {
      $query->where('total', 'like', '%' . $filters['filter_total'] . '%');
    }

    return $query;
  }

  public function updateTransactionTotals($currency)
  {
    //$currency = $this->transaction->currency_id;
    $changeType = in_array($this->transaction->document_type, [Transaction::PROFORMA, Transaction::NOTACREDITO, Transaction::NOTADEBITO])
      ? $this->transaction->proforma_change_type
      : $this->transaction->factura_change_type;
    $bank_id = $this->transaction->bank_id;
    $discounts = !is_null($this->discounts) ? $this->discounts : collect([]);
    //$taxes = !is_null($this->taxes) ? $this->taxes : collect([]);

    $this->discount = $this->getDescuento() ?? 0;
    $this->subtotal = $this->getSubtotal() ?? 0;

    $this->impuestosEspeciales = $this->getImpuestosEspeciales();

    $this->baseImponible = $this->getBaseImponible();

    $this->hasRegaliaOrBonificacion = $this->getHasRegaliaOrBonificacion();
    $this->hasImpuestoEspecifico = $this->getHasImpuestoEspecifico();

    $this->tax = $this->getImpuesto() ?? 0;

    $this->impuestoAsumidoEmisorFabrica = $this->transaction->document_type == 'FEC' ? 0 : $this->getImpuestoAsumidoEmisorFabrica();

    // Servicios
    $this->servGravados = $this->getServGravado() ?? 0;

    $this->servExentos = $this->getServExento() ?? 0;

    $this->servExonerados = $this->getServExonerado() ?? 0;
    //$this->getImpuestoServExonerado() ?? 0;  // para borrarlo

    $this->servNoSujeto = $this->getServNoSujeto() ?? 0;

    // Mercancias
    $this->mercGravadas = $this->getMercanciaGravada() ?? 0;

    $this->mercExentas = $this->getMercanciaExenta() ?? 0;

    $this->mercExoneradas = $this->getMercExonerada() ?? 0;

    $this->mercNoSujeta = $this->getMercNoSujeta() ?? 0;

    $this->exoneration = $this->servExonerados + $this->mercExoneradas;

    $this->impuestoNeto = $this->getMontoImpuestoNeto() ?? 0;

    $this->total = $this->getMontoTotalLinea() ?? 0;

    $this->save();
  }

  protected function getMontoColones($currency, $amount, $changeType)
  {
    $result = $currency != Currency::COLONES ? $amount * $changeType : $amount;
    return $result;
  }

  public function getMonto()
  {
    $amount = $this->price * $this->quantity;
    return number_format($amount, 5, '.', '');
  }

  // Es para forma el xml de FE
  public function getMontoTotal()
  {
    $amount = $this->getMonto();
    return number_format($amount, 5, '.', '');
  }

  public function getDescuento()
  {
    $discounts = !is_null($this->discounts) ? $this->discounts : collect([]);
    $monto = $this->getMonto();
    $descuento = $this->calculaMontoDescuentos($monto, $discounts);

    return number_format($descuento ?? 0, 5, '.', '');
  }

  public function getSubTotal()
  {
    $subtotal = $this->getMonto() - $this->discount;
    return number_format($subtotal, 5, '.', '');
  }

  // Se puede incluir un máximo de 5 repeticiones de descuentos, cada descuento adicional
  //se calcula sobre la base menos el descuento anterior.
  protected function calculaMontoDescuentos($monto, $discounts)
  {
    $total_discount = 0;

    foreach ($discounts as $discount) {
      // Aplica cada descuento sobre el monto restante
      $descuento_aplicado = $monto * ($discount->discount_percent / 100);

      $discount->discount_amount = $descuento_aplicado;
      $discount->save();

      $total_discount += $descuento_aplicado;

      // Resta el descuento del monto
      $monto -= $descuento_aplicado;
    }

    $total_discount = round($total_discount, 2);

    return $total_discount;
  }

  public function getImpuesto()
  {
    $taxes = !is_null($this->taxes) ? $this->taxes : collect([]);
    $subtotal = $this->getSubTotal();
    $tax = $this->calculaMontoImpuestos($subtotal, $taxes);

    return number_format($tax ?? 0, 5, '.', '');
  }

  //Cada tax se calcula sobre la base menos el impuesto
  protected function calculaMontoImpuestos($subtotal, $taxes)
  {
    $total_tax = 0;

    // CACERES
    foreach ($taxes as $tax) {
      $tax->tax_amount = $this->calculaMontoImpuestoConReglasHacienda($tax);
      $tax->save();
      $total_tax += $tax->tax_amount;
      /*
      // Aplica cada impuesto sobre el monto restante
      $tax_aplicado = $subtotal * ($tax->tax / 100);

      // Se actualiza el monto del tax
      $tax->tax_amount = $tax_aplicado;
      $tax->save();

      $total_tax += $tax_aplicado;

      // Resta el impuesto del monto
      $subtotal -= $tax_aplicado;
      */
    }

    $total_tax = round($total_tax, 2);

    return $total_tax;
  }

  // Devuelve el monto de precio * cantidad si el servicio está gravado
  public function getServGravado()
  {
    // Obtiene el impuesto si es un servicio
    $gravado = 0;
    $taxes = !is_null($this->taxes) ? $this->taxes : collect([]);
    if ($this->product->type == 'service') {
      if ($this->calculaMontoImpuestoExonerado() > 0) {
        //(1-porcentaje de exoneración) por el monto de la venta
        //▪Porcentaje de exoneración: (Tarifa Exonerada /Tarifa IVA)
        //$gravado = (1 - $this->exoneration_percent / 100) * $this->getSubtotal();
        $gravado = $this->getMontoTotal() - $this->calculaMontoImpuestoExonerado();
      } else if (!empty($taxes)) {
        $gravado = $this->getMontoTotal();
      }
    }
    return number_format($gravado, 5, '.', '');
  }

  // Devuelve el monto de precio * cantidad si la mercancia está gravado
  public function getMercanciaGravada()
  {
    // Obtiene el impuesto si es un producto
    $gravado = 0;
    $taxes = !is_null($this->taxes) ? $this->taxes : collect([]);
    if ($this->product->type != 'service') {
      if ($this->calculaMontoImpuestoExonerado() > 0) {
        //(1-porcentaje de exoneración) por el monto de la venta
        //▪Porcentaje de exoneración: (Tarifa Exonerada /Tarifa IVA)
        //$gravado = (1 - $this->exoneration_percent / 100) * $this->getSubtotal();
        $gravado = $this->getMontoTotal() - $this->calculaMontoImpuestoExonerado();
      } else if (!empty($taxes)) {
        $gravado = $this->getMontoTotal();
      }
    }
    return number_format($gravado, 5, '.', '');
  }

  public function getServExonerado()
  {
    // Obtiene el impuesto si es un servicio
    if ($this->product->type == 'service')
      $impuesto = $this->calculaMontoImpuestoExonerado();
    else
      $impuesto = 0;
    return number_format($impuesto, 5, '.', '');
  }

  public function getMercExonerada()
  {
    // Obtiene el impuesto si es un servicio
    if ($this->product->type != 'service')
      $impuesto = $this->calculaMontoImpuestoExonerado();
    else
      $impuesto = 0;
    return number_format($impuesto, 5, '.', '');
  }

  protected function calculaMontoImpuestoExonerado()
  {
    $monto_exonerado = 0;

    $taxes = !is_null($this->taxes) ? $this->taxes : collect([]);
    //$subtotal = $this->getSubTotal();

    foreach ($taxes as $tax) {
      /*
      // Aplica cada impuesto sobre el monto restante
      $tax_aplicado = $subtotal * ($tax->tax / 100);

      // Resta el descuento del monto
      $subtotal -= $tax_aplicado;
      */

      if (!is_null($tax->exoneration_type_id) && !is_null($tax->exoneration_institution_id) && $tax->exoneration_percent > 0) {
        $monto_exonerado += $tax->tax_amount * $tax->exoneration_percent / 100;
      }
    }

    //$monto_exonerado = round($monto_exonerado, 2);
    return $monto_exonerado;
  }

  public function getImpuestoServGravado()
  {
    // Obtiene el impuesto si es un servicio
    if ($this->product->type == 'service')
      $impuesto = $this->getImpuesto();
    else
      $impuesto = 0;
    return number_format($impuesto, 5, '.', '');
  }

  public function getImpuestoMercanciaGravada()
  {
    // Obtiene el impuesto si es un producto
    if ($this->product->type != 'service')
      $impuesto = $this->getImpuesto();
    else
      $impuesto = 0;
    return number_format($impuesto, 5, '.', '');
  }

  public function getImpuestoServExonerado()
  {
    // Obtiene el impuesto si es un servicio
    if ($this->product->type == 'service')
      $impuesto = $this->calculaMontoImpuestoExonerado();
    else
      $impuesto = 0;
    return number_format($impuesto, 5, '.', '');
  }

  public function getImpuestoMercanciaExonerado()
  {
    // Obtiene el impuesto si es un product
    if ($this->product->type != 'service')
      $impuesto = $this->calculaMontoImpuestoExonerado();
    else
      $impuesto = 0;
    return number_format($impuesto, 5, '.', '');
  }

  public function getMontoImpuestoNeto()
  {
    //Este monto se obtiene al restar el campo “Monto del Impuesto” menos “Monto del Impuesto Exonerado” o el
    //campo “Impuestos Asumidos por el Emisor o cobrado a Nivel de Fábrica” cuando corresponda. ▪En caso de no contar con datos en los campos “Monto del
    //Impuesto Exonerado” o “Impuestos Asumidos por el Emisor o cobrado a Nivel de Fábrica” el monto será el mismo al del impuesto calculado
    $impuestoNeto = $this->tax - ($this->exoneration ? $this->exoneration : 0);

    if ($this->impuestoAsumidoEmisorFabrica > 0) {
      $impuestoNeto = $this->tax - $this->impuestoAsumidoEmisorFabrica;
    }

    return number_format($impuestoNeto, 5, '.', '');
  }

  public function getServExento()
  {
    $exento = 0;
    // Obtiene el monto exento si es un servicio
    if ($this->product->type == 'service') {
      $taxes = $this->taxes;
      foreach ($taxes as $tax) {
        if (in_array($tax->taxRate->code, ['01', '11'])) {
          return number_format($this->getMontoTotal(), 5, '.', '');
        }
      }
    }
    return number_format($exento, 5, '.', '');
  }

  public function getMercanciaExenta()
  {
    $exento = 0;
    // Obtiene el monto exento si es un servicio
    if ($this->product->type == 'service') {
      $taxes = $this->taxes;
      foreach ($taxes as $tax) {
        if (in_array($tax->taxRate->code, ['01', '11'])) {
          return number_format($this->getMontoTotal(), 5, '.', '');
        }
      }
    }
    return number_format($exento, 5, '.', '');
  }

  public function getMontoTotalLinea()
  {
    //Es la sumatoria de los campos “Subtotal” e “Impuesto Neto”
    $montoTotalLinea = $this->subtotal + $this->impuestoNeto;

    return number_format($montoTotalLinea, 5, '.', '');
  }

  public function getHtmlColumnAction(): string
  {
    $user = auth()->user();
    $iconSize = 'bx-md';

    $html = '<div class="d-flex align-items-center flex-nowrap">';

    // Editar
    if ($user->can('edit-lineas-proformas') && $this->transaction->proforma_status == Transaction::PROCESO) {
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
    if ($user->can('delete-lineas-proformas') && $this->transaction->proforma_status == Transaction::PROCESO) {
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

  //*************************************************//
  //******Funciones para el calculo de la FE ********//
  private function getBaseImponible()
  {
    $baseImponible = $this->getSubTotal() + $this->impuestosEspeciales;
    return $baseImponible;
  }

  private function getImpuestosEspeciales()
  {
    $iva = 0;

    foreach ($this->taxes as $tax) {
      if ($tax->taxType->code == '02') { // Impuesto Selectivo de Consumo
        $iva += number_format($this->getSubTotal() * $tax->factor_calculo_tax, 5, '.', '');
        // Nota: En el caso de utilizar el nodo “Detalle de productos del surtido, paquetes o combos”, este campo se calcula como la
        // sumatoria de los montos del Impuesto Selectivo de Consumo individuales de las líneas de detalle del surtido que se deben
        // incluir en estos casos, en caso de contar con más de una
      }

      if ($tax->taxType->code == '04') { // Impuesto específico de Bebidas Alcohólicas
        $iva += number_format($this->getCantidad() * $tax->proporcion * $tax->impuesto_unidad, 5, '.', '');
        // Nota: En el caso de utilizar el nodo “Detalle de productos del surtido,paquetes o combos”, este campo se calcula como la
        // sumatoria de los montos del Impuesto Específico a las Bebidas Alcohólicas individuales de las líneas de detalle del
        // surtido que se deben incluir en estos casos, en caso de contar con más de una unidad de surtido dicho monto se debe de
        // multiplicar por la cantidad de la línea principal
      }

      if ($tax->taxType->code == '05') { // Impuesto Específico sobre las bebidas envasadas sin contenido alcohólico y jabones de tocador
        // Nota: En el caso de utilizar el nodo “Detalle de productos del surtido,paquetes o combos”, este campo se calcula como la
        // sumatoria de los montos del Impuesto Específico a las Bebidas Alcohólicas individuales de las líneas de detalle del
        // surtido que se deben incluir en estos casos, en caso de contar con más de una unidad de surtido dicho monto se debe de
        // multiplicar por la cantidad de la línea principal

        // si el producto es jabón de tocador
        if ($this->itemIsBebida($this->codigocabys)) {
          $div = $tax->impuesto_unidad / ($tax->volumen_unidad_consumo ?? 1);
          $iva += number_format($this->getCantidad() * $tax->count_unit_type * $div, 5, '.', '');
        } else
        if ($this->itemIsJabon($this->codigocabys)) {
          $iva += number_format($this->getCantidad() * $tax->volumen_unidad_consumo * $tax->impuesto_unidad, 5, '.', '');
        }
      }

      if ($tax->taxType->code == '12') { // Impuesto Específico al Cemento
        $iva += number_format($this->getSubTotal() * $tax->factor_calculo_tax, 5, '.', '');
      }
    }
    return $iva;
  }

  private function getHasRegaliaOrBonificacion()
  {
    /*
    Al utilizar el código de Naturaleza del Descuento 01
    correspondiente a “Regalías” o 03 de “Bonificaciones” y el
    código de impuesto 01, se debe utilizar para el cálculo del
    impuesto el campo denominado “Monto Total” y la tarifa
    */
    $discounts = $this->discounts;
    foreach ($discounts as $discount) {
      if (in_array($discount->discountType->code, ['01', '03'])) {
        return true; // Devuelve true inmediatamente al encontrar una coincidencia
      }
    }
    return false; // Solo llega aquí si no encontró coincidencias
  }

  private function getHasImpuestoEspecifico()
  {
    $taxes = $this->taxes;
    foreach ($taxes as $tax) {
      if (in_array($tax->taxType->code, ['03', '04', '05', '06', '12'])) {
        return true;
      }
    }
    return false;
  }

  private function calculaMontoImpuestoConReglasHacienda($tax)
  {
    $hasRegaliaOrBonificacion = $this->getHasRegaliaOrBonificacion();

    // Calcular el monto del impuesto
    $iva = $tax->tax_amount;
    if ($tax->taxType->code == '10') // Tarifa exenta
      $iva = 0.00000;

    if ($tax->taxType->code == '01' && $hasRegaliaOrBonificacion) {
      $iva = number_format((float)($this->getMontoTotal() * $tax->tax) / 100, 5, '.', '');
    }

    if ($tax->taxType->code == '08') { // IVA Régimen de Bienes Usados (Factor)
      $iva = number_format($this->getSubTotal() * $tax->factor_calculo_tax, 5, '.', '');
    }

    if ($tax->taxType->code == '07') { // IVA (cálculo especial)
      $iva = number_format((float)($this->getMontoTotal() * $tax->tax) / 100, 5, '.', '');
      // Nota: En el caso de utilizar el nodo “Detalle de productos del surtido, paquetes o combos”, este
      // campo se calcula como la sumatoria de los montos de IVA individuales de las líneas de detalle del surtido que se deben
      // incluir en estos casos, en caso de contar con más de una unidad de surtido dicho monto se debe de multiplicar por la
      // cantidad de la línea principal.
    }

    if ($tax->taxType->code == '02') { // Impuesto Selectivo de Consumo
      $iva = number_format($this->getSubTotal() * $tax->factor_calculo_tax, 5, '.', '');
      // Nota: En el caso de utilizar el nodo “Detalle de productos del surtido, paquetes o combos”, este campo se calcula como la
      // sumatoria de los montos del Impuesto Selectivo de Consumo individuales de las líneas de detalle del surtido que se deben
      // incluir en estos casos, en caso de contar con más de una
    }

    if ($tax->taxType->code == '03') { // Impuesto Único a los Combustibles
      $iva = number_format($tax->count_unit_type * $tax->impuesto_unidad, 5, '.', '');
    }

    if ($tax->taxType->code == '04') { // Impuesto específico de Bebidas Alcohólicas
      $iva = number_format($this->getCantidad() * $tax->proporcion * $tax->impuesto_unidad, 5, '.', '');
      // Nota: En el caso de utilizar el nodo “Detalle de productos del surtido,paquetes o combos”, este campo se calcula como la
      // sumatoria de los montos del Impuesto Específico a las Bebidas Alcohólicas individuales de las líneas de detalle del
      // surtido que se deben incluir en estos casos, en caso de contar con más de una unidad de surtido dicho monto se debe de
      // multiplicar por la cantidad de la línea principal
    }

    if ($tax->taxType->code == '05') { // Impuesto Específico sobre las bebidas envasadas sin contenido alcohólico y jabones de tocador
      // Nota: En el caso de utilizar el nodo “Detalle de productos del surtido,paquetes o combos”, este campo se calcula como la
      // sumatoria de los montos del Impuesto Específico a las Bebidas Alcohólicas individuales de las líneas de detalle del
      // surtido que se deben incluir en estos casos, en caso de contar con más de una unidad de surtido dicho monto se debe de
      // multiplicar por la cantidad de la línea principal

      // si el producto es jabón de tocador
      if ($this->itemIsBebida($this->codigocabys)) {
        $div = $tax->impuesto_unidad / ($tax->volumen_unidad_consumo ?? 1);
        $iva = number_format($this->getCantidad() * $tax->count_unit_type * $div, 5, '.', '');
      } else
        if ($this->itemIsJabon($this->codigocabys)) {
        $iva = number_format($this->getCantidad() * $tax->volumen_unidad_consumo * $tax->impuesto_unidad, 5, '.', '');
      }
    }

    if ($tax->taxType->code == '06') { // Impuesto a los Productos de Tabaco
      $iva = number_format($this->getCantidad() * $tax->count_unit_type, 5, '.', '');
      // Nota: En el caso de utilizar el nodo “Detalle de productos del surtido,paquetes o combos”, este campo se calcula como la
      // sumatoria de los montos del Impuesto Específico a las Bebidas Alcohólicas individuales de las líneas de detalle del
      // surtido que se deben incluir en estos casos, en caso de contar con más de una unidad de surtido dicho monto se debe de
      // multiplicar por la cantidad de la línea principal
    }

    if ($tax->taxType->code == '12') { // Impuesto Específico al Cemento
      $iva = number_format($this->getSubTotal() * $tax->factor_calculo_tax, 5, '.', '');
    }

    return $iva;
  }

  private function getImpuestoAsumidoEmisorFabrica()
  {
    $hasRegaliaOrBonificacion = $this->hasRegaliaOrBonificacion;
    $hasImpuestoEspecifico = $this->hasImpuestoEspecifico;
    $impuestoAsumido = 0;
    if ($hasRegaliaOrBonificacion || $hasImpuestoEspecifico) {
      foreach ($this->taxes as $tax) {
        if ($hasRegaliaOrBonificacion || in_array($tax->taxType->code, ['03', '04', '05', '06', '12']))
          $impuestoAsumido += $tax->tax_amount;
      }
    }

    return number_format($impuestoAsumido, 5, '.', '');
  }

  private function itemIsBebida($codigocabys)
  {
    // Obtener los primeros 3 caracteres
    $primerosTres = substr($codigocabys, 0, 3);

    // Lista de valores permitidos
    $valoresPermitidos = ['244'];

    // Retorna true si está en la lista
    return in_array($primerosTres, $valoresPermitidos);
  }

  private function itemIsJabon($codigocabys)
  {
    // Obtener los primeros 3 caracteres
    $primerosTres = substr($codigocabys, 0, 3);

    // Lista de valores permitidos
    $valoresPermitidos = ['353'];

    // Retorna true si está en la lista
    return in_array($primerosTres, $valoresPermitidos);
  }

  private function getServNoSujeto()
  {
    if ($this->product->type != 'service')
      return number_format(0, 5, '.', '');

    $taxes = $this->taxes;
    foreach ($taxes as $tax) {
      if (in_array($tax->taxRate->code, ['01', '11'])) {
        return number_format($this->getMontoTotal(), 5, '.', '');
      }
    }
    return number_format(0, 5, '.', '');
  }

  private function getMercNoSujeta()
  {
    if ($this->product->type == 'service')
      return number_format(0, 5, '.', '');

    $taxes = $this->taxes;
    foreach ($taxes as $tax) {
      if (in_array($tax->taxRate->code, ['01', '11'])) {
        return number_format($this->getMontoTotal(), 5, '.', '');
      }
    }
    return number_format(0, 5, '.', '');
  }
}

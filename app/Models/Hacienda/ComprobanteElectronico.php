<?php

namespace App\Models\Hacienda;

use \Exception;
use App\Models\Business;
use App\Models\ConditionSale;
use App\Models\Transaction; // ✅ Agregado
use App\Models\Hacienda\EmisorType;
use App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType;
use App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\ResumenComprobanteAType;
use App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\InformacionReferenciaAType;
use App\Models\Hacienda\IdentificacionType;
use App\Models\Hacienda\TelefonoType;
use App\Models\Hacienda\UbicacionType;
use Carbon\Carbon;
use DateTime;
use DOMDocument;
use DOMElement;
use Illuminate\Support\Facades\Session;

/**
 * Class representing ComprobanteElectronico
 *
 * Elemento Raiz de la Facturacion Electrónica
 */
class ComprobanteElectronico extends ComprobanteElectronico\ComprobanteElectronicoAType
{
  private Transaction $transaction; // ✅ Propiedad corregida

  private $hasRegaliaOrBonificacion;

  /**
   * Constructor para inicializar la Factura Electrónica con los datos de la transacción.
   */
  public function __construct(Transaction $transaction)
  {
    $this->transaction = $transaction;

    // ✅ Asignación de valores al objeto ComprobanteElectronico
    $this->setClave($transaction->key);

    $business = Session::get('user.business');
    if (!$business) {
      $business = Business::find(1);
    }
    $this->setProveedorSistemas($business->proveedorSistemas);

    // Si es FEC se invierten los datos de emisor y receptor
    if ($transaction->document_type == 'FEC') {
      $this->setCodigoActividadEmisor($transaction->contactEconomicActivity->code);
      $this->setCodigoActividadReceptor($transaction->locationEconomicActivity->code);
    } else
      $this->setCodigoActividadEmisor($transaction->locationEconomicActivity->code);

    if ($transaction->document_type != 'TE' && $transaction->document_type != 'FEC' && !is_null($transaction->contactEconomicActivity)) {
      $this->setCodigoActividadReceptor($transaction->contactEconomicActivity->code);
    }

    $this->setNumeroConsecutivo($transaction->consecutivo);

    $fechaEmision = Carbon::parse($transaction->transaction_date, 'America/Costa_Rica');

    $this->setFechaEmision($fechaEmision);

    // ✅ Configuración del Emisor
    $emisor = new EmisorType($this->transaction);
    // ✅ Asignar Emisor
    $this->setEmisor($emisor);

    // ✅ Configuración del Receptor
    if ($transaction->document_type != 'TE'){
      $receptor = new ReceptorType($this->transaction);

      // ✅ Asignar Receptor
      $this->setReceptor($receptor);
    }

    // ✅ Asignar La condición de venta
    $this->setCondicionVenta($this->transaction->condition_sale);

    if ($this->transaction->condition_sale == ConditionSale::OTHER) {
      $this->setCondicionVentaOtros($this->transaction->condition_sale_other);
    }

    if ($this->transaction->condition_sale == ConditionSale::CREDIT || $this->transaction->condition_sale == ConditionSale::SELLCREDIT) {
      $this->setPlazoCredito($this->transaction->pay_term_number);
    }

    if (!empty($this->transaction->lines)) {
      $detalle = new DetalleServicioAType($this->transaction->lines);
      $this->setDetalleServicio($detalle->getLineaDetalle());
    }

    if (!empty($this->transaction->otherCharges)) {
      foreach ($this->transaction->otherCharges as $cargo) {
        $otrosCargos = new OtrosCargosType($cargo);
        $this->addToOtrosCargos($otrosCargos);
      }
    }

    $resumenFactura = new ResumenComprobanteAType($this->transaction);
    $this->setResumenFactura($resumenFactura);

    if (in_array($this->transaction->document_type, ['NCE', 'NDE', 'PRC', 'FEC'])) {
      $informacionReferencia = new InformacionReferenciaAType($this->transaction);
      $this->addToInformacionReferencia($informacionReferencia);
    }
  }

  /**
   * Genera el XML de la factura electrónica como string.
   *
   * @return string XML generado.
   */
  public function toXml(): string
  {
    $doc = new DOMDocument('1.0', 'UTF-8');
    $doc->formatOutput = true;

    switch ($this->transaction->document_type) {
      case 'FE':
        $namespace = 'https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/facturaElectronica';
        $rootElementName = 'FacturaElectronica';
        $schema = 'https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/facturaElectronica https://tribunet.hacienda.go.cr/docs/esquemas/2025/v4.4/facturaElectronica.xsd';
        break;
      case 'TE':
        $namespace = 'https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/tiqueteElectronico';
        $rootElementName = 'TiqueteElectronico';
        $schema = 'https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/tiqueteElectronico https://tribunet.hacienda.go.cr/docs/esquemas/2025/v4.4/tiqueteElectronico.xsd';
        break;
      case 'NCE':
        $namespace = 'https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/notaCreditoElectronica';
        $rootElementName = 'NotaCreditoElectronica';
        $schema = 'https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/notaCreditoElectronica https://tribunet.hacienda.go.cr/docs/esquemas/2025/v4.4/notaCreditoElectronica.xsd';
        break;
      case 'NDE':
        $namespace = 'https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/notaDebitoElectronica';
        $rootElementName = 'NotaDebitoElectronica';
        $schema = 'https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/notaDebitoElectronica https://tribunet.hacienda.go.cr/docs/esquemas/2025/v4.4/notaDebitoElectronica.xsd';
        break;
      case 'PRC':
        $namespace = 'https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/facturaElectronicaCompra';
        $rootElementName = 'FacturaElectronicaCompra';
        $schema = 'https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/facturaElectronicaCompra https://tribunet.hacienda.go.cr/docs/esquemas/2025/v4.4/facturaElectronicaCompra.xsd';
        break;
      case 'FEC':
        $namespace = 'https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/facturaElectronicaCompra';
        $rootElementName = 'FacturaElectronicaCompra';
        $schema = 'https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/facturaElectronicaCompra https://tribunet.hacienda.go.cr/docs/esquemas/2025/v4.4/facturaElectronicaCompra.xsd';
        break;
      case 'FEE':
        $namespace = 'https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/facturaElectronicaExportacion';
        $rootElementName = 'FacturaElectronicaExportacion';
        $schema = 'https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/facturaElectronicaExportacion https://tribunet.hacienda.go.cr/docs/esquemas/2025/v4.4/facturaElectronicaExportacion.xsd';
        break;
      case 'REP':
        $namespace = 'https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/reciboElectronicoPago';
        $rootElementName = 'ReciboElectronicoPago';
        $schema = 'https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/reciboElectronicoPago https://tribunet.hacienda.go.cr/docs/esquemas/2025/v4.4/reciboElectronicoPago.xsd';
        break;
      default:
        # code...
        throw new Exception('Error al generar XML tipo de Documento electrónico no soportado');
        break;
    }

    // Crear el nodo raíz con el namespace correcto
    $root = $doc->createElementNS(
      $namespace,
      $rootElementName
    );
    $doc->appendChild($root);

    // Agregar atributos de namespace
    $root->setAttributeNS(
      'http://www.w3.org/2000/xmlns/',
      'xmlns:xsd',
      'http://www.w3.org/2001/XMLSchema'
    );
    $root->setAttributeNS(
      'http://www.w3.org/2000/xmlns/',
      'xmlns:xsi',
      'http://www.w3.org/2001/XMLSchema-instance'
    );
    $root->setAttributeNS(
      'http://www.w3.org/2001/XMLSchema-instance',
      'xsi:schemaLocation',
      $schema
    );

    // Agregar elementos usando el método directo
    if ($this->getClave()) {
      $nodo = $doc->createElement('Clave', $this->getClave());
      $root->appendChild($nodo);
    }

    if ($this->getProveedorSistemas()) {
      $nodo = $doc->createElement('ProveedorSistemas', $this->getProveedorSistemas());
      $root->appendChild($nodo);
    }

    if ($this->getCodigoActividadEmisor()) {
      $nodo = $doc->createElement('CodigoActividadEmisor', $this->getCodigoActividadEmisor());
      $root->appendChild($nodo);
    }

    if ($this->getCodigoActividadReceptor()) {
      $nodo = $doc->createElement('CodigoActividadReceptor', $this->getCodigoActividadReceptor());
      $root->appendChild($nodo);
    }

    if ($this->getNumeroConsecutivo()) {
      $nodo = $doc->createElement('NumeroConsecutivo', $this->getNumeroConsecutivo());
      $root->appendChild($nodo);
    }

    if ($this->getFechaEmision()) {
      $nodo = $doc->createElement('FechaEmision', $this->getFechaEmision()->format('Y-m-d\TH:i:sP'));
      $root->appendChild($nodo);
    }

    // Agregar Emisor
    if ($this->getEmisor()) {
      $emisor = $this->getEmisor();
      $nodo = $this->generarEmisor($doc, $emisor);
      $root->appendChild($nodo);
    }

    // Agregar Receptor
    if ($this->getReceptor()) {
      $receptor = $this->getReceptor();
      $nodo = $this->generarReceptor($doc, $receptor);
      $root->appendChild($nodo);
    }

    // Agregar CondicionVenta, CondicionVentaOtros y PlazoCredito al mismo nivel
    $this->generarCondicionVenta($doc, $root);

    // Agregar DetalleServicio
    $this->generarDetalleServicio($doc, $root);

    // Agregar OtrosCargos
    $this->generarOtrosCargos($doc, $root);

    // Agregar ResumenFactura
    $this->generarResumenFactura($doc, $root);

    // Agragar información de referencia a los comprobantes que lo requieran
    $this->generarInformacionReferencia($doc, $root);

    // Retornar XML como string
    return $doc->saveXML();
  }

  /**
   * Método auxiliar para crear elementos XML con texto.
   */
  private function createElement(DOMDocument $dom, string $name, ?string $value): DOMElement
  {
    return $dom->createElement($name, htmlspecialchars($value ?? ''));
  }

  /**
   * Genera el nodo XML de Emisor.
   */
  private function generarEmisor(DOMDocument $dom, $emisor): DOMElement
  {
    $element = $dom->createElement('Emisor');
    $element->appendChild($this->createElement($dom, 'Nombre', $emisor->getNombre()));

    $nodoIdentificacion = $dom->createElement('Identificacion');
    $nodoIdentificacion->appendChild($this->createElement($dom, 'Tipo', $emisor->getIdentificacion()->getTipo()));
    $nodoIdentificacion->appendChild($this->createElement($dom, 'Numero', $emisor->getIdentificacion()->getNumero()));
    $element->appendChild($nodoIdentificacion);

    if (!is_null($emisor->getRegistrofiscal8707()) && !empty($emisor->getRegistrofiscal8707())) {
      $element->appendChild($this->createElement($dom, 'Registrofiscal8707', $emisor->getRegistrofiscal8707()));
    }
    if (!is_null($emisor->getNombreComercial()) && !empty($emisor->getNombreComercial())) {
      $element->appendChild($this->createElement($dom, 'NombreComercial', $emisor->getNombreComercial()));
    }

    $ubicacion = $emisor->getUbicacion();

    if ($ubicacion->getProvincia() && $ubicacion->getCanton() && $ubicacion->getDistrito()) {
      $nodoUbicacion = $dom->createElement('Ubicacion');
      $nodoUbicacion->appendChild($this->createElement($dom, 'Provincia', $ubicacion->getProvincia()));
      $nodoUbicacion->appendChild($this->createElement($dom, 'Canton', $ubicacion->getCanton()));
      $nodoUbicacion->appendChild($this->createElement($dom, 'Distrito', $ubicacion->getDistrito()));
      if (!is_null($ubicacion->getOtrasSenas()) && !empty($ubicacion->getOtrasSenas()))
        $nodoUbicacion->appendChild($this->createElement($dom, 'OtrasSenas', $ubicacion->getOtrasSenas()));

      $element->appendChild($nodoUbicacion);
    }

    $telefono = $emisor->getTelefono();
    if ($telefono->getCodigoPais() && $telefono->getNumTelefono()) {
      $nodoTelefono = $dom->createElement('Telefono');
      $nodoTelefono->appendChild($this->createElement($dom, 'CodigoPais', $telefono->getCodigoPais()));
      $nodoTelefono->appendChild($this->createElement($dom, 'NumTelefono', $telefono->getNumTelefono()));
      $element->appendChild($nodoTelefono);
    }

    if (!empty($emisor->getCorreoElectronico())) {
      foreach ($emisor->getCorreoElectronico() as $email)
        $element->appendChild($this->createElement($dom, 'CorreoElectronico', $email));
    }

    return $element;
  }

  /**
   * Genera el nodo XML de Receptor.
   */
  private function generarReceptor(DOMDocument $dom, $receptor): DOMElement
  {
    $element = $dom->createElement('Receptor');
    $element->appendChild($this->createElement($dom, 'Nombre', $receptor->getNombre()));

    $nodoIdentificacion = $dom->createElement('Identificacion');
    $nodoIdentificacion->appendChild($this->createElement($dom, 'Tipo', $receptor->getIdentificacion()->getTipo()));
    $nodoIdentificacion->appendChild($this->createElement($dom, 'Numero', $receptor->getIdentificacion()->getNumero()));
    $element->appendChild($nodoIdentificacion);

    if (!is_null($receptor->getNombreComercial()) && !empty($receptor->getNombreComercial())) {
      $element->appendChild($this->createElement($dom, 'NombreComercial', $receptor->getNombreComercial()));
    }

    $ubicacion = $receptor->getUbicacion();

    if ($ubicacion->getProvincia() && $ubicacion->getCanton() && $ubicacion->getDistrito()) {
      $nodoUbicacion = $dom->createElement('Ubicacion');
      $nodoUbicacion->appendChild($this->createElement($dom, 'Provincia', $ubicacion->getProvincia()));
      $nodoUbicacion->appendChild($this->createElement($dom, 'Canton', $ubicacion->getCanton()));
      $nodoUbicacion->appendChild($this->createElement($dom, 'Distrito', $ubicacion->getDistrito()));

      if (!is_null($ubicacion->getOtrasSenas()) && !empty($ubicacion->getOtrasSenas()))
        $nodoUbicacion->appendChild($this->createElement($dom, 'OtrasSenas', $ubicacion->getOtrasSenas()));

      $element->appendChild($nodoUbicacion);
    }

    if (!is_null($receptor->getOtrasSenasExtranjero()) && !empty($receptor->getOtrasSenasExtranjero()))
      $element->appendChild($this->createElement($dom, 'OtrasSenasExtranjero', $receptor->getOtrasSenasExtranjero()));

    $telefono = $receptor->getTelefono();
    if ($telefono && $telefono->getCodigoPais() && $telefono->getNumTelefono()) {
      $nodoTelefono = $dom->createElement('Telefono');
      $nodoTelefono->appendChild($this->createElement($dom, 'CodigoPais', $telefono->getCodigoPais()));
      $nodoTelefono->appendChild($this->createElement($dom, 'NumTelefono', $telefono->getNumTelefono()));
      $element->appendChild($nodoTelefono);
    }

    if (!empty($receptor->getCorreoElectronico())) {
      $element->appendChild($this->createElement($dom, 'CorreoElectronico', $receptor->getCorreoElectronico()));
    }

    return $element;
  }

  /**
   * Genera el nodo XML de PlazoCredito.
   */
  private function generarCondicionVenta(DOMDocument $dom, DOMElement $root): void
  {
    // Agregar CondicionVenta al nodo raíz
    $root->appendChild($this->createElement($dom, 'CondicionVenta', $this->getCondicionVenta()));

    // Agregar CondicionVentaOtros si aplica
    if (!is_null($this->getCondicionVentaOtros()) && !empty($this->getCondicionVentaOtros())) {
      $root->appendChild($this->createElement($dom, 'CondicionVentaOtros', $this->getCondicionVentaOtros()));
    }

    // Agregar PlazoCredito si aplica
    if (!is_null($this->getPlazoCredito()) && !empty($this->getPlazoCredito())) {
      $root->appendChild($this->createElement($dom, 'PlazoCredito', $this->getPlazoCredito()));
    }
  }

  /**
   * Genera el nodo XML de DetalleServicio y lo agrega al nodo raíz.
   */
  private function generarDetalleServicio(DOMDocument $dom, DOMElement $root): void
  {
    $lines = $this->getDetalleServicio();

    if (!empty($lines)) {
      // Crear el nodo DetalleServicio
      $element = $dom->createElement('DetalleServicio');

      foreach ($lines as $index => $line) {
        $nodoLineaDetalle = $dom->createElement('LineaDetalle');
        $nodoLineaDetalle->appendChild($this->createElement($dom, 'NumeroLinea', $index + 1));

        // Agregar CodigoCABYS
        $nodoLineaDetalle->appendChild($this->createElement($dom, 'CodigoCABYS', $line->getCodigoCABYS()));

        /*
              CodigoComercial {0,5}
              Se puede incluir un máximo de 5 repeticiones de códigos de producto/servicio.
              ▪Este será un requisito obligatorio para las líneas de detalle que utilicen uno de los códigos de producto/servicio de
              “surtidos” que estén habilitados en el CAByS, entendidos como la combinación de más de dos productos con diferentes
              códigos de producto/servicio.
              ▪Validación: Deberá incluir al menos 3 caracteres cuando se utilice uno de los códigos CAByS habilitados para surtidos.
              Caso contrario se rechazará el comprobante
              */

        // Agregar CodigoComercial
        $codigoscomerciales = $line->getCodigoComercial();
        foreach ($codigoscomerciales as $codigocomercial) {
          $nodoCodigoComercial = $dom->createElement('CodigoComercial');
          $nodoCodigoComercial->appendChild($this->createElement($dom, 'Tipo', $codigocomercial->getTipo()));
          $nodoCodigoComercial->appendChild($this->createElement($dom, 'Codigo', $codigocomercial->getCodigo()));

          $nodoLineaDetalle->appendChild($nodoCodigoComercial);
        }

        // Agregar Cantidad
        $nodoLineaDetalle->appendChild($this->createElement($dom, 'Cantidad', $line->getCantidad()));
        $nodoLineaDetalle->appendChild($this->createElement($dom, 'UnidadMedida', $line->getUnidadMedida()));
        if ($this->transaction->document_type != 'TE') {
          $nodoLineaDetalle->appendChild($this->createElement($dom, 'TipoTransaccion', $line->getTipoTransaccion()));
        }

        // Convertir caracteres especiales a entidades numéricas (seguro para XML)
        $convmap = array(0x80, 0xff, 0, 0xff);
        $cleanDetail = mb_encode_numericentity($line->getDetalle(), $convmap, "UTF-8");

        // Verificar longitud con mb_strlen() para manejar caracteres multibyte correctamente
        if (mb_strlen($cleanDetail, "UTF-8") > 200) {
          // Truncar la cadena manteniendo palabras completas
          $cleanDetail = mb_substr($cleanDetail, 0, 197, "UTF-8") . '...'; // Agrega puntos suspensivos si es necesario
        }

        // Aplicar htmlspecialchars después de truncar la cadena
        $cleanDetail = htmlspecialchars($cleanDetail, ENT_XML1, "UTF-8");
        $nodoLineaDetalle->appendChild($this->createElement($dom, 'Detalle', $cleanDetail));

        // Agregar RegistroMedicamento
        $this->generarRegistroMedicamento($dom, $nodoLineaDetalle, $line);

        // Agregar PrecioUnitario
        $nodoLineaDetalle->appendChild($this->createElement($dom, 'PrecioUnitario', $line->getPrecioUnitario()));

        // Agregar MontoTotal
        $nodoLineaDetalle->appendChild($this->createElement($dom, 'MontoTotal', $line->getMontoTotal()));

        // Agregar Descuentos
        $discounts = $line->getDescuento();
        $this->generarDescuentos($dom, $nodoLineaDetalle, $discounts);

        // Agregar SubTotal
        $nodoLineaDetalle->appendChild($this->createElement($dom, 'SubTotal', $line->getSubTotal()));

        // Agregar BaseImponible
        $taxes = $line->getImpuesto();
        $baseimponible = $line->getBaseImponible();
        $nodoLineaDetalle->appendChild($this->createElement($dom, 'BaseImponible', $baseimponible));

        // Agregar Impuestos
        $this->generarImpuestos($dom, $nodoLineaDetalle, $taxes);

        if ($this->transaction->document_type != 'FEC' && $this->transaction->document_type != 'PRC') {
          $nodoLineaDetalle->appendChild($this->createElement($dom, 'ImpuestoAsumidoEmisorFabrica', $line->getImpuestoAsumidoEmisorFabrica()));
        }

        // Agregar ImpuestoNeto
        if ($this->transaction->document_type != 'TE')
          $nodoLineaDetalle->appendChild($this->createElement($dom, 'ImpuestoNeto', $line->getImpuestoNeto()));

        // Agregar MontoTotalLinea
        $nodoLineaDetalle->appendChild($this->createElement($dom, 'MontoTotalLinea', $line->getMontoTotalLinea()));

        $element->appendChild($nodoLineaDetalle);
      }

      // Agregar DetalleServicio al nodo raíz
      $root->appendChild($element);
    }
  }

  /**
   * Genera el nodo XML de RegistroMedicamento.
   */
  private function generarRegistroMedicamento(DOMDocument $dom, DOMElement $nodoLineaDetalle, $item)
  {
    $business = Session::get('user.business');
    if (!$business) {
      $business = Business::find(1);
    }
    if ($business->registro_medicamento && $business->forma_farmaceutica && $this->itemIsMedicine($item->getCodigoCABYS())) {
      $nodoLineaDetalle->appendChild($this->createElement($dom, 'RegistroMedicamento', $item->getRegistroMedicamento()));
      $nodoLineaDetalle->appendChild($this->createElement($dom, 'FormaFarmaceutica', $item->getFormaFarmaceutica()));
    }
  }

  /**
   * Genera el nodo XML de Descuento.
   */
  private function generarDescuentos(DOMDocument $dom, DOMElement $nodoLineaDetalle, $discounts)
  {
    foreach ($discounts as $discount) {
      $nododiscount = $dom->createElement('Descuento');
      $nododiscount->appendChild($this->createElement($dom, 'MontoDescuento', $discount->getMontoDescuento()));
      $nododiscount->appendChild($this->createElement($dom, 'CodigoDescuento', $discount->getCodigoDescuento()));

      if (in_array($discount->getCodigoDescuento(), ['01', '03'])) {
        $this->hasRegaliaOrBonificacion = true;
      }
      if ($discount->getCodigoDescuentoOTRO()) {
        $nododiscount->appendChild($this->createElement($dom, 'CodigoDescuentoOTRO', $discount->getCodigoDescuentoOTRO()));
      }

      if ($discount->getNaturalezaDescuento()) {
        $nododiscount->appendChild($this->createElement($dom, 'NaturalezaDescuento', $discount->getNaturalezaDescuento()));
      }

      $nodoLineaDetalle->appendChild($nododiscount);
    }
  }

  /**
   * Genera el nodo XML de Impuesto.
   */
  private function generarImpuestos(DOMDocument $dom, DOMElement $nodoLineaDetalle, $taxes)
  {
    foreach ($taxes as $tax) {
      $nodotax = $dom->createElement('Impuesto');
      $nodotax->appendChild($this->createElement($dom, 'Codigo', $tax->getCodigo()));

      if ($tax->getCodigoImpuestoOTRO()) {
        $nodotax->appendChild($this->createElement($dom, 'CodigoImpuestoOTRO', $tax->getCodigoImpuestoOTRO()));
      }

      $nodotax->appendChild($this->createElement($dom, 'CodigoTarifaIVA', $tax->getCodigoTarifaIVA()));
      $nodotax->appendChild($this->createElement($dom, 'Tarifa', $tax->getTarifa()));

      if ($tax->getFactorCalculoIVA())
        $nodotax->appendChild($this->createElement($dom, 'FactorCalculoIVA', $tax->getFactorCalculoIVA()));

      if (in_array($tax->getCodigo(), ['03', '04', '05', '06'])) {
        $especificTax = $tax->getDatosImpuestoEspecifico();
        $nodoEspecificTax = $dom->createElement('DatosImpuestoEspecifico');

        if ($especificTax->getCantidadUnidadMedida())
          $nodoEspecificTax->appendChild($this->createElement($dom, 'CantidadUnidadMedida', $especificTax->getCantidadUnidadMedida()));

        if ($tax->getCodigo() == '04' && $especificTax->getPorcentaje())
          $nodoEspecificTax->appendChild($this->createElement($dom, 'Porcentaje', $especificTax->getPorcentaje()));

        if ($tax->getCodigo() == '04' && $especificTax->getProporcion())
          $nodoEspecificTax->appendChild($this->createElement($dom, 'Proporcion', $especificTax->getProporcion()));

        if ($tax->getCodigo() == '05' && $especificTax->getVolumenUnidadConsumo())
          $nodoEspecificTax->appendChild($this->createElement($dom, 'VolumenUnidadConsumo', $especificTax->getVolumenUnidadConsumo()));

        if ($especificTax->getImpuestoUnidad())
          $nodoEspecificTax->appendChild($this->createElement($dom, 'ImpuestoUnidad', $especificTax->getImpuestoUnidad()));

        $nodotax->appendChild($nodoEspecificTax);
      }

      // Calcular el monto del impuesto
      /*
      $iva = $tax->getMonto();

      if ($tax->getCodigoTarifaIVA() == '10') // Tarifa exenta
        $iva = 0.00000;

      if ($tax->getCodigo() == '01' && $hasRegaliaOrBonificacion) {
        $iva = number_format((float)($line->getMontoTotal() * $tax->getTarifa()) / 100, 5, '.', '');
        //dd("El iva calculado es: ".$iva);
      }

      if ($tax->getCodigo() == '08') { // IVA Régimen de Bienes Usados (Factor)
        $iva = number_format($line->getSubTotal() * $tax->getFactorCalculoIVA(), 5, '.', '');
      }

      if ($tax->getCodigo() == '07') { // IVA (cálculo especial)
        $iva = number_format((float)($line->getMontoTotal() * $tax->getTarifa()) / 100, 5, '.', '');
        // Nota: En el caso de utilizar el nodo “Detalle de productos del surtido, paquetes o combos”, este
        // campo se calcula como la sumatoria de los montos de IVA individuales de las líneas de detalle del surtido que se deben
        // incluir en estos casos, en caso de contar con más de una unidad de surtido dicho monto se debe de multiplicar por la
        // cantidad de la línea principal.
      }

      if ($tax->getCodigo() == '02') { // Impuesto Selectivo de Consumo
        $iva = number_format($line->getSubTotal() * $tax->getFactorCalculoIVA(), 5, '.', '');
        // Nota: En el caso de utilizar el nodo “Detalle de productos del surtido, paquetes o combos”, este campo se calcula como la
        // sumatoria de los montos del Impuesto Selectivo de Consumo individuales de las líneas de detalle del surtido que se deben
        // incluir en estos casos, en caso de contar con más de una
      }

      $datosImpuestoEspecifico = $tax->getDatosImpuestoEspecifico();
      if ($tax->getCodigo() == '03') { // Impuesto Único a los Combustibles
        $iva = number_format($datosImpuestoEspecifico->getCantidadUnidadMedida() * $datosImpuestoEspecifico->getImpuestoUnidad(), 5, '.', '');
      }

      if ($tax->getCodigo() == '04') { // Impuesto específico de Bebidas Alcohólicas
        $iva = number_format($line->getCantidad() * $datosImpuestoEspecifico->getProporcion(), 5, '.', '');
        // Nota: En el caso de utilizar el nodo “Detalle de productos del surtido,paquetes o combos”, este campo se calcula como la
        // sumatoria de los montos del Impuesto Específico a las Bebidas Alcohólicas individuales de las líneas de detalle del
        // surtido que se deben incluir en estos casos, en caso de contar con más de una unidad de surtido dicho monto se debe de
        // multiplicar por la cantidad de la línea principal
      }

      if ($tax->getCodigo() == '05') { // Impuesto Específico sobre las bebidas envasadas sin contenido alcohólico y jabones de tocador
        // Nota: En el caso de utilizar el nodo “Detalle de productos del surtido,paquetes o combos”, este campo se calcula como la
        // sumatoria de los montos del Impuesto Específico a las Bebidas Alcohólicas individuales de las líneas de detalle del
        // surtido que se deben incluir en estos casos, en caso de contar con más de una unidad de surtido dicho monto se debe de
        // multiplicar por la cantidad de la línea principal

        // si el producto es jabón de tocador
        if ($line->itemIsBebida()) {
          $div = $datosImpuestoEspecifico->getImpuestoUnidad() / ($datosImpuestoEspecifico->getVolumenUnidadConsumo() ?? 1);
          $iva = number_format($line->getCantidad() * $datosImpuestoEspecifico->getCantidadUnidadMedida() * $div, 5, '.', '');
        } else
        if ($line->itemIsJabon()) {
          $iva = number_format($line->getCantidad() * $datosImpuestoEspecifico->getVolumenUnidadConsumo() * $datosImpuestoEspecifico->getImpuestoUnidad(), 5, '.', '');
        }
      }

      if ($tax->getCodigo() == '06') { // Impuesto a los Productos de Tabaco
        $iva = number_format($line->getCantidad() * $datosImpuestoEspecifico->getCantidadUnidadMedida(), 5, '.', '');
        // Nota: En el caso de utilizar el nodo “Detalle de productos del surtido,paquetes o combos”, este campo se calcula como la
        // sumatoria de los montos del Impuesto Específico a las Bebidas Alcohólicas individuales de las líneas de detalle del
        // surtido que se deben incluir en estos casos, en caso de contar con más de una unidad de surtido dicho monto se debe de
        // multiplicar por la cantidad de la línea principal
      }

      if ($tax->getCodigo() == '12') { // Impuesto Específico al Cemento
        $iva = number_format($line->getSubTotal() * $tax->getFactorCalculoIVA(), 5, '.', '');
      }
      $tax->setMonto($iva);
      */

      $nodotax->appendChild($this->createElement($dom, 'Monto', $tax->getMonto()));

      $exoneracion = $tax->getExoneracion();
      if ($exoneracion && $exoneracion->getTipoDocumentoEX1() && $exoneracion->getNumeroDocumento() && $exoneracion->getNombreInstitucion() && $exoneracion->getTarifaExonerada()) {
        $nodoExoneracion = $dom->createElement('Exoneracion');
        $nodoExoneracion->appendChild($this->createElement($dom, 'TipoDocumentoEX1', $exoneracion->getTipoDocumentoEX1()));

        if ($exoneracion->getTipoDocumentoOTRO())
          $nodoExoneracion->appendChild($this->createElement($dom, 'TipoDocumentoOTRO', $exoneracion->getTipoDocumentoOTRO()));

        if ($exoneracion->getNumeroDocumento())
          $nodoExoneracion->appendChild($this->createElement($dom, 'NumeroDocumento', $exoneracion->getNumeroDocumento()));

        if ($exoneracion->getArticulo())
          $nodoExoneracion->appendChild($this->createElement($dom, 'Articulo', $exoneracion->getArticulo()));

        if ($exoneracion->getInciso())
          $nodoExoneracion->appendChild($this->createElement($dom, 'Inciso', $exoneracion->getInciso()));

        $nodoExoneracion->appendChild($this->createElement($dom, 'NombreInstitucion', $exoneracion->getNombreInstitucion()));

        if ($exoneracion->getNombreInstitucionOtros())
          $nodoExoneracion->appendChild($this->createElement($dom, 'NombreInstitucionOtros', $exoneracion->getNombreInstitucionOtros()));

        if ($exoneracion->getFechaEmisionEX()) {
          $fechaCarbon = Carbon::instance($exoneracion->getFechaEmisionEX());
          $fechaFormateada = $fechaCarbon->format('Y-m-d\TH:i:sP');
          $nodoExoneracion->appendChild($this->createElement($dom, 'FechaEmisionEX', $fechaFormateada));
        }

        $nodoExoneracion->appendChild($this->createElement($dom, 'TarifaExonerada', $exoneracion->getTarifaExonerada()));
        $nodoExoneracion->appendChild($this->createElement($dom, 'MontoExoneracion', $exoneracion->getMontoExoneracion()));

        $nodotax->appendChild($nodoExoneracion);
      }

      $nodoLineaDetalle->appendChild($nodotax);
    }
  }

  /**
   * Genera el nodo XML de OtrosCargos y lo agrega al nodo raíz.
   */
  private function generarOtrosCargos(DOMDocument $dom, DOMElement $root): void
  {
    $otrosCargos = $this->getOtrosCargos();

    if (!empty($otrosCargos)) {
      foreach ($otrosCargos as $index => $cargo) {
        $nodoOtrosCargos = $dom->createElement('OtrosCargos');

        // Agregar TipoDocumentoOC
        $nodoOtrosCargos->appendChild($this->createElement($dom, 'TipoDocumentoOC', $cargo->getTipoDocumentoOC()));

        // Agregar TipoDocumentoOTROS
        if ($cargo->getTipoDocumentoOTROS()) {
          $nodoOtrosCargos->appendChild($this->createElement($dom, 'TipoDocumentoOTROS', $cargo->getTipoDocumentoOTROS()));
        }

        // Agregar IdentificacionTercero
        if ($cargo->getIdentificacionTercero()) {
          $identificacion = $cargo->getIdentificacionTercero();
          $nodoIdentificacion = $dom->createElement('IdentificacionTercero');
          $nodoIdentificacion->appendChild($this->createElement($dom, 'Tipo', $identificacion->getTipo()));
          $nodoIdentificacion->appendChild($this->createElement($dom, 'Numero', $identificacion->getNumero()));
          $nodoOtrosCargos->appendChild($nodoIdentificacion);
        }

        // Agregar NombreTercero
        if ($cargo->getNombreTercero())
          $nodoOtrosCargos->appendChild($this->createElement($dom, 'NombreTercero', $cargo->getNombreTercero()));

        // Agregar Detalle
        $nodoOtrosCargos->appendChild($this->createElement($dom, 'Detalle', $cargo->getDetalle()));

        // Agregar PorcentajeOC
        if ($cargo->getPorcentajeOC())
          $nodoOtrosCargos->appendChild($this->createElement($dom, 'PorcentajeOC', $cargo->getPorcentajeOC()));

        // Agregar MontoCargo
        $nodoOtrosCargos->appendChild($this->createElement($dom, 'MontoCargo', $cargo->getMontoCargo()));

        $root->appendChild($nodoOtrosCargos);
      }
    }
  }

  private function itemIsMedicine($codigocabys)
  {
    // Obtener los primeros 3 caracteres
    $primerosTres = substr($codigocabys, 0, 3);

    // 356 son los cabys de medicamento
    $valoresPermitidos = ['356'];

    // Retorna true si está en la lista
    return in_array($primerosTres, $valoresPermitidos);
  }

  /**
   * Genera el nodo XML de ResumenFactura.
   */
  private function generarResumenFactura(DOMDocument $dom, DOMElement $root): void
  {
    $resumen = $this->getResumenFactura();

    // Crear el nodo DetalleServicio
    $nodoResumenFactura = $dom->createElement('ResumenFactura');

    $codigoMoneda = $resumen->getCodigoTipoMoneda();
    $nodoCodigoMoneda = $dom->createElement('CodigoTipoMoneda');
    $nodoCodigoMoneda->appendChild($this->createElement($dom, 'CodigoMoneda', $codigoMoneda->getCodigoMoneda()));
    $nodoCodigoMoneda->appendChild($this->createElement($dom, 'TipoCambio', $codigoMoneda->getTipoCambio()));

    $nodoResumenFactura->appendChild($nodoCodigoMoneda);

    $nodoResumenFactura->appendChild($this->createElement($dom, 'TotalServGravados', $resumen->getTotalServGravados()));
    $nodoResumenFactura->appendChild($this->createElement($dom, 'TotalServExentos', $resumen->getTotalServExentos()));
    $nodoResumenFactura->appendChild($this->createElement($dom, 'TotalServExonerado', $resumen->getTotalServExonerado()));
    $nodoResumenFactura->appendChild($this->createElement($dom, 'TotalServNoSujeto', $resumen->getTotalServNoSujeto()));

    $nodoResumenFactura->appendChild($this->createElement($dom, 'TotalMercanciasGravadas', $resumen->getTotalMercGravadas()));
    $nodoResumenFactura->appendChild($this->createElement($dom, 'TotalMercanciasExentas', $resumen->getTotalMercExentas()));
    $nodoResumenFactura->appendChild($this->createElement($dom, 'TotalMercExonerada', $resumen->getTotalMercExonerada()));
    $nodoResumenFactura->appendChild($this->createElement($dom, 'TotalMercNoSujeta', $resumen->getTotalMercNoSujeta()));

    $nodoResumenFactura->appendChild($this->createElement($dom, 'TotalGravado', $resumen->getTotalGravado()));
    $nodoResumenFactura->appendChild($this->createElement($dom, 'TotalExento', $resumen->getTotalExento()));
    $nodoResumenFactura->appendChild($this->createElement($dom, 'TotalExonerado', $resumen->getTotalExonerado()));
    $nodoResumenFactura->appendChild($this->createElement($dom, 'TotalNoSujeto', $resumen->getTotalNoSujeto()));

    $nodoResumenFactura->appendChild($this->createElement($dom, 'TotalVenta', $resumen->getTotalVenta()));
    $nodoResumenFactura->appendChild($this->createElement($dom, 'TotalDescuentos', $resumen->getTotalDescuentos()));
    $nodoResumenFactura->appendChild($this->createElement($dom, 'TotalVentaNeta', $resumen->getTotalVentaNeta()));

    if (!empty($resumen->getTotalDesgloseImpuesto())) {
      $this->setDesgloseImpuesto($resumen, $dom, $nodoResumenFactura);
    }

    $nodoResumenFactura->appendChild($this->createElement($dom, 'TotalImpuesto', $resumen->getTotalImpuesto()));

    if ($this->transaction->document_type != 'TE' && $this->transaction->document_type != 'FEC' && $this->transaction->document_type != 'PRC') {
      $nodoResumenFactura->appendChild($this->createElement($dom, 'TotalImpAsumEmisorFabrica', $resumen->getTotalImpAsumEmisorFabrica()));
      $nodoResumenFactura->appendChild($this->createElement($dom, 'TotalIVADevuelto', $resumen->getTotalIVADevuelto()));
    }

    $nodoResumenFactura->appendChild($this->createElement($dom, 'TotalOtrosCargos', $resumen->getTotalOtrosCargos()));

    if (!empty($resumen->getMedioPago())) {
      $nodoMedioPago = $dom->createElement('MedioPago');
      /*
      $payments = $resumen->getMedioPago();
      foreach ($payments as $payment) {
        $nodoMedioPago->appendChild($this->createElement($dom, 'TipoMedioPago', $payment->getTipoMedioPago()));
        if ($payment->getMedioPagoOtros())
          $nodoMedioPago->appendChild($this->createElement($dom, 'MedioPagoOtros', $payment->getMedioPagoOtros()));
        $nodoMedioPago->appendChild($this->createElement($dom, 'TotalMedioPago', $payment->getTotalMedioPago()));
      }
      */
      $payments = $resumen->getMedioPago();
      $payment = $payments[0];
      $nodoMedioPago->appendChild($this->createElement($dom, 'TipoMedioPago', $payment->getTipoMedioPago()));
      if ($payment->getMedioPagoOtros())
        $nodoMedioPago->appendChild($this->createElement($dom, 'MedioPagoOtros', $payment->getMedioPagoOtros()));
      $nodoMedioPago->appendChild($this->createElement($dom, 'TotalMedioPago', $resumen->getTotalComprobante()));
      $nodoResumenFactura->appendChild($nodoMedioPago);
    }

    $nodoResumenFactura->appendChild($this->createElement($dom, 'TotalComprobante', $resumen->getTotalComprobante()));

    $root->appendChild($nodoResumenFactura);
  }

  /**
   * Genera el nodo XML de Normativa.
   */
  private function generarNormativa(DOMDocument $dom, $normativa): DOMElement
  {
    $element = $dom->createElement('Normativa');
    $element->appendChild($this->createElement($dom, 'NumeroResolucion', $normativa->getNumeroResolucion()));
    $element->appendChild($this->createElement($dom, 'FechaResolucion', $normativa->getFechaResolucion()));
    return $element;
  }

  private function setDesgloseImpuesto($resumen, &$dom, &$nodoResumenFactura)
  {
    $desgloseImpuestos = $resumen->getTotalDesgloseImpuesto();

    // Agrupar impuestos por código y tarifa
    $impuestosAgrupados = [];
    foreach ($desgloseImpuestos as $desglose) {
      $clave = $desglose->getCodigo() . '|' . $desglose->getCodigoTarifaIVA();

      if (!isset($impuestosAgrupados[$clave])) {
        $impuestosAgrupados[$clave] = [
          'codigo' => $desglose->getCodigo(),
          'tarifa' => $desglose->getCodigoTarifaIVA(),
          'total' => 0
        ];
      }

      $impuestosAgrupados[$clave]['total'] += $desglose->getTotalMontoImpuesto();
    }

    // Crear nodos agrupados
    foreach ($impuestosAgrupados as $impuesto) {
      $nodoDesgloseImpuesto = $dom->createElement('TotalDesgloseImpuesto');
      $nodoDesgloseImpuesto->appendChild($this->createElement($dom, 'Codigo', $impuesto['codigo']));
      $nodoDesgloseImpuesto->appendChild($this->createElement($dom, 'CodigoTarifaIVA', $impuesto['tarifa']));
      $nodoDesgloseImpuesto->appendChild($this->createElement($dom, 'TotalMontoImpuesto', $impuesto['total']));
      $nodoResumenFactura->appendChild($nodoDesgloseImpuesto);
    }
  }

  /**
   * Genera el nodo XML de InformacionReferencia .
   */
  private function generarInformacionReferencia(DOMDocument $dom, DOMElement $root): void
  {
    $referencias = $this->getInformacionReferencia();
    foreach ($referencias as $referencia) {
      if ($referencia->getTipoDocIR() && $referencia->getNumero() && $referencia->getCodigo()) {
        $nodoReferencia = $dom->createElement('InformacionReferencia');
        $nodoReferencia->appendChild($this->createElement($dom, 'TipoDocIR', $referencia->getTipoDocIR()));
        $nodoReferencia->appendChild($this->createElement($dom, 'Numero', $referencia->getNumero()));

        $nodoReferencia->appendChild($this->createElement($dom, 'FechaEmisionIR', $referencia->getFechaEmisionIR()->format('Y-m-d\TH:i:sP')));
        $nodoReferencia->appendChild($this->createElement($dom, 'Codigo', $referencia->getCodigo()));
        $nodoReferencia->appendChild($this->createElement($dom, 'Razon', $referencia->getRazon()));

        $root->appendChild($nodoReferencia);
      }
    }
  }
}

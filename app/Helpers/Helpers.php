<?php

namespace App\Helpers;

use App\Livewire\Movimientos\MovimientosFacturas;
use App\Mail\CasoAsignadoMail;
use App\Mail\InvoiceMail;
use App\Mail\InvoiceRechazadaMail;
use App\Mail\MovimientoMail;
use App\Mail\ProformaMail;
use App\Mail\UserCredentialMail;
use App\Models\Bank;
use App\Models\Business;
use App\Models\Caso;
use App\Models\CasoSituacion;
use App\Models\Comprobante;
use App\Models\Contact;
use App\Models\Cuenta;
use App\Models\Currency;
use App\Models\Department;
use App\Models\Hacienda\ComprobanteElectronico;
use App\Models\Hacienda\FacturaElectronica\EmisorType;
use App\Models\Hacienda\FacturaElectronica\IdentificacionType;
use App\Models\Hacienda\FacturaElectronica\TelefonoType;
use App\Models\Hacienda\FacturaElectronica\UbicacionType;
use App\Models\Movimiento;
use App\Models\MovimientoBalanceMensual;
use App\Models\Transaction;
use App\Models\TransactionCommission;
use App\Models\TransactionLine;
use App\Models\TransactionOtherCharge;
use App\Services\Hacienda\firmarXML\hacienda\Firmador;
use BaconQrCode\Encoder\QrCode;
use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Carbon\Carbon;
use DOMDocument;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Spatie\Browsershot\Browsershot;
use Spatie\MediaLibrary\Conversions\ImageGenerators\Svg;

class Helpers
{
  public static function appClasses()
  {

    $data = config('custom.custom');


    // default data array
    $DefaultData = [
      'myLayout' => 'vertical',
      'myTheme' => 'theme-default',
      'myStyle' => 'light',
      'myRTLSupport' => true,
      'myRTLMode' => true,
      'hasCustomizer' => true,
      'showDropdownOnHover' => true,
      'displayCustomizer' => true,
      'contentLayout' => 'compact',
      'headerType' => 'fixed',
      'navbarType' => 'fixed',
      'menuFixed' => true,
      'menuCollapsed' => false,
      'footerFixed' => false,
      'customizerControls' => [
        'rtl',
        'style',
        'headerType',
        'contentLayout',
        'layoutCollapsed',
        'showDropdownOnHover',
        'layoutNavbarOptions',
        'themes',
      ],
      //   'defaultLanguage'=>'en',
    ];

    // if any key missing of array from custom.php file it will be merge and set a default value from dataDefault array and store in data variable
    $data = array_merge($DefaultData, $data);

    // All options available in the template
    $allOptions = [
      'myLayout' => ['vertical', 'horizontal', 'blank', 'front'],
      'menuCollapsed' => [true, false],
      'hasCustomizer' => [true, false],
      'showDropdownOnHover' => [true, false],
      'displayCustomizer' => [true, false],
      'contentLayout' => ['compact', 'wide'],
      'headerType' => ['fixed', 'static'],
      'navbarType' => ['fixed', 'static', 'hidden'],
      'myStyle' => ['light', 'dark', 'system'],
      'myTheme' => ['theme-default', 'theme-bordered', 'theme-semi-dark'],
      'myRTLSupport' => [true, false],
      'myRTLMode' => [true, false],
      'menuFixed' => [true, false],
      'footerFixed' => [true, false],
      'customizerControls' => [],
      // 'defaultLanguage'=>array('en'=>'en','fr'=>'fr','de'=>'de','ar'=>'ar'),
    ];

    //if myLayout value empty or not match with default options in custom.php config file then set a default value
    foreach ($allOptions as $key => $value) {
      if (array_key_exists($key, $DefaultData)) {
        if (gettype($DefaultData[$key]) === gettype($data[$key])) {
          // data key should be string
          if (is_string($data[$key])) {
            // data key should not be empty
            if (isset($data[$key]) && $data[$key] !== null) {
              // data key should not be exist inside allOptions array's sub array
              if (!array_key_exists($data[$key], $value)) {
                // ensure that passed value should be match with any of allOptions array value
                $result = array_search($data[$key], $value, 'strict');
                if (empty($result) && $result !== 0) {
                  $data[$key] = $DefaultData[$key];
                }
              }
            } else {
              // if data key not set or
              $data[$key] = $DefaultData[$key];
            }
          }
        } else {
          $data[$key] = $DefaultData[$key];
        }
      }
    }
    $styleVal = $data['myStyle'] == "dark" ? "dark" : "light";
    $styleUpdatedVal = $data['myStyle'] == "dark" ? "dark" : $data['myStyle'];
    // Determine if the layout is admin or front based on cookies
    $layoutName = $data['myLayout'];
    $isAdmin = Str::contains($layoutName, 'front') ? false : true;

    $modeCookieName = $isAdmin ? 'admin-mode' : 'front-mode';
    $colorPrefCookieName = $isAdmin ? 'admin-colorPref' : 'front-colorPref';

    // Determine style based on cookies, only if not 'blank-layout'
    if ($layoutName !== 'blank') {
      if (isset($_COOKIE[$modeCookieName])) {
        $styleVal = $_COOKIE[$modeCookieName];
        if ($styleVal === 'system') {
          $styleVal = isset($_COOKIE[$colorPrefCookieName]) ? $_COOKIE[$colorPrefCookieName] : 'light';
        }
        $styleUpdatedVal = $_COOKIE[$modeCookieName];
      }
    }

    isset($_COOKIE['theme']) ? $themeVal = $_COOKIE['theme'] : $themeVal = $data['myTheme'];

    $directionVal = isset($_COOKIE['direction']) ? ($_COOKIE['direction'] === "true" ? 'rtl' : 'ltr') : $data['myRTLMode'];

    //layout classes
    $layoutClasses = [
      'layout' => $data['myLayout'],
      'theme' => $themeVal,
      'themeOpt' => $data['myTheme'],
      'style' => $styleVal,
      'styleOpt' => $data['myStyle'],
      'styleOptVal' => $styleUpdatedVal,
      'rtlSupport' => $data['myRTLSupport'],
      'rtlMode' => $data['myRTLMode'],
      'textDirection' => $directionVal, //$data['myRTLMode'],
      'menuCollapsed' => $data['menuCollapsed'],
      'hasCustomizer' => $data['hasCustomizer'],
      'showDropdownOnHover' => $data['showDropdownOnHover'],
      'displayCustomizer' => $data['displayCustomizer'],
      'contentLayout' => $data['contentLayout'],
      'headerType' => $data['headerType'],
      'navbarType' => $data['navbarType'],
      'menuFixed' => $data['menuFixed'],
      'footerFixed' => $data['footerFixed'],
      'customizerControls' => $data['customizerControls'],
    ];

    // sidebar Collapsed
    if ($layoutClasses['menuCollapsed'] == true) {
      $layoutClasses['menuCollapsed'] = 'layout-menu-collapsed';
    }

    // Header Type
    if ($layoutClasses['headerType'] == 'fixed') {
      $layoutClasses['headerType'] = 'layout-menu-fixed';
    }
    // Navbar Type
    if ($layoutClasses['navbarType'] == 'fixed') {
      $layoutClasses['navbarType'] = 'layout-navbar-fixed';
    } elseif ($layoutClasses['navbarType'] == 'static') {
      $layoutClasses['navbarType'] = '';
    } else {
      $layoutClasses['navbarType'] = 'layout-navbar-hidden';
    }

    // Menu Fixed
    if ($layoutClasses['menuFixed'] == true) {
      $layoutClasses['menuFixed'] = 'layout-menu-fixed';
    }


    // Footer Fixed
    if ($layoutClasses['footerFixed'] == true) {
      $layoutClasses['footerFixed'] = 'layout-footer-fixed';
    }

    // RTL Supported template
    if ($layoutClasses['rtlSupport'] == true) {
      $layoutClasses['rtlSupport'] = '/rtl';
    }

    // RTL Layout/Mode
    if ($layoutClasses['rtlMode'] == true) {
      $layoutClasses['rtlMode'] = 'rtl';
      $layoutClasses['textDirection'] = isset($_COOKIE['direction']) ? ($_COOKIE['direction'] === "true" ? 'rtl' : 'ltr') : 'rtl';
    } else {
      $layoutClasses['rtlMode'] = 'ltr';
      $layoutClasses['textDirection'] = isset($_COOKIE['direction']) && $_COOKIE['direction'] === "true" ? 'rtl' : 'ltr';
    }

    // Show DropdownOnHover for Horizontal Menu
    if ($layoutClasses['showDropdownOnHover'] == true) {
      $layoutClasses['showDropdownOnHover'] = true;
    } else {
      $layoutClasses['showDropdownOnHover'] = false;
    }

    // To hide/show display customizer UI, not js
    if ($layoutClasses['displayCustomizer'] == true) {
      $layoutClasses['displayCustomizer'] = true;
    } else {
      $layoutClasses['displayCustomizer'] = false;
    }

    return $layoutClasses;
  }

  public static function updatePageConfig($pageConfigs)
  {
    $demo = 'custom';
    if (isset($pageConfigs)) {
      if (count($pageConfigs) > 0) {
        foreach ($pageConfigs as $config => $val) {
          Config::set('custom.' . $demo . '.' . $config, $val);
        }
      }
    }
  }

  static public function formatDecimal($number, $decimalPlaces = 2, $decimalSeparator = '.', $thousandsSeparator = ',')
  {
    if (!is_numeric($number)) {
      return $number; // Retorna el valor original si no es numérico
    }

    return number_format($number, $decimalPlaces, $decimalSeparator, $thousandsSeparator);
  }

  static public function generateProformaPdf($invoiceId, $receipt_type, $destination = 'browser')
  {
    //$logo = public_path('assets/img/invoice/logo.png');
    $business = Business::find(1);
    $logoFileName = $business->logo;
    $logo = public_path("storage/assets/img/logos/{$logoFileName}");
    if (!file_exists(public_path("storage/assets/img/logos/{$logoFileName}"))) {
      $logo = public_path("storage/assets/default-image.png");
    }

    $transaction = Transaction::with(['lines', 'otherCharges', 'cuenta'])
      ->findOrFail($invoiceId);

    $transaction_lines = $transaction->lines;
    $transaction_other_charges = $transaction->otherCharges;

    $email_cc = str_replace([';', ','], "\n", $transaction->email_cc);

    $titleFrom = 'Proforma';
    $consecutivo = $transaction->proforma_no;

    if ($transaction->document_type == Transaction::NOTACREDITO) {
      $title = 'NOTA DE CRÉDITO';
      $consecutivo = $transaction->consecutivo;
    } else {
      $title = 'PROFORMA';
    }


    $watermark = '';
    if ($transaction->proforma_status == Transaction::RECHAZADA)
      $watermark = 'RECHAZADA';
    else
      if ($transaction->proforma_status == Transaction::ANULADA)
      $watermark = 'ANULADA';

    $html = view('livewire.transactions.export.proforma-receipt', compact(
      'transaction',
      'transaction_lines',
      'transaction_other_charges',
      'logo',
      'title',
      'consecutivo',
      'titleFrom',
      'receipt_type',
      'email_cc',
      'watermark'
    ))->render();

    // Rutas
    $filename = "proforma_{$consecutivo}.pdf";
    $relativePath = "proformas/$filename";
    $storagePath = "public/$relativePath";
    $fullPath = storage_path("app/$storagePath");

    // 🧹 LIMPIEZA AUTOMÁTICA DE PDFs ANTIGUOS (> 3 min)
    $pdfDirectory = storage_path("app/public/proformas");
    if (File::exists($pdfDirectory)) {
      foreach (File::files($pdfDirectory) as $file) {
        try {
          $modified = Carbon::createFromTimestamp($file->getMTime());
          if ($modified->diffInMinutes(now()) >= 10) {
            File::delete($file->getPathname());
          }
        } catch (\Throwable $e) {
          Log::warning("No se pudo eliminar PDF: {$file->getFilename()} - {$e->getMessage()}");
        }
      }
    } else {
      File::makeDirectory($pdfDirectory, 0777, true);
    }

    // Generar PDF
    try {
      Browsershot::html($html)
        ->setNodeBinary(env('BROWSERSHOT_NODE_BINARY'))
        ->setNpmBinary(env('BROWSERSHOT_NPM_BINARY'))
        ->setChromePath(env('BROWSERSHOT_CHROMIUM_BINARY'))
        ->setOption('args', [
          '--disable-gpu',
          '--no-sandbox',
          '--disable-dev-shm-usage',
          '--disable-extensions',
          '--blink-settings=imagesEnabled=true'
        ])
        ->noSandbox() // evita conflictos
        ->showBackground()
        ->format('A4')
        ->margins(0, 0, 0, 0)
        ->save($fullPath);
    } catch (\Exception $e) {
      Log::error('Error generating PDF: ' . $e->getMessage());
      throw $e;
    }
    if ($destination == 'file')
      return $pdfDirectory . "/" . $filename;
    else
      return $filename;
  }

  static public function generateComprobanteElectronicoPdf($invoiceId, $destination = 'browser')
  {
    // Generar PDF
    try {
      $receipt_type = 'sencillo';
      $business = Business::find(1);
      $logoFileName = $business->logo;
      $logo = public_path("storage/assets/img/logos/{$logoFileName}");
      if (!file_exists(public_path("storage/assets/img/logos/{$logoFileName}"))) {
        $logo = public_path("storage/assets/default-image.png");
      }

      $transaction = Transaction::with(['lines', 'otherCharges'])
        ->findOrFail($invoiceId);

      $transaction_lines = $transaction->lines;
      $transaction_other_charges = $transaction->otherCharges;

      $email_cc = str_replace([';', ','], "\n", $transaction->email_cc);

      $title = Helpers::getTitle($transaction);

      $consecutivo = (empty($transaction->key) || empty($transaction->consecutivo))
        ? $transaction->proforma_no
        : $transaction->consecutivo;

      $identification = $transaction->contact->identification;
      $address = $transaction->contact->address ?? '';
      $phone = $transaction->contact->phone ?? '';

      $sellCondition = Helpers::getSellCondition($transaction->condition_sale);

      $currency = $transaction->currency->code;
      $changeType = Helpers::formatDecimal($transaction->factura_change_type);

      $paymentMethod = Helpers::getpaymentMethod($transaction->payments);

      $watermark = '';
      if ($transaction->status == Transaction::RECHAZADA)
        $watermark = 'RECHAZADA';
      else
      if ($transaction->status == Transaction::ANULADA)
        $watermark = 'ANULADA';

      // Generar QR (elige una opción)
      $qrContent = Helpers::generateQrSvg($transaction->key ?? '-');       // SVG

      // Para incrustar en PDF
      if (str_contains($qrContent, '<svg')) {
        // Es SVG
        $qrBase64 = base64_encode($qrContent);
        $qrDataUri = 'data:image/svg+xml;base64,' . $qrBase64;
      } else {
        // Es PNG
        $qrBase64 = base64_encode($qrContent);
        $qrDataUri = 'data:image/png;base64,' . $qrBase64;
      }

      $qrCode = $qrDataUri;

      $showReferencia = false;
      $referencia = [];
      if ($transaction->RefTipoDoc && $transaction->RefNumero && $transaction->RefFechaEmision) {
        $showReferencia = true;
        $transactionReferencia = Transaction::where('key', trim($transaction->RefNumero))->first();

        $referencia = [
          'tipo' => Helpers::getTipoDocumentoReferencia($transaction->RefTipoDoc),
          'numero' => $transaction->RefNumero,
          'consecutivo' => $transactionReferencia ? $transactionReferencia->consecutivo : '-',
          'fechaEmision' => $transaction->RefFechaEmision,
          'razon' => $transaction->RefRazon,
          'title' => $transactionReferencia->status == Transaction::ANULADA ? 'ANULA FACTURA' : 'MODIFICA FACTURA'
        ];
      }

      $nota = [];
      $showNotaAnula = false;
      if ($transaction->status == Transaction::ANULADA) {
        // Se debe buscar la transaction que la referencia
        $transactionRef = Transaction::where('RefNumero', $transaction->key)
          ->where('status', Transaction::ACEPTADA)
          ->first();
        if ($transactionRef) {
          $showNotaAnula = true;
          $nota = [
            'title' => 'Anulado por Nota de crédito',
            'consecutivo' => $transactionRef->consecutivo
          ];
        }
      }

      $html = view('livewire.transactions.export.invoice-receipt', compact(
        'transaction',
        'transaction_lines',
        'transaction_other_charges',
        'logo',
        'title',
        'consecutivo',
        'receipt_type',
        'email_cc',
        'identification',
        'address',
        'phone',
        'sellCondition',
        'currency',
        'changeType',
        'paymentMethod',
        'watermark',
        'qrCode',
        'showReferencia',
        'referencia',
        'showNotaAnula',
        'nota'
      ))->render();

      // Rutas
      $filename = "{$consecutivo}.pdf";
      $relativePath = "invoices/$filename";
      $storagePath = "public/$relativePath";
      $fullPath = storage_path("app/$storagePath");

      // 🧹 LIMPIEZA AUTOMÁTICA DE PDFs ANTIGUOS (> 3 min)
      $pdfDirectory = storage_path("app/public/invoices");
      if (File::exists($pdfDirectory)) {
        foreach (File::files($pdfDirectory) as $file) {
          try {
            $modified = Carbon::createFromTimestamp($file->getMTime());
            if ($modified->diffInMinutes(now()) >= 10) {
              File::delete($file->getPathname());
            }
          } catch (\Throwable $e) {
            Log::warning("No se pudo eliminar PDF: {$file->getFilename()} - {$e->getMessage()}");
          }
        }
      } else {
        File::makeDirectory($pdfDirectory, 0777, true);
      }

      Browsershot::html($html)
        ->setNodeBinary(env('BROWSERSHOT_NODE_BINARY'))
        ->setNpmBinary(env('BROWSERSHOT_NPM_BINARY'))
        ->setChromePath(env('BROWSERSHOT_CHROMIUM_BINARY'))
        ->setOption('args', [
          '--disable-gpu',
          '--no-sandbox',
          '--disable-dev-shm-usage',
          '--disable-extensions',
          '--blink-settings=imagesEnabled=true'
        ])
        ->noSandbox() // evita conflictos
        ->showBackground()
        ->format('A4')
        ->margins(0, 0, 0, 0)
        ->save($fullPath);
    } catch (\Exception $e) {
      Log::error('Error generating PDF: ' . $e->getMessage());
      throw $e;
    }
    if ($destination == 'file')
      return $pdfDirectory . "/" . $filename;
    else
      return $filename;
  }

  public static function generateComprobanteElectronicoXML($transaction, $encode = false, $destination = 'browser')
  {
    $invoice = new ComprobanteElectronico($transaction);
    $location = $transaction->location;

    // Ruta al certificado (desde storage público)
    $relativePath = $location->certificate_digital_file;
    $pfxPath = public_path("storage/assets/certificates/{$relativePath}");

    // Validación rápida
    if (!file_exists($pfxPath)) {
      throw new \Exception("Certificado no encontrado: {$pfxPath}");
    }

    // PIN (esto puede venir desde la configuración o un campo en la tabla)
    $pin = $location->certificate_pin; // Ajustalo según tu lógica

    // El XML a firmar (puede venir desde DB, archivo o generado dinámicamente)
    $xml = $invoice->toXml();

    // Firmar
    $firmador = new Firmador();
    $format = $encode == true ? $firmador::TO_BASE64_STRING : $firmador::TO_XML_STRING;
    $xmlFirmado = $firmador->firmarXml($pfxPath, $pin, $xml, $format);

    // Si el destino es 'browser', lo enviamos para su visualización o descarga
    if ($destination === 'browser') {
      $filename = $transaction->key ?  $transaction->key . '.xml' : 'ComprobanteElectronico' . '.xml';
      // Retornar la respuesta en el navegador utilizando streamDownload
      return response()->streamDownload(function () use ($xmlFirmado) {
        echo $xmlFirmado;
      }, $filename, [
        'Content-Type' => 'application/xml',
        'Content-Disposition' => "inline; filename=" . $filename . "",  // Esto forzará a que se muestre en el navegador
      ]);
    } elseif ($destination == 'file') {
      // Si el destino no es 'browser', devolvemos el XML
      //return $xmlFirmado;

      // Obtener el año y mes de la fecha de la transacción
      $invoiceDate = \Carbon\Carbon::parse($transaction->invoice_date); // Asumiendo que invoice_date está en formato de fecha
      $year = $invoiceDate->format('Y');  // Año
      $month = $invoiceDate->format('m'); // Mes

      // Crear la carpeta de almacenamiento organizada por emisor, año y mes
      $emisorId = $transaction->location->id; // Obtener el ID del emisor
      $baseDir = storage_path('app/public/hacienda/' . $emisorId . '/' . $year . '/' . $month);

      // Crear las carpetas si no existen
      if (!file_exists($baseDir)) {
        mkdir($baseDir, 0777, true);
      }

      // Definir el nombre del archivo y la ruta completa
      $nombre_archivo = $transaction->key . '.xml';
      $filePath = $baseDir . '/' . $nombre_archivo;

      // Guardar el archivo XML en la ruta especificada
      file_put_contents($filePath, $xmlFirmado);

      // Actualizar la transacción con la ruta relativa del archivo
      $xmlDirectory = storage_path("app/public/hacienda/") . $emisorId . '/' . $year . '/' . $month . '/' . $nombre_archivo;
      return $xmlDirectory;
    } else {
      // Si el destino no es 'browser', devolvemos el XML
      return $xmlFirmado;
    }
  }

  public static function generateMensajeElectronicoXML($comprobante, $encode = false, $destination = 'browser')
  {
    $location = $comprobante->location;

    // Ruta al certificado (desde storage público)
    $relativePath = $location->certificate_digital_file;
    $pfxPath = public_path("storage/assets/certificates/{$relativePath}");

    // Validación rápida
    if (!file_exists($pfxPath)) {
      throw new \Exception("Certificado no encontrado: {$pfxPath}");
    }

    // PIN (esto puede venir desde la configuración o un campo en la tabla)
    $pin = $location->certificate_pin; // Ajustalo según tu lógica

    // El XML a firmar (puede venir desde DB, archivo o generado dinámicamente)
    $xml = $comprobante->toXml();

    // Firmar
    $firmador = new Firmador();
    $format = $encode == true ? $firmador::TO_BASE64_STRING : $firmador::TO_XML_STRING;
    $xmlFirmado = $firmador->firmarXml($pfxPath, $pin, $xml, $format);

    // Si el destino es 'browser', lo enviamos para su visualización o descarga
    if ($destination === 'browser') {
      $filename = $comprobante->key ?  $comprobante->key . '.xml' : 'ComprobanteElectronico' . '.xml';
      // Retornar la respuesta en el navegador utilizando streamDownload
      return response()->streamDownload(function () use ($xmlFirmado) {
        echo $xmlFirmado;
      }, $filename, [
        'Content-Type' => 'application/xml',
        'Content-Disposition' => "inline; filename=" . $filename . "",  // Esto forzará a que se muestre en el navegador
      ]);
    } elseif ($destination == 'file') {
      // Si el destino no es 'browser', devolvemos el XML
      //return $xmlFirmado;

      // Obtener el año y mes de la fecha de la transacción
      $invoiceDate = \Carbon\Carbon::parse($comprobante->created_at); // Asumiendo que invoice_date está en formato de fecha
      $year = $invoiceDate->format('Y');  // Año
      $month = $invoiceDate->format('m'); // Mes

      // Crear la carpeta de almacenamiento organizada por emisor, año y mes
      $emisorId = $comprobante->location->id; // Obtener el ID del emisor
      $baseDir = storage_path('app/public/hacienda/' . $emisorId . '/' . $year . '/' . $month);

      // Crear las carpetas si no existen
      if (!file_exists($baseDir)) {
        mkdir($baseDir, 0777, true);
      }

      // Definir el nombre del archivo y la ruta completa
      $nombre_archivo = $comprobante->key . '.xml';
      $filePath = $baseDir . '/' . $nombre_archivo;

      // Guardar el archivo XML en la ruta especificada
      file_put_contents($filePath, $xmlFirmado);

      // Actualizar la transacción con la ruta relativa del archivo
      $xmlDirectory = storage_path("app/public/hacienda/") . $emisorId . '/' . $year . '/' . $month . '/' . $nombre_archivo;
      return $xmlDirectory;
    } else {
      // Si el destino no es 'browser', devolvemos el XML
      return $xmlFirmado;
    }
  }

  public static function validateProformaToRequestInvoice($transaction)
  {
    $msg = [];

    if ($transaction->totalComprobante <= 0) {
      $msg[] = __('The total invoice amount must be greater than 0') . '<br>';
    }

    if (count($transaction->lines) <= 0 && count($transaction->otherCharges) <= 0) {
      $msg[] = __('The invoice must have at least one line of detail or other charges') . '<br>';
    }

    $distributorPercentAmount = $transaction->commisions()->sum('percent');
    if (is_null($distributorPercentAmount) || $distributorPercentAmount < 100) {
      $msg[] = __('The percentage distribution in the cost center information is incomplete') . '<br>';
    }

    $distributorCommisionPercentAmount = $transaction->commisions()->sum('commission_percent');
    if (is_null($distributorCommisionPercentAmount) || $distributorCommisionPercentAmount > 100) {
      $msg[] = __('Commissions exceed 100%, please correct the information and try again') . '<br>';
    }

    return $msg;
  }

  public static function validateProformaToConvertInvoice($transaction)
  {
    $msg = [];

    if ($transaction->totalComprobante <= 0) {
      $msg[] = __('The total invoice amount must be greater than 0') . '<br>';
    }

    if (count($transaction->lines) <= 0 && count($transaction->otherCharges) <= 0) {
      $msg[] = __('The invoice must have at least one line of detail or other charges') . '<br>';
    }

    //Validar que tenga el emisor
    if (!$transaction->location) {
      $msg[] = __('You must define the issuer of the invoice') . '<br>';
    }

    //Validar que tenga la actividad económica del emisor
    if (!$transaction->locationEconomicActivity) {
      $msg[] = __('Must define the economic activity of the issuer') . '<br>';
    }

    //Validar datos del cliente si tiene marcado tipo FACTURA
    if ($transaction->contact->invoice_type == Contact::FACTURA) {
      if (!$transaction->contact->province_id)
        $msg[] = __('El cliente no tiene definida la provincia, por favor corrija e intente nuevamente') . '<br>';

      if (!$transaction->contact->canton_id)
        $msg[] = __('El cliente no tiene definido el cantón, por favor corrija e intente nuevamente') . '<br>';

      if (!$transaction->contact->district_id)
        $msg[] = __('El cliente no tiene definido el distrito, por favor corrija e intente nuevamente') . '<br>';

      if (!$transaction->contact->other_signs || strlen($transaction->contact->other_signs) < 5)
        $msg[] = __('El cliente no tiene definida la información de otras señas o tiene menos de 5 caracteres, por favor corrija e intente nuevamente') . '<br>';
    }

    return $msg;
  }

  public static function validateCotizacionToConvertProforma($transaction)
  {
    $msg = [];

    if ($transaction->totalComprobante <= 0) {
      $msg[] = __('The total invoice amount must be greater than 0') . '<br>';
    }

    if (count($transaction->lines) <= 0 && count($transaction->otherCharges) <= 0) {
      $msg[] = __('The invoice must have at least one line of detail or other charges') . '<br>';
    }

    return $msg;
  }

  public static function validateFacturaCompraToConvertInvoice($transaction)
  {
    $msg = [];

    if ($transaction->totalComprobante <= 0) {
      $msg[] = __('The total invoice amount must be greater than 0') . '<br>';
    }

    if (count($transaction->lines) <= 0 && count($transaction->otherCharges) <= 0) {
      $msg[] = __('The invoice must have at least one line of detail or other charges') . '<br>';
    }

    //Validar que tenga el emisor
    if (!$transaction->location) {
      $msg[] = __('You must define the issuer of the invoice') . '<br>';
    }

    //Validar que tenga la actividad económica del emisor
    if (!$transaction->locationEconomicActivity) {
      $msg[] = __('Must define the economic activity of the issuer') . '<br>';
    }

    //Validar datos del cliente si tiene marcado tipo FACTURA
    if (!$transaction->contact->province_id)
      $msg[] = __('El cliente no tiene definida la provincia, por favor corrija e intente nuevamente') . '<br>';

    if (!$transaction->contact->canton_id)
      $msg[] = __('El cliente no tiene definido el cantón, por favor corrija e intente nuevamente') . '<br>';

    if (!$transaction->contact->district_id)
      $msg[] = __('El cliente no tiene definido el distrito, por favor corrija e intente nuevamente') . '<br>';

    if (!$transaction->contact->other_signs || strlen($transaction->contact->other_signs) < 5)
      $msg[] = __('El cliente no tiene definida la información de otras señas o tiene menos de 5 caracteres, por favor corrija e intente nuevamente') . '<br>';

    return $msg;
  }

  public static function sendComprobanteElectronicoEmail($transactionId)
  {
    $sent = false;

    try {
      $transaction = Transaction::findOrFail($transactionId);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
      throw new \Exception("No se ha encontrado la factura");
    }

    if (in_array($transaction->document_type, ['PRC', 'FEC'])) {
      $recipientEmail = $transaction->location->email;
      $recipientName = $transaction->location->name;
    } else {
      $recipientEmail = $transaction->contact->email;
      $recipientName = $transaction->contact->name;
    }
    $ccEmails = $transaction->email_cc;
    $fromEmail = env('MAIL_USERNAME');
    $message = "Estimado/a " . $recipientName . ",\n\nAdjunto encontrará el comprobante electrónico.\n\nSaludos cordiales.";

    $data = [
      'recipientEmail' => $recipientEmail,
      'recipientName' => $recipientName,
      'ccEmails' => $ccEmails,
      'fromEmail' => $fromEmail
    ];

    Log::info('sendComprobanteElectronicoEmail:', $data);

    $typeComprobante = Helpers::getPdfTitle($transaction->document_type);

    $subject = $typeComprobante . 'No.' . $transaction->consecutivo;

    Log::info('subject de email:', [$subject]);

    $attachments = [];

    // 1. Adjuntar PDF de comprobante
    $filePathPdf = Helpers::generateComprobanteElectronicoPdf($transaction->id, 'file');
    $attachments[] = [
      'path' => $filePathPdf,
      'name' => $transaction->key . '.pdf',
      'mime' => 'application/pdf',
    ];

    // 2. Adjuntar XML de comprobante
    $filePathXml = Helpers::generateComprobanteElectronicoXML($transaction, false, 'file');
    $attachments[] = [
      'path' => $filePathXml,
      'name' => $transaction->key . '.xml',
      'mime' => 'application/xml',
    ];

    // 3. Adjuntar XML de respuesta de Hacienda (CORRECCIÓN)
    $xmlDirectory = storage_path("app/public/");
    $xmlResponsePath = $xmlDirectory . $transaction->response_xml;

    if (file_exists($xmlResponsePath)) {
      $filenameResponse = $transaction->key . '_respuesta.xml';

      // CORRECCIÓN: Usar la ruta del archivo directamente
      $attachments[] = [
        'path' => $xmlResponsePath,
        'name' => $filenameResponse,
        'mime' => 'application/xml', // MIME type corregido
      ];
    }

    // 4. Adjuntar documentos adicionales
    $mediaAttachments = $transaction->media
      ->filter(fn($media) => $media->getCustomProperty('attach_to_email', false) === true)
      ->map(fn($media) => [
        'path' => $media->getPath(),
        'name' => Str::slug($media->name) . '.' . pathinfo($media->file_name, PATHINFO_EXTENSION),
        'mime' => $media->mime_type,
      ])
      ->values()
      ->toArray();

    $attachments = array_merge($attachments, $mediaAttachments);

    $data = [
      'id'      => $transaction->id,
      'from'    => $fromEmail,
      'nombre'  => $recipientName,
      'subject' => $subject,
      'message' => $message,
    ];

    try {
      // Enviar el correo con CC si es necesario
      $mail = Mail::to($recipientEmail);

      $rawCcList = collect(preg_split('/[,;]+/', $ccEmails ?? ''))
        ->map(fn($email) => trim($email))
        ->filter(fn($email) => $email !== '');

      $ccList = $rawCcList
        ->filter(fn($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
        ->unique()
        ->toArray();

      if (!empty($ccList)) {
        $mail->cc($ccList);

        $test = [
          'mailcc' => $ccList,
        ];
        Log::info('Antes de enviar el email:', $test);
      }

      if ($mail->send(new InvoiceMail($data, $attachments))) {
        $sent = true;
      }
    } catch (\Exception $e) {
      Log::error('Error sending email: ' . $e->getMessage());
      // Opcional: notificar a administradores
    }

    return $sent;
  }

  public static function sendNotificationComprobanteElectronicoRejected($transactionId, $type = 'FE')
  {
    $sent = false;
    $transaction = Transaction::find($transactionId);

    if (!$transaction) {
      // Opcional: Log de la respuesta para auditoría
      Log::info('No se ha encontrado el comprobante electrónico:', $transactionId);

      return $sent;
    }

    $business = Business::find(1);

    if (!$business || empty($business->notification_email)) {
      return $sent;
    }

    // Procesar lista de emails (separados por , o ;)
    $emails = preg_split('/[;,]/', $business->notification_email);
    $emails = array_map('trim', $emails);
    $emails = array_filter($emails);

    if (empty($emails)) {
      return $sent;
    }

    // Separar el primer email como principal y el resto como CC
    $recipientEmail = array_shift($emails);
    $recipientName = 'Administración';
    $additionalCC = $emails;  // Los emails restantes de notification_email

    // Obtener CC de la transacción si existe
    $transactionCC = [];
    if (!empty($transaction->email_cc)) {
      $transactionCC = array_map('trim', explode(',', $transaction->email_cc));
      $transactionCC = array_filter($transactionCC);
    }

    // Combinar todos los CC
    $allCC = array_merge($additionalCC, $transactionCC);
    $allCC = array_unique($allCC);  // Eliminar duplicados

    $fromEmail = env('MAIL_USERNAME');
    $subject = 'Comprobante electrónico rechazado';
    $message = "Estimado/a,\n\nAdjunto encontrará el documento electrónico rechazado por hacienda.\n\nSaludos cordiales.";

    $attachments = [];

    // 1. Adjuntar PDF de factura
    $filePathPdf = Helpers::generateComprobanteElectronicoPdf($transaction->id, 'file');
    $attachments[] = [
      'path' => $filePathPdf,
      'name' => $transaction->key . '.pdf',
      'mime' => 'application/pdf',
    ];

    // 2. Adjuntar XML de factura
    $filePathXml = Helpers::generateComprobanteElectronicoXML($transaction, false, 'file');
    $attachments[] = [
      'path' => $filePathXml,
      'name' => $transaction->key . '.xml',
      'mime' => 'application/xml',
    ];

    // 3. Adjuntar XML de respuesta de Hacienda
    $xmlDirectory = storage_path("app/public/");
    $xmlResponsePath = $xmlDirectory . $transaction->response_xml;

    if (file_exists($xmlResponsePath)) {
      $filenameResponse = $transaction->key . '_respuesta.xml';
      $attachments[] = [
        'path' => $xmlResponsePath,
        'name' => $filenameResponse,
        'mime' => 'application/xml',
      ];
    }

    // 4. Adjuntar documentos adicionales
    $mediaAttachments = $transaction->media
      ->filter(fn($media) => $media->getCustomProperty('attach_to_email', false) === true)
      ->map(fn($media) => [
        'path' => $media->getPath(),
        'name' => Str::slug($media->name) . '.' . pathinfo($media->file_name, PATHINFO_EXTENSION),
        'mime' => $media->mime_type,
      ])
      ->values()
      ->toArray();

    $attachments = array_merge($attachments, $mediaAttachments);

    $data = [
      'id'      => $transaction->id,
      'from'    => $fromEmail,
      'nombre'  => $recipientName,
      'subject' => $subject,
      'message' => $message,
      'type'    => $type,
    ];

    try {
      $mail = Mail::to($recipientEmail);

      if (!empty($allCC)) {
        $mail->cc($allCC);
      }

      if ($mail->send(new InvoiceRechazadaMail($data, $attachments))) {
        $sent = true;
      }
    } catch (\Exception $e) {
      Log::error('Error sending email: ' . $e->getMessage());
      // Opcional: notificar a administradores
    }

    return $sent;
  }

  public static function getDocumentPrefix($documentType)
  {
    $prefix = '';
    if ($documentType == Transaction::FACTURAELECTRONICA) {
      $prefix = 'FE';
    } else
    if ($documentType == Transaction::TIQUETEELECTRONICO) {
      $prefix = 'TE';
    } else
    if ($documentType == Transaction::NOTACREDITOELECTRONICA) {
      $prefix = 'NCE';
    } else
    if ($documentType == Transaction::NOTADEBITOELECTRONICA) {
      $prefix = 'NDE';
    } else
    if ($documentType == Transaction::FACTURACOMPRAELECTRONICA) {
      $prefix = 'FEC';
    } else
    if ($documentType == Transaction::FACTURAEXPORTACIONELECTRONICA) {
      $prefix = 'FEE';
    } else
    if ($documentType == Transaction::RECIBOELECTRONICOPAGO) {
      $prefix = 'RPE';
    }
    return $prefix;
  }

  public static function getPdfTitle($documentType, $transaction = null)
  {
    if ($transaction && (empty($transaction->key) || empty($transaction->consecutivo))) {
      return 'PROFORMA';
    }

    $title = '-';
    if ($documentType == Transaction::FACTURAELECTRONICA) {
      $title = 'FACTURA ELECTRONICA';
    } else
    if ($documentType == Transaction::TIQUETEELECTRONICO) {
      $title = 'TIQUETE ELECTRONICO';
    } else
    if ($documentType == Transaction::NOTACREDITOELECTRONICA) {
      $title = 'NOTA DE CREDITO ELECTRONICA';
    } else
    if ($documentType == Transaction::NOTADEBITOELECTRONICA) {
      $title = 'NOTA DE DEBITO ELECTRONICA';
    } else
    if ($documentType == Transaction::FACTURACOMPRAELECTRONICA) {
      $title = 'FACTURA DE COMPRA ELECTRONICA';
    } else
    if ($documentType == Transaction::FACTURAEXPORTACIONELECTRONICA) {
      $title = 'FACTURA DE EXPORTACION ELECTRONICA';
    } else
    if ($documentType == Transaction::RECIBOELECTRONICOPAGO) {
      $title = 'RECIBO DE PAGO ELECTRONICO';
    }
    return $title;
  }

  public static function getPdfView($documentType)
  {
    $view = '';
    if ($documentType == Transaction::FACTURAELECTRONICA) {
      $view = 'livewire.transactions.export.invoice-receipt';
    } else
    if ($documentType == Transaction::TIQUETEELECTRONICO) {
      $view = 'livewire.transactions.export.invoice-receipt';
    } else
    if ($documentType == Transaction::NOTACREDITOELECTRONICA) {
      $view = 'livewire.transactions.export.invoice-receipt';
    } else
    if ($documentType == Transaction::NOTADEBITOELECTRONICA) {
      $view = 'livewire.transactions.export.invoice-receipt';
    } else
    if ($documentType == Transaction::FACTURACOMPRAELECTRONICA) {
      $view = 'livewire.transactions.export.invoice-receipt';
    } else
    if ($documentType == Transaction::FACTURAEXPORTACIONELECTRONICA) {
      $view = 'livewire.transactions.export.invoice-receipt';
    } else
    if ($documentType == Transaction::RECIBOELECTRONICOPAGO) {
      $view = 'livewire.transactions.export.invoice-receipt';
    }

    return $view;
  }

  public static function sendUserCredentialEmail($name, $email, $clave)
  {
    $sent = false;
    $recipientEmail = $email;
    $recipientName = $name;

    $fromEmail = env('MAIL_USERNAME');
    $subject = __('Portal de facturación electrónica, información de acceso');

    $bussines = Business::find(1);
    $logoRelativePath = 'assets/img/logos/' . ($bussines->logo ?? 'default-logo.png');

    $data = [
      'from'      => $fromEmail,
      'name'      => $recipientName,
      'subject'   => $subject,
      'username'  => $email,
      'clave'     => $clave,
      'logo_path' => storage_path('app/public/' . $logoRelativePath),
      'logo_url'  => asset('storage/' . $logoRelativePath),
    ];

    try {
      $mail = Mail::to($recipientEmail);
      $mail->send(new UserCredentialMail($data));

      $sent = true;
    } catch (\Exception $e) {
      Log::error('Error sending email: ' . $e->getMessage());
    }
    return $sent;
  }


  /********************Módulo de banco ******************/
  public static function calculaBalance($cuentasId, $dataDate, $status, $formato = true): array
  {
    $dateStart = null;
    $dateEnd = null;
    $sumarBloqueados = false;

    if (!is_null($dataDate) && !empty($dataDate['DateStart']) && !empty($dataDate['DateEnd'])) {
      $dateStart = $dataDate['DateStart'];
      $dateEnd = $dataDate['DateEnd'];

      if (Carbon::parse($dateStart)->format('m') === Carbon::now()->format('m')) {
        $sumarBloqueados = true;
      }
    }

    $dataSaldo = Movimiento::getSaldoInicial($cuentasId, $dateStart, $dateEnd);
    $dataDebito = Movimiento::getDebito($cuentasId, $dateStart, $dateEnd, $status, false);
    $dataTransito = Movimiento::getTransito($cuentasId, $dateStart, $dateEnd, 'REVISION', false);
    $dataCredito = Movimiento::getCredito($cuentasId, $dateStart, $dateEnd, $status, false);

    $bloqueadoUsd = 0;
    $bloqueadoCrc = 0;

    if ($sumarBloqueados) {
      $dataDebitoBloqueado = Movimiento::getDebito($cuentasId, $dateStart, $dateEnd, $status, true);
      $dataTransitoBloqueado = Movimiento::getTransito($cuentasId, $dateStart, $dateEnd, 'REVISION', true);

      $bloqueadoUsd = $dataDebitoBloqueado['total_debito_usd'] + $dataTransitoBloqueado['total_transito_usd'];
      $bloqueadoCrc = $dataDebitoBloqueado['total_debito_crc'] + $dataTransitoBloqueado['total_transito_crc'];
    }

    $saldoInicialUsd = $dataSaldo['total_saldo_usd'];
    $saldoInicialCrc = $dataSaldo['total_saldo_crc'];

    $debitoUsd = $dataDebito['total_debito_usd'];
    $debitoCrc = $dataDebito['total_debito_crc'];

    $transitoUsd = $dataTransito['total_transito_usd'];
    $transitoCrc = $dataTransito['total_transito_crc'];

    $creditoUsd = $dataCredito['total_credito_usd'];
    $creditoCrc = $dataCredito['total_credito_crc'];

    $saldoFinalUsd = $saldoInicialUsd - $debitoUsd - $transitoUsd + $creditoUsd - $bloqueadoUsd;
    $saldoFinalCrc = $saldoInicialCrc - $debitoCrc - $transitoCrc + $creditoCrc - $bloqueadoCrc;

    $result = [
      'saldo_inicial_crc' => $saldoInicialCrc,
      'saldo_inicial_usd' => $saldoInicialUsd,
      'debito_crc' => $debitoCrc,
      'debito_usd' => $debitoUsd,
      'transito_crc' => $transitoCrc,
      'transito_usd' => $transitoUsd,
      'credito_crc' => $creditoCrc,
      'credito_usd' => $creditoUsd,
      'bloqueado_crc' => $bloqueadoCrc,
      'bloqueado_usd' => $bloqueadoUsd,
      'saldo_final_crc' => $saldoFinalCrc,
      'saldo_final_usd' => $saldoFinalUsd,
    ];

    if ($formato) {
      return array_map(fn($value) => number_format($value, 2, ".", ","), $result);
    }
    return $result;
  }

  public static function getSaldoCuenta($cuenta_id)
  {
    $cuenta = Cuenta::find($cuenta_id);
    if (!$cuenta) {
      return 0; // O puedes lanzar una excepción si prefieres
    }

    $ids = [$cuenta_id];
    $status = 'REGISTRADO';

    // Asume que tienes la función calculaBalance implementada como método estático
    $balance = self::calculaBalance($ids, null, $status, false);

    return $cuenta->moneda_id == Currency::COLONES
      ? $balance['saldo_final_crc']
      : $balance['saldo_final_usd'];
  }

  public static function getSaldoMesCuenta($cuenta_id, $fecha)
  {
    //$mes = date('m', strtotime($fecha));
    //$anno = date('Y', strtotime($fecha));
    $mes = Carbon::parse($fecha)->format('m');  // '07'
    $anno = Carbon::parse($fecha)->year;

    $mesActual = Carbon::now()->format('m');
    $annoActual = Carbon::now()->year;
    $saldo = 0;

    // Buscar balance mensual del mes/anno dado
    $balanceMensual = MovimientoBalanceMensual::where('cuenta_id', $cuenta_id)
      ->where('anno', $anno)
      ->where('mes', $mes)
      ->first();

    if ($balanceMensual) {
      $saldo = $balanceMensual->saldo_final;
    } else {
      // Buscar el balance mensual anterior más cercano
      $balanceAnterior = MovimientoBalanceMensual::where('cuenta_id', $cuenta_id)
        ->where(function ($query) use ($anno, $mes) {
          $query->where('anno', '<', $anno)
            ->orWhere(function ($q) use ($anno, $mes) {
              $q->where('anno', $anno)
                ->where('mes', '<', $mes);
            });
        })
        ->orderByDesc('anno')
        ->orderByDesc('mes')
        ->first();

      if ($balanceAnterior) {
        $saldo = $balanceAnterior->saldo_final;
      }
    }

    $total_bloqueado = 0;

    if ($mes == $mesActual && $anno == $annoActual) {
      $DateStart = null;
      $DateEnd = null;
      $status = 'REGISTRADO';

      // Asume que los métodos getDebito y getTransito existen y están definidos como métodos estáticos en Movimiento
      $dataDebitoBloqueado = Movimiento::getDebito([$cuenta_id], $DateStart, $DateEnd, $status, true);
      $dataTransitoBloqueado = Movimiento::getTransito([$cuenta_id], $DateStart, $DateEnd, 'REVISION', true);

      $bloqueado_usd = $dataDebitoBloqueado['total_debito_usd'] + $dataTransitoBloqueado['total_transito_usd'];
      $bloqueado_crc = $dataDebitoBloqueado['total_debito_crc'] + $dataTransitoBloqueado['total_transito_crc'];

      $total_bloqueado = $bloqueado_usd + $bloqueado_crc;
    }

    return $saldo - $total_bloqueado;
  }

  public static function initSaldosCuentas($cuenta_id = null)
  {
    // Paso 1: Obtener las cuentas
    $cuentas = Cuenta::when($cuenta_id, fn($q) => $q->where('id', $cuenta_id))->get();

    // Obtener el primer movimiento no bloqueado
    $movimiento = Movimiento::selectRaw('YEAR(fecha) AS anno, MONTH(fecha) AS mes, fecha')
      ->where('bloqueo_fondos', '!=', 1)
      ->orderByRaw('anno ASC, mes ASC')
      ->first();

    // Asignar valores por defecto si no hay movimientos
    $anno = $movimiento->anno ?? '2024';
    $mes = isset($movimiento->mes) ? str_pad($movimiento->mes, 2, '0', STR_PAD_LEFT) : '05';

    foreach ($cuentas as $cuenta) {
      self::InitTablaBalanceCuentaMes($cuenta->id, $mes, $anno);
    }

    // Paso 2: Recalcular balances por mes para cada cuenta
    foreach ($cuentas as $cuenta) {
      $movimientos = Movimiento::selectRaw('YEAR(fecha) AS anno, MONTH(fecha) AS mes, fecha')
        ->where('cuenta_id', $cuenta->id)
        ->where('bloqueo_fondos', '!=', 1)
        ->groupBy('anno', 'mes')
        ->orderByRaw('anno ASC, mes ASC')
        ->get();

      foreach ($movimientos as $mov) {
        self::recalcularBalancesMensuales($cuenta->id, $mov->fecha);
      }
    }
  }

  public static function InitTablaBalanceCuentaMes($cuenta_id, $mes, $anno)
  {
    $cuenta = Cuenta::find($cuenta_id);

    if (!$cuenta) {
      // Puedes lanzar una excepción o simplemente salir
      return;
    }

    // Buscar el registro existente de balance mensual
    $balanceMensual = MovimientoBalanceMensual::where('cuenta_id', $cuenta_id)
      ->where('anno', $anno)
      ->where('mes', $mes)
      ->first();

    if (is_null($balanceMensual)) {
      $balanceMensual = new MovimientoBalanceMensual();
      $balanceMensual->cuenta_id = $cuenta_id;
      $balanceMensual->moneda_id = $cuenta->moneda_id;
      $balanceMensual->mes = $mes;
      $balanceMensual->anno = $anno;
      $balanceMensual->saldo_inicial = $cuenta->saldo;
      $balanceMensual->saldo_final = $cuenta->saldo;
    } else {
      $balanceMensual->saldo_inicial = $cuenta->saldo;
      // El saldo_final permanece como esté
    }

    $balanceMensual->save();
  }

  public static function recalcularBalancesMensuales($cuentaId, $fecha)
  {
    $mes = date('m', strtotime($fecha));
    $anno = date('Y', strtotime($fecha));

    $cuenta = Cuenta::find($cuentaId);

    $balanceMensual = MovimientoBalanceMensual::where('cuenta_id', $cuentaId)
      ->where('anno', $anno)
      ->where('mes', $mes)
      ->first();

    if (!$balanceMensual) {
      $balanceMensual = new MovimientoBalanceMensual();
      $balanceMensual->cuenta_id = $cuentaId;
      $balanceMensual->moneda_id = $cuenta->moneda_id;
      $balanceMensual->mes = $mes;
      $balanceMensual->anno = $anno;
      $balanceMensual->saldo_inicial = 0;
      $balanceMensual->saldo_final = 0;
      $balanceMensual->save();
    }

    $balancesMensuales = MovimientoBalanceMensual::where('cuenta_id', $cuentaId)
      ->where(function ($query) use ($anno, $mes) {
        $query->where('anno', '>', $anno)
          ->orWhere(function ($q) use ($anno, $mes) {
            $q->where('anno', $anno)
              ->where('mes', '>=', $mes);
          });
      })
      ->orderBy('anno')
      ->orderBy('mes')
      ->get();

    $saldoInicial = null;

    foreach ($balancesMensuales as $balance) {
      if ($saldoInicial === null) {
        $balanceAnterior = MovimientoBalanceMensual::where('cuenta_id', $cuentaId)
          ->where(function ($query) use ($balance) {
            $query->where('anno', '<', $balance->anno)
              ->orWhere(function ($q) use ($balance) {
                $q->where('anno', $balance->anno)
                  ->where('mes', '<', $balance->mes);
              });
          })
          ->orderByDesc('anno')
          ->orderByDesc('mes')
          ->first();

        $saldoInicial = $balanceAnterior ? $balanceAnterior->saldo_final : $cuenta->saldo;
      }

      $balance->saldo_inicial = $saldoInicial;

      $status = 'REGISTRADO';
      $transitoStatus = 'REVISION';

      $movimientosDebitosDelMes = Movimiento::where('cuenta_id', $cuentaId)
        ->whereMonth('fecha', $balance->mes)
        ->whereYear('fecha', $balance->anno)
        ->where('bloqueo_fondos', '!=', 1)
        ->where('status', $status)
        ->where('clonando', 0)
        ->whereIn('tipo_movimiento', ['ELECTRONICO', 'CHEQUE'])
        ->sum(DB::raw('COALESCE(monto, 0) + COALESCE(impuesto, 0)'));

      $movimientosTransitosDelMes = Movimiento::where('cuenta_id', $cuentaId)
        ->whereMonth('fecha', $balance->mes)
        ->whereYear('fecha', $balance->anno)
        ->where('bloqueo_fondos', '!=', 1)
        ->where('status', $transitoStatus)
        ->where('clonando', 0)
        ->where('tipo_movimiento', 'CHEQUE')
        ->sum(DB::raw('COALESCE(monto, 0) + COALESCE(impuesto, 0)'));

      $movimientosCreditosDelMes = Movimiento::where('cuenta_id', $cuentaId)
        ->whereMonth('fecha', $balance->mes)
        ->whereYear('fecha', $balance->anno)
        ->where('bloqueo_fondos', '!=', 1)
        ->where('status', $status)
        ->where('clonando', 0)
        ->where('tipo_movimiento', 'DEPOSITO')
        ->sum(DB::raw('COALESCE(monto, 0) + COALESCE(impuesto, 0)'));

      $balance->saldo_final = $balance->saldo_inicial - $movimientosDebitosDelMes - $movimientosTransitosDelMes + $movimientosCreditosDelMes;
      $balance->save();

      $saldoInicial = $balance->saldo_final;
    }
  }

  public static function getDateStartAndDateEnd(?string $fecha, bool $mesActual = false): array
  {
    $dateStart = null;
    $dateEnd = null;

    if (!empty($fecha)) {
      $fechas = explode(' to ', $fecha);
      if (count($fechas) === 2) {
        $dateStart = Carbon::createFromFormat('d-m-Y', trim($fechas[0]))->format('Y-m-d');
        $dateEnd = Carbon::createFromFormat('d-m-Y', trim($fechas[1]))->format('Y-m-d');
      } else {
        $dateStart = Carbon::createFromFormat('d-m-Y', trim($fecha))->format('Y-m-d');
        $dateEnd = Carbon::createFromFormat('d-m-Y', trim($fecha))->format('Y-m-d');
      }
    } elseif ($mesActual) {
      $dateStart = Carbon::now()->startOfMonth()->format('Y-m-d');
      $dateEnd = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    return [
      'DateStart' => $dateStart,
      'DateEnd' => $dateEnd,
    ];
  }

  public static function getSaldoCancelar($movimientoId, $retencion)
  {
    $saldoCancelar = 0;

    $movimiento = Movimiento::with(['transactions'])->find($movimientoId);

    if (!$movimiento) {
      return 0;
    }

    foreach ($movimiento->transactions as $invoice) {
      $base = $invoice->totalHonorarios - $invoice->totalDiscount;
      $retencion2 = ($base * 2) / 100;
      $subtotal = $base - $retencion2;
      $saldoCancelar += $subtotal + $invoice->totalTax + $invoice->totalOtrosCargos;
    }

    return $saldoCancelar;
  }

  public static function sendComprobanteMovimientoEmail($movimiento, $concepto, $email)
  {
    $sent = false;
    $recipientEmail = $email;
    $recipientName = '';
    $ccEmails = '';

    $fromEmail = env('MAIL_USERNAME');
    $subject   = 'Solicitud de factura por honorarios profesionales';
    $message   = '';

    $attachments = [];

    $data = [
      'movimiento' => $movimiento,
      'from'       => $fromEmail,
      'nombre'     => $recipientName,
      'subject'    => $subject,
      'message'    => $message,
      'concepto'   => $concepto,
    ];

    try {
      // Procesar múltiples CCs separados por , o ;
      $rawCcList = collect(preg_split('/[,;]+/', $ccEmails ?? ''))
        ->map(fn($email) => trim($email))
        ->filter(fn($email) => $email !== '');

      $ccList = $rawCcList
        ->filter(fn($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
        ->unique()
        ->toArray();

      // Enviar el correo con los archivos adjuntos
      $mail = Mail::to($recipientEmail);

      if (!empty($ccList)) {
        $mail->cc($ccList);
      }

      $mail->send(new MovimientoMail($data, $attachments));

      $sent = true;
    } catch (\Exception $e) {
      Log::error('Error sending email: ' . $e->getMessage());
    }

    return $sent;
  }

  public static function getDiasTranscurridos($fechaInicio, $fechaFin = null, array $diasFeriados = [])
  {
    if (empty($fechaInicio)) {
      return 0;
    }

    $fechaInicio = Carbon::parse($fechaInicio)->startOfDay();
    $fechaFin = $fechaFin ? Carbon::parse($fechaFin)->startOfDay() : now()->startOfDay();

    if ($fechaFin->lessThan($fechaInicio)) {
      return 0;
    }

    $dias = [];

    for ($fecha = $fechaInicio->copy(); $fecha->lte($fechaFin); $fecha->addDay()) {
      if (!in_array($fecha->toDateString(), $diasFeriados)) {
        $dias[] = $fecha->toDateString();
      }
    }

    return max(count($dias) - 1, 0); // restar 1 si hay más de un día
  }

  static public function generateEstadoCuentaPdf(array $transactionsIds)
  {
    $business = Business::find(1);
    $logoFileName = $business->logo;
    $logo = public_path("storage/assets/img/logos/{$logoFileName}");
    if (!file_exists(public_path("storage/assets/img/logos/{$logoFileName}"))) {
      $logo = public_path("storage/assets/default-image.png");
    }

    $html = view('livewire.transactions.export.estado-cuenta-init')->render();

    // Agrupar transacciones por contacto
    $transactionsByContact = Transaction::with('contact', 'lines', 'payments', 'location', 'currency')
      ->whereIn('id', $transactionsIds)
      ->get()
      ->groupBy('contact_id');


    foreach ($transactionsByContact as $contactId => $transactions) {
      if ($transactions->isEmpty()) continue;

      // Header con primera transacción
      $transaction = $transactions->first();
      $html .= view('livewire.transactions.export.estado-cuenta-header', compact('transaction', 'logo'))->render();

      $total_CRC = 0;
      $total_USD = 0;
      $suma_payments_CRC = 0;
      $suma_payments_USD = 0;

      foreach ($transactions as $transaction) {
        $total_factura_CRC = 0;
        $total_factura_USD = 0;

        if ($transaction->currency_id == Currency::COLONES) {
          $total_CRC += $transaction->totalComprobante;
          $total_factura_CRC = $transaction->totalComprobante;

          $total_USD += $transaction->totalComprobante / $transaction->proforma_change_type;
          $total_factura_USD = $transaction->totalComprobante / $transaction->proforma_change_type;
        } else {
          $total_CRC += $transaction->totalComprobante * $transaction->proforma_change_type;
          $total_factura_CRC = $transaction->totalComprobante * $transaction->proforma_change_type;

          $total_USD += $transaction->totalComprobante;
          $total_factura_USD = $transaction->totalComprobante;
        }

        $lines = $transaction->lines;
        $payments = $transaction->payments;

        $html .= view('livewire.transactions.export.estado-cuenta-row', compact('transaction', 'lines'))->render();


        if (!empty($payments)) {
          foreach (['Abonos', 'Recibo No.', 'Fecha', 'Tipo', 'Referencia', 'Banco', 'Monto CRC', 'Monto USD'] as $label) {
            $html .= "<th align=\"center\" style=\"font-size: 10px; font-weight: bold; text-align:center\">
                        $label
                      </th>";
          }

          $total_medio_pago_payments_CRC = 0;
          $total_medio_pago_payments_USD = 0;
          $index = 1;

          foreach ($payments as $payment) {
            $reciboNumero = '-';
            $fecha = \Carbon\Carbon::parse($payment->created_at)->format('d-m-Y');
            $descripcionMedio = '';
            switch ($payment->tipo_medio_pago) {
              case "01":
                $descripcionMedio = 'Efectivo';
                break;
              case "02":
                $descripcionMedio = 'Tarjeta';
                break;
              case "03":
                $descripcionMedio = 'Cheque';
                break;
              case "04":
                $descripcionMedio = 'Transferencia – depósito bancario';
                break;
              case "05":
                $descripcionMedio = 'Recaudado por terceros';
                break;
              case "06":
                $descripcionMedio = 'SINPE MOVIL';
                break;
              case "07":
                $descripcionMedio = 'Plataforma Digital';
                break;
              case "99":
                $descripcionMedio = 'Otros';
                break;
            }
            $referencia = $payment->referencia ?? '';
            $banco = $payment->banco ?? '';

            // CRC
            if ($transaction->currency_id == Currency::COLONES) {
              $payment_crc = $payment->total_medio_pago;
            } else {
              $payment_crc = $payment->total_medio_pago * $transaction->proforma_change_type;
            }
            $total_medio_pago_payments_CRC += $payment_crc;

            // USD
            if ($transaction->moneda_id == Currency::DOLARES) {
              $payment_usd = $payment->total_medio_pago;
            } else {
              $payment_usd = $payment->total_medio_pago / $transaction->proforma_change_type;
            }
            $total_medio_pago_payments_USD += $payment_usd;

            $html .= view('livewire.transactions.export.estado-cuenta-payments-row', compact(
              'index',
              'reciboNumero',
              'fecha',
              'descripcionMedio',
              'referencia',
              'banco',
              'payment_crc',
              'payment_usd'
            ))->render();

            $index++;
          }

          $suma_payments_CRC += $total_medio_pago_payments_CRC;
          $suma_payments_USD += $total_medio_pago_payments_USD;
          $saldo_CRC = $total_factura_CRC - $total_medio_pago_payments_CRC;
          $saldo_USD = $total_factura_USD - $total_medio_pago_payments_USD;

          $html .= view('livewire.transactions.export.estado-cuenta-payments-footer', compact(
            'total_medio_pago_payments_CRC',
            'saldo_CRC',
            'total_medio_pago_payments_USD',
            'saldo_USD'
          ))->render();
        }
      }

      $html .= view('livewire.transactions.export.estado-cuenta-footer', compact(
        'total_CRC',
        'suma_payments_CRC',
        'total_USD',
        'suma_payments_USD'
      ))->render();
    }

    $html .= view('livewire.transactions.export.estado-cuenta-close')->render();

    // Preparar ruta y limpiar PDFs antiguos
    $filename = 'estado-cuenta_' . now()->format('Ymd_His') . '.pdf';
    $relativePath = "proformas/$filename";
    $storagePath = "public/$relativePath";
    $fullPath = storage_path("app/$storagePath");

    $pdfDirectory = storage_path("app/public/proformas");
    if (!File::exists($pdfDirectory)) {
      File::makeDirectory($pdfDirectory, 0777, true);
    } else {
      foreach (File::files($pdfDirectory) as $file) {
        try {
          $modified = Carbon::createFromTimestamp($file->getMTime());
          if ($modified->diffInMinutes(now()) >= 10) {
            File::delete($file->getPathname());
          }
        } catch (\Throwable $e) {
          Log::warning("No se pudo eliminar PDF: {$file->getFilename()} - {$e->getMessage()}");
        }
      }
    }

    try {
      Browsershot::html($html)
        ->setNodeBinary(env('BROWSERSHOT_NODE_BINARY'))
        ->setNpmBinary(env('BROWSERSHOT_NPM_BINARY'))
        ->setChromePath(env('BROWSERSHOT_CHROMIUM_BINARY'))
        ->setOption('args', [
          '--disable-gpu',
          '--no-sandbox',
          '--disable-dev-shm-usage',
          '--disable-extensions',
          '--blink-settings=imagesEnabled=true'
        ])
        ->noSandbox()
        ->showBackground()
        ->format('A4')
        ->margins(0, 0, 0, 0)
        ->save($fullPath);
    } catch (\Exception $e) {
      Log::error('Error generating PDF: ' . $e->getMessage());
      throw $e;
    }

    return $filename;
  }

  static public function getSellCondition($condition_sale)
  {
    $condicionVenta = '';
    switch ($condition_sale) {
      case '01':
        $condicionVenta = 'Contado';
        break;
      case '02':
        $condicionVenta = 'Crédito';
        break;
      case '03':
        $condicionVenta = 'Consignación';
        break;
      case '04':
        $condicionVenta = 'Apartado';
        break;
      case '05':
        $condicionVenta = 'Arrendamiento con opción de compra';
        break;
      case '06':
        $condicionVenta = 'Arrendamiento en función financiera';
        break;
      case '07':
        $condicionVenta = 'Cobro a favor de un tercero';
        break;
      case '08':
        $condicionVenta = 'Servicios prestados al Estado';
        break;
      case '09':
        $condicionVenta = 'Pago de servicios prestado al Estado';
        break;
      case '10':
        $condicionVenta = 'Venta a crédito en IVA hasta 90 días (Artículo 27, LIVA)';
        break;
      case '11':
        $condicionVenta = 'Pago de venta a crédito en IVA hasta 90 días (Artículo 27, LIVA)';
        break;
      case '12':
        $condicionVenta = 'Venta Mercancía No Nacionalizada';
        break;
      case '13':
        $condicionVenta = 'Venta Bienes Usados No Contribuyente';
        break;
      case '14':
        $condicionVenta = 'Arrendamiento Operativo';
        break;
      case '15':
        $condicionVenta = 'Arrendamiento Financiero';
        break;
      case '16':
        $condicionVenta = 'Otro';
        break;
    }

    return $condicionVenta;
  }

  static public function getpaymentMethod($payments)
  {
    $paymentMethod = [];
    foreach ($payments as $payment) {
      switch ($payment->tipo_medio_pago) {
        case '01':
          $paymentMethod[] = 'Efectivo';
          break;
        case '02':
          $paymentMethod[] = 'Tarjeta';
          break;
        case '03':
          $paymentMethod[] = 'Cheque';
          break;
        case '04':
          $paymentMethod[] = 'Transferencia';
          break;
        case '05':
          $paymentMethod[] = 'Recaudado por terceros';
          break;
        case '06':
          $paymentMethod[] = 'SINPE MOVIL';
          break;
        case '07':
          $paymentMethod[] = 'Plataforma Digital';
          break;
        case '08':
          $paymentMethod[] = 'Otro';
          break;
      }
    }
    return implode(', ', $paymentMethod); // Une elementos con coma y espacio
  }

  static function generateQrSvg(string $key): string
  {
    $publicUrl = route('invoice.download.public', ['key' => $key]);

    // 1. Configurar estilo
    $rendererStyle = new RendererStyle(150); // Tamaño en píxeles

    // 2. Usar backend SVG
    $svgBackEnd = new SvgImageBackEnd();

    // 3. Crear renderer
    $renderer = new ImageRenderer($rendererStyle, $svgBackEnd);

    // 4. Generar QR
    $writer = new Writer($renderer);

    return $writer->writeString($publicUrl);
  }

  static function generateQrPng(string $key): string
  {
    $publicUrl = route('invoice.download.public', ['key' => $key]);

    $rendererStyle = new RendererStyle(150);
    $pngBackEnd = new ImagickImageBackEnd();
    $renderer = new ImageRenderer($rendererStyle, $pngBackEnd);

    $writer = new Writer($renderer);
    return $writer->writeString($publicUrl);
  }

  static function generateQrPngWithGd(string $key): string
  {
    $publicUrl = route('invoice.download.public', ['key' => $key]);

    // GD tiene parámetro de tamaño único (no ancho/alto separados)
    $renderer = new GDLibRenderer(150); // Tamaño en píxeles

    $writer = new Writer($renderer);
    return $writer->writeString($publicUrl);
  }

  static public function getTitle($transaction)
  {
    if (empty($transaction->key) || empty($transaction->consecutivo)) {
      return 'Proforma';
    }

    switch ($transaction->document_type) {
      case "FE":
        $title = 'Factura electrónica';
        break;
      case "TE":
        $title = 'Tiquete electrónico';
        break;
      case "NCE":
        $title = 'Nota de crédito electrónica';
        break;
      case "NDE":
        $title = 'Nota de débito electrónica';
        break;
      case "FEC":
        $title = 'Factura electrónica de compra';
        break;
      case "FEE":
        $title = 'Factura electrónica de exportación';
        break;
      case "REP":
        $title = 'Recibo de pago electrónico';
        break;
      default:
        $title = 'No definido';
        break;
    }
    return $title;
  }

  private static function getTipoDocumentoReferencia($tipo)
  {
    $result = '';
    switch ($tipo) {
      case "01":
        $result = 'Factura electrónica';
        break;
      case "02":
        $result = 'Nota de débito electrónica';
        break;
      case "03":
        $result = 'Nota de crédito electrónica';
        break;
      case "04":
        $result = 'Tiquete electrónico';
        break;
      case "05":
        $result = 'Nota de despacho';
        break;
      case "06":
        $result = 'Contrato';
        break;
      case "07":
        $result = 'Procedimiento';
        break;
      case "08":
        $result = 'Comprobante emitido en contingencia';
        break;
      case "09":
        $result = 'Devolución mercadería';
        break;
      case "10":
        $result = 'Comprobante electrónico rechazado por el Ministerio de >Hacienda';
        break;
      case "11":
        $result = 'Sustituye factura rechazada por el Receptor del comprobante';
        break;
      case "12":
        $result = 'Sustituye Factura de exportación';
        break;
      case "13":
        $result = 'Facturación mes vencido';
        break;
      case "14":
        $result = 'Comprobante aportado por contribuyente de Régimen Especial.';
        break;
      case "15":
        $result = 'Sustituye una Factura electrónica de Compra';
        break;
      case "16":
        $result = 'Comprobante de Proveedor No Domiciliado';
        break;
      case "17":
        $result = 'Nota de Crédito a Factura Electrónica de Compra';
        break;
      case "18":
        $result = 'Nota de Débito a Factura Electrónica de Compra';
        break;
    }
    return $result;
  }

  public static function sendNotificationMensajeElectronicoRejected($transactionId)
  {
    $sent = false;
    $transaction = Comprobante::find($transactionId);

    if (!$transaction) {
      // Opcional: Log de la respuesta para auditoría
      Log::info('No se ha encontrado el comprobante electrónico:', $transactionId);

      return $sent;
    }

    $business = Business::find(1);

    if (!$business || empty($business->notification_email)) {
      return $sent;
    }

    // Procesar lista de emails (separados por , o ;)
    $emails = preg_split('/[;,]/', $business->notification_email);
    $emails = array_map('trim', $emails);
    $emails = array_filter($emails);

    if (empty($emails)) {
      return $sent;
    }

    // Separar el primer email como principal y el resto como CC
    $recipientEmail = array_shift($emails);
    $recipientName = 'Administración';
    $additionalCC = $emails;  // Los emails restantes de notification_email

    // Obtener CC de la transacción si existe
    $transactionCC = [];

    // Combinar todos los CC
    $allCC = array_merge($additionalCC, $transactionCC);
    $allCC = array_unique($allCC);  // Eliminar duplicados

    $fromEmail = env('MAIL_USERNAME');
    $subject = 'Comprobante electrónico rechazado';
    $message = "Estimado/a,\n\nAdjunto encontrará el documento electrónico rechazado por hacienda.\n\nSaludos cordiales.";

    $attachments = [];

    // 1. Adjuntar PDF de factura
    /*
    $filePathPdf = Helpers::generateComprobanteElectronicoPdf($transaction->id, 'file');
    $attachments[] = [
      'path' => $filePathPdf,
      'name' => $transaction->key . '.pdf',
      'mime' => 'application/pdf',
    ];
    */

    // 2. Adjuntar XML de factura
    $filePathXml = Helpers::generateMensajeElectronicoXML($transaction, false, 'file');
    $attachments[] = [
      'path' => $filePathXml,
      'name' => $transaction->key . '.xml',
      'mime' => 'application/xml',
    ];

    // 3. Adjuntar XML de respuesta de Hacienda
    $xmlDirectory = storage_path("app/public/");
    $xmlResponsePath = $xmlDirectory . $transaction->xml_respuesta_confirmacion_path;

    if (file_exists($xmlResponsePath)) {
      $filenameResponse = $transaction->key . '-' . $transaction->consecutivo . '_respuesta.xml';
      $attachments[] = [
        'path' => $xmlResponsePath,
        'name' => $filenameResponse,
        'mime' => 'application/xml',
      ];
    }

    $data = [
      'id'      => $transaction->id,
      'from'    => $fromEmail,
      'nombre'  => $recipientName,
      'subject' => $subject,
      'message' => $message,
      'type'    => 'MR',
    ];

    try {
      $mail = Mail::to($recipientEmail);

      if (!empty($allCC)) {
        $mail->cc($allCC);
      }

      if ($mail->send(new InvoiceRechazadaMail($data, $attachments))) {
        $sent = true;
      }
    } catch (\Exception $e) {
      Log::error('Error sending email: ' . $e->getMessage());
      // Opcional: notificar a administradores
    }

    return $sent;
  }
}

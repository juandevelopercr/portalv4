<?php
use App\Models\Currency;
?>
<!DOCTYPE html>
<html class="no-js" lang="en">

<head>
  <!-- Meta Tags -->
  <meta charset="utf-8">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="author" content="SoftwareSolutions">
  <!-- Site Title -->
  <title>Reporte de calculo del registro</title>


  <style>
    {!! file_get_contents(public_path('css/reportes-pdf.css')) !!}
  </style>
</head>

<body>
  @php
  /*
  <div class="tm_container">
    <div class="tm_invoice_wrap">
      <div class="tm_invoice tm_style1 tm_type1" id="tm_download_section">
        <div class="tm_invoice_in">
          <div class="tm_invoice_head tm_top_head tm_mb15 tm_align_center">
            <div class="tm_invoice_left">
              <div class="tm_logo">
                <img src="{{ $logo }}" alt="Logo">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  */
  @endphp

  <table width="100%" cellpading="10">
    <tr>
      <td width="66%" valign="middle">
        <span style="font-weight: bold; font-size: 14px;">PAGO DE REGISTRO PÚBLICO</span>
      </td>
      <td align="right" style="padding-right:5;" width="33%">
        <span style="font-weight: bold; font-size: 14px;">No.: <?= $transaction->consecutivo ?></span><br />
        <span style="font-weight: normal; font-size: 12px;">Fecha:</span><span style="font-size: 12px;"> <?= date('d/m/Y h:i a', strtotime($transaction->transaction_date)); ?></span><br /><br />
      </td>
    </tr>
  </table>

  <div class="boxrounded" style="width:100%">
    <div class="boxrounded" style="padding:5px;">
      <div style="width:80%; float:left; text-align:left">
        <span style="font-size: 14px; font-weight: bold;">CLIENTE: <?= $transaction->customer_name; ?> <?= $transaction->infoCaso; ?></span>
      </div>
      <div style="width:20%; float:left; text-align:right">
        <span style="font-size: 11px; font-weight: bold;"></span>
      </div>
      <div style="clear: both;"></div>
      <?php
      $abogado = '';
      if (!is_null($transaction->caso) && !is_null($transaction->caso->abogadoCargo))
      {
        $user = $transaction->caso->abogadoCargo;
        $abogado = $user->name;
      }
      ?>
      <table border="0" cellpadding="5" cellspacing="0" width="100%">
        <tr>
          <th style="font-size: 11px;" width="33%">CASO: <?= !is_null($transaction->caso) ? $transaction->caso->numero: '-' ?></th>
          <th style="font-size: 11px;" width="33%">Abogado: <?= $abogado ?></th>
          <th style="font-size: 11px;" width="33%">Banco: <?= !is_null($transaction->bank) ? $transaction->bank->name: '' ?></th>
        </tr>
      </table>
    </div>
    <div style="clear:both"></div>
    <div class="box" style="width:100%; float:left; height:auto; padding-top:0px;">
      <table border="0" cellpadding="5" cellspacing="0" width="100%">
        <tr>
          <th style="font-size: 11px;" width="33%">Detalle</th>
          <th style="font-size: 11px;" width="16%">Monto Cobrado</th>
          <th style="font-size: 11px;" width="16%">Monto pagar según escritura o valor fiscal</th>
          <th style="font-size: 11px;" width="10%">Tipo de cambio</th>
          <th style="font-size: 11px;" width="9%">Cantidad</th>
          <th style="font-size: 11px;" width="16%">Monto a pagar en colones</th>
        </tr>
        <?php
        $montoTotal = 0;
        $totalTimbresConDescuento = 0;
        $totalTimbresSinDescuento = 0;
        $total_item_con_descuento_seis_porciento = 0;
        $total_item_sin_descuento_seis_porciento = 0;

        $total_descuento_seis_porciento = 0;

        if ($transaction->currency_id == 16)
          $moneda = 'COLONES';
        else
          $moneda = 'DOLARES';

        foreach ($transaction_lines as $index=>$line) {

          //$line->registro_cantidad = empty($line->registro_cantidad) ? 1 : $line->registro_cantidad;
          $monto_escritura_colones = $line->getMontoEscrituraColones($transaction->currency_id, $transaction->proforma_change_type);
          //$moneda = $transaction->currency_id == Currency::COLONES ? 'COLONES' : 'DOLARES';
          /*
          if (!$line->product->enable_registration_calculation) {
            $registro_monto_escritura = $line->getMontoOriginalValorEscritura($moneda, $transaction->proforma_change_type);
          } else
            $registro_monto_escritura = (float)$line->registro_monto_escritura;

          $estado_escritura = "PENDIENTE";
          if (!is_null($line->fecha_reporte_gasto) && !empty($line->fecha_reporte_gasto))
            $estado_escritura = "PAGADO";
          */

          $sub_total_descuento_seis_porciento = 0;
          $sub_totalTimbresSinDescuento = 0;
          $line->fecha_reporte_gasto = date('Y-m-d');

          //if (is_null($line->codigocabys) || empty($line->codigocabys))
          // $line->codigocabys = 'x';
          //$line->save();
          $monto_sin_descuento = 0;
          $tipo_cambio = (!is_null($line->registro_change_type) && !empty($line->registro_change_type)) ? $line->registro_change_type : $transaction->factura_change_type;

          // Si no está habilitado modificar el monto en el calculo del registro entonces usar como precio el monto del acto en colones
          if (!$line->product->enable_registration_calculation){
            $precio = $line->price;

            if ($transaction->currency_id == Currency::DOLARES)
              $precio_calculo = $precio * $tipo_cambio; // Precio siempre en colones
            else
              $precio_calculo = $precio;
          }
          else
          {
            //CAMBIO DE AYER
            if (!is_null($line->registro_currency_id) && (float)$line->registro_monto_escritura > 0) {
              $precio_calculo = $monto_escritura_colones;  // Precio siempre en colones
            }
            else
            if (!is_null($line->registro_currency_id) && (float)$line->registro_change_type > 0)
            {
              $precio_calculo = $monto_escritura_colones;  // Precio siempre en colones
            }
            else
            {
              $precio = $line->price;

              if ($transaction->currency_id == Currency::DOLARES)
                $precio_calculo = $precio * $tipo_cambio; // Precio siempre en colones
              else
                $precio_calculo = $precio;
            }
          }

          $dataRegistro = [];
          $tipo_reporte_registro = 'normal';
          if (in_array($line->id, $ids_normal)) {
            $tipo_reporte_registro = 'normal';
            $line->calculo_registro_normal = 1;

            $dataRegistro = [
              'fecha_reporte_gasto' =>$line->fecha_reporte_gasto,
              'calculo_registro_normal'=> 1,
              //'calculo_registro_iva'=>0,
              //'calculo_registro_no_iva'=>0
            ];
          }
          else
          if (in_array($line->id, $ids_iva)) {
            $tipo_reporte_registro = 'iva';
            $line->calculo_registro_iva = 1;

            $dataRegistro = [
              'fecha_reporte_gasto' =>$line->fecha_reporte_gasto,
              //'calculo_registro_normal'=> 0,
              'calculo_registro_iva'=>1,
              //'calculo_registro_no_iva'=>0
            ];
          }
          else
          if (in_array($line->id, $ids_no_iva)) {
            $tipo_reporte_registro = 'no_iva';
            $line->calculo_registro_no_iva = 1;

            $dataRegistro = [
              'fecha_reporte_gasto' =>$line->fecha_reporte_gasto,
              //'calculo_registro_normal'=> 0,
              //'calculo_registro_iva'=>0,
              'calculo_registro_no_iva'=>1
            ];
          }

          if (!empty($dataRegistro)) {
            $line->fill($dataRegistro)->save();
          }

          $tipo = 'detallado';
          $desglose_formula_timbres = $line->desgloseTimbreFormulaRegistro($transaction->bank_id, 'GASTO', $precio_calculo, $tipo_reporte_registro);

          // Tabla Timbre Abogados
          $desglose_tabla_abogados_timbres = $line->desgloseTablaAbogadosRegistro($transaction->bank_id, 'GASTO', $precio_calculo, $tipo_reporte_registro);

          // Fijo
          $desglose_calculos_fijos_timbres = $line->desgloseCalculosFijosRegistro($transaction->bank_id, 'GASTO', $precio_calculo, $tipo_reporte_registro);

          // Monto Manual
          $desglose_calculos_monto_manual_timbres = $line->desgloseCalculaMontoManualRegistro($transaction->bank_id, 'GASTO', $precio_calculo, $tipo_reporte_registro);


          $total_item_con_descuento_seis_porciento += $desglose_formula_timbres['monto_sin_descuento'] + $desglose_formula_timbres['monto_con_descuento'];
          $total_item_con_descuento_seis_porciento += $desglose_tabla_abogados_timbres['monto_sin_descuento'] + $desglose_tabla_abogados_timbres['monto_con_descuento'];
          $total_item_con_descuento_seis_porciento += $desglose_calculos_fijos_timbres['monto_sin_descuento'] + $desglose_calculos_fijos_timbres['monto_con_descuento'];
          $total_item_con_descuento_seis_porciento += $desglose_calculos_monto_manual_timbres['monto_sin_descuento'] + $desglose_calculos_monto_manual_timbres['monto_con_descuento'];

          $total_item_sin_descuento_seis_porciento += $desglose_formula_timbres['monto_sin_descuento'] + $desglose_formula_timbres['monto_con_descuento'];
          $total_item_sin_descuento_seis_porciento += $desglose_tabla_abogados_timbres['monto_sin_descuento'] + $desglose_tabla_abogados_timbres['monto_con_descuento'];
          $total_item_sin_descuento_seis_porciento += $desglose_calculos_fijos_timbres['monto_sin_descuento'] + $desglose_calculos_fijos_timbres['monto_con_descuento'];
          $total_item_sin_descuento_seis_porciento += $desglose_calculos_monto_manual_timbres['monto_sin_descuento'] + $desglose_calculos_monto_manual_timbres['monto_con_descuento'];

          $total_descuento_seis_porciento += $desglose_formula_timbres['sumdescuento_seis_porciento'] + $desglose_tabla_abogados_timbres['sumdescuento_seis_porciento'] + $desglose_calculos_fijos_timbres['sumdescuento_seis_porciento'] + $desglose_calculos_monto_manual_timbres['sumdescuento_seis_porciento'];
          $sub_total_descuento_seis_porciento += $desglose_formula_timbres['sumdescuento_seis_porciento'] + $desglose_tabla_abogados_timbres['sumdescuento_seis_porciento'] + $desglose_calculos_fijos_timbres['sumdescuento_seis_porciento'] + $desglose_calculos_monto_manual_timbres['sumdescuento_seis_porciento'];
          ?>
          <tr>
            <td align="left">
              <span style="font-size: 11px; font-weight: bold;">
                <?= $line->detail; ?>
                <?php
                $totalTimbres_temp = $desglose_formula_timbres['monto_sin_descuento'] + $desglose_tabla_abogados_timbres['monto_sin_descuento'] + $desglose_calculos_fijos_timbres['monto_sin_descuento'] +
                  $desglose_calculos_monto_manual_timbres['monto_sin_descuento'];

                if ($transaction->currency_id == Currency::DOLARES) // $
                {
                  $totalTimbres_temp = $totalTimbres_temp * $tipo_cambio;
                }
                $total_temp_sin_descuento = round($totalTimbres_temp);
                $total_temp_con_descuento = $total_temp_sin_descuento;
                if ($line->monto_descuento > 0) {
                  $descuento = $total_temp_con_descuento * $line->monto_descuento / 100;
                  $total_temp_con_descuento = round($total_temp_con_descuento - $descuento);
                }
                ?>
              </span>
            </td>
            <td align="center">
              <span style="font-size: 11px; font-weight: normal;">
              <?php
                if ((int)$transaction->currency_id == Currency::DOLARES) {
                  $strmonto = $line->price;
                  echo '$ ' . number_format($strmonto, 2, ',', '.');
                } else {
                  $strmonto = $line->price;
                  echo '¢ ' . number_format($strmonto, 2, ',', '.');
                }
                ?>
              </span>
            </td>

            <td align="right">
              <span style="font-size: 11px; font-weight: normal;">
                <?php
                if ($line->registro_currency_id == Currency::DOLARES) {
                  if ((float)$line->registro_change_type > 0)
                    echo '$ '. number_format(round($monto_escritura_colones / $line->registro_change_type), 2, ',', '.') ;
                } else
                if ($line->registro_currency_id != Currency::DOLARES) {
                  echo '¢ '. number_format(round($monto_escritura_colones), 2, ',', '.') ;
                }
                ?>
              </span>
            </td>

            <td align="center">
              <span style="font-size: 11px; font-weight: normal;">
              <?php
                if ((int)$line->registro_currency_id == 1)
                  echo number_format($line->registro_change_type, 2, ',', '.');
                ?>
              </span>
            </td>

            <td align="center">
              <span style="font-size: 11px; font-weight: normal;">
              <?php
                if ((int)$line->product->enable_registration_calculation)
                  $cantidad = $line->registro_cantidad;
                else
                  $cantidad = $line->quantity;

                echo (int)$cantidad;
                ?>
              </span>
            </td>

            <td align="right">
              <span style="font-size: 11px; font-weight: bold;">
                <?php
                echo '¢ ' . number_format($monto_escritura_colones, 2, ',', '.');
                ?>
              </span>
            </td>
          </tr>

          <?php
          // DESGLOSE
          if ($tipo == 'detallado') {
            if (!empty($desglose_formula_timbres['datos']))
            {
              foreach ($desglose_formula_timbres['datos'] as $d) : ?>
                <?php
                  $datatimbre = $d['monto_con_descuento'] + $d['monto_sin_descuento'];
                ?>
                <tr>
                  <td align="left">
                    <span style="font-size: 11px; font-weight: normal;">
                      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $d['titulo']; ?>
                    </span>
                  </td>
                  <td>

                  </td>
                  <td align="right">
                    <span style="font-size: 11px; font-weight: normal;">
                      <?php
                      if ($line->registro_currency_id == Currency::DOLARES) {
                        $t = (!is_null($line->registro_change_type) && (float)$line->registro_change_type > 0) ? $line->registro_change_type : 1;
                        echo number_format($datatimbre / $t, 2, ',', '.');
                      }
                      ?>
                    </span>
                  </td>
                  <td>

                  </td>
                  <td>

                  </td>
                  <td align="right">
                    <span style="font-size: 11px; font-weight: normal;">
                      <?= number_format(round($datatimbre), 2, ',', '.'); ?>
                    </span>
                  </td>
                </tr>
              <?php
                $totalTimbresConDescuento += round($d['monto_con_descuento']);
                $totalTimbresSinDescuento += round($d['monto_sin_descuento']);
                $sub_totalTimbresSinDescuento += round($d['monto_sin_descuento']);
              endforeach;
              ?>
              <?php
            }
          } else {
            $totalTimbresConDescuento += round($d['monto_con_descuento']);
            $totalTimbresSinDescuento += round($d['monto_sin_descuento']);
            $sub_totalTimbresSinDescuento += round($d['monto_sin_descuento']);
          }

          if ($tipo == 'detallado') {
            if (!empty($desglose_tabla_abogados_timbres['datos']))
            {
              foreach ($desglose_tabla_abogados_timbres['datos'] as $d) : ?>
                <?php
                  $datatimbre = $d['monto_con_descuento'] + $d['monto_sin_descuento'];
                ?>
                <tr>
                  <td align="left">
                    <span style="font-size: 11px; font-weight: normal;">
                      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $d['titulo']; ?>
                    </span>
                  </td>
                  <td>

                  </td>
                  <td align="right">
                    <span style="font-size: 11px; font-weight: normal;">
                      <?php
                      if ($line->registro_currency_id == Currency::DOLARES) {
                        $t = (!is_null($line->registro_change_type) && (float)$line->registro_change_type > 0) ? $line->registro_change_type : 1;
                        echo number_format($datatimbre / $t, 2, ',', '.');
                      }
                      ?>
                    </span>
                  </td>
                  <td>

                  </td>
                  <td>

                  </td>
                  <td align="right">
                    <span style="font-size: 11px; font-weight: normal;">
                      <?= number_format(round($datatimbre), 2, ',', '.'); ?>
                    </span>
                  </td>
                </tr>
              <?php
                $totalTimbresConDescuento += round($d['monto_con_descuento']);
                $totalTimbresSinDescuento += round($d['monto_sin_descuento']);
                $sub_totalTimbresSinDescuento += round($d['monto_sin_descuento']);
              endforeach;
              ?>
              <?php
            }
          } else {
            $totalTimbresConDescuento += round($d['monto_con_descuento']);
            $totalTimbresSinDescuento += round($d['monto_sin_descuento']);
            $sub_totalTimbresSinDescuento += round($d['monto_sin_descuento']);
          }

          if ($tipo == 'detallado') {
            if (!empty($desglose_calculos_fijos_timbres['datos']))
            {
              foreach ($desglose_calculos_fijos_timbres['datos'] as $d) : ?>
                <?php
                  $datatimbre = $d['monto_con_descuento'] + $d['monto_sin_descuento'];
                ?>
                <tr>
                  <td align="left">
                    <span style="font-size: 11px; font-weight: normal;">
                      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $d['titulo']; ?>
                    </span>
                  </td>
                  <td>

                  </td>
                  <td align="right">
                    <span style="font-size: 11px; font-weight: normal;">
                      <?php
                      if ($line->registro_currency_id == Currency::DOLARES) {
                        $t = (!is_null($line->registro_change_type) && (float)$line->registro_change_type > 0) ? $line->registro_change_type : 1;
                        echo number_format($datatimbre / $t, 2, ',', '.');
                      }
                      ?>
                    </span>
                  </td>
                  <td>

                  </td>
                  <td>

                  </td>
                  <td align="right">
                    <span style="font-size: 11px; font-weight: normal;">
                      <?= number_format(round($datatimbre), 2, ',', '.'); ?>
                    </span>
                  </td>
                </tr>
              <?php
                $totalTimbresConDescuento += round($d['monto_con_descuento']);
                $totalTimbresSinDescuento += round($d['monto_sin_descuento']);
                $sub_totalTimbresSinDescuento += round($d['monto_sin_descuento']);
              endforeach;
              ?>
              <?php
            }
          } else {
            $totalTimbresConDescuento += round($d['monto_con_descuento']);
            $totalTimbresSinDescuento += round($d['monto_sin_descuento']);
            $sub_totalTimbresSinDescuento += round($d['monto_sin_descuento']);
          }

          if ($tipo == 'detallado') {
            if (!empty($desglose_calculos_monto_manual_timbres['datos']))
            {
              foreach ($desglose_calculos_monto_manual_timbres['datos'] as $d) : ?>
                <?php
                  $datatimbre = $d['monto_con_descuento'] + $d['monto_sin_descuento'];
                ?>
                <tr>
                  <td align="left">
                    <span style="font-size: 11px; font-weight: normal;">
                      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $d['titulo']; ?>
                    </span>
                  </td>
                  <td>

                  </td>
                  <td align="right">
                    <span style="font-size: 11px; font-weight: normal;">
                      <?php
                      if ($line->registro_currency_id == Currency::DOLARES) {
                        $t = (!is_null($line->registro_change_type) && (float)$line->registro_change_type > 0) ? $line->registro_change_type : 1;
                        echo number_format($datatimbre / $t, 2, ',', '.');
                      }
                      ?>
                    </span>
                  </td>
                  <td>

                  </td>
                  <td>

                  </td>
                  <td align="right">
                    <span style="font-size: 11px; font-weight: normal;">
                      <?= number_format(round($datatimbre), 2, ',', '.'); ?>
                    </span>
                  </td>
                </tr>
              <?php
                $totalTimbresConDescuento += round($d['monto_con_descuento']);
                $totalTimbresSinDescuento += round($d['monto_sin_descuento']);
                $sub_totalTimbresSinDescuento += round($d['monto_sin_descuento']);
              endforeach;
              ?>

              <?php
            }
            ?>
        <?php
          } else {
            $totalTimbresConDescuento += round($d['monto_con_descuento']);
            $totalTimbresSinDescuento += round($d['monto_sin_descuento']);
            $sub_totalTimbresSinDescuento += round($d['monto_sin_descuento']);
          }

          ?>
          <tr>
            <td align="left">

            </td>
            <td>

            </td>
            <td align="right">

            </td>
            <td align="right" colspan="3" style="color: red;">
              <span style="font-size: 11px; font-weight: normal;">
              <strong>SUBTOTAL: <?= number_format(ceil($sub_total_descuento_seis_porciento + $sub_totalTimbresSinDescuento), 2, ',', '.'); ?></strong>
              </span>
            </td>
          </tr>
          <?php
        }
        ?>
      </table>
    </div>

    <table border="0" cellpadding="5" cellspacing="0" width="100%">
      <tr>
        <td width="55%">
          <span style="font-size: 11px; font-weight: bold;"></span>

        </td>
        <td width="25%">
          <span style="font-size: 11px; font-weight: bold;">SUBTOTAL CON DESCUENTO:</span>
        </td>
        <td width="20%" align="right">
          <span style="font-size: 11px; font-weight: bold;">
            ¢ <?= number_format(round($totalTimbresConDescuento), 2, ',', '.'); ?>
          </span>
        </td>
      </tr>
      <tr>
        <td>

        </td>
        <td>
          <span style="font-size: 11px; font-weight: bold;">TOTAL CON DESCUENTO 6%:</span>
        </td>
        <td align="right">
          <span style="font-size: 11px; font-weight: bold;">
            ¢ <?= number_format(round($totalTimbresConDescuento * 0.94), 2, ',', '.'); ?>
          </span>
        </td>
      </tr>
      <tr>
        <td>

        </td>
        <td>
          <span style="font-size: 11px; font-weight: bold;">TOTAL SIN DESCUENTO:</span>
        </td>
        <td align="right">
          <span style="font-size: 11px; font-weight: bold;">
            ¢ <?= number_format(round($totalTimbresSinDescuento), 2, ',', '.'); ?>
          </span>
        </td>
      </tr>
      <tr>
        <td>

        </td>
        <td>
          <span style="font-size: 11px; font-weight: bold;">IMPUESTO A CANCELAR:</span>
        </td>
        <td align="right" style="background-color: yellow; border: 1px solid black;">
          <span style="font-size: 11px; font-weight: bold;">
          ¢ <?= number_format(ceil($total_descuento_seis_porciento + $totalTimbresSinDescuento), 2, ',', '.'); ?></span>
        </td>
      </tr>
    </table>
  </div>
</body>
</html>

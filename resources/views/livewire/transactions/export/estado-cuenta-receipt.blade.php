<!DOCTYPE html>
<html class="no-js" lang="en">

<head>
  <!-- Meta Tags -->
  <meta charset="utf-8">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="author" content="SoftwareSolutions">
  <!-- Site Title -->
  <title>{{ $titleFrom }}</title>
  <link rel="stylesheet" href="{{ public_path('css/reportes-pdf') }}" type="text/css">
</head>
<body>
  <table width="100%" cellpading="10">
	<tr>
		<td width="33%" valign="middle">
        <img src="{{ $logo }}" alt="Logo">
        </td>
        <td width="33%" align="center" valign="top">
          <br />
          <span style="font-size: 13px;"><strong><?= !empty($facturas[0]->customer_comercial_name) ? $facturas[0]->customer_comercial_name : $facturas[0]->customer_name ?></strong></span><br />
          <span style="font-size: 11px;">Identificación: <?= $facturas[0]->cliente->identificacion ?></span><br />
          <span style="font-size: 11px;">Dirección: <?= wordwrap( $facturas[0]->cliente->direccion, 50 ) ?></span><br />
          <span style="font-size: 11px;">Teléfono: <?= $facturas[0]->cliente->telefono ?></span><br />
          <span style="font-size: 11px;">Correo: <?= $facturas[0]->email_cliente ?></span><br />
        </td>
        <td align="right" style="padding-right:5;" width="33%">
        	<br />
          <br />
			    <span style="font-weight: normal; font-size: 12px;">Fecha:</span><span style="font-size: 12px;"> <?= date('d-m-Y h:i a'); ?></span><br /><br />
        </td>
    </tr>
    <tr>
        <td align="right" colspan="3">&nbsp;

        </td>
    </tr>
</table>
<table width="100%" cellpadding="2" cellspacing="2">
<tr>
    <td align="center">
        <span style="font-size: 10px; font-weight: bold;">ESTADO DE CUENTA</span>
    </td>
</tr>
</table>
<br />
<table class="table table-bordered" border="1" cellspacing="0" cellpadding="5" width="100%">
	<thead>
		<tr>
            <th align="center" width="18%" colspan="2" style="font-size: 10px; font-weight: bold; text-align:center">
                No Factura
            </th>
            <th align="center" width="18%" style="font-size: 10px; font-weight: bold; text-align:center">
                Emisor
            </th>
            <th align="center" width="8%" style="font-size: 10px; font-weight: bold; text-align:center">
                Fecha Vencimiento
            </th>
            <th align="center" width="8%" style="font-size: 10px; font-weight: bold; text-align:center">
                Moneda
            </th>
            <th align="center" width="8%" style="font-size: 10px; font-weight: bold; text-align:center">
                Tipo de Cambio
            </th>
            <th align="center" width="20%" style="font-size: 10px; font-weight: bold; text-align:center">
                Monto CRC
            </th>
            <th align="center" width="20%" style="font-size: 10px; font-weight: bold; text-align:center">
                Monto USD
            </th>
        </tr>
	</thead>
	<tbody>
	<!-- ITEMS HERE -->
        <?php
        $total_CRC = 0;
        $total_USD = 0;
        $simbolo = '';
		$suma_abonos_CRC = 0;
		$suma_abonos_USD = 0;
		$pendiente_pago_CRC = 0;
		$pendiente_pago_USD = 0;
        foreach ($facturas as $factura){
			$total_factura_CRC = 0;
			$total_factura_USD = 0;

            $emailcliente = $factura->email_cliente;
            $simbolo = $factura->moneda->codigo;
			if ($factura->moneda_id == 16)
			{
            	$total_CRC += $factura->totalComprobante;
	            $total_factura_CRC = $factura->totalComprobante;

	            $total_USD += $factura->totalComprobante / $factura->tipo_cambio;
				$total_factura_USD = $factura->totalComprobante / $factura->tipo_cambio;
			}
			else
			{
            	$total_CRC += $factura->totalComprobante * $factura->tipo_cambio;
	            $total_factura_CRC = $factura->totalComprobante * $factura->tipo_cambio;

	            $total_USD += $factura->totalComprobante;
				$total_factura_USD = $factura->totalComprobante;
			}
            ?>
            <tr>
                <td align="center" colspan="2">
                    <span style="font-size: 10px; font-weight: normal;"><?= $factura->consecutivo ?></span>
                </td>
                <td align="left">
                    <span style="font-size: 10px; font-weight: normal;"><?= $factura->emisor->nombre ?></span>
                </td>
                <td align="center">
                    <span style="font-size: 10px; font-weight: normal;">
                    	<?php
						if ($factura->plazo_credito > 0)
							echo date('d-m-Y', strtotime($factura->fecha_emision."+ ".$factura->plazo_credito." days"));
						else
							echo date('d-m-Y', strtotime($factura->fecha_emision));
						?>
                    </span>
                </td>
                <td align="center">
                    <span style="font-size: 10px; font-weight: normal;"><?= $factura->moneda->codigo ?></span>
                </td>
                <td align="center">
                    <span style="font-size: 10px; font-weight: normal;"><?= Utiles::Formatear_Colonos_SinSigno($factura->tipo_cambio, 2) ?></span>
                </td>
                <td align="right">
                    <span style="font-size: 10px; font-weight: normal;">
                    	<?php
						if ($factura->moneda_id == 16)
							echo 'CRC' . '&nbsp;' . Utiles::Formatear_Colonos_SinSigno($factura->totalComprobante, 2);
						else
							echo 'CRC' . '&nbsp;' . Utiles::Formatear_Colonos_SinSigno($factura->totalComprobante * $factura->tipo_cambio, 2);
						?>
                    </span>
                </td>
                <td align="right">
                    <span style="font-size: 10px; font-weight: normal;">
                    	<?php
						if ($factura->moneda_id == 1)
							echo 'USD' . '&nbsp;' . Utiles::Formatear_Colonos_SinSigno($factura->totalComprobante, 2);
						else
							echo 'USD' . '&nbsp;' . Utiles::Formatear_Colonos_SinSigno($factura->totalComprobante / $factura->tipo_cambio, 2);
						?>
                    </span>
                </td>
            </tr>

            <?php
            $factura_abonos = FacturasAbonos::find()->where(['factura_id'=>$factura->id])->orderBy('fecha ASC')->all();
            if (!empty($factura_abonos)) : ?>
                <tr>
                    <th align="center" style="font-size: 10px; font-weight: bold; text-align:center">
                        Abonos
                    </th>
                    <th align="center" style="font-size: 10px; font-weight: bold; text-align:center">
                        Recibo No.
                    </th>
                    <th align="center" style="font-size: 10px; font-weight: bold; text-align:center">
                        Fecha
                    </th>
                    <th align="center" style="font-size: 10px; font-weight: bold; text-align:center">
                        Tipo
                    </th>
                    <th align="center" style="font-size: 10px; font-weight: bold; text-align:center">
                        Referencia
                    </th>
                    <th align="center" style="font-size: 10px; font-weight: bold; text-align:center">
                        Banco
                    </th>
                    <th align="center" style="font-size: 10px; font-weight: bold; text-align:center">
                        Monto CRC
                    </th>
                    <th align="center" style="font-size: 10px; font-weight: bold; text-align:center">
                        Monto USD
                    </th>
                </tr>
                <?php
                $monto_abonos_CRC = 0;
                $monto_abonos_USD = 0;
                $index = 1;
                foreach ($factura_abonos as $abono) : ?>
                <tr>
                    <td align="right">
                        <span style="font-size: 10px; font-weight: normal;"><?= $index; ?></span>
                    </td>
                    <td>
                       <span style="font-size: 10px; font-weight: normal;"><?= !is_null($abono->recibo) ? $abono->recibo->numero : '-' ?></span>
                    </td>
                    <td align="center">
                        <span style="font-size: 10px; font-weight: normal;"><?= date('d-m-Y', strtotime($abono->fecha)) ?></span>
                    </td>
                    <td align="center">
                        <span style="font-size: 10px; font-weight: normal;"><?= $abono->medioPago->descripcion ?></span>
                    </td>
                    <td align="center">
                        <span style="font-size: 10px; font-weight: normal;"><?= $abono->referencia ?></span>
                    </td>
                    <td align="center">
                        <span style="font-size: 10px; font-weight: normal;"><?= $abono->banco ?></span>
                    </td>
                    <td align="right">
                        <span style="font-size: 10px; font-weight: normal;">
						<?php
                        if ($factura->moneda_id == 16){
							echo 'CRC' . '&nbsp;' . Utiles::Formatear_Colonos_SinSigno($abono->monto, 2);
                            $abono_crc = $abono->monto;
						}
						else
						{
							$abono_crc = $abono->monto * $factura->tipo_cambio;
							echo 'CRC' . '&nbsp;' . Utiles::Formatear_Colonos_SinSigno($abono_crc, 2);
						}
						$monto_abonos_CRC += $abono_crc;
						?>
                        </span>
                    </td>
                    <td align="right">
                        <span style="font-size: 10px; font-weight: normal;">
                        <?php
                        if ($factura->moneda_id == 1){
							echo 'USD' . '&nbsp;' . Utiles::Formatear_Colonos_SinSigno($abono->monto, 2);
                            $abono_usd = $abono->monto;
						}
						else
						{
							$abono_usd = $abono->monto / $factura->tipo_cambio;
							echo 'USD' . '&nbsp;' . Utiles::Formatear_Colonos_SinSigno($abono_usd, 2);
						}
						$monto_abonos_USD += $abono_usd;
						?>
                        </span>
                    </td>
                </tr>
                <?php
                $index++;
                endforeach;

                $suma_abonos_CRC += $monto_abonos_CRC;
                $suma_abonos_USD += $monto_abonos_USD;
                $saldo_CRC = $total_factura_CRC - $monto_abonos_CRC;
                $saldo_USD = $total_factura_USD - $monto_abonos_USD;
                ?>
                <tr>
                    <th align="right" colspan="6" style="font-size: 10px; font-weight: bold; text-align:center">
                        TOTAL ABONO(S): <br />
                        PENDIENTE DE PAGO:
                    </th>
                    <th align="right" style="font-size: 10px; font-weight: bold; text-align:center">
                        <?= 'CRC' . '&nbsp;' . Utiles::Formatear_Colonos_SinSigno($monto_abonos_CRC, 2) ?><br />
                        <?= 'CRC' . '&nbsp;' . Utiles::Formatear_Colonos_SinSigno($saldo_CRC, 2) ?></span>
                    </th>
                    <th align="right" style="font-size: 10px; font-weight: bold; text-align:center">
                        <?= 'USD' . '&nbsp;' . Utiles::Formatear_Colonos_SinSigno($monto_abonos_USD, 2) ?><br />
                        <?= 'USD' . '&nbsp;' . Utiles::Formatear_Colonos_SinSigno($saldo_USD, 2) ?></span>
                    </th>

                </tr>
            <?php
            endif;
            ?>
        <?php
        }
        ?>

        <tr>
            <td align="right" colspan="6" style="font-size: 10px; font-weight: bold; text-align:center">
                <strong>TOTAL FACTURA(S):</strong><br />
                <strong>TOTAL ABONO(S):</strong><br />
                <strong><span style="color:#F00">TOTAL PENDIENTE:</span></strong><br />
            </td>
            <td align="right" style="font-size: 10px; font-weight: bold; text-align:center">
                <?= 'CRC' . '&nbsp;' . Utiles::Formatear_Colonos_SinSigno($total_CRC, 2) ?><br />
                <?= 'CRC' . '&nbsp;' . Utiles::Formatear_Colonos_SinSigno($suma_abonos_CRC, 2) ?><br />
                <span style="color:#F00"><?= 'CRC' . '&nbsp;' . Utiles::Formatear_Colonos_SinSigno($total_CRC - $suma_abonos_CRC, 2) ?></span></strong>
            </td>
            <td align="right" style="font-size: 10px; font-weight: bold; text-align:center">
                <?= 'USD' . '&nbsp;' . Utiles::Formatear_Colonos_SinSigno($total_USD, 2) ?><br />
                <?= 'USD' . '&nbsp;' . Utiles::Formatear_Colonos_SinSigno($suma_abonos_USD, 2) ?><br />
                <span style="color:#F00"><?= 'USD' . '&nbsp;' . Utiles::Formatear_Colonos_SinSigno($total_USD - $suma_abonos_USD, 2) ?></span></strong>
            </td>
        </tr>
		</tbody>
    </table>
    <table width="100%">
        <tr>
            <td align="center" style="font-size: 10px; font-weight: normal; text-align:center">
                <?php // $configuracion->piepagina_estado_cuenta; ?>
            </td>
        </tr>
    </table>
</body>
</html>

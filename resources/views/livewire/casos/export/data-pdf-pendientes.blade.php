<html xmlns:o='urn:schemas-microsoft-com:office:office'
      xmlns:x='urn:schemas-microsoft-com:office:excel'
      xmlns='http://www.w3.org/TR/REC-html40'>
<head>
  <meta http-equiv='Content-Type' content='text/html;charset=utf-8' />
</head>

<body class="kv-wrap">

<table width="100%" cellpading="10">
	<tr>
		<td width="33%" valign="middle">
        	<div class="tm_logo">
            <img src="{{ $logo }}" alt="Logo">
          </div>
        </td>
        <td width="33%" align="center" valign="top">
        </td>
        <td align="right" style="padding-right:5;" width="33%">
          <span style="font-weight: normal; font-size: 12px;">Fecha:</span><span style="font-size: 12px;">
             {{ \Carbon\Carbon::parse($caso->fecha_creacion)->format('d/m/Y h:i a') }}
          </span><br /><br />
        </td>
    </tr>

    <tr>
        <td align="center" colspan="3">
           <span style="font-weight: bold; font-size: 14px;"><?= $title ?></span>
        </td>
    </tr>

</table>

<div class="boxroundedPadding1">
	<span style="font-weight: bold; font-size: 11px;">CASO: </span> <span style="font-size: 11px;"><?= $caso->numero; ?></span><br />
	<span style="font-weight: bold; font-size: 11px;">DEUDOR: </span> <span style="font-size: 11px;"><?= $caso->deudor; ?></span><br />
	<span style="font-weight: bold; font-size: 11px;">ABOGADO A CARGO: </span> <span style="font-size: 11px;">
    {{ optional($caso->abogadoCargo)->name ?? '-' }}
  </span>

</div>
<br />
<table class="kv-grid-table table table-bordered table-striped kv-table-wrap" width="100%" border="1" style="border:solid 0.3px #CCC border-collapse: collapse; border-spacing: 0; background-color:transparent" cellpadding="5px" cellspacing="0">
    <tr>
        <th><span style="font-weight: bold; font-size: 11px;">DESCRIPCIÓN</span></th>
        <th><span style="font-weight: bold; font-size: 11px;">RESPONSABLE</span></th>
        <th><span style="font-weight: bold; font-size: 11px;">FECHA</span></th>
        <th><span style="font-weight: bold; font-size: 11px;">ESTADO</span></th>
    </tr>
<?php
foreach ($pendientes as $model)
{
    ?>
    <tr>
        <td align="left">
            <span style="font-size: 10px; font-weight: bold;">
                <?= $model->name; ?>
            </span>
        </td>
        <td align="left">
            <span style="font-size: 10px; font-weight: bold;">
                <?= $model->responsable	?>
            </span>
        </td>
        <td align="center">
            <span style="font-size: 10px; font-weight: bold;">
                <?= date('d-m-Y', strtotime($model->fecha));	?>
            </span>
        </td>
        <td align="center">
            <span style="font-size: 10px; font-weight: bold;">
                <?= $model->estado	?>
            </span>
        </td>
    </tr>
<?php
}
?>
</table>


</body>
</html>

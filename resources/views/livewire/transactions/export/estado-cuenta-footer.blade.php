    <tr>
      <td align="right" colspan="6" style="font-size: 10px; font-weight: bold; text-align:center">
          <strong>TOTAL FACTURA(S):</strong><br />
          <strong>TOTAL ABONO(S):</strong><br />
          <strong><span style="color:#F00">TOTAL PENDIENTE:</span></strong><br />
      </td>
      <td align="right" style="font-size: 10px; font-weight: bold; text-align:center">
          <?= 'CRC' . '&nbsp;' . \Helper::formatDecimal($total_CRC) ?><br />
          <?= 'CRC' . '&nbsp;' . \Helper::formatDecimal($suma_payments_CRC) ?><br />
          <span style="color:#F00"><?= 'CRC' . '&nbsp;' . \Helper::formatDecimal($total_CRC - $suma_payments_CRC) ?></span></strong>
      </td>
      <td align="right" style="font-size: 10px; font-weight: bold; text-align:center">
          <?= 'USD' . '&nbsp;' . \Helper::formatDecimal($total_USD) ?><br />
          <?= 'USD' . '&nbsp;' . \Helper::formatDecimal($suma_payments_USD) ?><br />
          <span style="color:#F00"><?= 'USD' . '&nbsp;' . \Helper::formatDecimal($total_USD - $suma_payments_USD) ?></span></strong>
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
<br><br>

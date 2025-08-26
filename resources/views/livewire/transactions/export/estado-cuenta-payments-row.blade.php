<tr>
    <td align="right"><span style="font-size: 10px;">{{ $index }}</span></td>
    <td><span style="font-size: 10px;">{{ $reciboNumero }}</span></td>
    <td align="center"><span style="font-size: 10px;">{{ $fecha }}</span></td>
    <td align="center"><span style="font-size: 10px;">{{ $descripcionMedio }}</span></td>
    <td align="center"><span style="font-size: 10px;">{{ $referencia }}</span></td>
    <td align="center"><span style="font-size: 10px;">{{ $banco }}</span></td>
    <td align="center">
        <span style="font-size: 10px;">CRC&nbsp;{{ \Helper::formatDecimal($payment_crc) }}</span>
    </td>
    <td align="center">
        <span style="font-size: 10px;">USD&nbsp;{{ \Helper::formatDecimal($payment_usd) }}</span>
    </td>
</tr>

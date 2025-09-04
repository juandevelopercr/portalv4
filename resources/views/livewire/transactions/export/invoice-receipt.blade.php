<!DOCTYPE html>
<html class="no-js" lang="en">

<head>
  <!-- Meta Tags -->
  <meta charset="utf-8">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="author" content="SoftwareSolutions">
  <!-- Site Title -->
  <title>{{ $title }}</title>
  <link rel="stylesheet" href="{{ public_path('css/blue-invoice.css') }}" type="text/css">
</head>

<body>
  @if(!empty($watermark)) <!-- Variable que debes pasar desde Laravel -->
  <div class="watermark">{{ $watermark }}</div>
  @endif

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
            <div class="tm_invoice_right tm_text_right tm_mobile_hide">
              <div class="tm_f24 tm_text_uppercase tm_white_color mt-5">
                {{ $title }}<br>
                <span class="tm_f12 tm_invoice_number">
                  <b>No: {{ $consecutivo }}</b>
                </span>
              </div>
            </div>
            <div class="tm_shape_bg tm_accent_bg tm_mobile_hide"></div>
          </div>

          @php
          /*
          <div style="text-align:right; float:right; width:100%">
            @if($showReferencia)
              <span class="tm_f12 tm_danger_color">
                <b>{{ $referencia['title'] }} #: {{ $referencia['consecutivo'] }}</b>
              </span>
            @endif

            @if($showNotaAnula && isset($nota['title']) && isset($nota['consecutivo']))
              <span class="tm_f12 tm_danger_color">
                <b>{{ $nota['title'] }} #: {{ $nota['consecutivo'] }}</b>
              </span>
            @endif
          </div>
          */
          @endphp

          <div class="tm_invoice_info tm_mb10">
            <div class="tm_card_note tm_mobile_hide"><b class="tm_primary_color"></b></div>
            <div class="tm_invoice_info_list tm_white_color">
              <p class="tm_invoice_date tm_m0">
                <b>
                  Fecha:
                  {{ \Carbon\Carbon::parse($transaction->transaction_date)
                  ->locale('es')
                  ->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
                </b>
              </p>
            </div>
            <div class="tm_invoice_seperator tm_accent_bg"></div>
          </div>

          <div class="tm_mb10" style="float:right">
            @if($showReferencia)
              <span class="tm_f12 tm_danger_color">
                <b>{{ $referencia['title'] }} #: {{ $referencia['consecutivo'] }}</b>
              </span>
            @endif
          </div>
          <div style="clear:both"></div>

          <div class="tm_invoice_head tm_mb10">
            <div class="tm_invoice_left">
              <p class="tm_mb2"><b class="tm_primary_color">{{ $title }} Para:</b></p>
              <p>
                <b class="tm_primary_color">Nombre:</b> {{ $transaction->customer_name }} <br>
                <b class="tm_primary_color">Identificación:</b> {{ $identification }} <br>
                @if (!empty($address))
                  <b class="tm_primary_color">Dirección:</b> {!! nl2br($address) !!}<br>
                @endif
                @if (!empty($phone))
                  <b class="tm_primary_color">Teléfono:</b> {!! $phone !!}<br>
                @endif
                <b class="tm_primary_color">Email:</b> {{ $transaction->customer_email }} <br>
                @if (!empty($email_cc))
                <b class="tm_primary_color">Email CC:</b> {!! nl2br($email_cc) !!}<br>
                @endif
              </p>
            </div>

            <div class="tm_invoice_right tm_text_right">
              <p class="tm_mb2"><b class="tm_primary_color">Pagar a:</b></p>
              <p>
                <b class="tm_primary_color">{{ strtoupper($transaction->location->name) }}</b><br>
                <b class="tm_primary_color">{{ $transaction->location->identification }}</b>
                <br>Portal Genral de Facturación Electrónica<br>
                <a href="/cdn-cgi/l/email-protection" class="__cf_email__"
                  data-cfemail="3054555d5f70575d51595c1e535f5d">{{ $transaction->location->email }}</a>
              </p>
              <p class="tm_mb2"><b class="tm_primary_color">Detalle del pago:</b></p>
              <p class="tm_mb2"><b class="tm_primary_color">Condición de venta:</b> {{ $sellCondition }}</p>
              <p class="tm_mb2"><b class="tm_primary_color">Moneda:</b> {{ $currency }}</p>
              @if ($currency != 'CRC')
                <p class="tm_mb2"><b class="tm_primary_color">Tipo de cambio:</b> {{ $changeType }}</p>
              @endif
              <p class="tm_mb2"><b class="tm_primary_color">Método de pago:</b> {{ $paymentMethod }}</p>
            </div>
          </div>

          <div class="tm_table tm_style1">
            <div class="tm_table tm_style1">
              <div class="">
                <div class="tm_table_responsive">
                  <table>
                    <thead>
                      <tr class="tm_accent_bg">
                        <th class="tm_width_4 tm_semi_bold tm_white_color">Asunto / Descripción</th>
                        <th class="tm_width_2 tm_semi_bold tm_white_color">Precio</th>
                        <th class="tm_width_1 tm_semi_bold tm_white_color">Cantidad</th>
                        <th class="tm_width_2 tm_semi_bold tm_white_color tm_text_right">Total</th>
                      </tr>
                    </thead>
                    <tbody>
                      @php
                      $montoTotal = 0;
                      $totalTimbres = 0;
                      $totalHonorarios = 0;
                      $cargoAdicional = 0;

                      $str = '';
                      @endphp

                      @foreach ($transaction_lines as $index => $line)
                      <tr>
                        <td class="tm_width_4">{!! html_entity_decode($line->detail) !!}</td>
                        <td class="tm_width_2">
                          @php
                          $monto = $line->price * $line->quantity;
                          @endphp
                          {{ $transaction->currency->symbol }} {{ Helper::formatDecimal($monto) }}
                        </td>
                        <td class="tm_width_1 tm_text_center">{{ (int)$line->quantity }}</td>
                        <td class="tm_width_2 tm_text_right">
                          {{ $transaction->currency->symbol }}
                          {{ Helper::formatDecimal($monto) }}
                        </td>
                      </tr>
                      @endforeach

                      <!--  Otros cargos -->
                      @if ($transaction_other_charges->isNotEmpty())
                        <tr>
                          <td class="tm_width_4"><strong>Otros Cargos</strong></td>
                          <td class="tm_width_2"></td>
                          <td class="tm_width_1 tm_text_center"></td>
                          <td class="tm_width_2 tm_text_right">
                          </td>
                        </tr>

                        @foreach ($transaction_other_charges as $charge)
                        <tr>
                          <td class="tm_width_4">{!! $charge->detail !!}</td>
                          <td class="tm_width_2"></td>
                          <td class="tm_width_1 tm_text_center"></td>
                          <td class="tm_width_2 tm_text_right">
                            {{ $transaction->currency->symbol.' '. Helper::formatDecimal($charge->amount *
                            $charge->quantity) }}
                          </td>
                        </tr>
                        @endforeach
                      @endif
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="tm_invoice_footer tm_border_top tm_m0_md">
                <div class="tm_left_footer">
                  <br>
                  @if (!empty($transaction->notes))
                  <p class="tm_f12 tm_m0">
                    <b class="tm_primary_color">Observaciones:</b> {{ $transaction->notes }}
                  </p>
                  @endif

                  <p class="tm_f12 tm_m0">
                    <b class="tm_primary_color">Fecha de Actualización:</b>
                    {{ \Carbon\Carbon::parse($transaction->updated_at)->translatedFormat('d F Y') }}
                  </p>
                </div>
                <div class="tm_right_footer">
                  <table class="tm_mb15">
                    <tbody>
                      <tr class="tm_gray_bg">
                        <td class="tm_width_3 tm_primary_color">IVA</td>
                        <td class="tm_width_3 tm_primary_color tm_text_right">
                          {{ $transaction->currency->symbol }}
                          {{ Helper::formatDecimal($transaction->totalTax) }}
                        </td>
                      </tr>
                      <tr class="tm_accent_bg">
                        <td class="tm_width_3 tm_border_top_0 tm_bold tm_f19 tm_white_color">Total </td>
                        <td class="tm_width_3 tm_border_top_0 tm_bold tm_f19 tm_white_color tm_text_right">
                          {{ $transaction->currency->symbol }}
                          {{ Helper::formatDecimal($transaction->totalComprobante) }}
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            @if($showReferencia)
              <div class="tm_note tm_font_style_normal">
                <hr class="tm_mb15">
                <p class="tm_mb2 tm_text_center"><b class="tm_primary_color">Información de referencia:</b></p>

                <p class="tm_f12 tm_m0 tm_text_left"><b class="tm_primary_color">Tipo de documento:</b>{{ $referencia['tipo'] }}</p>
                <p class="tm_f12 tm_m0 tm_text_left"><b class="tm_primary_color">Número:</b>{{ $referencia['numero'] }}</p>
                <p class="tm_f12 tm_m0 tm_text_left"><b class="tm_primary_color">Consecutivo:</b>{{ $referencia['consecutivo'] }}</p>
                <p class="tm_f12 tm_m0 tm_text_left"><b class="tm_primary_color">Fecha emisión:</b>
                {{ \Carbon\Carbon::parse($referencia['fechaEmision'])
                  ->locale('es')
                  ->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
                </p>
                <p class="tm_f12 tm_m0 tm_text_left"><b class="tm_primary_color">Razón:</b>{{ $referencia['razon'] }}</p>
              </div><!-- .tm_note -->
            @endif
            <div class="tm_note tm_font_style_normal">
                <table width="100%">
                  <tr>
                    <td align="center">
                      <img src="{{ $qrCode }}" width="80" height="80">
                      <p class="tm_f12 tm_text_center">Clave numérica: {{ $transaction->key }}</p>
                      <p class="tm_f12 tm_text_center">
                          Emitida conforme lo establecido en la resolución de Factura Electrónica, Nº DGT-R-033-2019<br> del veinte de junio de dos mil diecinueve de la Dirección General de Tributación.
                      </p>
							        <p class="tm_f12">Factura Generada por: www.softwaresolutions.co.cr Versión 4.4</p>
                    </td>
                  </tr>
                </table>
            </div><!-- .tm_note -->
          </div>
        </div>
      </div>
    </div>
</body>
</html>

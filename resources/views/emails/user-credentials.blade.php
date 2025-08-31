<!DOCTYPE html>

<html lang="en" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:v="urn:schemas-microsoft-com:vml">

<head>
  <title></title>
  <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <!--[if mso]>
<xml><w:WordDocument xmlns:w="urn:schemas-microsoft-com:office:word"><w:DontUseAdvancedTypographyReadingMail/></w:WordDocument>
<o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch><o:AllowPNG/></o:OfficeDocumentSettings></xml>
<![endif]-->
  <!--[if !mso]><!-->
  <link href="https://fonts.googleapis.com/css?family=Roboto+Slab" rel="stylesheet" type="text/css" />
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;600;700;800;900" rel="stylesheet"
    type="text/css" />
  <!--<![endif]-->
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      padding: 0;
    }

    a[x-apple-data-detectors] {
      color: inherit !important;
      text-decoration: inherit !important;
    }

    #MessageViewBody a {
      color: inherit;
      text-decoration: none;
    }

    p {
      line-height: inherit
    }

    .desktop_hide,
    .desktop_hide table {
      mso-hide: all;
      display: none;
      max-height: 0px;
      overflow: hidden;
    }

    .image_block img+div {
      display: none;
    }

    sup,
    sub {
      font-size: 75%;
      line-height: 0;
    }

    @media (max-width:670px) {
      .mobile_hide {
        display: none;
      }

      .row-content {
        width: 100% !important;
      }

      .stack .column {
        width: 100%;
        display: block;
      }

      .mobile_hide {
        min-height: 0;
        max-height: 0;
        max-width: 0;
        overflow: hidden;
        font-size: 0px;
      }

      .desktop_hide,
      .desktop_hide table {
        display: table !important;
        max-height: none !important;
      }
    }
  </style>
  <!--[if mso ]><style>sup, sub { font-size: 100% !important; } sup { mso-text-raise:10% } sub { mso-text-raise:-10% }</style> <![endif]-->
</head>

<body class="body"
  style="background-color: #85a4cd; margin: 0; padding: 0; -webkit-text-size-adjust: none; text-size-adjust: none;">
  <table border="0" cellpadding="0" cellspacing="0" class="nl-container" role="presentation"
    style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #85a4cd;" width="100%">
    <tbody>
      <tr>
        <td>
          <table align="center" border="0" cellpadding="0" cellspacing="0" class="row row-1" role="presentation"
            style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #f3f6fe;" width="100%">
            <tbody>
              <tr>
                <td>
                  <table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content stack"
                    role="presentation"
                    style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; color: #000000; width: 650px; margin: 0 auto;"
                    width="650">
                    <tbody>
                      <tr>
                        <td class="column column-1"
                          style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; padding-bottom: 15px; padding-top: 15px; vertical-align: top;"
                          width="100%">
                          <table border="0" cellpadding="0" cellspacing="0" class="image_block block-1"
                            role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;" width="100%">
                            <tr>
                              <td class="pad" style="width:100%;">
                                <div align="center" class="alignment">
                                  <div style="max-width: 250px;">
                                    <img src="{{ $message->embed($data['logo_path']) }}" height="auto" alt="Logo"
                                      style="display: block; height: auto; border: 0; width: 100%;" width="250">
                                  </div>
                                </div>
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </td>
              </tr>
            </tbody>
          </table>
          <table align="center" border="0" cellpadding="0" cellspacing="0" class="row row-2" role="presentation"
            style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;" width="100%">
            <tbody>
              <tr>
                <td>
                  <table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content stack"
                    role="presentation"
                    style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; color: #000000; width: 650px; margin: 0 auto;"
                    width="650">
                    <tbody>
                      <tr>
                        <td class="column column-1"
                          style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; padding-bottom: 5px; padding-top: 5px; vertical-align: top;"
                          width="100%">
                          <div class="spacer_block block-1" style="height:60px;line-height:60px;font-size:1px;"> </div>
                          <table border="0" cellpadding="0" cellspacing="0" class="heading_block block-2"
                            role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;" width="100%">
                            <tr>
                              <td class="pad" style="padding-bottom:10px;text-align:center;width:100%;">
                                <h1
                                  style="margin: 0; color: #ffffff; direction: ltr; font-family: 'Roboto Slab', Arial, 'Helvetica Neue', Helvetica, sans-serif; font-size: 30px; font-weight: normal; letter-spacing: 2px; line-height: 1.2; text-align: center; margin-top: 0; margin-bottom: 0; mso-line-height-alt: 36px;">
                                  <strong>{{ __('Welcome greeting', ['name' => $data['name']]) }}</strong>
                                </h1>
                              </td>
                            </tr>
                          </table>
                          <div class="spacer_block block-3" style="height:20px;line-height:20px;font-size:1px;"> </div>
                          <table border="0" cellpadding="0" cellspacing="0" class="paragraph_block block-4"
                            role="presentation"
                            style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
                            <tr>
                              <td class="pad"
                                style="padding-bottom:5px;padding-left:10px;padding-right:10px;padding-top:5px;">
                                <div
                                  style="color:#3f4d75;font-family:Roboto Slab, Arial, Helvetica Neue, Helvetica, sans-serif;font-size:20px;line-height:1.2;text-align:center;mso-line-height-alt:24px;">
                                  <p style="margin: 0;">{{ __('Credential message') }}
                                  </p>
                                </div>
                              </td>
                            </tr>
                          </table>
                          <table border="0" cellpadding="0" cellspacing="0" class="paragraph_block block-5"
                            role="presentation"
                            style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
                            <tr>
                              <td class="pad"
                                style="padding-bottom:5px;padding-left:5px;padding-right:10px;padding-top:5px;">
                                <div
                                  style="color:#3f4d75;font-family:Roboto Slab, Arial, Helvetica Neue, Helvetica, sans-serif;font-size:22px;line-height:1.2;text-align:left;mso-line-height-alt:26px;">
                                  <p style="margin: 0; word-break: break-word;"><span style="word-break: break-word;"> 
                                      <strong>{{ __('Username') }}:</strong> {{ $data['username'] }}</span>
                                  </p>
                                  <p style="margin: 0; word-break: break-word;"><span style="word-break: break-word;"> 
                                      <strong>{{ __('Password') }}:</strong> {{ $data['clave'] }}</span>
                                  </p>
                                </div>
                              </td>
                            </tr>
                          </table>
                          <div class="spacer_block block-6" style="height:20px;line-height:20px;font-size:1px;"> </div>
                          <table border="0" cellpadding="10" cellspacing="0" class="paragraph_block block-7"
                            role="presentation"
                            style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
                            <tr>
                              <td class="pad">
                                <div
                                  style="color:#cb0a17;direction:ltr;font-family:Roboto Slab, Arial, Helvetica Neue, Helvetica, sans-serif;font-size:16px;font-weight:400;letter-spacing:0px;line-height:1.2;text-align:left;mso-line-height-alt:19px;">
                                  <p style="margin: 0; padding-left:5px;">
                                    {{ __('Do not share this information with anyone') }}</p>
                                </div>
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </td>
              </tr>
            </tbody>
          </table>
          <table align="center" border="0" cellpadding="0" cellspacing="0" class="row row-3" role="presentation"
            style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;" width="100%">
            <tbody>
              <tr>
                <td>
                  <table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content stack"
                    role="presentation"
                    style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-radius: 0; color: #000000; width: 650px; margin: 0 auto;"
                    width="650">
                    <tbody>
                      <tr>
                        <td class="column column-1"
                          style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; padding-bottom: 5px; padding-top: 5px; vertical-align: top;"
                          width="100%">
                          <table border="0" cellpadding="10" cellspacing="0" class="button_block block-1"
                            role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;" width="100%">
                            <tr>
                              <td class="pad">
                                <div align="center" class="alignment"><a href="{{ config('app.url') }}"
                                    style="color:#3f4d75;text-decoration:none;" target="_blank">
                                    <!--[if mso]>
<v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word"  href="{{ config('app.url') }}"  style="height:56px;width:154px;v-text-anchor:middle;" arcsize="17%" fillcolor="#ffffff">
<v:stroke dashstyle="Solid" weight="2px" color="#3F4D75"/>
<w:anchorlock/>
<v:textbox inset="0px,0px,0px,0px">
<center dir="false" style="color:#3f4d75;font-family:sans-serif;font-size:18px">
<![endif]--><span class="button" style="background-color: #ffffff; border-bottom: 2px solid #3F4D75; border-left: 2px solid #3F4D75; border-radius: 10px; border-right: 2px solid #3F4D75; border-top: 2px solid #3F4D75; color: #3f4d75; display: inline-block; font-family: Roboto Slab, Arial, Helvetica Neue, Helvetica, sans-serif; font-size: 18px; font-weight: undefined; mso-border-alt: none; padding-bottom: 10px; padding-top: 10px; padding-left: 25px; padding-right: 25px; text-align: center; width: auto; word-break: keep-all; letter-spacing: normal;"><span
                                        style="word-break: break-word;"><span data-mce-style=""
                                          style="word-break: break-word; line-height: 36px;">{{ __('Go to system')
                                          }}</span></span></span>
                                    <!--[if mso]></center></v:textbox></v:roundrect><![endif]-->
                                  </a></div>
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </td>
              </tr>
            </tbody>
          </table>
          <table align="center" border="0" cellpadding="0" cellspacing="0" class="row row-4" role="presentation"
            style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #c4d6ec;" width="100%">
            <tbody>
              <tr>
                <td>
                  <table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content stack"
                    role="presentation"
                    style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; color: #000000; width: 650px; margin: 0 auto;"
                    width="650">
                    <tbody>
                      <tr>
                        <td class="column column-1"
                          style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; padding-bottom: 20px; padding-top: 20px; vertical-align: top;"
                          width="100%">
                          <table border="0" cellpadding="10" cellspacing="0" class="paragraph_block block-1"
                            role="presentation"
                            style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
                            <tr>
                              <td class="pad">
                                <div
                                  style="color:#3f4d75;font-family:Roboto Slab, Arial, Helvetica Neue, Helvetica, sans-serif;font-size:12px;line-height:1.2;text-align:center;mso-line-height-alt:14px;">
                                  <p>{{ __('This message was sent by the Portal General administration team') }}</p>
                                   
                                </div>
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </td>
              </tr>
            </tbody>
          </table>
        </td>
      </tr>
    </tbody>
  </table><!-- End -->
</body>

</html>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Factura electrónica</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      margin: 0;
      padding: 20px;
    }

    .container {
      background: white;
      padding: 20px;
      border-radius: 5px;
      max-width: 800px;
      margin: auto;
    }

    .header {
      text-align: center;
    }

    .logo {
      max-width: 150px;
    }

    .content {
      margin-top: 20px;
    }

    .footer {
      margin-top: 20px;
      text-align: center;
      font-size: 12px;
      color: gray;
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="header">
      <img class="logo" src="{{ $logo }}" alt="Logo" style="height: 150px; width: auto;">
    </div>
    <div class="content">
      {{ $data['message'] }}
    </div>
    <div class="footer">
      <p>&copy; {{ date('Y') }} Portal General de Facturación Electrónica de Costa Rica</p>
    </div>
  </div>
</body>

</html>

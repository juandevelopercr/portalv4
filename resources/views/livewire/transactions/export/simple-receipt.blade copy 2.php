<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Invoice</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 20px;
      color: #333;
    }

    .invoice-container {
      width: 100%;
      max-width: 800px;
      margin: auto;
      background: #fff;
      padding: 20px;
      border: 1px solid #ddd;
    }

    .header {
      width: 100%;
      margin-bottom: 20px;
    }

    .header img {
      max-width: 150px;
    }

    .company-info {
      text-align: right;
    }

    .client-info {
      margin-bottom: 20px;
    }

    .table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }

    .table th,
    .table td {
      border: 1px solid #ddd;
      padding: 10px;
      text-align: left;
    }

    .table th {
      background: #f5f5f5;
    }

    .totals {
      text-align: right;
      margin-top: 20px;
    }

    .footer {
      text-align: center;
      margin-top: 20px;
      font-size: 12px;
      color: #777;
    }
  </style>
</head>

<body>
  <div class="invoice-container">
    <table class="header">
      <tr>
        <td><img src="{{ public_path('assets/img/invoice/logo.png') }}" alt="Logo"></td>
        <td class="company-info">
          <h3>Company</h3>
          <p>Address</p>
          <p>Email: </p>
          <p>Phone: </p>
        </td>
      </tr>
    </table>

    <div class="client-info">
      <p><b>Invoice To:</b> Cliente</p>
      <p>Address</p>
      <p>Email: email</p>
    </div>

    <table class="table">
      <thead>
        <tr>
          <th>Description</th>
          <th>Quantity</th>
          <th>Unit Price</th>
          <th>Total</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>description</td>
          <td>quantity</td>
          <td>unit_price</td>
          <td>quantity</td>
        </tr>
      </tbody>
    </table>

    <div class="totals">
      <p><strong>Subtotal:</strong> 20</p>
      <p><strong>Tax 13%:</strong> 12</p>
      <p><strong>Total:</strong> 32</p>
    </div>

    <div class="footer">
      <p>Thank you for your business!</p>
      <p>Company | website</p>
    </div>
  </div>
</body>

</html>

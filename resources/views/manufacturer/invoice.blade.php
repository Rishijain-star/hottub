<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice – Manufacturer Panel</title>
    <style>
        :root{--teal:#0ea5a3;--gray-700:#374151;--gray-900:#111827;}
        body{font-family: 'DM Sans', system-ui, -apple-system, Segoe UI, Roboto, sans-serif;background:#f9fafb;color:var(--gray-700);margin:0}
        .wrap{max-width:860px;margin:2rem auto;padding:0 1rem}
        .brand{display:flex;align-items:center;gap:.85rem}
        .logo{width:42px;height:42px;border-radius:12px;background:var(--teal);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800}
        .subtitle{font-weight:800;letter-spacing:.12em;color:var(--teal);font-size:.8rem}
        .section{margin-top:1rem}
        .card{background:#fff;border-radius:14px;box-shadow:0 10px 30px rgba(0,0,0,.06);border:1px solid #e5e7eb;padding:1rem}
        .card-head{font-weight:800;color:var(--gray-900);margin-bottom:.5rem}
        .meta{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;font-size:.9rem}
        .table{width:100%;border-collapse:collapse;margin-top:.5rem}
        .table th,.table td{border-bottom:1px solid #e5e7eb;padding:.5rem;text-align:left}
        .totals{display:flex;justify-content:flex-end;margin-top:.5rem}
        .total-line{font-weight:800;color:var(--gray-900)}
        .footer{margin-top:1.5rem;text-align:center;font-size:.9rem;color:#6b7280}
        .dl{display:flex;gap:.5rem;justify-content:flex-end;margin-top:.75rem}
        .btn{padding:.5rem .75rem;border-radius:10px;font-weight:700;border:2px solid transparent;background:var(--teal);color:#fff;cursor:pointer}
        .btn-outline{background:#fff;border:2px solid var(--teal);color:var(--teal)}
    </style>
    </head>
<body>
    <div class="wrap">
        <div class="brand">
            <div class="logo">HT</div>
            <div>
                <h1>Hot Tub Buyer</h1>
                <div class="subtitle">INVOICE</div>
            </div>
        </div>

        <div class="dl">
            <a class="btn" href="{{ route('manufacturer.invoice.download', $invoice) }}">Download HTML</a>
            <button class="btn btn-outline" onclick="window.print()">Print / Save as PDF</button>
        </div>

        <div class="card section">
            <div class="card-head">Invoice Details</div>
            <div class="meta">
                <div><b>Invoice Number:</b> {{ $invoice }}<br><b>Invoice Date:</b> {{ $date }} {{ isset($time)?$time:'' }}</div>
                <div><b>Customer:</b> {{ $customer }}<br><span class="status">Status: <span class="badge badge-danger">{{ strtoupper($status) }}</span></span></div>
            </div>
            <table class="table">
                <thead><tr><th>Description</th><th>Quantity</th><th>Unit Price</th><th>Total</th></tr></thead>
                <tbody>
                @foreach($items as $it)
                    <tr>
                        <td>
                            <div class="desc">
                                <div style="font-weight:700;color:var(--gray-900)">{{ $it['title'] }}</div>
                                <div style="color:#6b7280;font-size:.9rem">{{ $it['desc'] }}</div>
                            </div>
                        </td>
                        <td>{{ $it['qty'] }}</td>
                        <td>£{{ number_format($it['unit'], 2) }}</td>
                        <td>£{{ number_format($it['total'], 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="totals"><div class="total-line">Total Amount: £{{ number_format($total, 2) }} {{ $currency }}</div></div>
        </div>

        <div class="footer">
            <div>Hot Tub Buyer Ltd</div>
            <div>Thank you for your business. For support, contact: support@hottubbuyer.com</div>
        </div>
    </div>
</body>
</html>


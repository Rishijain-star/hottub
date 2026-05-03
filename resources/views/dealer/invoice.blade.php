<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,600;9..40,700;9..40,800&display=swap" rel="stylesheet">
    <style>
        :root{--teal:#00a896;--teal-dk:#007f73;--gray-100:#f1f5f4;--gray-200:#e4eceb;--gray-300:#c8d8d6;--gray-400:#96b0ae;--gray-500:#628280;--gray-700:#2d4443;--gray-900:#0f2423}
        *{box-sizing:border-box}html,body{margin:0;padding:0}
        body{font-family:'DM Sans',system-ui,sans-serif;color:var(--gray-700);line-height:1.6;background:#fff}
        .wrap{max-width:900px;margin:40px auto;padding:0 24px}
        .brand{display:flex;align-items:center;gap:10px;margin-bottom:4px}
        .brand .logo{width:42px;height:42px;border-radius:10px;background:linear-gradient(145deg,var(--teal) 0%, #00c4b0 100%);display:flex;align-items:center;justify-content:center;color:#fff}
        h1{margin:0;font-size:28px;color:var(--gray-900)}
        .subtitle{color:var(--gray-500);margin-top:2px;font-weight:600}
        .section{margin-top:22px}
        .card{border:1px solid var(--gray-200);border-radius:14px;overflow:hidden;background:#fff}
        .card-head{padding:14px 18px;border-bottom:1px solid var(--gray-200);font-weight:800;color:var(--gray-900)}
        .meta{display:grid;grid-template-columns: 1fr 1fr;gap:10px;padding:16px 18px}
        .meta b{color:var(--gray-900)}
        .status{display:inline-flex;align-items:center;gap:8px;margin-top:6px;font-weight:800}
        .badge{display:inline-block;padding:6px 10px;border-radius:999px;font-size:12px;font-weight:800}
        .badge-danger{background:#fee2e2;color:#b91c1c}
        .table{width:100%;border-collapse:collapse}
        .table th{background:#f8fafb;text-align:left;font-size:12px;letter-spacing:.03em;color:#6b7280;padding:10px 12px;border-bottom:1px solid var(--gray-200)}
        .table td{padding:12px;border-bottom:1px solid var(--gray-200);vertical-align:top}
        .desc{display:flex;flex-direction:column;gap:4px}
        .desc small{color:var(--gray-500)}
        .totals{display:flex;justify-content:flex-end;padding:18px}
        .total-line{font-weight:800;color:var(--gray-900);font-size:18px}
        .footer{margin-top:28px;color:var(--gray-500);font-size:14px}
        @media print {.dl{display:none} body{background:#fff} .wrap{margin:0 auto}}
        .dl{margin:16px 0;display:flex;gap:10px}
        .btn{display:inline-flex;align-items:center;gap:8px;padding:9px 14px;border-radius:10px;background:linear-gradient(135deg,var(--teal) 0%, #00c4b0 100%);color:#fff;text-decoration:none;font-weight:800}
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
            <a class="btn" href="{{ route('dealer.invoice.download', $invoice) }}">Download HTML</a>
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
                                <small>{{ $it['desc'] }}</small>
                            </div>
                        </td>
                        <td>{{ $it['qty'] }}</td>
                        <td>£{{ number_format($it['unit'], 2) }}</td>
                        <td>£{{ number_format($it['total'], 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            @php
                $net = $netAmount ?? (isset($total) ? round($total / 1.2, 2) : 0);
                $vat = $vatAmount ?? (isset($total) ? round($total - $net, 2) : 0);
            @endphp
            <div style="padding:12px 18px;border-top:1px solid var(--gray-200);font-size:13px;color:var(--gray-700)">
                <div style="display:flex;justify-content:space-between;margin-bottom:6px"><span>Net (excl. VAT)</span><span>£{{ number_format($net, 2) }}</span></div>
                <div style="display:flex;justify-content:space-between;margin-bottom:6px"><span>VAT ({{ $vatRatePercent ?? 20 }}%)</span><span>£{{ number_format($vat, 2) }}</span></div>
            </div>
            <div class="totals"><div class="total-line">Total (incl. VAT): £{{ number_format($total, 2) }} {{ $currency }}</div></div>
        </div>

        <div class="card section">
            <div class="card-head">Plan & Inclusions</div>
            <div class="meta">
                <div><b>Plan Name:</b> {{ $items[0]['title'] ?? 'Credit Plan' }}</div>
                <div><b>Credits Purchased:</b> {{ $items[0]['qty'] ?? 0 }}</div>
            </div>
            <div style="padding:14px 18px;color:var(--gray-700);">
                <div style="font-weight:800;color:var(--gray-900);margin-bottom:6px;">What is included in the plan</div>
                <div style="white-space:pre-wrap;">{{ $items[0]['desc'] ?? '' }}</div>
            </div>
        </div>

        <div class="card section">
            <div class="card-head">Payment Details</div>
            <div style="padding:14px 18px;display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <div><b>Payment ID:</b> {{ $paymentId ?? 'N/A' }}</div>
                <div><b>Stripe Session ID:</b> {{ $stripeSessionId ?? 'N/A' }}</div>
                <div><b>Payment Status:</b> {{ $paymentDetails['payment_status'] ?? $status }}</div>
                <div><b>Payment Method:</b> {{ $paymentMethodText ?? 'N/A' }}</div>
                <div style="grid-column:1/-1;"><b>Customer Email:</b> {{ $paymentDetails['customer_email'] ?? 'N/A' }}</div>
                <div style="grid-column:1/-1;"><b>Amount Charged:</b> £{{ number_format((float)($paymentDetails['amount_total'] ?? $total), 2) }} {{ $currency }}</div>
            </div>
        </div>

        @php
            $biz = $siteBusinessDetails ?? ['company_name'=>'Hot Tub Buyer Ltd','company_email'=>'support@hottubbuyer.com','company_address'=>null,'vat_number'=>null,'company_number'=>null,'fca_number'=>null];
        @endphp
        <div class="card section">
            <div class="card-head">Issuer Details</div>
            <div style="padding:14px 18px;display:grid;grid-template-columns:1fr 1fr;gap:10px;font-size:.95rem;">
                <div><b>Company:</b> {{ $biz['company_name'] ?? 'Hot Tub Buyer Ltd' }}</div>
                @if(!empty($biz['company_email']))
                    <div><b>Email:</b> {{ $biz['company_email'] }}</div>
                @endif
                @if(!empty($biz['company_address']))
                    <div style="grid-column:1/-1;"><b>Registered Address:</b> {{ $biz['company_address'] }}</div>
                @endif
                @if(!empty($biz['vat_number']))
                    <div><b>VAT Number:</b> {{ $biz['vat_number'] }}</div>
                @endif
                @if(!empty($biz['company_number']))
                    <div><b>Company Number:</b> {{ $biz['company_number'] }}</div>
                @endif
                @if(!empty($biz['fca_number']))
                    <div><b>FCA Number:</b> {{ $biz['fca_number'] }}</div>
                @endif
            </div>
        </div>

        <div class="footer">
            <div>{{ $biz['company_name'] ?? 'Hot Tub Buyer Ltd' }}</div>
            <div>Thank you for your business. For support, contact: {{ $biz['company_email'] ?? 'support@hottubbuyer.com' }}</div>
        </div>
    </div>
</body>
</html>

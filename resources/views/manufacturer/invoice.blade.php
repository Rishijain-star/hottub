<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('panel.invoice.title') }}</title>
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
                <div class="subtitle">{{ __('panel.invoice.invoice') }}</div>
            </div>
        </div>

        <div class="dl">
            <a class="btn" href="{{ route('manufacturer.invoice.download', $invoice) }}">{{ __('panel.invoice.download_html') }}</a>
            <button class="btn btn-outline" onclick="window.print()">{{ __('panel.invoice.print_save_pdf') }}</button>
        </div>

        <div class="card section">
            <div class="card-head">{{ __('panel.invoice.invoice_details') }}</div>
            <div class="meta">
                <div><b>{{ __('panel.invoice.invoice_number') }}</b> {{ $invoice }}<br><b>{{ __('panel.invoice.invoice_date') }}</b> {{ $date }} {{ isset($time)?$time:'' }}</div>
                <div><b>{{ __('panel.invoice.customer') }}</b> {{ $customer }}<br><span class="status">{{ __('panel.invoice.status') }} <span class="badge badge-danger">{{ strtoupper($status) }}</span></span></div>
            </div>
            <table class="table">
                <thead><tr><th>{{ __('panel.invoice.description') }}</th><th>{{ __('panel.invoice.quantity') }}</th><th>{{ __('panel.invoice.unit_price') }}</th><th>{{ __('panel.invoice.total') }}</th></tr></thead>
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
            @php
                $net = $netAmount ?? (isset($total) ? round($total / 1.2, 2) : 0);
                $vat = $vatAmount ?? (isset($total) ? round($total - $net, 2) : 0);
            @endphp
            <div style="padding:12px 18px;border-top:1px solid var(--gray-200);font-size:13px;color:var(--gray-700)">
                <div style="display:flex;justify-content:space-between;margin-bottom:6px"><span>{{ __('panel.invoice.net_ex_vat') }}</span><span>£{{ number_format($net, 2) }}</span></div>
                <div style="display:flex;justify-content:space-between;margin-bottom:6px"><span>{{ __('panel.invoice.vat', ['rate' => $vatRatePercent ?? 20]) }}</span><span>£{{ number_format($vat, 2) }}</span></div>
            </div>
            <div class="totals"><div class="total-line">{{ __('panel.invoice.total_inc_vat') }} £{{ number_format($total, 2) }} {{ $currency }}</div></div>
        </div>

        <div class="card section">
            <div class="card-head">{{ __('panel.invoice.plan_inclusions') }}</div>
            <div class="meta">
                <div><b>{{ __('panel.invoice.plan_name') }}</b> {{ $items[0]['title'] ?? __('panel.invoice.credit_plan') }}</div>
                <div><b>{{ __('panel.invoice.credits_purchased') }}</b> {{ $items[0]['qty'] ?? 0 }}</div>
            </div>
            <div style="margin-top:.75rem;color:var(--gray-700);">
                <b>{{ __('panel.invoice.included_title') }}</b>
                <div style="white-space:pre-wrap;margin-top:6px;color:#6b7280;">{{ $items[0]['desc'] ?? '' }}</div>
            </div>
        </div>

        <div class="card section">
            <div class="card-head">{{ __('panel.invoice.payment_details') }}</div>
            <div style="margin-top:.5rem;display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <div><b>{{ __('panel.invoice.payment_id') }}</b> {{ $paymentId ?? 'N/A' }}</div>
                <div><b>{{ __('panel.invoice.stripe_session_id') }}</b> {{ $stripeSessionId ?? 'N/A' }}</div>
                <div><b>{{ __('panel.invoice.payment_status') }}</b> {{ $paymentDetails['payment_status'] ?? $status }}</div>
                <div><b>{{ __('panel.invoice.payment_method') }}</b> {{ $paymentMethodText ?? 'N/A' }}</div>
                <div style="grid-column:1/-1;"><b>{{ __('panel.invoice.customer_email') }}</b> {{ $paymentDetails['customer_email'] ?? 'N/A' }}</div>
                <div style="grid-column:1/-1;"><b>{{ __('panel.invoice.amount_charged') }}</b> £{{ number_format((float)($paymentDetails['amount_total'] ?? $total), 2) }} {{ $currency }}</div>
            </div>
        </div>

        @php
            $biz = $siteBusinessDetails ?? ['company_name'=>'Hot Tub Buyer Ltd','company_email'=>'support@hottubbuyer.com','company_address'=>null,'vat_number'=>null,'company_number'=>null,'fca_number'=>null];
        @endphp
        <div class="card section">
            <div class="card-head">{{ __('panel.invoice.issuer_details') }}</div>
            <div style="margin-top:.5rem;display:grid;grid-template-columns:1fr 1fr;gap:10px;font-size:.95rem;">
                <div><b>{{ __('panel.invoice.company') }}</b> {{ $biz['company_name'] ?? 'Hot Tub Buyer Ltd' }}</div>
                @if(!empty($biz['company_email']))
                    <div><b>{{ __('panel.invoice.email') }}</b> {{ $biz['company_email'] }}</div>
                @endif
                @if(!empty($biz['company_address']))
                    <div style="grid-column:1/-1;"><b>{{ __('panel.invoice.registered_address') }}</b> {{ $biz['company_address'] }}</div>
                @endif
                @if(!empty($biz['vat_number']))
                    <div><b>{{ __('panel.invoice.vat_number') }}</b> {{ $biz['vat_number'] }}</div>
                @endif
                @if(!empty($biz['company_number']))
                    <div><b>{{ __('panel.invoice.company_number') }}</b> {{ $biz['company_number'] }}</div>
                @endif
                @if(!empty($biz['fca_number']))
                    <div><b>{{ __('panel.invoice.fca_number') }}</b> {{ $biz['fca_number'] }}</div>
                @endif
            </div>
        </div>

        <div class="footer">
            <div>{{ $biz['company_name'] ?? 'Hot Tub Buyer Ltd' }}</div>
            <div>{{ __('panel.invoice.thank_you', ['email' => $biz['company_email'] ?? 'support@hottubbuyer.com']) }}</div>
        </div>
    </div>
</body>
</html>


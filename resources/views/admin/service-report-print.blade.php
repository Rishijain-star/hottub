<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Service Report - {{ $req->product_name }}</title>
    <style>
        body { font-family: sans-serif; color: #333; line-height: 1.5; padding: 40px; }
        .header { text-align: center; border-bottom: 2px solid #eee; margin-bottom: 30px; padding-bottom: 20px; }
        .title { font-size: 24px; font-weight: bold; margin: 0; }
        .subtitle { color: #666; font-size: 14px; }
        .section { margin-bottom: 30px; }
        .section-title { font-size: 18px; font-weight: bold; border-bottom: 1px solid #eee; margin-bottom: 15px; padding-bottom: 5px; color: #0ea5a3; }
        .grid { display: table; width: 100%; }
        .grid-row { display: table-row; }
        .grid-cell { display: table-cell; padding: 10px; width: 50%; vertical-align: top; }
        .label { font-size: 12px; color: #888; text-transform: uppercase; margin-bottom: 4px; }
        .value { font-weight: bold; }
        .checklist-item { margin-bottom: 10px; }
        .signature { font-family: cursive; font-size: 32px; margin-top: 10px; color: #000; }
        .footer { margin-top: 50px; font-size: 12px; color: #aaa; text-align: center; border-top: 1px solid #eee; padding-top: 20px; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #0ea5a3; color: #fff; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">Print / Save as PDF</button>
    </div>

    <div class="header">
        <div class="title">Service Completion Report</div>
        <div class="subtitle">Generated on {{ date('d M Y H:i') }}</div>
    </div>

    <div class="section">
        <div class="section-title">General Information</div>
        <div class="grid">
            <div class="grid-row">
                <div class="grid-cell">
                    <div class="label">Customer</div>
                    <div class="value">{{ $req->customer->name ?? 'Unknown' }}</div>
                    <div style="font-size: 13px;">{{ $req->customer->email ?? '' }}</div>
                </div>
                <div class="grid-cell">
                    <div class="label">Dealer</div>
                    <div class="value">{{ $req->dealer->name ?? 'Not Assigned' }}</div>
                    <div style="font-size: 13px;">{{ $req->dealer->email ?? '' }}</div>
                </div>
            </div>
            <div class="grid-row">
                <div class="grid-cell">
                    <div class="label">Service For</div>
                    <div class="value">{{ $req->product_name }} ({{ ucwords($req->type) }})</div>
                </div>
                <div class="grid-cell">
                    <div class="label">Status</div>
                    <div class="value">{{ strtoupper(str_replace('_', ' ', $req->status)) }}</div>
                </div>
            </div>
        </div>
    </div>

    @php $data = $req->checklist_data ?? []; @endphp
    <div class="section">
        <div class="section-title">Work Checklist (Dealer)</div>
        <div class="checklist-item">
            <div class="label">Service Type</div>
            <div>{{ $data['service_type'] ?? 'N/A' }}</div>
        </div>
        <div class="checklist-item">
            <div class="label">Service Date</div>
            <div>{{ $data['service_date'] ?? 'N/A' }}</div>
        </div>
        <div class="checklist-item">
            <div class="label">Work Summary</div>
            <div style="background: #fcfcfc; padding: 10px; border: 1px solid #f0f0f0;">{{ $data['work_summary'] ?? 'N/A' }}</div>
        </div>
        <div class="checklist-item">
            <div class="label">Parts Replaced</div>
            <div>{{ $data['parts_replaced'] ?? 'None' }}</div>
        </div>
        <div class="checklist-item">
            <div class="label">Dealer Notes</div>
            <div>{{ $data['notes'] ?? 'None' }}</div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Customer Confirmation</div>
        <div class="checklist-item">
            <div class="label">Review / Feedback</div>
            <div style="font-style: italic;">"{{ $req->customer_review ?: 'No review provided.' }}"</div>
        </div>
        <div class="checklist-item" style="margin-top: 20px;">
            <div class="label">Digital Signature</div>
            <div class="signature">{{ $req->customer_signature ?: 'PENDING SIGNATURE' }}</div>
            <div style="font-size: 11px; color: #999; margin-top: 5px;">
                Electronically confirmed on: {{ $req->completed_at ? $req->completed_at->format('d M Y H:i:s') : 'N/A' }}
            </div>
        </div>
    </div>

    <div class="footer">
        &copy; {{ date('Y') }} Hot Tub Buyer - Professional CRM Service Module
    </div>

    <script>
        // Auto-open print dialog if needed
        // window.print();
    </script>
</body>
</html>

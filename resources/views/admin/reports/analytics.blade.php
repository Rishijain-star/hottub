<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Analytics Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 12px; }
        h1, h2, h3 { margin: 0 0 8px 0; }
        .muted { color: #64748b; }
        .section { margin-top: 18px; }
        .grid { width: 100%; border-collapse: collapse; }
        .grid th, .grid td { border: 1px solid #e2e8f0; padding: 8px; text-align: left; }
        .grid th { background: #f8fafc; }
        .kpi { width: 100%; border-collapse: separate; border-spacing: 8px; }
        .kpi td { border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px; vertical-align: top; }
        .kpi-label { color: #64748b; font-size: 11px; margin-bottom: 6px; }
        .kpi-value { font-weight: 700; font-size: 18px; }
    </style>
</head>
<body>
    <h1>Admin Analytics Report</h1>
    <div class="muted">Period: {{ $periodLabel }}</div>
    <div class="muted">Generated: {{ $generatedAt->format('d M Y, H:i') }}</div>

    <div class="section">
        <h2>Core Metrics</h2>
        <table class="kpi">
            <tr>
                <td><div class="kpi-label">Total Leads</div><div class="kpi-value">{{ $leadsTotal }}</div></td>
                <td><div class="kpi-label">Active Leads</div><div class="kpi-value">{{ $activeLeadsCount }}</div></td>
                <td><div class="kpi-label">Converted Leads</div><div class="kpi-value">{{ $totalConverted }}</div></td>
                <td><div class="kpi-label">Total Revenue</div><div class="kpi-value">£{{ number_format($revenue, 2) }}</div></td>
            </tr>
            <tr>
                <td><div class="kpi-label">Dealer Purchases</div><div class="kpi-value">{{ $dealerPurchasedCount }}</div></td>
                <td><div class="kpi-label">Manufacturer Purchases</div><div class="kpi-value">{{ $manufacturerPurchasedCount }}</div></td>
                <td><div class="kpi-label">Overall Conversion</div><div class="kpi-value">{{ number_format($overallConversionRate, 1) }}%</div></td>
                <td><div class="kpi-label">Total Conversion %</div><div class="kpi-value">{{ number_format($overallConversionRate, 1) }}%</div></td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Product Insights</h2>
        <table class="grid">
            <tr>
                <th>Insight</th>
                <th>Value</th>
            </tr>
            <tr>
                <td>Most Popular Model</td>
                <td>{{ $mostPopularModel }}</td>
            </tr>
            <tr>
                <td>Most Popular Colour</td>
                <td>{{ $mostPopularColour }}</td>
            </tr>
            <tr>
                <td>Dealer Conversion Rate</td>
                <td>{{ number_format($dealerConversionRate, 1) }}%</td>
            </tr>
            <tr>
                <td>Manufacturer Conversion Rate</td>
                <td>{{ number_format($manufacturerConversionRate, 1) }}%</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Brand Performance</h2>
        <table class="grid">
            <tr>
                <th>#</th>
                <th>Brand</th>
                <th>Won Leads</th>
                <th>% Share</th>
            </tr>
            @php $brandTotalWins = max(1, (int) collect($brandPerformance)->sum('wins')); @endphp
            @forelse($brandPerformance as $idx => $item)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ $item['brand'] }}</td>
                    <td>{{ $item['wins'] }}</td>
                    <td>{{ number_format(($item['wins'] / $brandTotalWins) * 100, 1) }}%</td>
                </tr>
            @empty
                <tr><td colspan="4">No brand performance data found for this period.</td></tr>
            @endforelse
        </table>
    </div>

    <div class="section">
        <h2>Dealer Ranking</h2>
        <table class="grid">
            <tr>
                <th>#</th>
                <th>Dealer</th>
                <th>Purchases</th>
                <th>Won Leads</th>
                <th>Conversion %</th>
            </tr>
            @forelse($dealerRankings as $idx => $item)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ $item['name'] }}</td>
                    <td>{{ $item['purchases'] }}</td>
                    <td>{{ $item['wins'] }}</td>
                    <td>{{ number_format($item['conversion_rate'], 1) }}%</td>
                </tr>
            @empty
                <tr><td colspan="5">No dealer ranking data found for this period.</td></tr>
            @endforelse
        </table>
    </div>

    <div class="section">
        <h2>Manufacturer Ranking</h2>
        <table class="grid">
            <tr>
                <th>#</th>
                <th>Manufacturer</th>
                <th>Purchases</th>
                <th>Won Leads</th>
                <th>Conversion %</th>
            </tr>
            @forelse($manufacturerRankings as $idx => $item)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ $item['name'] }}</td>
                    <td>{{ $item['purchases'] }}</td>
                    <td>{{ $item['wins'] }}</td>
                    <td>{{ number_format($item['conversion_rate'], 1) }}%</td>
                </tr>
            @empty
                <tr><td colspan="5">No manufacturer ranking data found for this period.</td></tr>
            @endforelse
        </table>
    </div>
</body>
</html>

{{-- User behaviour improvements (additive) --}}
<div class="analytics-dash__subsection" style="margin-top:1.5rem;padding-top:1.25rem;border-top:1px dashed var(--gray-200,#e5e7eb);">
    <h3 class="fw-800" style="font-size:0.95rem;margin:0 0 1rem;">User behaviour improvements</h3>

    <div class="analytics-dash__grid-2">
        <div class="card" style="padding:0;">
            <div class="fw-800" style="padding:1rem 1rem 0;">Returning visitor frequency</div>
            @if(!($returningVisitorFrequency['ok'] ?? false))
                <p class="text-sm text-muted" style="padding:1rem;">{{ $returningVisitorFrequency['error'] ?? 'Data unavailable.' }}</p>
            @else
                @php $rv = $returningVisitorFrequency['data'] ?? []; @endphp
                <table class="table analytics-table">
                    <thead><tr><th>Visitor type</th><th>Sessions</th></tr></thead>
                    <tbody>
                        @forelse($rv['by_visitor_type'] ?? [] as $row)
                        <tr>
                            <td>{{ $row['type'] }}</td>
                            <td>{{ number_format($row['sessions']) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-muted text-center">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                @if(!empty($rv['returning_by_campaign']))
                    <div class="fw-700 text-sm" style="padding:0.75rem 1rem 0;">Returning sessions by campaign</div>
                    <table class="table analytics-table">
                        <thead><tr><th>Campaign</th><th>Sessions</th></tr></thead>
                        <tbody>
                            @foreach($rv['returning_by_campaign'] as $row)
                            <tr>
                                <td>{{ $row['campaign'] }}</td>
                                <td>{{ number_format($row['sessions']) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            @endif
        </div>
        <div class="card" style="padding:0;">
            <div class="fw-800" style="padding:1rem 1rem 0;">Popular filters / categories</div>
            <p class="text-sm text-muted" style="padding:0 1rem;margin:0;">Pages under <code>/hot-tubs</code> or <code>/swim-spas</code></p>
            @if(!($popularFilterCategories['ok'] ?? false))
                <p class="text-sm text-muted" style="padding:1rem;">{{ $popularFilterCategories['error'] ?? 'Data unavailable.' }}</p>
            @else
                <table class="table analytics-table">
                    <thead><tr><th>Page</th><th>Views</th><th>Sessions</th></tr></thead>
                    <tbody>
                        @forelse($popularFilterCategories['data']['categories'] ?? [] as $row)
                        <tr>
                            <td><code style="font-size:0.75rem;">{{ $row['page'] }}</code></td>
                            <td>{{ number_format($row['views']) }}</td>
                            <td>{{ number_format($row['sessions']) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-muted text-center">No category page data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>

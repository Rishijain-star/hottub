{{-- User Behaviour — Microsoft Clarity (additive partial) --}}
@php
    $cl = $clarityResult['data'] ?? [];
    $links = $cl['links'] ?? [];
    $metrics = $cl['metrics'] ?? [];
    $range = $cl['range'] ?? [];
    $projectId = $cl['project_id'] ?? config('clarity.project_id');
@endphp

<section class="analytics-dash__section" style="margin-top:2.5rem;padding-top:2rem;border-top:1px solid var(--gray-200,#e5e7eb);">
    <h2 class="analytics-dash__section-title">User Behaviour</h2>
    <p class="text-sm text-muted" style="margin:-0.5rem 0 1rem;">
        Microsoft Clarity — project <code>{{ $projectId }}</code>
        @if(!empty($range['label']))
            · {{ $range['label'] }}
        @endif
    </p>

    @if(!empty($range['note']))
        <p class="text-sm text-muted" style="margin:0 0 1rem;">{{ $range['note'] }}</p>
    @endif

    <div class="analytics-dash__grid-2" style="margin-bottom:1.25rem;">
        <div class="card" style="padding:1rem;">
            <div class="fw-800" style="margin-bottom:0.75rem;">Open in Clarity</div>
            <div style="display:flex;flex-wrap:wrap;gap:0.5rem;">
                <a href="{{ $links['dashboard'] ?? '#' }}" class="btn btn--primary btn--sm" target="_blank" rel="noopener">Dashboard</a>
                <a href="{{ $links['recordings'] ?? '#' }}" class="btn btn--ghost btn--sm" target="_blank" rel="noopener">Recordings</a>
                <a href="{{ $links['heatmaps'] ?? '#' }}" class="btn btn--ghost btn--sm" target="_blank" rel="noopener">Heatmaps</a>
            </div>
            <p class="text-sm text-muted" style="margin:0.75rem 0 0;">Session replays and heatmaps open in Clarity (embedding is not supported by Microsoft).</p>
        </div>
        <div class="card" style="padding:0;overflow:hidden;min-height:200px;">
            <div class="fw-800" style="padding:1rem 1rem 0;">Dashboard preview</div>
            <iframe
                title="Microsoft Clarity dashboard"
                src="{{ $links['dashboard'] ?? '' }}"
                style="width:100%;height:280px;border:0;border-top:1px solid var(--gray-200,#e5e7eb);"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
            ></iframe>
            <p class="text-sm text-muted" style="padding:0.5rem 1rem 1rem;margin:0;">If the preview is blank, sign in to Clarity using the links above.</p>
        </div>
    </div>

    @if(!($clarityResult['ok'] ?? false))
        <div class="alert alert--warning">
            <strong>Clarity API metrics unavailable.</strong><br>
            {{ $clarityResult['error'] ?? 'Unknown error.' }}
            <p class="text-sm" style="margin:0.5rem 0 0;">Generate an API token under Clarity → Settings → Data Export, then set <code>CLARITY_API_TOKEN</code> in <code>.env</code>.</p>
        </div>
    @else
        <div class="panel-stats-grid">
            <div class="panel-stat-card">
                <div class="panel-stat-card__label">Total sessions</div>
                <div class="panel-stat-card__value">{{ number_format($metrics['total_sessions'] ?? 0) }}</div>
            </div>
            <div class="panel-stat-card">
                <div class="panel-stat-card__label">Dead clicks</div>
                <div class="panel-stat-card__value">{{ number_format($metrics['dead_clicks'] ?? 0) }}</div>
            </div>
            <div class="panel-stat-card">
                <div class="panel-stat-card__label">Rage clicks</div>
                <div class="panel-stat-card__value">{{ number_format($metrics['rage_clicks'] ?? 0) }}</div>
            </div>
            <div class="panel-stat-card">
                <div class="panel-stat-card__label">Scroll depth (avg)</div>
                <div class="panel-stat-card__value">
                    @if(isset($metrics['scroll_depth_avg']) && $metrics['scroll_depth_avg'] !== null)
                        {{ number_format($metrics['scroll_depth_avg'], 1) }}%
                    @else
                        —
                    @endif
                </div>
            </div>
        </div>
    @endif
</section>

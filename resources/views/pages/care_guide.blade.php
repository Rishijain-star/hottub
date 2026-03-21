@extends('layouts.app')
@section('title', 'Hot Tub Care Guide – Complete Maintenance Guide')
@section('content')

{{-- ══ HERO ══════════════════════════════════════════════════════════════════ --}}
<section class="svc-hero" style="border-bottom:none;">
    <div class="container" style="text-align:center;">
        <h1 class="svc-hero__title" style="font-size:clamp(2rem,4vw,2.8rem);">Hot Tub Care Guide</h1>
        <p class="svc-hero__desc">Complete maintenance guide for keeping your hot tub in perfect condition</p>
    </div>
</section>

{{-- ══ MAIN CONTENT ═════════════════════════════════════════════════════════ --}}
<section class="section section--white" style="padding-top:1rem;">
    <div class="container" style="max-width:760px;">

        {{-- Daily Maintenance --}}
        <div class="care-block">
            <div class="care-block__header">
                <div class="care-block__icon" style="background:#e0f7f5;color:var(--teal);">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
                <h2 class="care-block__title">Daily Maintenance</h2>
            </div>
            <div class="care-items">
                <div class="care-item">
                    <div class="care-item__check">
                        <svg width="16" height="16" fill="none" stroke="var(--teal)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                    <div>
                        <strong>Check Water Temperature</strong>
                        <p>Ensure temperature is at desired level (typically 37–40°C). Adjust if necessary.</p>
                    </div>
                </div>
                <div class="care-item">
                    <div class="care-item__check">
                        <svg width="16" height="16" fill="none" stroke="var(--teal)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                    <div>
                        <strong>Skim Surface Debris</strong>
                        <p>Remove leaves, insects, and floating debris with a skimmer net.</p>
                    </div>
                </div>
                <div class="care-item">
                    <div class="care-item__check">
                        <svg width="16" height="16" fill="none" stroke="var(--teal)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                    <div>
                        <strong>Keep Cover On When Not in Use</strong>
                        <p>Reduces heat loss, keeps debris out, and improves safety.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Weekly Maintenance --}}
        <div class="care-block">
            <div class="care-block__header">
                <div class="care-block__icon" style="background:#e0f2fe;color:#0284c7;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2a7 7 0 0 1 7 7c0 5-7 13-7 13S5 14 5 9a7 7 0 0 1 7-7z"/></svg>
                </div>
                <h2 class="care-block__title">Weekly Maintenance</h2>
            </div>
            <div class="care-items">
                <div class="care-item">
                    <div class="care-item__check">
                        <svg width="16" height="16" fill="none" stroke="var(--teal)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                    <div>
                        <strong>Test Water Chemistry (2–3 times)</strong>
                        <p>Use test strips or liquid kit to check:</p>
                        <ul class="care-sublist">
                            <li>pH: 7.2–7.6 (ideal 7.4)</li>
                            <li>Total Alkalinity: 80–120 ppm</li>
                            <li>Sanitiser: 3–5 ppm (chlorine) or 3–6 ppm (bromine)</li>
                            <li>Calcium Hardness: 150–250 ppm</li>
                        </ul>
                    </div>
                </div>
                <div class="care-item">
                    <div class="care-item__check">
                        <svg width="16" height="16" fill="none" stroke="var(--teal)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                    <div>
                        <strong>Add Sanitiser</strong>
                        <p>Maintain proper chlorine or bromine levels. Shock treat if needed.</p>
                    </div>
                </div>
                <div class="care-item">
                    <div class="care-item__check">
                        <svg width="16" height="16" fill="none" stroke="var(--teal)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                    <div>
                        <strong>Rinse Filter Cartridge</strong>
                        <p>Remove and rinse with hose to remove debris. Rotate if you have spares.</p>
                    </div>
                </div>
                <div class="care-item">
                    <div class="care-item__check">
                        <svg width="16" height="16" fill="none" stroke="var(--teal)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                    <div>
                        <strong>Wipe Waterline</strong>
                        <p>Remove oils and residue from waterline with spa cleaner.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Monthly Maintenance --}}
        <div class="care-block">
            <div class="care-block__header">
                <div class="care-block__icon" style="background:#ede9fe;color:#7c3aed;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                </div>
                <h2 class="care-block__title">Monthly Maintenance</h2>
            </div>
            <div class="care-items">
                <div class="care-item">
                    <div class="care-item__check">
                        <svg width="16" height="16" fill="none" stroke="var(--teal)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                    <div>
                        <strong>Deep Clean Filters</strong>
                        <p>Soak filter cartridges overnight in filter cleaning solution, rinse thoroughly.</p>
                    </div>
                </div>
                <div class="care-item">
                    <div class="care-item__check">
                        <svg width="16" height="16" fill="none" stroke="var(--teal)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                    <div>
                        <strong>Shock Treatment</strong>
                        <p>Apply shock treatment to oxidise contaminants and restore clarity.</p>
                    </div>
                </div>
                <div class="care-item">
                    <div class="care-item__check">
                        <svg width="16" height="16" fill="none" stroke="var(--teal)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                    <div>
                        <strong>Clean Cover</strong>
                        <p>Wash cover with spa cover cleaner and apply UV protectant.</p>
                    </div>
                </div>
                <div class="care-item">
                    <div class="care-item__check">
                        <svg width="16" height="16" fill="none" stroke="var(--teal)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                    <div>
                        <strong>Inspect Equipment</strong>
                        <p>Check pumps, heater, jets for proper operation. Look for leaks.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Every 3-4 Months --}}
        <div class="care-block">
            <div class="care-block__header">
                <div class="care-block__icon" style="background:#fef9c3;color:#a16207;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                </div>
                <h2 class="care-block__title">Every 3–4 Months</h2>
            </div>
            <div class="care-items">
                <div class="care-item">
                    <div class="care-item__check">
                        <svg width="16" height="16" fill="none" stroke="var(--teal)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                    <div>
                        <strong>Complete Water Change</strong>
                        <p>Drain, clean, and refill your hot tub:</p>
                        <ol class="care-sublist care-sublist--ordered">
                            <li>Turn off power at breaker</li>
                            <li>Drain using drain valve or submersible pump</li>
                            <li>Clean shell with non-abrasive spa cleaner</li>
                            <li>Flush pipes with cleaning product</li>
                            <li>Refill with fresh water using hose filter if possible</li>
                            <li>Balance water chemistry before use</li>
                        </ol>
                    </div>
                </div>
                <div class="care-item">
                    <div class="care-item__check">
                        <svg width="16" height="16" fill="none" stroke="var(--teal)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                    <div>
                        <strong>Replace Filter Cartridges</strong>
                        <p>Install new filters (or as needed if damaged/heavily stained).</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Important Tips --}}
        <div class="care-tips-box">
            <div class="care-tips__header">
                <svg width="20" height="20" fill="none" stroke="rgba(255,255,255,.9)" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <h3>Important Tips</h3>
            </div>
            <ul class="care-tips__list">
                <li>Always shower before entering to reduce body oils and contaminants</li>
                <li>Never mix different types of chemicals directly</li>
                <li>Keep chemicals stored in a cool, dry place away from children</li>
                <li>Run jets on high for 15–20 minutes after adding chemicals</li>
                <li>Keep a maintenance log to track chemical additions and filter changes</li>
                <li>Consider a professional service annually for deep maintenance</li>
            </ul>
        </div>

        {{-- Common Issues --}}
        <div class="care-issues">
            <h2 class="care-issues__title">Common Issues &amp; Solutions</h2>
            <div class="care-issue-list">
                <div class="care-issue care-issue--teal">
                    <strong>Cloudy Water</strong>
                    <p><span class="care-issue__label">Causes:</span> Poor chemistry, dirty filters, insufficient sanitiser</p>
                    <p><span class="care-issue__label">Fix:</span> Test and balance water, clean/replace filters, shock treat</p>
                </div>
                <div class="care-issue care-issue--green">
                    <strong>Green Water</strong>
                    <p><span class="care-issue__label">Causes:</span> Algae growth from low sanitiser levels</p>
                    <p><span class="care-issue__label">Fix:</span> Shock treat heavily, run filtration 24hrs, clean filters, may need to drain</p>
                </div>
                <div class="care-issue care-issue--purple">
                    <strong>Foam</strong>
                    <p><span class="care-issue__label">Causes:</span> Body oils, detergent residue, low calcium</p>
                    <p><span class="care-issue__label">Fix:</span> Add anti-foam, shock treat, check calcium levels</p>
                </div>
                <div class="care-issue care-issue--orange">
                    <strong>Scale Buildup</strong>
                    <p><span class="care-issue__label">Causes:</span> High calcium, high pH, hard water</p>
                    <p><span class="care-issue__label">Fix:</span> Balance pH, add scale prevention product, use water softener</p>
                </div>
            </div>
        </div>

    </div>
</section>

@endsection
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel – Hot Tub Buyer')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700;9..40,800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/panel.css') }}">

    @yield('styles')
</head>
<body class="panel-body">

    {{-- ══ TOP HEADER (same as public site) ══════════════════════════════ --}}
    @include('layouts.header')

    {{-- ══ PANEL WRAPPER ══════════════════════════════════════════════════ --}}
    <div class="panel-wrapper">

        {{-- Sidebar --}}
        @include('components.admin-sidebar')

        {{-- Main Content --}}
        <div class="panel-main">
            <div id="adminSkeleton" style="padding:1rem;display:flex;gap:12px;flex-wrap:wrap">
                <div style="flex:1;min-width:280px;border:1px solid var(--gray-200);border-radius:12px;padding:16px;background:#fff">
                    <div style="height:16px;width:40%;background:#eef2f7;border-radius:6px;margin-bottom:10px"></div>
                    <div style="height:12px;width:60%;background:#f3f4f6;border-radius:6px;margin-bottom:8px"></div>
                    <div style="height:12px;width:50%;background:#f3f4f6;border-radius:6px;margin-bottom:8px"></div>
                    <div style="height:32px;width:120px;background:#e5e7eb;border-radius:999px;margin-top:6px"></div>
                </div>
                <div style="flex:1;min-width:280px;border:1px solid var(--gray-200);border-radius:12px;padding:16px;background:#fff">
                    <div style="height:16px;width:40%;background:#eef2f7;border-radius:6px;margin-bottom:10px"></div>
                    <div style="height:12px;width:60%;background:#f3f4f6;border-radius:6px;margin-bottom:8px"></div>
                    <div style="height:12px;width:50%;background:#f3f4f6;border-radius:6px;margin-bottom:8px"></div>
                    <div style="height:32px;width:120px;background:#e5e7eb;border-radius:999px;margin-top:6px"></div>
                </div>
            </div>
            <div class="panel-content" style="display:none">
                @yield('content')
            </div>
        </div>

    </div>

    @yield('modals')

    <div class="modal-backdrop" id="confirmModal">
        <div class="modal modal--sm">
            <div class="modal-header">
                <div class="modal-title" id="confirmModalTitle">Are you sure?</div>
            </div>
            <div class="text-sm text-muted" style="margin-top:-0.5rem; margin-bottom:1.25rem;" id="confirmModalDesc">This action cannot be undone.</div>
            <div class="modal-actions">
                <button type="button" class="btn btn--ghost" style="background: #f3f4f6; color: #374151; border: 1px solid #d1d5db;" onclick="document.getElementById('confirmModal').classList.remove('active')">No, Cancel</button>
                <button type="button" class="btn btn--danger" id="confirmModalYes">Yes, Delete</button>
            </div>
        </div>
    </div>

    @yield('scripts')
    <script>
        function showConfirmationModal(form, title, desc, buttonText) {
            document.getElementById('confirmModalTitle').textContent = title || 'Are you sure?';
            document.getElementById('confirmModalDesc').textContent = desc || 'This action cannot be undone.';
            const yesBtn = document.getElementById('confirmModalYes');
            yesBtn.textContent = buttonText || 'Yes, Delete';

            document.getElementById('confirmModal').classList.add('active');

            yesBtn.onclick = function() {
                form.submit();
            }
        }

        document.querySelectorAll('.panel-nav-link').forEach(link => {
            if (link.getAttribute('href') === window.location.pathname) {
                link.classList.add('active');
            }
        });

        function toggleSidebar() {
            document.querySelector('.panel-sidebar').classList.toggle('panel-sidebar--open');
        setNavbarHeightVar();
        }

        // ── Admin skeleton loader (reuse same visual style as product skeleton) ──
        (function(){
            function showContent(){
                const sk = document.getElementById('adminSkeleton');
                const pc = document.querySelector('.panel-content');
                if (sk) sk.style.display = 'none';
                if (pc) pc.style.display = '';
            }
            if (document.readyState === 'complete' || document.readyState === 'interactive') {
                setTimeout(showContent, 100);
            } else {
                window.addEventListener('DOMContentLoaded', function(){ setTimeout(showContent, 100); });
            }
        })();

        // ── Progress bar on form submit (same component as Hot Tubs Create) ──
        (function(){
            document.addEventListener('submit', function(e){
                const form = e.target;
                if (!(form instanceof HTMLFormElement)) return;
                const wrap = form.querySelector('#uploadProgress');
                const bar  = form.querySelector('#uploadProgressBar');
                if (wrap && bar) {
                    wrap.style.display = '';
                    bar.style.width = '10%';
                    let pct = 10;
                    const timer = setInterval(function(){
                        pct = Math.min(pct + 8, 90);
                        bar.style.width = pct + '%';
                    }, 200);
                    window.addEventListener('unload', function(){ clearInterval(timer); });
                }
            }, true);
        })();
    </script>
</body>

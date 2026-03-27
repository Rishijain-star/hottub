<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Manufacturer Panel – Hot Tub Buyer')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700;9..40,800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/panel.css') }}">

    @yield('styles')
    </head>
<body class="panel-body">

    @include('layouts.header')

    @if(isset($isAccountRestricted) && $isAccountRestricted)
        <div style="position:fixed; inset:0; background:rgba(15, 23, 42, 0.9); backdrop-filter:blur(8px); z-index:99999; display:flex; align-items:center; justify-content:center; padding:1.5rem;">
            <div class="card" style="max-width:550px; width:100%; padding:3.5rem 2rem; text-align:center; border:2px solid #ef4444; box-shadow:0 25px 50px -12px rgba(239, 68, 68, 0.25);">
                <div style="width:80px; height:80px; background:#fef2f2; color:#ef4444; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 2rem; font-size:2.5rem; border:4px solid #fee2e2;">
                    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h1 style="font-size:2rem; font-weight:800; color:#111827; margin-bottom:1rem; letter-spacing:-0.025em;">Account {{ ucfirst($restrictionStatus) }}</h1>
                <p style="color:#4b5563; font-size:1.1rem; line-height:1.6; margin-bottom:2.5rem;">Your manufacturer account has been <strong>{{ $restrictionStatus }}</strong> by the administrator. All functionality has been locked. Please contact support to resolve this.</p>
                
                <div id="supportFormContainer" style="background:#f9fafb; padding:2rem; border-radius:12px; border:1px solid #e5e7eb; text-align:left;">
                    <h3 style="font-size:1rem; font-weight:700; color:#374151; margin-bottom:1rem; display:flex; align-items:center; gap:8px;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Send Support Request
                    </h3>
                    @php
                        $requestCount = \App\Models\Message::where('sender_id', auth()->id())->where('receiver_id', 1)->count();
                    @endphp
                    
                    @if($requestCount < 3)
                        <form id="restrictedSupportForm">
                            @csrf
                            <textarea name="content" id="supportContent" class="form-input" rows="3" placeholder="Explain your situation or request reactivation..." required style="margin-bottom:1rem; border-color:#d1d5db;"></textarea>
                            <button type="submit" id="btnSendSupport" class="btn btn--danger btn--full" style="padding:0.8rem;">Send Request ({{ $requestCount }}/3)</button>
                        </form>
                        <div id="supportSuccessMsg" style="display:none; margin-top:1rem; padding:0.75rem; background:#dcfce7; color:#166534; border-radius:8px; font-size:0.9rem; text-align:center; font-weight:600;">
                            Your request has been submitted successfully.
                        </div>
                    @else
                        <div style="padding:1rem; background:#fee2e2; color:#b91c1c; border-radius:8px; font-weight:600; font-size:0.9rem; text-align:center;">
                            Maximum of 3 support requests reached.
                        </div>
                    @endif
                </div>
                
                <form action="{{ route('logout') }}" method="POST" style="margin-top:2rem;">
                    @csrf
                    <button type="submit" class="btn btn--ghost" style="color:#6b7280; border-color:#d1d5db;">Sign Out</button>
                </form>
            </div>
        </div>
    @else
        <div class="panel-wrapper">
            @include('components.manufacturer-sidebar')
            <main class="panel-main">
                @yield('content')
            </main>
        </div>
    @endif

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

    @if(file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
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
    </script>
    
    @if(isset($isAccountRestricted) && $isAccountRestricted)
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('restrictedSupportForm');
        if (form) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                const btn = document.getElementById('btnSendSupport');
                const content = document.getElementById('supportContent').value;
                const successMsg = document.getElementById('supportSuccessMsg');
                
                btn.disabled = true;
                btn.textContent = 'Sending...';
                
                try {
                    const response = await fetch('{{ route("manufacturer.api.send_message", 1) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ content: content })
                    });
                    
                    const data = await response.json();
                    
                    if (response.ok) {
                        form.style.display = 'none';
                        successMsg.style.display = 'block';
                    } else {
                        alert(data.msg || 'Error sending request');
                        btn.disabled = false;
                        btn.textContent = 'Send Request';
                    }
                } catch (error) {
                    alert('Network error. Please try again.');
                    btn.disabled = false;
                    btn.textContent = 'Send Request';
                }
            });
        }
    });
    </script>
    @endif
</body>
</html>


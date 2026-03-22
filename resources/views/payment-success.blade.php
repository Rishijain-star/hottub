<!DOCTYPE html>
<html>
<head>
    <title>Payment Successful</title>
    <style>
        body { font-family: sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; background: #f8fafc; }
        .card { background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); text-align: center; max-width: 400px; }
        .icon { color: #22c55e; font-size: 48px; margin-bottom: 1rem; }
        h1 { margin: 0 0 0.5rem; color: #1e293b; font-size: 24px; }
        p { color: #64748b; margin: 0 0 1.5rem; line-height: 1.5; }
        .btn { display: inline-block; background: #22c55e; color: white; padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; font-weight: 600; transition: background 0.2s; }
        .btn:hover { background: #16a34a; }
        .loader { border: 3px solid #f3f3f3; border-top: 3px solid #3498db; border-radius: 50%; width: 16px; height: 16px; animation: spin 1s linear infinite; display: inline-block; vertical-align: middle; margin-right: 8px; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">✓</div>
        <h1>Payment Successful!</h1>
        <p>Your transaction has been completed successfully.</p>
        
        <div style="margin-bottom: 1rem;">
            <div class="loader"></div> <span style="color:#64748b; font-size:14px;">Refreshing dashboard and closing window...</span>
        </div>

        {{-- Commented out for now as requested: manual button --}}
        {{-- <a href="{{ route('dashboard') }}" class="btn">Return to Dashboard</a> --}}
    </div>

    <script>
        // Notify the parent window if it exists to refresh the dashboard
        if (window.opener) {
            window.opener.location.reload();
            
            // Close this window after a delay
            setTimeout(function() {
                window.close();
            }, 2500);
        } else {
            // If not in a popup, redirect to dashboard
            setTimeout(function() {
                window.location.href = "{{ route('dashboard') }}";
            }, 2500);
        }
    </script>
</body>
</html>

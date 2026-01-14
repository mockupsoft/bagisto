<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Provisioning Progress</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; }
        .progress { width: 100%; max-width: 500px; height: 18px; background: #eee; border-radius: 4px; overflow: hidden; margin: 1rem 0; }
        .progress-bar { height: 100%; background: #4caf50; width: 0%; transition: width 0.3s ease; }
        .error { color: #c0392b; }
        .muted { color: #666; }
        .success { color: #2e7d32; }
    </style>
</head>
<body>
    <h1>Provisioning your store</h1>
    <p class="muted">We are setting up your tenant. This page will refresh automatically.</p>

    <div id="status-line"><strong>Status:</strong> <span id="status-value">loading...</span></div>
    <div class="progress" aria-label="Provisioning progress">
        <div id="progress-bar" class="progress-bar"></div>
    </div>
    <div id="message" class="muted"></div>
    <div id="last-error" class="error" style="display:none;"></div>

    <div id="ready-actions" style="display:none; margin-top: 1rem;">
        <a id="admin-link" href="#" style="padding: 0.75rem 1rem; background: #4caf50; color: #fff; border-radius: 4px; text-decoration: none;">Go to store admin</a>
    </div>

    <script>
        const statusUrl = "{{ $statusUrl }}";
        const adminUrl = "https://{{ $tenant->subdomain }}.{{ config('saas.base_domain') }}/admin";
        const statusValueEl = document.getElementById('status-value');
        const progressBarEl = document.getElementById('progress-bar');
        const messageEl = document.getElementById('message');
        const errorEl = document.getElementById('last-error');
        const readyActionsEl = document.getElementById('ready-actions');
        const adminLinkEl = document.getElementById('admin-link');

        adminLinkEl.href = adminUrl;

        function render(data) {
            statusValueEl.textContent = data.status || 'unknown';
            progressBarEl.style.width = (data.percent || 0) + '%';
            messageEl.textContent = data.message || '';

            if (data.last_error) {
                errorEl.style.display = '';
                errorEl.textContent = data.last_error;
            } else {
                errorEl.style.display = 'none';
                errorEl.textContent = '';
            }

            if (data.status === 'active' || data.status === 'ready' || data.db_status === 'ready') {
                readyActionsEl.style.display = '';
            }
        }

        function poll() {
            fetch(statusUrl, { headers: { 'Accept': 'application/json' } })
                .then(response => response.json())
                .then(render)
                .catch(() => {
                    messageEl.textContent = 'Unable to fetch provisioning status. Retrying...';
                });
        }

        poll();
        setInterval(poll, 2000);
    </script>
</body>
</html>

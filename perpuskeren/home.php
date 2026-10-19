<?php
// Minimal Home page: no recap cards (dashboard shows the recap)
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Home</title>
</head>

<body>

    <div style="padding:18px;">
        <h2 style="margin:0;color:#111827;">Selamat datang👍</h2>
        <p style="color:#6b7280;">Kelola data perpustakaan dengan mudah melalui dashboard ini</p>
    <script>
        // Notify parent to refresh recap when Home loads inside iframe
        if (window.top !== window.self) {
            window.addEventListener('DOMContentLoaded', function () {
                try { window.parent.postMessage({ type: 'recap-refresh' }, '*'); } catch (e) { }
            });
        }
    </script>

</body>

</html>
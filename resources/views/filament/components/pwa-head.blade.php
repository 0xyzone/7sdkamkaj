<!-- PWA Manifest & Meta Tags -->
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#ed1c24">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Kamkaj">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon.png">

<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js').catch((err) => {
                console.log('SW registration error:', err);
            });
        });
    }
</script>

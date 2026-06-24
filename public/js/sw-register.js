// Service Worker Registration - Non-blocking
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(registration => {
                console.log('✓ Service Worker registered:', registration.scope);
                
                // Check for updates every hour
                setInterval(() => {
                    registration.update();
                }, 3600000);
            })
            .catch(error => {
                console.log('✗ Service Worker registration failed:', error);
            });
    });
}

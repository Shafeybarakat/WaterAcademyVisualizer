/**
 * Prevent caching for role switching functionality
 */
// Expose the initialization function globally
window.initPreventCache = function() {
    // Add cache-busting parameter to all switch back links
    const switchBackLinks = document.querySelectorAll('a[href*="switch_back.php"]');
    
    switchBackLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Prevent the default action
            e.preventDefault();
            
            // Get the href
            let href = this.getAttribute('href');
            
            // Add or update the nocache parameter
            if (href.includes('nocache=')) {
                href = href.replace(/nocache=\d+/, 'nocache=' + Date.now());
            } else {
                href += (href.includes('?') ? '&' : '?') + 'nocache=' + Date.now();
            }
            
            // Force reload the page by setting location.href
            window.location.href = href;
        });
    });
    
    // If we have a message parameter in the URL indicating we've switched back
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('message') && urlParams.get('message') === 'switched_back') {
        // Force a hard reload to ensure fresh content
        window.location.reload(true);
    }
}; // End of initPreventCache function

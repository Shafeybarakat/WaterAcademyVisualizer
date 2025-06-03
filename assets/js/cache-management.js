/**
 * assets/js/cache-management.js
 * Centralized script for managing browser caching and cache-busting.
 */

(function() {
    /**
     * Initializes cache prevention for specific elements, like role switch links.
     * This function is adapted from the original assets/js/_old/prevent-cache.js.
     */
    function initSpecificCachePrevention() {
        const switchBackLinks = document.querySelectorAll('a[href*="switch_back.php"]');
        
        switchBackLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                let href = this.getAttribute('href');
                href = addOrUpdateUrlParam(href, 'nocache', Date.now());
                window.location.href = href; // Force navigation with cache-busting
            });
        });
        
        // If we have a message parameter in the URL indicating we've switched back
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('message') && urlParams.get('message') === 'switched_back') {
            // Force a hard reload to ensure fresh content after role switch
            window.location.reload(true);
        }
    }

    /**
     * Adds or updates a URL parameter.
     * @param {string} url The original URL.
     * @param {string} param The parameter name.
     * @param {string} value The parameter value.
     * @returns {string} The updated URL.
     */
    function addOrUpdateUrlParam(url, param, value) {
        const regex = new RegExp('([?&])' + param + '=[^&]*(&|$)', 'i');
        const separator = url.includes('?') ? '&' : '?';
        if (url.match(regex)) {
            return url.replace(regex, '$1' + param + '=' + value + '$2');
        }
        return url + separator + param + '=' + value;
    }

    /**
     * Clears common browser storage caches (localStorage and sessionStorage).
     * Note: This does not clear HTTP cache (handled by server headers/cache-busting URLs).
     */
    function clearBrowserCaches() {
        try {
            localStorage.clear();
            sessionStorage.clear();
            console.log('Browser localStorage and sessionStorage cleared.');
        } catch (e) {
            console.error('Error clearing browser caches:', e);
        }
    }

    /**
     * Applies a global cache-busting parameter to the current page URL and reloads.
     * This is useful for forcing a fresh load of the current page.
     */
    function applyGlobalCacheBust() {
        const currentUrl = window.location.href;
        const newUrl = addOrUpdateUrlParam(currentUrl, 'global_cache_bust', Date.now());
        if (currentUrl !== newUrl) {
            window.location.href = newUrl; // Force reload with new parameter
        } else {
            // If URL didn't change (e.g., no params to begin with), force hard reload
            window.location.reload(true);
        }
    }

    // Initialize specific cache prevention on DOMContentLoaded
    document.addEventListener('DOMContentLoaded', initSpecificCachePrevention);

    // Optional: Expose global functions for manual triggering if needed (e.g., from console)
    window.CacheManager = {
        clearBrowserCaches: clearBrowserCaches,
        applyGlobalCacheBust: applyGlobalCacheBust
    };

    // Example of how to trigger a global cache bust on certain conditions (e.g., after an update)
    // This would typically be triggered by a server-side flag or a specific user action.
    // For demonstration, let's say we want to force a global cache bust on every page load
    // if a specific query parameter is present (e.g., ?force_refresh=true)
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('force_refresh') && urlParams.get('force_refresh') === 'true') {
        // Remove the parameter to prevent infinite reloads
        const cleanUrl = addOrUpdateUrlParam(window.location.href, 'force_refresh', '');
        window.history.replaceState({}, document.title, cleanUrl); // Clean URL without reloading
        
        clearBrowserCaches(); // Clear local storage
        window.location.reload(true); // Force hard reload
    }

})();

/**
 * Water Academy Page Transitions
 * Added: May 31, 2025
 * Features: PowerPoint-like transition effects between pages
 */

// Expose the initialization function globally
window.initPageTransitions = function() {
    // Make sure jQuery is loaded
    if (typeof $ === 'undefined' && typeof jQuery !== 'undefined') {
        $ = jQuery;
    }
    
    // Store the current page URL to detect navigation
    let currentPage = window.location.href;
    
    // Apply initial entrance animation
    applyEntranceAnimation();
    
    // Intercept all internal link clicks
    document.addEventListener('click', function(e) {
        // Find closest anchor tag
        const link = e.target.closest('a');
        
        // If this is a link and it's internal (same origin)
        if (link && link.href && link.href.startsWith(window.location.origin) && 
            !link.getAttribute('target') && !link.getAttribute('download') &&
            !e.ctrlKey && !e.metaKey && !e.shiftKey) {
            
            // Don't intercept dropdown toggles or tab links
            if (link.getAttribute('data-bs-toggle') || 
                link.getAttribute('role') === 'tab' ||
                link.closest('.dropdown-menu')) {
                return;
            }
            
            // Prevent default navigation
            e.preventDefault();
            
            // Apply exit animation
            applyExitAnimation().then(() => {
                // Navigate to the new page after animation completes
                window.location.href = link.href;
            });
        }
    });
    
    // Handle browser back/forward buttons
    window.addEventListener('popstate', function() {
        if (currentPage !== window.location.href) {
            // Apply exit animation
            applyExitAnimation().then(() => {
                // Reload the page to show the new content
                window.location.reload();
            });
        }
    });
    
    /**
     * Apply entrance animation to the page content
     */
    function applyEntranceAnimation() {
        // Add animation class to content wrapper
        const contentWrapper = document.querySelector('.content-wrapper');
        if (contentWrapper) {
            contentWrapper.style.animation = 'none';
            contentWrapper.offsetHeight; // Force reflow
            contentWrapper.style.animation = 'fadeIn 0.5s ease-in-out forwards';
        }
        
        // Animate cards with staggered delay
        animateElements('.stat-card, .action-card, .card:not(.stat-card):not(.action-card)', 'slideUp', 0.1);
        
        // Animate tables
        animateElements('table', 'fadeIn', 0);
        
        // Animate buttons
        animateElements('.btn:not(.dropdown-toggle)', 'fadeIn', 0.05);
    }
    
    /**
     * Apply exit animation before navigating away
     * @returns {Promise} Resolves when animation is complete
     */
    function applyExitAnimation() {
        return new Promise((resolve) => {
            // Create overlay for transition effect
            const overlay = document.createElement('div');
            overlay.className = 'page-transition-overlay';
            overlay.style.position = 'fixed';
            overlay.style.top = '0';
            overlay.style.left = '0';
            overlay.style.width = '100%';
            overlay.style.height = '100%';
            overlay.style.backgroundColor = 'rgba(58, 165, 255, 0.2)';
            overlay.style.backdropFilter = 'blur(10px)';
            overlay.style.zIndex = '9999';
            overlay.style.opacity = '0';
            overlay.style.transition = 'opacity 0.4s ease-in-out';
            document.body.appendChild(overlay);
            
            // Fade in the overlay
            setTimeout(() => {
                overlay.style.opacity = '1';
                
                // Wait for animation to complete
                setTimeout(() => {
                    resolve();
                }, 400);
            }, 10);
        });
    }
    
    /**
     * Animate multiple elements with staggered delay
     * @param {string} selector - CSS selector for elements to animate
     * @param {string} animationName - Name of the animation to apply
     * @param {number} staggerDelay - Delay between each element's animation
     */
    function animateElements(selector, animationName, staggerDelay) {
        const elements = document.querySelectorAll(selector);
        elements.forEach((el, index) => {
            el.style.animation = 'none';
            el.style.opacity = '0';
            el.offsetHeight; // Force reflow
            
            setTimeout(() => {
                el.style.animation = `${animationName} 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards`;
                el.style.animationDelay = `${index * staggerDelay}s`;
            }, 50);
        });
    }
    
    // Add CSS for transition effects if not already present
    function addTransitionStyles() {
        if (!document.getElementById('page-transition-styles')) {
            const style = document.createElement('style');
            style.id = 'page-transition-styles';
            style.textContent = `
                @keyframes slideFromRight {
                    from { opacity: 0; transform: translateX(50px); }
                    to { opacity: 1; transform: translateX(0); }
                }
                
                @keyframes slideFromLeft {
                    from { opacity: 0; transform: translateX(-50px); }
                    to { opacity: 1; transform: translateX(0); }
                }
                
                @keyframes slideFromTop {
                    from { opacity: 0; transform: translateY(-30px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                
                @keyframes slideFromBottom {
                    from { opacity: 0; transform: translateY(30px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                
                @keyframes zoomIn {
                    from { opacity: 0; transform: scale(0.9); }
                    to { opacity: 1; transform: scale(1); }
                }
                
                @keyframes zoomOut {
                    from { opacity: 1; transform: scale(1); }
                    to { opacity: 0; transform: scale(1.1); }
                }
                
                @keyframes fadeIn {
                    from { opacity: 0; }
                    to { opacity: 1; }
                }
                
                @keyframes fadeOut {
                    from { opacity: 1; }
                    to { opacity: 0; }
                }
                
                @keyframes rotateIn {
                    from { opacity: 0; transform: rotate(-10deg) scale(0.9); }
                    to { opacity: 1; transform: rotate(0) scale(1); }
                }
                
                .page-transition-overlay {
                    animation: fadeIn 0.4s ease-in-out;
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    // Add transition styles
    addTransitionStyles();
    
    // Return public methods
    return {
        refresh: applyEntranceAnimation
    };
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (typeof initPageTransitions === 'function') {
        window.pageTransitions = initPageTransitions();
    }
});

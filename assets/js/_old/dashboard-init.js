/**
 * Water Academy Dashboard Initialization
 * Sets up the dashboard components and ensures proper theme application
 * Updated: May 30, 2025
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard initialization starting');
    
    // Apply the current theme
    const currentTheme = getCurrentTheme();
    document.documentElement.setAttribute('data-theme', currentTheme);
    document.documentElement.setAttribute('data-bs-theme', currentTheme);
    document.body.setAttribute('data-theme', currentTheme);
    
    // Ensure all theme classes are properly set
    if (currentTheme === 'dark') {
        document.documentElement.classList.add('theme-dark', 'dark-style');
        document.documentElement.classList.remove('theme-light', 'light-style');
        document.body.classList.add('theme-dark', 'dark-style');
        document.body.classList.remove('theme-light', 'light-style');
    } else {
        document.documentElement.classList.add('theme-light', 'light-style');
        document.documentElement.classList.remove('theme-dark', 'dark-style');
        document.body.classList.add('theme-light', 'light-style');
        document.body.classList.remove('theme-dark', 'dark-style');
    }
    
    // Apply theme to all components
    applyThemeToComponents(currentTheme);
    
    // Wait for all elements to be fully loaded
    window.addEventListener('load', function() {
        console.log('Window loaded, reapplying theme');
        applyThemeToComponents(getCurrentTheme());
        initializeComponents();
    });
    
    // Listen for theme changes
    window.addEventListener('themeChanged', function(e) {
        console.log('Theme changed event received:', e.detail.theme);
        applyThemeToComponents(e.detail.theme);
    });
    
    /**
     * Apply theme-specific styles to dashboard components
     */
    function applyThemeToComponents(theme) {
        console.log('Applying theme to components:', theme);
        
        // Apply to all cards
        applyThemeToCards(theme);
        
        // Apply to tables
        applyThemeToTables(theme);
        
        // Apply to forms
        applyThemeToForms(theme);
        
        // Apply to dropdowns
        applyThemeToDropdowns(theme);
        
        // Apply to navbar
        applyThemeToNavbar(theme);
        
        // Apply to sidebar
        applyThemeToSidebar(theme);
        
        // Force proper card spacing
        fixCardSpacing();
    }
    
    /**
     * Apply theme to all cards
     */
    function applyThemeToCards(theme) {
        // Remove inline styles that might be overriding CSS classes
        document.querySelectorAll('.card, .stat-card, .action-card, .events-card').forEach(card => {
            // Remove inline styles that affect theming
            card.style.backgroundColor = '';
            card.style.color = '';
            
            // Add theme class for CSS styling
            card.classList.remove('theme-dark', 'theme-light');
            card.classList.add('theme-' + theme);
            
            // Make sure cards have correct padding
            if (!card.style.padding) {
                card.style.padding = '1.5rem';
            }
        });
        
        // Stats Cards - find all containers with stat cards
        document.querySelectorAll('.dashboard-stats').forEach(container => {
            // Fix container styles
            container.style.display = 'flex';
            container.style.flexWrap = 'wrap';
            container.style.gap = '1.25rem';
            container.style.marginBottom = '2rem';
            container.style.marginTop = '1.5rem';
            
            // Fix stat cards inside this container
            container.querySelectorAll('.stat-card, .card').forEach(card => {
                card.style.flex = '1 1 calc(20% - 1.25rem)';
                card.style.minWidth = '12rem';
                card.style.borderRadius = '0.375rem';
                card.style.display = 'flex';
                card.style.flexDirection = 'column';
                card.style.alignItems = 'center';
                card.style.justifyContent = 'center';
                card.style.textAlign = 'center';
                card.style.transition = 'all 0.3s ease';
                
                // Check if card has no children with stat classes
                const hasStatContent = card.querySelector('.stat-icon, .stat-value, .stat-label');
                if (!hasStatContent) {
                    // If this is a card without stat classes but in the dashboard-stats container,
                    // add necessary structure for stats display
                    const cardBody = card.querySelector('.card-body') || card;
                    const cardText = cardBody.textContent.trim();
                    
                    // Only restructure if it looks like a stat card (has a number and not too much text)
                    if (/\d+/.test(cardText) && cardText.length < 50) {
                        // Clear the content
                        cardBody.innerHTML = '';
                        
                        // Determine an appropriate icon based on content
                        let iconClass = 'bx bx-user';
                        if (cardText.toLowerCase().includes('group')) iconClass = 'bx bx-group';
                        if (cardText.toLowerCase().includes('course')) iconClass = 'bx bx-book';
                        if (cardText.toLowerCase().includes('trainee')) iconClass = 'bx bx-user-pin';
                        if (cardText.toLowerCase().includes('instructor')) iconClass = 'bx bx-chalkboard';
                        
                        // Extract the number
                        const number = cardText.match(/\d+/)[0];
                        
                        // Get the label by removing the number
                        let label = cardText.replace(number, '').trim();
                        
                        // Add the structure
                        cardBody.innerHTML = `
                            <div class="stat-icon ${iconClass}"></div>
                            <div class="stat-value">${number}</div>
                            <div class="stat-label">${label}</div>
                        `;
                    }
                }
            });
        });
    }
    
    /**
     * Apply theme to all tables
     */
    function applyThemeToTables(theme) {
        document.querySelectorAll('table').forEach(table => {
            // Set appropriate Bootstrap table classes
            if (theme === 'dark') {
                table.classList.add('table-dark');
                table.classList.remove('table-light');
            } else {
                table.classList.remove('table-dark');
                table.classList.add('table-light');
            }
            
            // Ensure table headers have proper color
            table.querySelectorAll('th').forEach(th => {
                th.style.color = '';  // Remove inline color to let CSS handle it
            });
        });
    }
    
    /**
     * Apply theme to all form elements
     */
    function applyThemeToForms(theme) {
        document.querySelectorAll('input, select, textarea, .form-control').forEach(input => {
            // Remove inline styles
            input.style.backgroundColor = '';
            input.style.color = '';
            input.style.borderColor = '';
            
            // Add theme classes
            input.classList.remove('theme-dark', 'theme-light');
            input.classList.add('theme-' + theme);
        });
    }
    
    /**
     * Apply theme to all dropdowns
     */
    function applyThemeToDropdowns(theme) {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            // Add theme classes
            menu.classList.remove('theme-dark', 'theme-light');
            menu.classList.add('theme-' + theme);
            
            // Make sure dropdown items have correct colors
            menu.querySelectorAll('.dropdown-item').forEach(item => {
                item.style.color = '';  // Remove inline styles to let CSS handle it
            });
        });
    }
    
    /**
     * Apply theme to navbar
     */
    function applyThemeToNavbar(theme) {
        const navbar = document.querySelector('.layout-navbar');
        if (navbar) {
            // Add theme classes
            navbar.classList.remove('theme-dark', 'theme-light');
            navbar.classList.add('theme-' + theme);
            
            // Make sure navbar elements have correct spacing
            navbar.style.marginBottom = '1rem';
            
            // Fix user dropdown
            const userDropdown = navbar.querySelector('.dropdown-user');
            if (userDropdown) {
                // Ensure dropdown is clickable
                const dropdownToggle = userDropdown.querySelector('.dropdown-toggle');
                if (dropdownToggle) {
                    dropdownToggle.style.cursor = 'pointer';
                    dropdownToggle.style.pointerEvents = 'auto';
                }
            }
            
            // Fix theme toggle button
            const themeToggleBtn = navbar.querySelector('#theme-toggle-btn');
            if (themeToggleBtn) {
                themeToggleBtn.style.cursor = 'pointer';
                themeToggleBtn.style.pointerEvents = 'auto';
                
                // Update icons
                const lightIcon = themeToggleBtn.querySelector('.theme-icon-light');
                const darkIcon = themeToggleBtn.querySelector('.theme-icon-dark');
                
                if (lightIcon && darkIcon) {
                    if (theme === 'dark') {
                        lightIcon.classList.add('d-none');
                        darkIcon.classList.remove('d-none');
                    } else {
                        lightIcon.classList.remove('d-none');
                        darkIcon.classList.add('d-none');
                    }
                }
            }
        }
    }
    
    /**
     * Apply theme to sidebar
     */
    function applyThemeToSidebar(theme) {
        const sidebar = document.querySelector('.menu.menu-vertical');
        if (sidebar) {
            // Add theme classes
            sidebar.classList.remove('theme-dark', 'theme-light');
            sidebar.classList.add('theme-' + theme);
            
            // Make sure sidebar has correct z-index
            sidebar.style.zIndex = '2000';
            
            // Make sure menu items are clickable
            sidebar.querySelectorAll('.menu-item, .menu-link').forEach(item => {
                item.style.position = 'relative';
                item.style.zIndex = '20';
                item.style.pointerEvents = 'auto';
            });
            
            // Set up correct logos
            const lightLogo = sidebar.querySelector('.light-logo');
            const darkLogo = sidebar.querySelector('.dark-logo');
            
            if (lightLogo && darkLogo) {
                if (theme === 'dark') {
                    lightLogo.style.display = 'none';
                    darkLogo.style.display = 'block';
                } else {
                    lightLogo.style.display = 'block';
                    darkLogo.style.display = 'none';
                }
            }
        }
    }
    
    /**
     * Fix card spacing issues
     */
    function fixCardSpacing() {
        // Add proper spacing to card rows
        document.querySelectorAll('.row').forEach(row => {
            row.style.marginBottom = '1.5rem';
        });
        
        // Fix spacing between cards
        document.querySelectorAll('.card').forEach(card => {
            card.style.marginBottom = '1.5rem';
        });
        
        // Add proper padding to container
        document.querySelectorAll('.container-xxl').forEach(container => {
            container.style.padding = '1.5rem';
        });
    }
    
    /**
     * Initialize Bootstrap components and other functionalities
     */
    function initializeComponents() {
        // Initialize Bootstrap components if available
        if (typeof bootstrap !== 'undefined') {
            // Initialize dropdowns
            if (bootstrap.Dropdown) {
                document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(el => {
                    new bootstrap.Dropdown(el);
                });
            }
            
            // Initialize tooltips
            if (bootstrap.Tooltip) {
                document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                    new bootstrap.Tooltip(el);
                });
            }
            
            // Initialize popovers
            if (bootstrap.Popover) {
                document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => {
                    new bootstrap.Popover(el);
                });
            }
        }
        
        // Fix dropdown toggle functionality with manual handling if Bootstrap isn't working
        document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const dropdown = this.nextElementSibling;
                if (dropdown && dropdown.classList.contains('dropdown-menu')) {
                    if (dropdown.classList.contains('show')) {
                        dropdown.classList.remove('show');
                        dropdown.style.display = 'none';
                    } else {
                        // Close all other dropdowns
                        document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                            menu.classList.remove('show');
                            menu.style.display = 'none';
                        });
                        
                        // Show this dropdown
                        dropdown.classList.add('show');
                        dropdown.style.display = 'block';
                    }
                }
            });
        });
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                    menu.classList.remove('show');
                    menu.style.display = 'none';
                });
            }
        });
    }
    
    /**
     * Get the current theme from various sources
     */
    function getCurrentTheme() {
        // Try to get theme from data attribute
        let theme = document.documentElement.getAttribute('data-theme');
        
        // Fallback to checking classes
        if (!theme) {
            if (document.documentElement.classList.contains('theme-dark') || document.body.classList.contains('theme-dark')) {
                theme = 'dark';
            } else if (document.documentElement.classList.contains('theme-light') || document.body.classList.contains('theme-light')) {
                theme = 'light';
            }
        }
        
        // Try localStorage
        if (!theme) {
            try {
                theme = localStorage.getItem('wa_theme');
            } catch (e) {
                console.warn('Could not access localStorage', e);
            }
        }
        
        // Try cookie
        if (!theme) {
            theme = getCookie('wa_theme');
        }
        
        // Default to dark
        return theme || 'dark';
    }
    
    /**
     * Get a cookie value by name
     */
    function getCookie(name) {
        const nameEQ = name + '=';
        const ca = document.cookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }
});

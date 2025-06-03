// assets/js/theme-switcher.js
(function() {
  const themeToggle = document.getElementById('theme-toggle');
  const htmlElement = document.documentElement; // The <html> element
  const bodyElement = document.body; // The <body> element

  // Function to apply theme
  function applyTheme(theme) {
    WA_Logger.info('ThemeSwitcher', `Applying theme: ${theme}`);
    htmlElement.setAttribute('data-bs-theme', theme);
    bodyElement.setAttribute('data-bs-theme', theme); // Apply to body as well for consistency
    
    // Update specific classes if needed (e.g., for older components)
    if (theme === 'dark') {
      bodyElement.classList.add('theme-dark');
      bodyElement.classList.remove('theme-light');
    } else {
      bodyElement.classList.add('theme-light');
      bodyElement.classList.remove('theme-dark');
    }

    // Dispatch a custom event for other components to react to theme changes
    document.dispatchEvent(new CustomEvent('wa.theme.changed', { detail: { theme: theme } }));
  }

  // Function to get stored theme
  function getStoredTheme() {
    if (WA_Config.get('theme.persistTheme', true)) {
      return localStorage.getItem(WA_Config.get('theme.themeStorageKey', 'wa_theme'));
    }
    return null;
  }

  // Function to set stored theme
  function setStoredTheme(theme) {
    if (WA_Config.get('theme.persistTheme', true)) {
      localStorage.setItem(WA_Config.get('theme.themeStorageKey', 'wa_theme'), theme);
    }
  }

  // Initialize theme on load
  const initialTheme = getStoredTheme() || WA_Config.get('theme.defaultTheme', 'dark');
  applyTheme(initialTheme);

  // Set initial state of the toggle button
  if (themeToggle) {
    themeToggle.checked = (initialTheme === 'dark');
  }

  // Event listener for theme toggle
  if (themeToggle) {
    themeToggle.addEventListener('change', function() {
      const newTheme = this.checked ? 'dark' : 'light';
      applyTheme(newTheme);
      setStoredTheme(newTheme);
    });
  }

  WA_Logger.info('ThemeSwitcher', 'Theme switcher initialized');
})();

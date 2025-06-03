const WA_Logger = (function() {
  const LOG_LEVELS = {
    ERROR: 0,
    WARN: 1,
    INFO: 2,
    DEBUG: 3
  };
  
  // Can be changed via localStorage for debugging
  let currentLevel = LOG_LEVELS.INFO;
  
  // Try to get level from localStorage
  try {
    const storedLevel = localStorage.getItem('wa_log_level');
    if (storedLevel && LOG_LEVELS[storedLevel] !== undefined) {
      currentLevel = LOG_LEVELS[storedLevel];
    }
  } catch (e) {
    // Ignore localStorage errors
  }
  
  function formatMessage(component, message) {
    return `[${component}] ${message}`;
  }
  
  function error(component, message, ...args) {
    if (currentLevel >= LOG_LEVELS.ERROR) {
      console.error(formatMessage(component, message), ...args);
    }
  }
  
  function warn(component, message, ...args) {
    if (currentLevel >= LOG_LEVELS.WARN) {
      console.warn(formatMessage(component, message), ...args);
    }
  }
  
  function info(component, message, ...args) {
    if (currentLevel >= LOG_LEVELS.INFO) {
      console.info(formatMessage(component, message), ...args);
    }
  }
  
  function debug(component, message, ...args) {
    if (currentLevel >= LOG_LEVELS.DEBUG) {
      console.debug(formatMessage(component, message), ...args);
    }
  }
  
  function setLevel(level) {
    if (LOG_LEVELS[level] !== undefined) {
      currentLevel = LOG_LEVELS[level];
      try {
        localStorage.setItem('wa_log_level', level);
      } catch (e) {
        // Ignore localStorage errors
      }
    }
  }
  
  return {
    error,
    warn,
    info,
    debug,
    setLevel,
    LEVELS: LOG_LEVELS
  };
})();

// Make it global
window.WA_Logger = WA_Logger;

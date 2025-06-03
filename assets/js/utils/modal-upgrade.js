(function() {
  function upgradeModalTriggers() {
    WA_Logger.info('ModalUpgrade', 'Scanning for old-style modal triggers');
    
    // Find all buttons/links with onclick handlers containing modal show
    const oldTriggers = document.querySelectorAll('[onclick*="Modal"]');
    let upgradeCount = 0;
    
    oldTriggers.forEach(trigger => {
      const onclickValue = trigger.getAttribute('onclick');
      
      // Check if it's a modal trigger
      if (onclickValue.includes('.show(') || onclickValue.includes('.modal(')) {
        WA_Logger.debug('ModalUpgrade', `Found old-style trigger: ${onclickValue}`);
        
        // Extract modal ID from the onclick handler
        const modalIdMatch = onclickValue.match(/['"]([^'"]+)['"]\)/);
        if (modalIdMatch && modalIdMatch[1]) {
          const modalId = modalIdMatch[1];
          
          // Check if there are parameters being passed in the onclick
          const dataParams = {};
          const paramMatches = onclickValue.match(/\w+\(['"]([^'"]+)['"],\s*['"]([^'"]+)['"]\)/g);
          if (paramMatches) {
            paramMatches.forEach(match => {
              const parts = match.match(/(\w+)\(['"]([^'"]+)['"],\s*['"]([^'"]+)['"]\)/);
              if (parts && parts.length >= 4) {
                const funcName = parts[1];
                const paramName = parts[2];
                const paramValue = parts[3];
                
                // If this is a data-loading function, add the parameter as a data attribute
                if (funcName.includes('load') || funcName.includes('get')) {
                  dataParams[`data-${paramName.toLowerCase()}`] = paramValue;
                }
              }
            });
          }
          
          // Update the trigger
          trigger.removeAttribute('onclick');
          trigger.setAttribute('data-modal-target', modalId);
          trigger.setAttribute('data-modal-action', 'show');
          
          // Add any extracted data attributes
          for (const [key, value] of Object.entries(dataParams)) {
            trigger.setAttribute(key, value);
          }
          
          upgradeCount++;
          WA_Logger.info('ModalUpgrade', `Upgraded trigger for modal ${modalId}`);
        }
      }
    });
    
    WA_Logger.info('ModalUpgrade', `Upgraded ${upgradeCount} modal triggers`);
  }
  
  // Run upgrade when the DOM is ready
  document.addEventListener('DOMContentLoaded', upgradeModalTriggers);
})();

/**
 * Water Academy Modal Fix
 * Ensures modal dialogs work correctly with jQuery fallback
 * Updated: May 30, 2025
 */

document.addEventListener('DOMContentLoaded', function() {
  // Check if jQuery is available
  const hasJQuery = (typeof $ !== 'undefined') || (typeof jQuery !== 'undefined');
  
  // Set $ to jQuery if only jQuery is defined
  if (typeof $ === 'undefined' && typeof jQuery !== 'undefined') {
    $ = jQuery;
  }
  
  console.log('Modal fix initializing. jQuery available:', hasJQuery);
  
  // Fix for modals not working
  function fixModals() {
    console.log('Applying modal fixes');
    
    // First check if Bootstrap Modal is available
    const hasBootstrapModal = (typeof bootstrap !== 'undefined' && typeof bootstrap.Modal !== 'undefined');
    console.log('Bootstrap Modal available:', hasBootstrapModal);
    
    // Use jQuery approach for modals if available
    if (hasJQuery) {
      console.log('Using jQuery for modal functionality');
      
      // Fix modal triggers
      $('[data-bs-toggle="modal"]').off('click').on('click', function(e) {
        e.preventDefault();
        
        const targetSelector = $(this).attr('data-bs-target') || $(this).attr('href');
        console.log('Modal target:', targetSelector);
        
        if (targetSelector) {
          try {
            $(targetSelector).modal('show');
          } catch (error) {
            console.error('Error showing modal with jQuery:', error);
            
            // Try native Bootstrap as fallback
            if (hasBootstrapModal) {
              try {
                const modalEl = document.querySelector(targetSelector);
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
              } catch (bsError) {
                console.error('Bootstrap fallback also failed:', bsError);
                
                // Last resort: manual show
                $(targetSelector).addClass('show').css('display', 'block');
                $('body').addClass('modal-open');
                
                // Add backdrop
                if ($('.modal-backdrop').length === 0) {
                  $('body').append('<div class="modal-backdrop fade show"></div>');
                }
              }
            } else {
              // Manual show if Bootstrap is unavailable
              $(targetSelector).addClass('show').css('display', 'block');
              $('body').addClass('modal-open');
              
              // Add backdrop
              if ($('.modal-backdrop').length === 0) {
                $('body').append('<div class="modal-backdrop fade show"></div>');
              }
            }
          }
        }
      });
      
      // Fix modal dismiss buttons
      $('[data-bs-dismiss="modal"]').off('click').on('click', function(e) {
        e.preventDefault();
        
        const modal = $(this).closest('.modal');
        
        try {
          modal.modal('hide');
        } catch (error) {
          console.error('Error hiding modal with jQuery:', error);
          
          // Manual hide
          modal.removeClass('show').css('display', 'none');
          $('body').removeClass('modal-open');
          $('.modal-backdrop').remove();
        }
      });
      
    } else {
      console.log('Using native DOM API for modal functionality');
      
      // Fallback to native DOM API if jQuery is not available
      // Create a simple shim for bootstrap.Modal if it doesn't exist
      if (!hasBootstrapModal) {
        console.warn('Bootstrap Modal not found - creating shim');
        
        window.bootstrap = window.bootstrap || {};
        window.bootstrap.Modal = window.bootstrap.Modal || function(element) {
          return {
            show: function() {
              console.log('Modal show called via shim');
              element.style.display = 'block';
              element.classList.add('show');
              document.body.classList.add('modal-open');
              
              // Create backdrop if it doesn't exist
              let backdrop = document.querySelector('.modal-backdrop');
              if (!backdrop) {
                backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                document.body.appendChild(backdrop);
              }
            },
            hide: function() {
              element.style.display = 'none';
              element.classList.remove('show');
              document.body.classList.remove('modal-open');
              
              // Remove backdrop
              const backdrop = document.querySelector('.modal-backdrop');
              if (backdrop) {
                backdrop.remove();
              }
            }
          };
        };
      }
      
      // Fix modal triggers
      const modalTriggers = document.querySelectorAll('[data-bs-toggle="modal"]');
      modalTriggers.forEach(function(trigger) {
        // Remove existing click listeners to prevent duplication
        const newTrigger = trigger.cloneNode(true);
        trigger.parentNode.replaceChild(newTrigger, trigger);
        
        newTrigger.addEventListener('click', function(e) {
          e.preventDefault();
          
          const targetSelector = this.getAttribute('data-bs-target') || 
                                this.getAttribute('href');
          
          if (targetSelector) {
            const modalElement = document.querySelector(targetSelector);
            if (modalElement) {
              try {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
              } catch (error) {
                console.error('Error showing modal:', error);
                
                // Fallback if Bootstrap Modal fails
                modalElement.style.display = 'block';
                modalElement.classList.add('show');
                document.body.classList.add('modal-open');
                
                // Create backdrop
                let backdrop = document.querySelector('.modal-backdrop');
                if (!backdrop) {
                  backdrop = document.createElement('div');
                  backdrop.className = 'modal-backdrop fade show';
                  document.body.appendChild(backdrop);
                }
              }
            } else {
              console.warn('Modal element not found:', targetSelector);
            }
          }
        });
      });
      
      // Fix modal dismiss buttons
      const modalDismissButtons = document.querySelectorAll('[data-bs-dismiss="modal"]');
      modalDismissButtons.forEach(function(button) {
        // Remove existing click listeners to prevent duplication
        const newButton = button.cloneNode(true);
        button.parentNode.replaceChild(newButton, button);
        
        newButton.addEventListener('click', function() {
          const modalElement = this.closest('.modal');
          if (modalElement) {
            try {
              const modalInstance = bootstrap.Modal.getInstance(modalElement);
              if (modalInstance) {
                modalInstance.hide();
              } else {
                // Fallback if no modal instance
                modalElement.style.display = 'none';
                modalElement.classList.remove('show');
                document.body.classList.remove('modal-open');
                
                // Remove backdrop
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                  backdrop.remove();
                }
              }
            } catch (error) {
              console.error('Error hiding modal:', error);
              
              // Fallback if Bootstrap Modal fails
              modalElement.style.display = 'none';
              modalElement.classList.remove('show');
              document.body.classList.remove('modal-open');
              
              // Remove backdrop
              const backdrop = document.querySelector('.modal-backdrop');
              if (backdrop) {
                backdrop.remove();
              }
            }
          }
        });
      });
    }
    
    // Make sure all modals have proper z-index and are clickable
    document.querySelectorAll('.modal').forEach(function(modal) {
      modal.style.zIndex = '1055'; // Higher than the default
    });
    
    // Ensure proper modal backdrop hiding
    document.addEventListener('click', function(event) {
      if (event.target.classList.contains('modal') && event.target.classList.contains('show')) {
        const backdrop = document.querySelector('.modal-backdrop');
        
        if (hasJQuery) {
          try {
            $(event.target).modal('hide');
          } catch (error) {
            console.error('Error hiding modal on backdrop click:', error);
            
            // Manual hide
            $(event.target).removeClass('show').css('display', 'none');
            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();
          }
        } else {
          try {
            const modal = bootstrap.Modal.getInstance(event.target);
            if (modal) {
              modal.hide();
            } else {
              // Fallback
              event.target.style.display = 'none';
              event.target.classList.remove('show');
              document.body.classList.remove('modal-open');
              if (backdrop) backdrop.remove();
            }
          } catch (error) {
            console.error('Error hiding modal on backdrop click:', error);
            
            // Manual hide
            event.target.style.display = 'none';
            event.target.classList.remove('show');
            document.body.classList.remove('modal-open');
            if (backdrop) backdrop.remove();
          }
        }
      }
    });
    
    console.log('Modal fixes applied');
  }
  
  // Apply fixes immediately
  fixModals();
  
  // Also apply when jQuery is fully loaded (for deferred loading)
  if (typeof $ !== 'undefined') {
    $(document).ready(fixModals);
  }
  
  // Reapply fixes when dynamically loading content
  document.addEventListener('DOMNodeInserted', function(e) {
    if (e.target.querySelector && 
        (e.target.querySelector('[data-bs-toggle="modal"]') || 
         e.target.querySelector('[data-bs-dismiss="modal"]') ||
         e.target.querySelector('.modal'))) {
      setTimeout(fixModals, 10); // Small delay to ensure elements are fully inserted
    }
  });
  
  // Try to load jQuery if not already available
  if (!hasJQuery) {
    console.log('jQuery not found, attempting to load it');
    
    const script = document.createElement('script');
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js';
    script.onload = function() {
      console.log('jQuery loaded successfully');
      $ = jQuery;
      
      // Load Bootstrap if needed
      if (typeof bootstrap === 'undefined' || !bootstrap.Modal) {
        const bsScript = document.createElement('script');
        bsScript.src = 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.min.js';
        bsScript.onload = function() {
          console.log('Bootstrap loaded successfully');
          setTimeout(fixModals, 100);
        };
        document.head.appendChild(bsScript);
      } else {
        setTimeout(fixModals, 100);
      }
    };
    document.head.appendChild(script);
  }
});

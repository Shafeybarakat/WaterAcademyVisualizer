/**
 * Water Academy DataTable Handler
 * Unified data table functionality for all application pages
 * 
 * Usage:
 * 1. Include this file in your page
 * 2. Add data-table attribute to your table
 * 3. Add data-sort attribute to table headers that should be sortable
 * 4. Add data-search-input attribute to search input field
 * 5. Add data-search-target attribute to the input with the ID of the table to search
 */

// Create namespace to avoid global conflicts
const WA_DataTable = (function() {
  // Store table configurations
  const tableConfigs = {};
  
  /**
   * Initialize data table functionality
   */
  function init() {
    // Initialize all tables with data-table attribute
    const tables = document.querySelectorAll('[data-table]');
    tables.forEach(table => {
      const tableId = table.id;
      if (!tableId) {
        console.error('Table with data-table attribute must have an ID');
        return;
      }
      
      // Initialize table
      initTable(tableId);
    });
    
    // Initialize search inputs
    const searchInputs = document.querySelectorAll('[data-search-input]');
    searchInputs.forEach(input => {
      const targetId = input.getAttribute('data-search-target');
      if (!targetId) {
        console.error('Search input must have data-search-target attribute');
        return;
      }
      
      // Initialize search
      initSearch(input, targetId);
    });
  }
  
  /**
   * Initialize a data table
   * @param {string} tableId - The ID of the table to initialize
   * @param {Object} options - Configuration options
   */
  function initTable(tableId, options = {}) {
    const table = document.getElementById(tableId);
    if (!table) {
      console.error(`Table with ID '${tableId}' not found`);
      return;
    }
    
    // Store configuration
    tableConfigs[tableId] = {
      sortColumn: null,
      sortDirection: 'asc',
      searchTerm: '',
      options: options
    };
    
    // Initialize sorting
    initSorting(table);
    
    // Apply theme-specific styling
    applyThemeStyling(table);
    
    // Watch for theme changes
    watchThemeChanges(table);
  }
  
  /**
   * Initialize sorting for a table
   * @param {HTMLElement} table - The table element
   */
  function initSorting(table) {
    const headers = table.querySelectorAll('th[data-sort]');
    
    headers.forEach(header => {
      // Add sort indicator icon if not present
      if (!header.querySelector('.sort-icon')) {
        const icon = document.createElement('i');
        icon.className = 'bx bx-sort-alt-2 sort-icon ms-1';
        header.appendChild(icon);
      }
      
      // Add pointer cursor
      header.style.cursor = 'pointer';
      
      // Add click event
      header.addEventListener('click', function() {
        const sortKey = this.getAttribute('data-sort');
        const tableId = table.id;
        const config = tableConfigs[tableId];
        
        // Toggle sort direction if same column
        if (config.sortColumn === sortKey) {
          config.sortDirection = config.sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
          config.sortColumn = sortKey;
          config.sortDirection = 'asc';
        }
        
        // Update sort indicators
        updateSortIndicators(table, sortKey, config.sortDirection);
        
        // Sort the table
        sortTable(table, sortKey, config.sortDirection);
      });
    });
  }
  
  /**
   * Update sort indicators in table headers
   * @param {HTMLElement} table - The table element
   * @param {string} sortKey - The sort key
   * @param {string} direction - The sort direction ('asc' or 'desc')
   */
  function updateSortIndicators(table, sortKey, direction) {
    const headers = table.querySelectorAll('th[data-sort]');
    
    headers.forEach(header => {
      const icon = header.querySelector('.sort-icon');
      if (icon) {
        if (header.getAttribute('data-sort') === sortKey) {
          icon.className = direction === 'asc' 
            ? 'bx bx-sort-up sort-icon ms-1 text-primary' 
            : 'bx bx-sort-down sort-icon ms-1 text-primary';
        } else {
          icon.className = 'bx bx-sort-alt-2 sort-icon ms-1 text-muted';
        }
      }
    });
  }
  
  /**
   * Sort a table by column
   * @param {HTMLElement} table - The table element
   * @param {string} sortKey - The sort key
   * @param {string} direction - The sort direction ('asc' or 'desc')
   */
  function sortTable(table, sortKey, direction) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    // Find column index based on the sort key
    const headers = table.querySelectorAll('th');
    let columnIndex = -1;
    
    for (let i = 0; i < headers.length; i++) {
      if (headers[i].getAttribute('data-sort') === sortKey) {
        columnIndex = i;
        break;
      }
    }
    
    if (columnIndex === -1) {
      console.error(`Column with sort key '${sortKey}' not found`);
      return;
    }
    
    // Sort rows
    rows.sort((rowA, rowB) => {
      let a = rowA.cells[columnIndex].textContent.trim();
      let b = rowB.cells[columnIndex].textContent.trim();
      
      // Try to detect value type
      if (!isNaN(a) && !isNaN(b)) {
        // Numeric comparison
        a = parseFloat(a);
        b = parseFloat(b);
      } else if (a.includes('/') && b.includes('/') && !isNaN(Date.parse(a)) && !isNaN(Date.parse(b))) {
        // Date comparison
        a = new Date(a);
        b = new Date(b);
      }
      
      // Compare values
      if (a < b) return direction === 'asc' ? -1 : 1;
      if (a > b) return direction === 'asc' ? 1 : -1;
      return 0;
    });
    
    // Reorder rows in the table
    rows.forEach(row => tbody.appendChild(row));
  }
  
  /**
   * Initialize search for a table
   * @param {HTMLElement} input - The search input element
   * @param {string} tableId - The ID of the table to search
   */
  function initSearch(input, tableId) {
    const table = document.getElementById(tableId);
    if (!table) {
      console.error(`Table with ID '${tableId}' not found`);
      return;
    }
    
    input.addEventListener('input', function() {
      const searchTerm = this.value.toLowerCase().trim();
      
      // Update configuration
      if (tableConfigs[tableId]) {
        tableConfigs[tableId].searchTerm = searchTerm;
      }
      
      // Filter rows
      const rows = table.querySelectorAll('tbody tr');
      
      rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const visible = text.includes(searchTerm);
        
        row.style.display = visible ? '' : 'none';
      });
    });
  }
  
  /**
   * Apply theme-specific styling to a table
   * @param {HTMLElement} table - The table element
   */
  function applyThemeStyling(table) {
    const isDarkTheme = document.body.classList.contains('theme-dark');
    
    // Apply styling based on theme
    if (isDarkTheme) {
      applyDarkThemeStyling(table);
    } else {
      applyLightThemeStyling(table);
    }
  }
  
  /**
   * Apply dark theme styling to a table
   * @param {HTMLElement} table - The table element
   */
  function applyDarkThemeStyling(table) {
    // Style table headers
    const headers = table.querySelectorAll('th');
    headers.forEach(header => {
      header.style.color = '#f1f5f9'; // Light text for dark theme
    });
    
    // Style table rows for better visibility
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(row => {
      row.addEventListener('mouseover', function() {
        this.style.backgroundColor = 'rgba(255, 255, 255, 0.05)';
      });
      
      row.addEventListener('mouseout', function() {
        this.style.backgroundColor = '';
      });
    });
  }
  
  /**
   * Apply light theme styling to a table
   * @param {HTMLElement} table - The table element
   */
  function applyLightThemeStyling(table) {
    // Style table headers
    const headers = table.querySelectorAll('th');
    headers.forEach(header => {
      header.style.color = '#0f172a'; // Dark text for light theme
    });
    
    // Style table rows for better visibility
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(row => {
      row.addEventListener('mouseover', function() {
        this.style.backgroundColor = 'rgba(0, 0, 0, 0.025)';
      });
      
      row.addEventListener('mouseout', function() {
        this.style.backgroundColor = '';
      });
    });
  }
  
  /**
   * Watch for theme changes and update styling
   * @param {HTMLElement} table - The table element
   */
  function watchThemeChanges(table) {
    // Create a mutation observer to watch for theme changes
    const observer = new MutationObserver(mutations => {
      mutations.forEach(mutation => {
        if (mutation.attributeName === 'class' && mutation.target === document.body) {
          applyThemeStyling(table);
        }
      });
    });
    
    // Start observing
    observer.observe(document.body, { attributes: true });
  }
  
  /**
   * Refresh a table after data changes
   * @param {string} tableId - The ID of the table to refresh
   */
  function refreshTable(tableId) {
    const table = document.getElementById(tableId);
    if (!table) {
      console.error(`Table with ID '${tableId}' not found`);
      return;
    }
    
    // If there's an active sort, re-apply it
    const config = tableConfigs[tableId];
    if (config && config.sortColumn) {
      sortTable(table, config.sortColumn, config.sortDirection);
    }
    
    // If there's an active search, re-apply it
    if (config && config.searchTerm) {
      const rows = table.querySelectorAll('tbody tr');
      
      rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const visible = text.includes(config.searchTerm);
        
        row.style.display = visible ? '' : 'none';
      });
    }
    
    // Apply theme styling
    applyThemeStyling(table);
  }
  
  // Initialize when the DOM is ready
  document.addEventListener('DOMContentLoaded', init);
  
  // Public API
  return {
    init,
    initTable,
    refreshTable,
    sortTable
  };
})();

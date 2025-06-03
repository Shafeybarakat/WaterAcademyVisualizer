/**
 * Water Academy Table Handler (wa-table.js)
 * Unified table functionality for the Water Academy application
 * 
 * This consolidated file combines table handling functionality from various files.
 * Refactored to remove direct style manipulation for theming.
 * 
 * Features:
 * - Sorting by clicking on column headers
 * - Live search filtering
 * - Responsive design support
 * - Dark/light theme compatibility (via CSS classes)
 * - Pagination support
 * 
 * Usage:
 * 1. Include this file in your page
 * 2. Add the 'data-table' attribute to your table
 * 3. Add 'data-sort="key"' to sortable column headers
 * 4. Add 'data-search-input' and 'data-search-target="tableId"' to search inputs
 */

// Create namespace to avoid global conflicts
// Assign to global scope for direct access
window.WA_Table = (function() {
  // Store table configurations
  const tableConfigs = {};
  
  /**
   * Initialize table functionality
   */
  function init() {
    console.log('WA_Table: Initializing table handler');
    
    // Initialize all tables with data-table attribute
    const tables = document.querySelectorAll('[data-table]');
    tables.forEach(table => {
      const tableId = table.id;
      if (!tableId) {
        console.warn('WA_Table: Table with data-table attribute must have an ID');
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
        console.warn('WA_Table: Search input must have data-search-target attribute');
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
    console.log(`WA_Table: Initializing table "${tableId}"`);
    
    const table = document.getElementById(tableId);
    if (!table) {
      console.error(`WA_Table: Table with ID '${tableId}' not found`);
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
    
    // Trigger an initialized event
    table.dispatchEvent(new CustomEvent('wa.table.initialized', {
      bubbles: true,
      detail: { tableId, config: tableConfigs[tableId] }
    }));
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
        icon.className = 'bx bx-sort-alt-2 sort-icon ms-1 text-muted';
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
        
        // Trigger a sorted event
        table.dispatchEvent(new CustomEvent('wa.table.sorted', {
          bubbles: true,
          detail: { 
            tableId: table.id, 
            sortColumn: sortKey, 
            sortDirection: config.sortDirection 
          }
        }));
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
      console.error(`WA_Table: Column with sort key '${sortKey}' not found`);
      return;
    }
    
    // Sort rows
    rows.sort((rowA, rowB) => {
      let cellA = rowA.cells[columnIndex];
      let cellB = rowB.cells[columnIndex];
      
      // Get cell content for comparison
      let a, b;
      
      // Check if there's a strong element (for name columns that might have additional information)
      const strongA = cellA.querySelector('strong');
      const strongB = cellB.querySelector('strong');
      
      if (strongA && strongB) {
        a = strongA.textContent.trim();
        b = strongB.textContent.trim();
      } else {
        a = cellA.textContent.trim();
        b = cellB.textContent.trim();
      }
      
      // Try to detect value type
      if (!isNaN(a) && !isNaN(b)) {
        // Numeric comparison
        a = parseFloat(a);
        b = parseFloat(b);
      } else if (isDateString(a) && isDateString(b)) {
        // Date comparison
        a = new Date(a);
        b = new Date(b);
      }
      
      // Special handling for 'Never' in date fields
      if (a === 'Never' && isDateString(b)) return direction === 'asc' ? -1 : 1;
      if (b === 'Never' && isDateString(a)) return direction === 'asc' ? 1 : -1;
      
      // Compare values
      if (a < b) return direction === 'asc' ? -1 : 1;
      if (a > b) return direction === 'asc' ? 1 : -1;
      return 0;
    });
    
    // Reorder rows in the table
    rows.forEach(row => tbody.appendChild(row));
  }
  
  /**
   * Check if a string is a valid date
   * @param {string} str - The string to check
   * @returns {boolean} True if the string is a valid date
   */
  function isDateString(str) {
    // Check for common date formats
    if (!str) return false;
    
    // Check if it's a date with slashes, dashes, or other common formats
    if (/^\d{1,4}[-/\.]\d{1,2}[-/\.]\d{1,4}/.test(str)) return true;
    
    // Check for month names like "Jan 5, 2023" or "January 5, 2023"
    const monthNames = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
    const lowerStr = str.toLowerCase();
    
    for (const month of monthNames) {
      if (lowerStr.startsWith(month)) return true;
    }
    
    // Try to parse the date
    const date = new Date(str);
    return !isNaN(date) && date.toString() !== 'Invalid Date';
  }
  
  /**
   * Initialize search for a table
   * @param {HTMLElement} input - The search input element
   * @param {string} tableId - The ID of the table to search
   */
  function initSearch(input, tableId) {
    console.log(`WA_Table: Initializing search for table "${tableId}"`);
    
    const table = document.getElementById(tableId);
    if (!table) {
      console.error(`WA_Table: Table with ID '${tableId}' not found`);
      return;
    }
    
    input.addEventListener('input', function() {
      const searchTerm = this.value.toLowerCase().trim();
      
      // Update configuration
      if (tableConfigs[tableId]) {
        tableConfigs[tableId].searchTerm = searchTerm;
      }
      
      // Filter rows
      filterRows(table, searchTerm);
      
      // Trigger a filtered event
      table.dispatchEvent(new CustomEvent('wa.table.filtered', {
        bubbles: true,
        detail: { 
          tableId: tableId, 
          searchTerm: searchTerm 
        }
      }));
    });
  }
  
  /**
   * Filter table rows based on search term
   * @param {HTMLElement} table - The table element
   * @param {string} searchTerm - The search term
   */
  function filterRows(table, searchTerm) {
    const rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
      const text = row.textContent.toLowerCase();
      const visible = text.includes(searchTerm);
      
      row.style.display = visible ? '' : 'none';
    });
  }
  
  /**
   * Refresh a table after data changes
   * @param {string} tableId - The ID of the table to refresh
   */
  function refreshTable(tableId) {
    console.log(`WA_Table: Refreshing table "${tableId}"`);
    
    const table = document.getElementById(tableId);
    if (!table) {
      console.error(`WA_Table: Table with ID '${tableId}' not found`);
      return;
    }
    
    // If there's an active sort, re-apply it
    const config = tableConfigs[tableId];
    if (config && config.sortColumn) {
      sortTable(table, config.sortColumn, config.sortDirection);
    }
    
    // If there's an active search, re-apply it
    if (config && config.searchTerm) {
      filterRows(table, config.searchTerm);
    }
    
    // Trigger a refreshed event
    table.dispatchEvent(new CustomEvent('wa.table.refreshed', {
      bubbles: true,
      detail: { tableId: tableId }
    }));
  }
  
  /**
   * Add a row to a table
   * @param {string} tableId - The ID of the table
   * @param {Array} rowData - Array of cell contents
   * @param {boolean} prepend - Whether to add the row at the top (true) or bottom (false)
   * @returns {HTMLElement} The created row element
   */
  function addRow(tableId, rowData, prepend = false) {
    console.log(`WA_Table: Adding row to table "${tableId}"`);
    
    const table = document.getElementById(tableId);
    if (!table) {
      console.error(`WA_Table: Table with ID '${tableId}' not found`);
      return null;
    }
    
    const tbody = table.querySelector('tbody');
    if (!tbody) {
      console.error(`WA_Table: Table body not found in table "${tableId}"`);
      return null;
    }
    
    // Create the new row
    const row = document.createElement('tr');
    
    // Add cells
    rowData.forEach(cellContent => {
      const cell = document.createElement('td');
      
      // Check if cellContent is HTML or plain text
      if (cellContent.indexOf('<') !== -1 && cellContent.indexOf('>') !== -1) {
        cell.innerHTML = cellContent;
      } else {
        cell.textContent = cellContent;
      }
      
      row.appendChild(cell);
    });
    
    // Add the row to the table
    if (prepend && tbody.firstChild) {
      tbody.insertBefore(row, tbody.firstChild);
    } else {
      tbody.appendChild(row);
    }
    
    // Refresh the table
    refreshTable(tableId);
    
    return row;
  }
  
  /**
   * Update a table cell
   * @param {HTMLElement} row - The row element
   * @param {number} columnIndex - The column index
   * @param {string} content - The cell content
   */
  function updateCell(row, columnIndex, content) {
    if (!row || !row.cells || columnIndex >= row.cells.length) {
      console.error('WA_Table: Invalid row or column index');
      return;
    }
    
    const cell = row.cells[columnIndex];
    
    // Check if content is HTML or plain text
    if (content.indexOf('<') !== -1 && content.indexOf('>') !== -1) {
      cell.innerHTML = content;
    } else {
      cell.textContent = content;
    }
    
    // Refresh the table
    const table = row.closest('table');
    if (table && table.id) {
      refreshTable(table.id);
    }
  }
  
  /**
   * Delete a row from a table
   * @param {HTMLElement} row - The row element to delete
   */
  function deleteRow(row) {
    if (!row) {
      console.error('WA_Table: Invalid row');
      return;
    }
    
    const table = row.closest('table');
    const tableId = table ? table.id : null;
    
    // Remove the row
    row.parentNode.removeChild(row);
    
    // Refresh the table
    if (tableId) {
      refreshTable(tableId);
    }
  }
  
  /**
   * Get all visible rows in a table
   * @param {string} tableId - The ID of the table
   * @returns {Array} Array of visible row elements
   */
  function getVisibleRows(tableId) {
    const table = document.getElementById(tableId);
    if (!table) {
      console.error(`WA_Table: Table with ID '${tableId}' not found`);
      return [];
    }
    
    const rows = table.querySelectorAll('tbody tr');
    return Array.from(rows).filter(row => row.style.display !== 'none');
  }
  
  // Initialize when the DOM is ready
  // Removed: document.addEventListener('DOMContentLoaded', init);
  // Public API
  return {
    init: init, // Expose init function
    initTable,
    refreshTable,
    sortTable,
    filterRows,
    addRow,
    updateCell,
    deleteRow,
    getVisibleRows
  };
})(); // End of IIFE

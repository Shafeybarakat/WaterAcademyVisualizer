/**
 * Water Academy Main Application JavaScript (app.js)
 * New entry point for custom JavaScript modules.
 * Initializes core UI components and modules after dependencies are loaded.
 */

// Alpine.js layout data
function layout() {
  return {
    sidebarOpen: window.innerWidth >= 768, // Open on desktop, closed on mobile
    isDarkMode: false, // Default to light mode
    sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true', // Sidebar collapse state
    initLayout() {
      // Check for dark mode preference
      this.isDarkMode = localStorage.getItem('darkMode') === 'true';
      if (this.isDarkMode) {
        document.documentElement.classList.add('dark');
      }
      
      // Check for sidebar collapsed state
      if (this.sidebarCollapsed) {
        document.documentElement.classList.add('layout-menu-collapsed');
      }
      
      // Listen for sidebar toggle events
      window.addEventListener('sidebar-toggle', (event) => {
        const collapsed = event.detail.collapsed;
        localStorage.setItem('sidebarCollapsed', collapsed);
        
        if (collapsed) {
          document.documentElement.classList.add('layout-menu-collapsed');
        } else {
          document.documentElement.classList.remove('layout-menu-collapsed');
        }
      });
      
      // Add resize listener to handle responsive behavior
      window.addEventListener('resize', () => {
        // Don't auto-close sidebar on desktop when resizing window
        if (window.innerWidth >= 768 && !this.sidebarOpen) {
          this.sidebarOpen = true;
        }
      });
      
      // Force sidebar to be visible after a short delay on desktop
      setTimeout(() => {
        if (window.innerWidth >= 768) {
          this.sidebarOpen = true;
          // Force Alpine to update the DOM
          this.$nextTick(() => {
            console.log('Sidebar should be visible on desktop');
          });
        }
      }, 300);
      
      console.log('Layout initialized, sidebar state:', this.sidebarOpen, 'dark mode:', this.isDarkMode);
    },
    
    // Toggle dark mode
    toggleDarkMode() {
      this.isDarkMode = !this.isDarkMode;
      localStorage.setItem('darkMode', this.isDarkMode);
      
      if (this.isDarkMode) {
        document.documentElement.classList.add('dark');
      } else {
        document.documentElement.classList.remove('dark');
      }
    }
  };
}

// Function to fetch event details for modal
function fetchEventDetails(eventId, eventType) {
  const loading = document.getElementById('eventDetailsLoading');
  const error = document.getElementById('eventDetailsError');
  const content = document.getElementById('eventDetailsContent');
  const sendEmailBtn = document.getElementById('sendEmailBtn');
  const extendDateBtn = document.getElementById('extendDateBtn');
  
  if (!loading || !error || !content) return;
  
  // Show loading, hide others
  loading.classList.remove('hidden');
  error.classList.add('hidden');
  content.classList.add('hidden');
  
  if (sendEmailBtn) sendEmailBtn.classList.add('hidden');
  if (extendDateBtn) extendDateBtn.classList.add('hidden');
  
  fetch(`${BASE_URL}dashboards/get_event_details.php?event_id=${eventId}&event_type=${eventType}`)
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.json();
    })
    .then(data => {
      loading.classList.add('hidden');
      content.classList.remove('hidden');
      
      // Populate content
      content.innerHTML = data.content;
      
      // Show/hide action buttons
      if (data.allowEmail && sendEmailBtn) {
        sendEmailBtn.classList.remove('hidden');
      }
      
      if (data.allowExtend && extendDateBtn) {
        extendDateBtn.classList.remove('hidden');
      }
    })
    .catch(error => {
      loading.classList.add('hidden');
      errorContent.classList.remove('hidden');
      
      const errorMessage = document.getElementById('eventDetailsErrorMessage');
      if (errorMessage) {
        errorMessage.textContent = error.message;
      }
    });
}

// Utility to initialize a Doughnut chart
function initDoughnutChart(chartId, dataValue, hexColor) {
  const ctx = document.getElementById(chartId);
  if (!ctx) return;
  new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: [chartId, 'Remaining'],
      datasets: [{
        data: [dataValue, 100 - dataValue],
        backgroundColor: [hexColor, '#E5E7EB'],
      }]
    },
    options: {
      cutout: '70%',
      plugins: { legend: { display: false } }
    }
  });
}

// Utility for Bar chart (attendance summary)
function initBarChart(chartId, labelsArray, dataArray) {
  const ctx = document.getElementById(chartId);
  if (!ctx) return;
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labelsArray,
      datasets: [{
        label: 'Percentage',
        data: dataArray,
        backgroundColor: '#3B82F6',
      }]
    },
    options: {
      responsive: true,
      scales: { y: { beginAtZero: true, max: 100 } }
    }
  });
}

document.addEventListener('DOMContentLoaded', function() {
  console.log('app.js: Initializing Water Academy application.');

  // Initialize charts for Group Performance Report
  if (document.getElementById('chartAvgScore')) {
    // These values should come from PHP variables embedded in the HTML
    // For now, using dummy values as per the guide's example
    const avgScore = parseInt(document.getElementById('chartAvgScore').dataset.value || '0');
    const avgAttendance = parseInt(document.getElementById('chartAvgAttendance').dataset.value || '0');
    const avgLGI = parseInt(document.getElementById('chartAvgLGI').dataset.value || '0');

    initDoughnutChart('chartAvgScore', avgScore, '#3B82F6');
    initDoughnutChart('chartAvgAttendance', avgAttendance, '#10B981');
    initDoughnutChart('chartAvgLGI', avgLGI, '#F59E0B');
  }

  // Attendance summary bar chart
  if (document.getElementById('chartAttendanceSummary')) {
    const labels = JSON.parse(document.getElementById('chartAttendanceSummary').dataset.labels);
    const data = JSON.parse(document.getElementById('chartAttendanceSummary').dataset.values);
    initBarChart('chartAttendanceSummary', labels, data);
  }

  // Listen for saving attendance via Alpine event
  document.addEventListener('save-attendance', event => {
    const { id, present, absent } = event.detail;
    fetch(window.location.origin + BASE_URL + 'dashboards/save_attendance.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ AttendanceID: id, PresentHours: present, AbsentHours: absent }),
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        alert('Attendance updated.');
        location.reload();
      } else {
        alert('Error: ' + data.message);
      }
    });
  });

  console.log('app.js: Water Academy application initialization complete.');
});

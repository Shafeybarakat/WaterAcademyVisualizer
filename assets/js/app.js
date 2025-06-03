/**
 * Water Academy Main Application JavaScript (app.js)
 * New entry point for custom JavaScript modules.
 * Initializes core UI components and modules after dependencies are loaded.
 */

// Alpine.js layout data
function layout() {
  return {
    sidebarOpen: window.innerWidth >= 768,
    initLayout() {
      if (window.innerWidth < 768) {
        this.sidebarOpen = false;
      }
      window.addEventListener('resize', () => {
        if (window.innerWidth >= 768) {
          this.sidebarOpen = true;
        }
      });
    }
  };
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

  // Initialize UI Components module (only if present)
  if (typeof UI_Components_Module !== 'undefined' && typeof UI_Components_Module.init === 'function') {
    UI_Components_Module.init();
  } else if (document.body.classList.contains('login-page')) {
    // Suppress error for login page where UI_Components is not expected
  } else {
    console.error('app.js: UI_Components module not found or not initialized.');
  }

  // Initialize Layout module (only if present)
  if (typeof Layout_Module !== 'undefined' && typeof Layout_Module.init === 'function') {
    Layout_Module.init();
  } else if (document.body.classList.contains('login-page')) {
    // Suppress error for login page where Layout_Module is not expected
  } else {
    console.error('app.js: Layout_Module not found or not initialized.');
  }

  // Initialize WA_Table module (only if present)
  if (typeof WA_Table !== 'undefined' && typeof WA_Table.init === 'function') {
    WA_Table.init();
  } else if (document.body.classList.contains('login-page')) {
    // Suppress error for login page where WA_Table is not expected
  } else {
    console.error('app.js: WA_Table module not found or not initialized.');
  }

  // Initialize Theme Switcher (only if present)
  if (typeof initThemeSwitcher === 'function') {
    initThemeSwitcher();
  } else if (document.body.classList.contains('login-page')) {
    // Suppress warning for login page where Theme Switcher is not expected
  } else {
    console.warn('app.js: initThemeSwitcher function not found. Theme switching may not work.');
  }

  // Initialize Sidebar Toggle (only if present)
  if (typeof initSidebarToggle === 'function') {
    initSidebarToggle();
  } else if (document.body.classList.contains('login-page')) {
    // Suppress warning for login page where Sidebar Toggle is not expected
  } else {
    console.warn('app.js: initSidebarToggle function not found. Sidebar toggle may not work.');
  }

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
    fetch('/dashboards/save_attendance.php', {
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

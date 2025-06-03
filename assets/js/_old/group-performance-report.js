/**
 * Water Academy - Group Performance Report JavaScript
 * Modern, clean chart implementation inspired by Canva Pro style
 */

document.addEventListener("DOMContentLoaded", function() {
    // Check if Chart.js is properly loaded
    if (typeof Chart === 'undefined') {
        console.error("Chart.js is not loaded! Please check script inclusion.");
        
        // Add error message to chart containers
        const performanceChartEl = document.getElementById("performanceChart");
        const attendanceChartEl = document.getElementById("attendanceChart");
        
        if (performanceChartEl) {
            performanceChartEl.innerHTML = '<div class="alert alert-danger">Chart.js library failed to load. Please refresh the page or contact support.</div>';
        }
        
        if (attendanceChartEl) {
            attendanceChartEl.innerHTML = '<div class="alert alert-danger">Chart.js library failed to load. Please refresh the page or contact support.</div>';
        }
        
        return;
    }
    
    console.log("Chart.js loaded successfully, version:", Chart.version);

    // Parameters are now passed via URL from reports.php
    // Use URL parameters instead of form controls

    const reportContent = document.getElementById("reportContent");
    const reportPlaceholder = document.getElementById("reportPlaceholder");
    const performanceChartEl = document.getElementById("performanceChart");
    const attendanceChartEl = document.getElementById("attendanceChart");
    const traineeTableContainer = document.getElementById("traineeTableContainer");
    const traineeTableBody = document.getElementById("traineeTableBody");

    // Color theme inspired by Canva Pro examples
    const chartColors = {
        performance: ['#3aa5ff', '#54d889', '#a078ea', '#ffba49', '#ff6b6b'],
        attendance: {
            Present: '#54d889',  // Green
            Late: '#ffba49',     // Yellow
            Absent: '#ff6b6b',   // Red
            Excused: '#a078ea'   // Purple
        },
        background: '#ffffff',
        gridLines: '#f0f0f0'
    };

    // Chart instances
    let performanceChart = null;
    let attendanceChart = null;
    
    // Gauge instances with proper initialization
    let attendanceGauge = null;
    let lgiGauge = null;

    /**
     * Helper function to safely parse float values
     */
    function safeParseFloat(value, defaultValue = 0) {
        if (value === null || value === undefined || value === "") return defaultValue;
        const parsed = parseFloat(value);
        return isNaN(parsed) ? defaultValue : parsed;
    }

    /**
     * Updates the summary cards with data from the API
     */
    function updateSummaryCards(summary) {
        // Update trainee count with icon cluster
        const traineeCount = parseInt(summary.TraineeCount) || 0;
        document.getElementById("summaryTraineeCount").textContent = traineeCount;
        updateTraineeIconCluster(traineeCount);
        
        // Update attendance percentage with gauge
        const attendancePercent = safeParseFloat(summary.AvgAttendance, 0);
        document.getElementById("summaryAvgAttendance").textContent = attendancePercent.toFixed(1) + "%";
        updateAttendanceGauge(attendancePercent);
        
        // Update average score with gauge
        const avgScore = safeParseFloat(summary.AvgTotal, 0);
        document.getElementById("summaryAvgTotal").textContent = avgScore.toFixed(1);
        updateScoreGauge(avgScore);
        
        // Update LGI with gauge
        const lgiPercent = safeParseFloat(summary.AvgLGI, 0);
        document.getElementById("summaryAvgLGI").textContent = lgiPercent.toFixed(1) + "%";
        updateLGIGauge(lgiPercent);
    }

    /**
     * Creates and updates the icon cluster for trainee count
     */
    function updateTraineeIconCluster(count) {
        const container = document.getElementById("traineeIconCluster");
        if (!container) return;
        
        container.innerHTML = '';
        
        // Determine how many icons to show (max 15)
        const iconCount = Math.min(Math.max(count, 1), 15);
        
        // Create individual icons
        for (let i = 0; i < iconCount; i++) {
            const icon = document.createElement('i');
            icon.className = 'fas fa-user trainee-icon';
            container.appendChild(icon);
        }
        
        // If more than 15, add a +X indicator
        if (count > 15) {
            const moreIndicator = document.createElement('span');
            moreIndicator.className = 'trainee-icon-more';
            moreIndicator.textContent = '+' + (count - 15);
            container.appendChild(moreIndicator);
        }
    }

    /**
     * Updates the attendance gauge with new value
     */
    function updateAttendanceGauge(value) {
        const gaugeValue = document.getElementById('attendanceGaugeValue');
        if (gaugeValue) {
            gaugeValue.textContent = value.toFixed(1) + '%';
        }
        
        // Update gauge visualization (either the canvas gauge or the fallback)
        const fallbackFill = document.querySelector('.attendance-gauge-container .gauge-fallback-fill');
        if (fallbackFill) {
            fallbackFill.style.width = value + '%';
        } else if (typeof JustGage !== 'undefined' && attendanceGauge) { // Check for JustGage correctly
            attendanceGauge.set(value);
        }
    }

    /**
     * Updates the LGI gauge with new value
     */
    function updateLGIGauge(value) {
        const gaugeValue = document.getElementById('lgiGaugeValue');
        if (gaugeValue) {
            gaugeValue.textContent = value.toFixed(1) + '%';
        }
        
        // Update gauge visualization (either the canvas gauge or the fallback)
        const fallbackFill = document.querySelector('.lgi-gauge-container .gauge-fallback-fill');
        if (fallbackFill) {
            fallbackFill.style.width = value + '%';
        } else if (typeof JustGage !== 'undefined' && lgiGauge) { // Check for JustGage correctly
            lgiGauge.set(value);
        }
    }

    /**
     * Updates the score gauge with new value
     */
    function updateScoreGauge(value) {
        const scoreValue = document.getElementById('totalScoreValue');
        const scoreFill = document.getElementById('totalScoreFill');
        
        if (scoreValue) {
            scoreValue.textContent = value.toFixed(1);
        }
        
        if (scoreFill) {
            // Set width percentage based on value (assuming max 100)
            const percentage = Math.min(Math.max(value, 0), 100);
            scoreFill.style.width = percentage + '%';
        }
    }

    /**
     * Renders the performance distribution chart using Chart.js
     */
    function renderPerformanceChart(distribution) {
        console.log("Performance distribution data:", distribution);
        
        // Safety check for data
        if (!Array.isArray(distribution)) {
            console.error("Performance distribution data is not an array:", distribution);
            performanceChartEl.innerHTML = "<p class=\"text-danger\">Error: Performance data is in an unexpected format.</p>";
            return;
        }

        // Ensure we have a proper distribution array with expected structure
        if (distribution.length === 0) {
            // Create a default distribution if empty
            distribution = [
                {ScoreRange: "90-100 (A)", Count: 0},
                {ScoreRange: "80-89 (B)", Count: 0},
                {ScoreRange: "70-79 (C)", Count: 0},
                {ScoreRange: "60-69 (D)", Count: 0},
                {ScoreRange: "<60 (F)", Count: 0}
            ];
        }

        // Prepare data for Chart.js
        const expectedCategories = ["90-100 (A)", "80-89 (B)", "70-79 (C)", "60-69 (D)", "<60 (F)"];
        const dataMap = new Map(distribution.map(item => [item.ScoreRange, parseInt(item.Count) || 0]));
        
        // Make sure all categories are present
        expectedCategories.forEach(cat => {
            if (!dataMap.has(cat)) {
                dataMap.set(cat, 0);
            }
        });

        // Create data for chart
        const labels = expectedCategories;
        const data = labels.map(cat => dataMap.get(cat) || 0);
        
        // Clear existing chart
        if (performanceChartEl.chart) {
            performanceChartEl.chart.destroy();
        }

        // Create canvas element for chart
        performanceChartEl.innerHTML = '';
        const canvas = document.createElement('canvas');
        performanceChartEl.appendChild(canvas);

        try {
            // Create the chart with Chart.js
            const ctx = canvas.getContext('2d');
            performanceChartEl.chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Number of Trainees',
                        data: data,
                        backgroundColor: chartColors.performance,
                        borderRadius: 12,
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(255, 255, 255, 0.9)',
                            titleColor: '#333',
                            bodyColor: '#333',
                            borderColor: '#ddd',
                            borderWidth: 1,
                            cornerRadius: 8,
                            boxPadding: 6,
                            usePointStyle: true,
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.raw;
                                }
                            }
                        },
                        title: {
                            display: true,
                            text: 'Performance Distribution',
                            font: {
                                size: 16,
                                family: "'Poppins', sans-serif",
                                weight: '500'
                            },
                            padding: {
                                top: 10,
                                bottom: 20
                            },
                            color: '#333'
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    family: "'Poppins', sans-serif"
                                }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: chartColors.gridLines
                            },
                            ticks: {
                                precision: 0,
                                font: {
                                    family: "'Poppins', sans-serif"
                                }
                            }
                        }
                    }
                }
            });
            
            console.log("Performance chart rendered successfully");
        } catch (error) {
            console.error("Error rendering performance chart:", error);
            performanceChartEl.innerHTML = 
                `<p class="text-danger">Error rendering chart: ${error.message}</p>`;
        }
    }

    /**
     * Renders the attendance summary chart using Chart.js
     */
    function renderAttendanceChart(statusCounts) {
        console.log("Attendance status data:", statusCounts);
        
        // Safety check for data
        if (!Array.isArray(statusCounts)) {
            console.error("Attendance status data is not an array:", statusCounts);
            attendanceChartEl.innerHTML = "<p class=\"text-danger\">Error: Attendance data is in an unexpected format.</p>";
            return;
        }

        // Create default data if empty
        if (statusCounts.length === 0) {
            statusCounts = [
                {Status: "Present", Count: 1},
                {Status: "Late", Count: 0},
                {Status: "Absent", Count: 0},
                {Status: "Excused", Count: 0}
            ];
        }

        // Process data for the chart
        const labels = statusCounts.map(item => item.Status);
        const data = statusCounts.map(item => parseInt(item.Count) || 0);
        const backgroundColors = labels.map(label => chartColors.attendance[label] || '#808080');

        // Clear existing chart
        if (attendanceChartEl.chart) {
            attendanceChartEl.chart.destroy();
        }

        // Create canvas element for chart
        attendanceChartEl.innerHTML = '';
        const canvas = document.createElement('canvas');
        attendanceChartEl.appendChild(canvas);

        try {
            // Create the chart with Chart.js
            const ctx = canvas.getContext('2d');
            attendanceChartEl.chart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: backgroundColors,
                        borderWidth: 0,
                        offset: 5,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                boxWidth: 12,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                font: {
                                    family: "'Poppins', sans-serif",
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(255, 255, 255, 0.9)',
                            titleColor: '#333',
                            bodyColor: '#333',
                            borderColor: '#ddd',
                            borderWidth: 1,
                            cornerRadius: 8,
                            boxPadding: 6,
                            usePointStyle: true,
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((acc, curr) => acc + curr, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        },
                        title: {
                            display: true,
                            text: 'Attendance Status',
                            font: {
                                size: 16,
                                family: "'Poppins', sans-serif",
                                weight: '500'
                            },
                            padding: {
                                top: 10,
                                bottom: 20
                            },
                            color: '#333'
                        }
                    }
                }
            });
            console.log("Attendance chart rendered successfully");
        } catch (error) {
            console.error("Error rendering attendance chart:", error);
            attendanceChartEl.innerHTML = 
                `<p class="text-danger">Error rendering chart: ${error.message}</p>`;
        }
    }

    /**
     * Populates the trainee table with data
     */
    function populateTraineeTable(trainees) {
        if (!traineeTableBody) {
            console.error("Trainee table body not found!");
            return;
        }
        traineeTableBody.innerHTML = ''; // Clear existing rows

        if (!Array.isArray(trainees) || trainees.length === 0) {
            const row = traineeTableBody.insertRow();
            const cell = row.insertCell();
            cell.colSpan = 6; // Adjust colspan based on number of columns
            cell.textContent = 'No trainee data available for the selected filters.';
            cell.style.textAlign = 'center';
            return;
        }

        trainees.forEach(trainee => {
            const row = traineeTableBody.insertRow();
            row.insertCell().textContent = trainee.TraineeName || 'N/A';
            row.insertCell().textContent = safeParseFloat(trainee.PreTestScore).toFixed(1);
            row.insertCell().textContent = safeParseFloat(trainee.AvgQuizScore).toFixed(1);
            row.insertCell().textContent = safeParseFloat(trainee.FinalScore).toFixed(1);
            row.insertCell().textContent = safeParseFloat(trainee.TotalScore).toFixed(1);
            row.insertCell().textContent = safeParseFloat(trainee.AttendancePercentage).toFixed(1) + '%';
        });
    }

    /**
     * Initializes the gauges using the JustGage library if available, otherwise uses fallback
     */
    function initializeGauges() {
        console.log("Initializing gauges...");
        if (typeof JustGage !== 'undefined') {
            console.log("JustGage library found. Initializing JustGage instances.");
            try {
                if (document.getElementById("attendanceGauge")) {
                    attendanceGauge = new JustGage({
                        id: "attendanceGauge",
                        value: 0,
                        min: 0,
                        max: 100,
                        title: "Avg. Attendance",
                        label: "%",
                        levelColors: ["#ff6b6b", "#ffba49", "#54d889"],
                        pointer: true,
                        pointerOptions: {
                            toplength: -15,
                            bottomlength: 10,
                            bottomwidth: 12,
                            color: '#8e8e93',
                            stroke: '#ffffff',
                            stroke_width: 3,
                            stroke_linecap: 'round'
                        },
                        gaugeWidthScale: 0.6,
                        counter: true,
                        relativeGaugeSize: true
                    });
                }

                if (document.getElementById("lgiGauge")) {
                    lgiGauge = new JustGage({
                        id: "lgiGauge",
                        value: 0,
                        min: 0,
                        max: 100,
                        title: "Avg. LGI",
                        label: "%",
                        levelColors: ["#54d889", "#ffba49", "#ff6b6b"], // Green good, Red bad for LGI
                        pointer: true,
                        pointerOptions: {
                            toplength: -15,
                            bottomlength: 10,
                            bottomwidth: 12,
                            color: '#8e8e93',
                            stroke: '#ffffff',
                            stroke_width: 3,
                            stroke_linecap: 'round'
                        },
                        gaugeWidthScale: 0.6,
                        counter: true,
                        relativeGaugeSize: true
                    });
                }
                console.log("JustGage instances initialized.");
            } catch (e) {
                console.error("Error initializing JustGage instances:", e);
                // Fallback to simple display if JustGage fails
                attendanceGauge = null;
                lgiGauge = null;
                console.warn("JustGage initialization failed, gauges might not display correctly without fallback HTML.");
            }
        } else {
            console.warn("JustGage library not found. Gauges will use fallback display.");
            // Ensure fallback display is set up if JustGage is not used by report-fixes.js
        }
        // This log was misleading as this function primarily tries to init JustGage.
        // console.log("Fallback gauges initialized"); 
        console.log("Gauge initialization attempt complete.");
    }

    /**
     * Fetches report data from the API and updates the UI
     */
    async function fetchReportData() {
        console.log("Fetching report data...");
        
        // Get parameters from URL instead of form controls
        const urlParams = new URLSearchParams(window.location.search);
        const groupId = urlParams.get('group_id');
        const courseId = urlParams.get('course_id');
        const startDate = urlParams.get('start_date');
        const endDate = urlParams.get('end_date');

        if (!groupId || !courseId) {
            if (reportPlaceholder) {
                reportPlaceholder.textContent = "Missing required parameters. Please return to Reports page and select a Group and Course.";
                reportPlaceholder.classList.remove("alert-info");
                reportPlaceholder.classList.add("alert-warning");
            }
            if (reportContent) reportContent.style.display = "none";
            if (reportPlaceholder) reportPlaceholder.style.display = "";
            return;
        }

        if (reportPlaceholder) {
            reportPlaceholder.textContent = "Loading report data...";
            reportPlaceholder.classList.remove("alert-warning", "alert-danger");
            reportPlaceholder.classList.add("alert-info");
        }
        if (reportContent) reportContent.style.display = "none";
        if (reportPlaceholder) reportPlaceholder.style.display = "";

        const params = new URLSearchParams({
            group_id: groupId,
            course_id: courseId
        });
        if (startDate) params.append("start_date", startDate);
        if (endDate) params.append("end_date", endDate);

        try {
            // Updated API endpoint path to dashboards directory
            const response = await fetch(`../dashboards/get_group_report_data.php?${params.toString()}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();

            if (data.error) {
                throw new Error(data.error);
            }

            console.log("Data received:", data);

            if (reportContent) reportContent.style.display = "block";
            if (reportPlaceholder) reportPlaceholder.style.display = "none";

            // Update UI elements with fetched data
            if (data.summary) {
                updateSummaryCards(data.summary);
            }
            if (data.performance_distribution) {
                renderPerformanceChart(data.performance_distribution);
            }
            if (data.attendance_status_counts) {
                renderAttendanceChart(data.attendance_status_counts);
            }
            if (data.trainee_details) {
                populateTraineeTable(data.trainee_details);
            }

        } catch (error) {
            console.error("Error fetching report data:", error);
            if (reportPlaceholder) {
                reportPlaceholder.textContent = `Error loading report: ${error.message}`;
                reportPlaceholder.classList.remove("alert-info");
                reportPlaceholder.classList.add("alert-danger");
                reportPlaceholder.style.display = "";
            }
            if (reportContent) reportContent.style.display = "none";
        }
    }

    // We no longer need event listeners for form controls since parameters come from URL

    // Initial setup
    initializeGauges(); 
    
    // Automatically fetch report data when the page loads
    fetchReportData();
});

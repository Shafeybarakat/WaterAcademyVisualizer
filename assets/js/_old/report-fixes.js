// Complete report-fixes.js file with focus on fixing course dropdown
document.addEventListener("DOMContentLoaded", function() {
    console.log("Report fixes script loaded - Complete version");

    // Add necessary styles for fallback gauges using theme variables
    const gaugeStyles = document.createElement('style');
    gaugeStyles.textContent = `
    .gauge-fallback {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 120px;
        width: 100%;
    }
    .gauge-fallback-bar {
        width: 90%;
        height: 12px;
        background-color: var(--wa-card-border-color, #C0D0E0);
        border-radius: 0.375rem;
        overflow: hidden;
        margin: 15px 0;
    }
    .gauge-fallback-fill {
        height: 100%;
        border-radius: 0.375rem;
        transition: width 1s ease-in-out;
    }
    .attendance-gauge-fill {
        background: linear-gradient(90deg, var(--wa-accent-danger, #dc3545) 0%, 
                                          #ffba49 40%, 
                                          var(--wa-accent-success, #28a745) 80%);
    }
    .lgi-gauge-fill {
        background: linear-gradient(90deg, var(--wa-accent-danger, #dc3545) 0%, 
                                          #ffba49 40%, 
                                          var(--wa-secondary-color, #3A8DDE) 80%);
    }
    .gauge-value {
        font-family: "Michroma", "Public Sans", sans-serif;
        font-weight: 600;
        font-size: 1.5rem;
        color: var(--wa-text-headings, #00406E);
    }
    #performanceChart, #attendanceChart {
        min-height: 300px;
    }
    .score-marker {
        font-family: "Ubuntu", "Public Sans", sans-serif;
    }
    .score-gauge-value {
        font-family: "Michroma", "Public Sans", sans-serif;
        color: var(--wa-text-headings, #00406E);
    }
    `;
    document.head.appendChild(gaugeStyles);

    // Create fallback gauges using theme styling
    function createFallbackGauges() {
        console.log("Initializing gauges with Water Academy theme...");
        
        // Attendance gauge fallback
        const attendanceContainer = document.querySelector('.attendance-gauge-container');
        if (attendanceContainer) {
            const canvas = attendanceContainer.querySelector('canvas');
            if (canvas) canvas.style.display = 'none';
            
            if (!attendanceContainer.querySelector('.gauge-fallback')) {
                const fallback = document.createElement('div');
                fallback.className = 'gauge-fallback';
                fallback.innerHTML = `
                    <div class="gauge-value" id="attendanceGaugeValue">0%</div>
                    <div class="gauge-fallback-bar">
                        <div class="gauge-fallback-fill attendance-gauge-fill" style="width: 0%"></div>
                    </div>
                `;
                attendanceContainer.appendChild(fallback);
            }
        }
        
        // LGI gauge fallback
        const lgiContainer = document.querySelector('.lgi-gauge-container');
        if (lgiContainer) {
            const canvas = lgiContainer.querySelector('canvas');
            if (canvas) canvas.style.display = 'none';
            
            if (!lgiContainer.querySelector('.gauge-fallback')) {
                const fallback = document.createElement('div');
                fallback.className = 'gauge-fallback';
                fallback.innerHTML = `
                    <div class="gauge-value" id="lgiGaugeValue">0%</div>
                    <div class="gauge-fallback-bar">
                        <div class="gauge-fallback-fill lgi-gauge-fill" style="width: 0%"></div>
                    </div>
                `;
                lgiContainer.appendChild(fallback);
            }
        }
        
        console.log("Fallback gauges initialized with Water Academy theme");
    }

    // Define the gauge update functions - EXPLICITLY add to window object
    window.updateAttendanceGauge = function(value) {
        console.log("Updating attendance gauge to:", value);
        const gaugeValue = document.getElementById('attendanceGaugeValue');
        if (gaugeValue) {
            gaugeValue.textContent = value.toFixed(1) + '%';
        }
        
        const fallbackFill = document.querySelector('.attendance-gauge-container .gauge-fallback-fill');
        if (fallbackFill) {
            fallbackFill.style.width = value + '%';
        }
    };

    window.updateLGIGauge = function(value) {
        console.log("Updating LGI gauge to:", value);
        const gaugeValue = document.getElementById('lgiGaugeValue');
        if (gaugeValue) {
            gaugeValue.textContent = value.toFixed(1) + '%';
        }
        
        const fallbackFill = document.querySelector('.lgi-gauge-container .gauge-fallback-fill');
        if (fallbackFill) {
            fallbackFill.style.width = value + '%';
        }
    };

    // Create dummy gauge objects to prevent errors
    window.attendanceGauge = { 
        set: function(value) {
            window.updateAttendanceGauge(value);
        }
    };
    
    window.lgiGauge = { 
        set: function(value) {
            window.updateLGIGauge(value);
        }
    };

    // Override the initialization functions
    window.initAttendanceGauge = function() {
        console.log("Using fallback attendance gauge");
        return window.attendanceGauge;
    };

    window.initLGIGauge = function() {
        console.log("Using fallback LGI gauge");
        return window.lgiGauge;
    };

    // Create the fallback gauges
    createFallbackGauges();

    // A safer version of updateSummaryCards that checks for element existence
    function updateSummaryCardsWithChecks(summary) {
        console.log("Updating summary cards with:", summary);
        
        // Update trainee count with safety checks
        const traineeCount = parseInt(summary.TraineeCount) || 0;
        const traineeCountElement = document.getElementById("summaryTraineeCount");
        if (traineeCountElement) {
            traineeCountElement.textContent = traineeCount;
        }
        
        // Update icon cluster if the function exists
        if (typeof window.updateTraineeIconCluster === 'function') {
            try {
                window.updateTraineeIconCluster(traineeCount);
            } catch (e) {
                console.error("Error updating trainee icon cluster:", e);
            }
        }
        
        // Update attendance percentage with safety checks
        const attendancePercent = parseFloat(summary.AvgAttendance) || 0;
        const attendanceElement = document.getElementById("summaryAvgAttendance");
        if (attendanceElement) {
            attendanceElement.textContent = attendancePercent.toFixed(1) + "%";
        }
        
        // Update attendance gauge
        window.updateAttendanceGauge(attendancePercent);
        
        // Update average score with safety checks
        const avgScore = parseFloat(summary.AvgTotal) || 0;
        const avgScoreElement = document.getElementById("summaryAvgTotal");
        if (avgScoreElement) {
            avgScoreElement.textContent = avgScore.toFixed(1);
        }
        
        // Update score gauge if the function exists
        if (typeof window.updateScoreGauge === 'function') {
            try {
                window.updateScoreGauge(avgScore);
            } catch (e) {
                console.error("Error updating score gauge:", e);
            }
        } else {
            console.warn("updateScoreGauge function not found");
            // Basic fallback for score gauge
            const scoreFill = document.getElementById('totalScoreFill');
            if (scoreFill) {
                scoreFill.style.width = Math.min(Math.max(avgScore, 0), 100) + '%';
            }
        }
        
        // Update LGI with safety checks
        const lgiPercent = parseFloat(summary.AvgLGI) || 0;
        const lgiElement = document.getElementById("summaryAvgLGI");
        if (lgiElement) {
            lgiElement.textContent = lgiPercent.toFixed(1) + "%";
        }
        
        // Update LGI gauge
        window.updateLGIGauge(lgiPercent);
    }

    // =====================================================
    // SIMPLIFIED COURSE DROPDOWN FIX - DIRECT APPROACH
    // =====================================================
    
    // Simple direct function to fetch courses
    function fetchCourses(groupId) {
        console.log("Simple fetchCourses called for group:", groupId);
        
        const courseSelect = document.getElementById("courseSelect");
        if (!courseSelect) {
            console.error("Course select element not found");
            return;
        }
        
        courseSelect.disabled = false;
        courseSelect.innerHTML = "<option value=\"\" selected>Loading courses...</option>";
        
        if (!groupId) {
            courseSelect.innerHTML = "<option value=\"\" selected>Select a Course</option>";
            courseSelect.disabled = false;
            return;
        }
        
        // Simple fetch without extra headers
        fetch(`https://wa.shafey.net/wa/api/get_courses.php?group_id=${encodeURIComponent(groupId)}`)
            .then(response => {
                console.log("Response status:", response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(text => {
                console.log("Raw response:", text);
                return JSON.parse(text);
            })
            .then(courses => {
                console.log("Parsed courses:", courses);
                courseSelect.innerHTML = "<option value=\"\" selected>Select a Course</option>";
                
                if (Array.isArray(courses) && courses.length > 0) {
                    courses.forEach(course => {
                        const option = document.createElement("option");
                        option.value = course.CourseID;
                        option.textContent = course.CourseName;
                        courseSelect.appendChild(option);
                    });
                    console.log("Added", courses.length, "course options");
                } else {
                    courseSelect.innerHTML = "<option value=\"\" selected>No courses found for this group</option>";
                }
                courseSelect.disabled = false;
            })
            .catch(error => {
                console.error("Error fetching courses:", error);
                courseSelect.innerHTML = "<option value=\"\" selected>Error loading courses</option>";
                courseSelect.disabled = false;
            });
    }
    
    // Direct event listener on the group select without any fancy replacement
    const groupSelect = document.getElementById("groupSelect");
    if (groupSelect) {
        // Add a simple listener that doesn't interfere with anything else
        console.log("Adding direct change event listener to groupSelect");
        groupSelect.addEventListener("change", function() {
            console.log("Group select changed directly to:", this.value);
            fetchCourses(this.value);
        });
        
        // If a group is already selected, load its courses
        if (groupSelect.value) {
            console.log("Group already selected, fetching courses for:", groupSelect.value);
            fetchCourses(groupSelect.value);
        }
    }

    // Improved fetchReportData function with safety checks
    async function fetchReportDataSafe() {
        console.log("Fetching report data (safe version)");
        
        const groupId = document.getElementById("groupSelect")?.value;
        const courseId = document.getElementById("courseSelect")?.value;
        const startDate = document.getElementById("startDate")?.value;
        const endDate = document.getElementById("endDate")?.value;

        const reportContent = document.getElementById("reportContent");
        const reportPlaceholder = document.getElementById("reportPlaceholder");

        if (!groupId || !courseId) {
            if (reportPlaceholder) {
                reportPlaceholder.textContent = "Please select both a Group and a Course.";
                reportPlaceholder.classList.remove("alert-info");
                reportPlaceholder.classList.add("alert-warning");
            }
            if (reportContent) {
                reportContent.style.display = "none";
            }
            if (reportPlaceholder) {
                reportPlaceholder.style.display = "";
            }
            return;
        }

        if (reportPlaceholder) {
            reportPlaceholder.textContent = "Loading report data...";
            reportPlaceholder.classList.remove("alert-warning", "alert-danger");
            reportPlaceholder.classList.add("alert-info");
        }
        if (reportContent) {
            reportContent.style.display = "none";
        }
        if (reportPlaceholder) {
            reportPlaceholder.style.display = "";
        }

        const params = new URLSearchParams({
            group_id: groupId,
            course_id: courseId
        });
        if (startDate) params.append("start_date", startDate);
        if (endDate) params.append("end_date", endDate);

        try {
            const endpoint = `../api/get_group_report_data.php?${params.toString()}`;
            console.log("Fetching data from:", endpoint);
            
            const response = await fetch(endpoint);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const responseText = await response.text();
            console.log("Raw response (first 200 chars):", responseText.substring(0, 200));
            
            let data;
            try {
                data = JSON.parse(responseText);
            } catch (jsonError) {
                console.error("Error parsing JSON:", jsonError);
                throw new Error("Invalid response format from server");
            }

            if (data.error) {
                throw new Error(data.error);
            }

            console.log("Data received from API:", data);

            // Replace null values with defaults in summary data
            if (data.summary) {
                const defaultSummary = {
                    TraineeCount: 0,
                    AvgPreTest: 0,
                    AvgQuizScore: 0,
                    AvgFinalScore: 0,
                    AvgTotal: 0,
                    AvgAttendance: 0,
                    AvgLGI: 0
                };
                
                // Fill in any missing or null values
                for (const key in defaultSummary) {
                    if (data.summary[key] === null || data.summary[key] === undefined) {
                        data.summary[key] = defaultSummary[key];
                    }
                }
                
                // Update summary cards with safety checks
                updateSummaryCardsWithChecks(data.summary);
            }
            
            // Reinitialize chart containers if they're empty
            const performanceChartEl = document.getElementById("performanceChart");
            const attendanceChartEl = document.getElementById("attendanceChart");
            
            if (performanceChartEl && !performanceChartEl.hasChildNodes()) {
                performanceChartEl.innerHTML = '<canvas></canvas>';
            }
            
            if (attendanceChartEl && !attendanceChartEl.hasChildNodes()) {
                attendanceChartEl.innerHTML = '<canvas></canvas>';
            }
            
            // Safely call other update functions
            if (typeof window.renderPerformanceChart === 'function' && data.performanceData) {
                try {
                    const distribution = Array.isArray(data.performanceData.distribution) 
                        ? data.performanceData.distribution 
                        : [];
                    window.renderPerformanceChart(distribution);
                } catch (e) {
                    console.error("Error rendering performance chart:", e);
                }
            }
            
            if (typeof window.renderAttendanceChart === 'function' && data.attendanceData) {
                try {
                    const statusCounts = Array.isArray(data.attendanceData.statusCounts) 
                        ? data.attendanceData.statusCounts 
                        : [];
                    window.renderAttendanceChart(statusCounts);
                } catch (e) {
                    console.error("Error rendering attendance chart:", e);
                }
            }
            
            if (typeof window.renderTraineeTable === 'function' && data.traineeDetails) {
                try {
                    window.renderTraineeTable(data.traineeDetails);
                } catch (e) {
                    console.error("Error rendering trainee table:", e);
                }
            }

            if (reportContent) {
                reportContent.style.display = "";
            }
            if (reportPlaceholder) {
                reportPlaceholder.style.display = "none";
            }

        } catch (error) {
            console.error("Error fetching report data:", error);
            if (reportPlaceholder) {
                reportPlaceholder.textContent = `Error loading report data: ${error.message}. Please try again.`;
                reportPlaceholder.classList.remove("alert-info");
                reportPlaceholder.classList.add("alert-danger");
            }
            if (reportContent) {
                reportContent.style.display = "none";
            }
            if (reportPlaceholder) {
                reportPlaceholder.style.display = "";
            }
        }
    }

    // Fix chart containers and ensure they're visible
    setTimeout(function() {
        // Fix card heights to be consistent
        const cards = document.querySelectorAll('.card');
        cards.forEach(card => {
            // Reset any fixed heights
            card.style.height = '';
        });
        
        // Ensure all cards have consistent structure
        const summaryCards = document.querySelectorAll('.card');
        summaryCards.forEach(card => {
            const cardBody = card.querySelector('.card-body');
            if (cardBody) {
                // If it doesn't have flex-column structure
                if (!cardBody.querySelector('.d-flex.flex-column')) {
                    // Preserve the original content, but wrap it in flex-column
                    const content = cardBody.innerHTML;
                    // Check if it already has d-flex but just missing flex-column
                    if (cardBody.querySelector('.d-flex')) {
                        const dFlex = cardBody.querySelector('.d-flex');
                        dFlex.classList.add('flex-column');
                    } else {
                        // Need to wrap the content completely
                        cardBody.innerHTML = `
                            <div class="d-flex flex-column">
                                ${content}
                            </div>
                        `;
                    }
                }
            }
        });
        
        // Fix performance and attendance chart containers
        const performanceChartEl = document.getElementById("performanceChart");
        const attendanceChartEl = document.getElementById("attendanceChart");
        
        if (performanceChartEl) {
            performanceChartEl.style.minHeight = '300px';
            if (performanceChartEl.childElementCount === 0) {
                const canvas = document.createElement('canvas');
                performanceChartEl.appendChild(canvas);
                
                // If the renderPerformanceChart function exists, use it
                if (typeof window.renderPerformanceChart === 'function') {
                    window.renderPerformanceChart([]);
                }
            }
        }
        
        if (attendanceChartEl) {
            attendanceChartEl.style.minHeight = '300px';
            if (attendanceChartEl.childElementCount === 0) {
                const canvas = document.createElement('canvas');
                attendanceChartEl.appendChild(canvas);
                
                // If the renderAttendanceChart function exists, use it
                if (typeof window.renderAttendanceChart === 'function') {
                    window.renderAttendanceChart([]);
                }
            }
        }
    }, 500);

    // Add event listener for the Apply Filters button
    const applyFiltersBtn = document.getElementById("applyFiltersBtn");
    if (applyFiltersBtn) {
        // Add our event listener
        applyFiltersBtn.addEventListener("click", fetchReportDataSafe);
    }

    // Make key functions globally available
    window.fetchCourses = fetchCourses;
    window.updateSummaryCardsWithChecks = updateSummaryCardsWithChecks;
    window.fetchReportDataSafe = fetchReportDataSafe;

    console.log("All fixes applied successfully with simplified course dropdown fix");
});
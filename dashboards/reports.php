<?php
// reports.php - Main Reports page
$pageTitle = "Reports";

// Authentication, config and header
include_once "../includes/header.php";

// Add necessary Select2 CSS and JS 
echo '<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />';
echo '<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>';

// Enforce permissions
enforceAnyPermission(['access_group_reports', 'access_trainee_reports', 'access_attendance_reports']);
// If execution continues, permissions are granted.

// Fetch all groups that have courses assigned to them
$groupsResult = $conn->query("
    SELECT DISTINCT g.GroupID, g.GroupName 
    FROM `Groups` g
    JOIN GroupCourses gc ON g.GroupID = gc.GroupID
    ORDER BY g.GroupName
");
$groups = $groupsResult->fetch_all(MYSQLI_ASSOC);
$selectedGroup = isset($_GET['group_id']) && $_GET['group_id'] !== '' ? (int)$_GET['group_id'] : ($groups[0]['GroupID'] ?? null);
$selectedCourse = $_GET['course_id'] ?? '';

// When a Group is chosen, fetch all its Courses
$courses = [];
if (!empty($selectedGroup)) {
    $stmt = $conn->prepare("
      SELECT DISTINCT c.CourseID, c.CourseName 
      FROM Courses c
      JOIN GroupCourses gc ON c.CourseID = gc.CourseID
      WHERE gc.GroupID = ?
      ORDER BY c.CourseName
    ");
    if ($stmt) {
        $stmt->bind_param("i", $selectedGroup);
        $stmt->execute();
        $courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        error_log("Error preparing statement to fetch courses for group: " . $conn->error);
    }

    // Default to first course if none selected yet
    if (empty($selectedCourse) && !empty($courses)) {
        $selectedCourse = $courses[0]['CourseID'];
    }
}
?>

<div class="container-xxl flex-grow-1 container-p-y pt-0">

    <!-- Filter Section -->
    <div class="action-card mb-4">
        <div class="action-card-header">
            <h5 class="action-card-title">Filters</h5>
        </div>
        <div class="action-card-content"> <!-- Removed p-4 -->
            <form method="get">
                <div class="flex flex-wrap -mx-2 md:flex-nowrap">
                    <!-- Group and Course filters side by side -->
                    <div class="w-full md:w-1/2 px-2 mb-3 md:mb-0">
                      <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Group</label>
                      <div class="input-group flex">
                        <span class="input-group-text bg-primary text-white">
                          <i class="ri-team-line"></i>
                        </span>
                        <select id="group_filter" name="group_id" class="form-select w-full">
                          <option value="">– Select Group –</option>
                          <?php foreach($groups as $g): ?>
                            <option
                              value="<?= $g['GroupID'] ?>"
                              <?= $g['GroupID']==$selectedGroup?'selected':''?>
                            ><?= htmlspecialchars($g['GroupName']) ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                    </div>

                    <div class="w-full md:w-1/2 px-2">
                      <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Course</label>
                      <div class="input-group flex">
                        <span class="input-group-text bg-success text-white">
                          <i class="ri-book-open-line"></i>
                        </span>
                        <select
                          id="course_filter"
                          name="course_id"
                          class="form-select w-full"
                          <?php if(empty($selectedGroup)) echo 'disabled' ?>
                        >
                          <option value="">– Select Course –</option>
                          <?php foreach($courses as $c): ?>
                            <option
                              value="<?= $c['CourseID'] ?>"
                              <?= $c['CourseID']==$selectedCourse?'selected':''?>
                            ><?= htmlspecialchars($c['CourseName']) ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                    </div>
                </div>
                
                <!-- Buttons in a separate row, aligned to the right -->
                <div class="flex justify-end mt-4 space-x-2">
                    <button type="submit" class="btn btn-primary action-btn">
                        <i class="ri-filter-3-line me-1"></i> Apply Filters
                    </button>
                    <a href="reports.php" class="btn btn-outline-secondary action-btn">
                        <i class="ri-refresh-line me-1"></i> Reset Filters
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Group Report Cards -->
    <h5 class="fw-bold py-3 mb-2">Group Reports</h5>
    <div class="flex flex-wrap -mx-2 mb-4"> <!-- Changed from row g-4 d-flex align-items-stretch -->
        <!-- Group Analytics Card -->
        <div class="w-full md:w-1/2 px-2 mb-4"> <!-- Changed from col-md-6 col-12 -->
            <div class="action-card h-100">
                <div class="action-card-header">
                    <i class="ri-pie-chart-line text-primary action-card-icon"></i>
                    <h5 class="action-card-title">Group Analytics</h5>
                </div>
                <div class="action-card-content text-center d-flex flex-column pb-3">
                    <p class="card-text mb-4 flex-grow-1">View detailed analytics and performance metrics for the selected group and course.</p>
                </div>
                <div class="flex justify-center">
                    <button type="button" onclick="window.location.href='group-analytics.php?group_id=<?= $selectedGroup ?>&course_id=<?= $selectedCourse ?>'"
                       class="action-card-button shining-btn w-4/5 mt-auto <?= (empty($selectedGroup) || empty($selectedCourse)) ? 'disabled' : '' ?>">
                        <i class="ri-bar-chart-2-line me-1"></i> View Analytics
                    </button>
                </div>
            </div>
        </div>

        <!-- Group Performance Card -->
        <div class="w-full md:w-1/2 px-2 mb-4"> <!-- Changed from col-md-6 col-12 -->
            <div class="action-card h-100">
                <div class="action-card-header">
                    <i class="ri-bar-chart-grouped-line text-success action-card-icon"></i>
                    <h5 class="action-card-title">Group Performance</h5>
                </div>
                <div class="action-card-content text-center d-flex flex-column pb-3">
                    <p class="card-text mb-4 flex-grow-1">Comprehensive performance report for the selected group with detailed metrics.</p>
                </div>
                <div class="flex justify-center">
                    <button type="button" onclick="window.location.href='report_group_performance.php?group_id=<?= $selectedGroup ?>&course_id=<?= $selectedCourse ?>'"
                       class="action-card-button shining-btn w-4/5 mt-auto <?= (empty($selectedGroup) || empty($selectedCourse)) ? 'disabled' : '' ?>">
                        <i class="ri-line-chart-line me-1"></i> View Performance
                    </button>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-4 border-light">

    <!-- Trainee Search Section -->
    <h5 class="fw-bold py-3 mb-2">Trainee Reports</h5>
    <div class="action-card mb-4">
        <div class="action-card-header">
            <h5 class="action-card-title">Search for a Trainee</h5>
        </div>
        <div class="action-card-content"> <!-- Removed p-4 -->
            <div class="row mb-3">
                <div class="col-md-12 d-flex justify-content-center"> <!-- Use flexbox to center content -->
                    <div class="input-group" style="max-width: 500px;"> <!-- Set max-width for centering -->
                        <span class="input-group-text bg-primary text-white">
                            <i class="ri-search-line"></i>
                        </span>
                        <input type="text" id="traineeSearchInput" class="form-control" placeholder="Search by name, ID, phone, or email...">
                        <button type="button" id="searchButton" class="btn btn-primary">
                            <i class="ri-search-line me-1"></i> Search
                        </button>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 text-center"> <!-- Center the small text -->
                    <small class="text-muted">Type at least 2 characters and click Search</small>
                    <div id="searchSuggestions" class="dropdown-menu w-100" style="max-height: 200px; overflow-y: auto;"></div>
                </div>
            </div>

            <!-- Search Results Table (initially hidden) -->
            <div id="searchResults" style="display: none;">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Trainee Name</th>
                                <th>Gov ID</th>
                                <th>Group</th>
                                <th>Course</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="traineeResultsBody">
                            <!-- Results will be populated dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Trainee Report Cards -->
    <div class="flex flex-wrap -mx-2 mb-4"> <!-- Changed from row g-4 d-flex align-items-stretch -->
        <!-- Trainee Performance Card -->
        <div class="w-full md:w-1/2 px-2 mb-4"> <!-- Changed from col-md-6 col-12 -->
            <div class="action-card h-100">
                <div class="action-card-header">
                    <i class="ri-user-star-line text-info action-card-icon"></i>
                    <h5 class="action-card-title">Trainee Performance</h5>
                </div>
                <div class="action-card-content text-center d-flex flex-column pb-3">
                    <p class="card-text mb-4 flex-grow-1">Individual trainee performance reports with detailed progress tracking.</p>
                </div>
                <div class="flex justify-center">
                    <button type="button" id="traineePerformanceBtn" 
                       class="action-card-button shining-btn w-4/5 mt-auto disabled">
                        <i class="ri-user-search-line me-1"></i> View Performance
                    </button>
                </div>
            </div>
        </div>

        <!-- Attendance Summary Card -->
        <div class="w-full md:w-1/2 px-2 mb-4"> <!-- Changed from col-md-6 col-12 -->
            <div class="action-card h-100">
                <div class="action-card-header">
                    <i class="ri-calendar-event-line text-warning action-card-icon"></i>
                    <h5 class="action-card-title">Attendance Summary</h5>
                </div>
                <div class="action-card-content text-center d-flex flex-column pb-3">
                    <p class="card-text mb-4 flex-grow-1">Comprehensive attendance report for the selected trainee and course.</p>
                </div>
                <div class="flex justify-center">
                    <button type="button" id="traineeAttendanceBtn" 
                       class="action-card-button shining-btn w-4/5 mt-auto disabled">
                        <i class="ri-calendar-check-line me-1"></i> View Attendance
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Include footer -->
<?php include_once "../includes/footer.php"; ?>

<script>
$(document).ready(function() {
    // Handle group filter change to update course options
    $('#group_filter').change(function() {
        const groupId = $(this).val();
        const courseSelect = $('#course_filter');
        
        if (groupId) {
            // Enable course select
            courseSelect.prop('disabled', false);
            
            // Fetch courses for the selected group
            $.ajax({
                url: 'ajax_get_courses_by_group.php',
                data: { group_id: groupId },
                dataType: 'json',
                success: function(data) {
                    courseSelect.empty().append('<option value="">All Courses</option>');
                    
                    if (data.courses && data.courses.length > 0) {
                        $.each(data.courses, function(i, course) {
                            courseSelect.append($('<option></option>').val(course.CourseID).text(course.CourseName));
                        });
                    }
                },
                error: function() {
                    console.error('Failed to fetch courses');
                }
            });
        } else {
            // Disable and reset course select
            courseSelect.prop('disabled', true).empty().append('<option value="">All Courses</option>');
        }
    });

    // Debug function
    function logDebug(message, data) {
        console.log(message, data);
    }

    // Trainee search functionality
    let searchTimeout;
    let selectedTraineeId = null;
    
    // Handle direct search button click
    $('#searchButton').click(function() {
        const searchTerm = $('#traineeSearchInput').val().trim();
        
        if (searchTerm.length < 2) {
            alert('Please enter at least 2 characters to search');
            return;
        }
        
        // Search for trainees matching the term
        $.ajax({
            url: 'ajax_search_trainees.php',
            type: 'GET',
            data: { term: searchTerm },
            dataType: 'json',
            success: function(data) {
                logDebug('Search results:', data);
                
                if (data.results && data.results.length > 0) {
                    // If only one result, select it automatically
                    if (data.results.length === 1) {
                        const trainee = data.results[0];
                        selectedTraineeId = trainee.id;
                        $('#traineeSearchInput').val(trainee.text);
                        fetchTraineeCourses(selectedTraineeId);
                    } else {
                        // Show dropdown with multiple results
                        const suggestions = $('#searchSuggestions').empty();
                        data.results.forEach(function(item) {
                            suggestions.append(
                                `<a class="dropdown-item" href="#" data-id="${item.id}">${item.text}</a>`
                            );
                        });
                        suggestions.show();
                    }
                } else {
                    // No results found
                    $('#searchResults').show();
                    $('#traineeResultsBody').html('<tr><td colspan="7" class="text-center">No trainees found matching your search. Please try a different search term.</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                logDebug('Search error:', {xhr, status, error});
                alert('Error searching for trainees. Please try again.');
            }
        });
    });
    
    // Search as you type
    $('#traineeSearchInput').on('keyup', function() {
        const searchTerm = $(this).val().trim();
        
        // Clear previous timeout
        clearTimeout(searchTimeout);
        
        // Clear suggestions if less than 2 characters
        if (searchTerm.length < 2) {
            $('#searchSuggestions').hide().empty();
            return;
        }
        
        // Set a timeout to prevent too many requests
        searchTimeout = setTimeout(function() {
            $.ajax({
                url: 'ajax_search_trainees.php',
                type: 'GET',
                data: { term: searchTerm },
                dataType: 'json',
                success: function(data) {
                    logDebug('Search suggestions:', data);
                    const suggestions = $('#searchSuggestions').empty();
                    
                    if (data.results && data.results.length > 0) {
                        data.results.forEach(function(item) {
                            suggestions.append(
                                `<a class="dropdown-item" href="#" data-id="${item.id}">${item.text}</a>`
                            );
                        });
                        suggestions.show();
                    } else {
                        suggestions.append('<span class="dropdown-item-text">No results found</span>');
                        suggestions.show();
                    }
                },
                error: function(xhr, status, error) {
                    logDebug('Suggestion error:', {xhr, status, error});
                }
            });
        }, 300);
    });
    
    // Handle suggestion click
    $(document).on('click', '#searchSuggestions .dropdown-item', function(e) {
        e.preventDefault();
        const traineeId = $(this).data('id');
        const traineeName = $(this).text();
        
        $('#traineeSearchInput').val(traineeName);
        selectedTraineeId = traineeId;
        $('#searchSuggestions').hide();
        
        // Fetch courses for selected trainee
        fetchTraineeCourses(traineeId);
    });
    
    // Function to fetch trainee courses
    function fetchTraineeCourses(traineeId) {
        logDebug('Fetching courses for trainee ID:', traineeId);
        
        $.ajax({
            url: 'get_trainee_data.php',
            type: 'GET',
            data: { trainee_id: traineeId },
            dataType: 'json',
            success: function(response) {
                logDebug('Trainee courses response:', response);
                $('#traineeResultsBody').empty();
                
                if (response.success && response.courses && response.courses.length > 0) {
                    // Add each course row
                    response.courses.forEach(function(course) {
                        var row = `
                            <tr>
                                <td>${course.TraineeName}</td>
                                <td>${course.GovID || 'N/A'}</td>
                                <td>${course.GroupName}</td>
                                <td>${course.CourseName}</td>
                                <td>${course.StartDate || 'N/A'}</td>
                                <td>${course.EndDate || 'N/A'}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary select-trainee-course" 
                                            data-trainee-id="${course.TID}" data-course-id="${course.CourseID}">
                                        <i class="ri-checkbox-circle-line me-1"></i> Select
                                    </button>
                                </td>
                            </tr>
                        `;
                        $('#traineeResultsBody').append(row);
                    });
                } else {
                    // Show no results message
                    $('#traineeResultsBody').html('<tr><td colspan="7" class="text-center">No courses found for this trainee.</td></tr>');
                }
                
                // Show results table
                $('#searchResults').show();
            },
            error: function(xhr, status, error) {
                logDebug('Trainee courses error:', {xhr, status, error});
                $('#traineeResultsBody').html('<tr><td colspan="7" class="text-center">Error fetching trainee courses. Please try again.</td></tr>');
                $('#searchResults').show();
            }
        });
    }

    // Handle row selection (same as before)
    $(document).on('click', '.select-trainee-course', function() {
        var traineeId = $(this).data('trainee-id');
        var courseId = $(this).data('course-id');
        
        // Highlight the selected row
        $('#traineeResultsBody tr').removeClass('table-primary');
        $(this).closest('tr').addClass('table-primary');
        
        // Update report buttons with proper URLs and enable them
        $('#traineePerformanceBtn')
            .attr('href', 'report_trainee_performance.php?trainee_id=' + traineeId + '&course_id=' + courseId)
            .removeClass('disabled');
            
        $('#traineeAttendanceBtn')
            .attr('href', 'report_attendance_summary.php?trainee_id=' + traineeId + '&course_id=' + courseId)
            .removeClass('disabled');
    });
    
    // Close suggestions when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#traineeSearchInput, #searchSuggestions').length) {
            $('#searchSuggestions').hide();
        }
    });
    
    // Handle Enter key in search input
    $(document).on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            $('#searchButton').click();
        }
    });
});
</script>

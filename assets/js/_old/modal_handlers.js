// Modal handler functions for attendance_grades.php
// These functions need to be globally accessible

// Global variables for modals
let gradesModal, attendanceModal;
let gradesSaveTimeout, attendanceSaveTimeout;

// Initialize modals when document is ready
$(document).ready(function() {
    try {
        // Initialize Bootstrap modals
        gradesModal = new bootstrap.Modal(document.getElementById('gradesModal'));
        attendanceModal = new bootstrap.Modal(document.getElementById('attendanceModal'));
        console.log("Modals initialized successfully");
        
        // Set up form submissions via AJAX
        setupFormSubmissions();
        
        // Set up data handlers
        setupDataHandlers();
    } catch (error) {
        console.error("Error initializing modals:", error);
    }
});

// Function to open the grades modal
function openGradesModal(groupCourseId) {
    console.log('Opening grades modal with groupCourseId:', groupCourseId);
    
    if (!groupCourseId) {
        console.error('No group course ID available');
        return;
    }
    
    // Set the group course ID in the modal form
    $('#grade_group_course_id').val(groupCourseId);
    
    // Show loading indicator
    $('#gradesLoading').show();
    $('#gradeEntryForm').hide();
    $('#gradesNoTraineesMessage').hide();
    $('#gradesErrorMessage').hide();
    
    try {
        // Show the modal
        if (typeof bootstrap !== 'undefined' && gradesModal) {
            gradesModal.show();
            console.log('Modal shown using Bootstrap 5 constructor');
        } else {
            // Fallback to jQuery method
            $('#gradesModal').modal('show');
            console.log('Modal shown using jQuery method');
        }
    } catch (error) {
        console.error('Error showing modal:', error);
        alert('Could not open the grades modal. Please try again or contact support.');
    }
    
    // Load trainees for the selected course
    loadTraineesForGrades(groupCourseId);
}

// Function to open the attendance modal
function openAttendanceModal(groupCourseId) {
    console.log('Opening attendance modal with groupCourseId:', groupCourseId);
    
    if (!groupCourseId) {
        console.error('No group course ID available');
        return;
    }
    
    // Set the group course ID in the modal form
    $('#attendance_group_course_id').val(groupCourseId);
    
    // Show loading indicator
    $('#attendanceLoading').show();
    $('#attendanceEntryForm').hide();
    $('#attendanceNoTraineesMessage').hide();
    $('#attendanceErrorMessage').hide();
    
    try {
        // Show the modal
        if (typeof bootstrap !== 'undefined' && attendanceModal) {
            attendanceModal.show();
            console.log('Modal shown using Bootstrap 5 constructor');
        } else {
            // Fallback to jQuery method
            $('#attendanceModal').modal('show');
            console.log('Modal shown using jQuery method');
        }
    } catch (error) {
        console.error('Error showing modal:', error);
        alert('Could not open the attendance modal. Please try again or contact support.');
    }
    
    // Load trainees for the selected course
    loadTraineesForAttendance(groupCourseId);
}

// Load trainees for grades entry
function loadTraineesForGrades(groupCourseId) {
    $.ajax({
        url: 'get_trainees_for_grades.php',
        data: { group_course_id: groupCourseId },
        dataType: 'json',
        success: function(data) {
            if (data.error) {
                $('#gradesErrorText').text(data.error);
                $('#gradesErrorMessage').show();
                $('#gradesLoading').hide();
                return;
            }
            
            if (data.length === 0) {
                $('#gradesNoTraineesMessage').show();
                $('#gradesLoading').hide();
                return;
            }
            
            const tableBody = $('#gradesTableBody');
            tableBody.empty();
            
            // Clear any existing feedback containers
            $('#gradeFeedbackContainer').remove();
            $('<div id="gradeFeedbackContainer"></div>').insertAfter('#gradesTable');
            
            data.forEach(function(trainee) {
                const row = $('<tr></tr>');
                
                // First Name
                row.append(
                    '<td>' +
                    '<input type="text" class="form-control form-control-sm" value="' + trainee.FirstName + '" readonly>' +
                    '<input type="hidden" name="trainee_ids[]" value="' + trainee.TID + '">' +
                    '</td>'
                );
                
                // Last Name
                row.append(
                    '<td>' +
                    '<input type="text" class="form-control form-control-sm" value="' + trainee.LastName + '" readonly>' +
                    '</td>'
                );
                
                // Gov ID
                row.append(
                    '<td>' +
                    '<input type="text" class="form-control form-control-sm" value="' + trainee.GovID + '" readonly>' +
                    '</td>'
                );
                
                // Pre-Test
                row.append(
                    '<td>' +
                    '<input type="number" class="form-control form-control-sm grade-input pretest" ' +
                    'name="pretest[]" min="0" max="50" step="0.1" ' +
                    'value="' + (trainee.PreTest !== null ? trainee.PreTest : '') + '">' +
                    '</td>'
                );
                
                // Attendance (read-only)
                row.append(
                    '<td>' +
                    '<input type="number" class="form-control form-control-sm attendance" ' +
                    'name="att_grade[]" min="0" max="10" step="0.1" ' +
                    'value="' + (trainee.AttGrade !== null ? trainee.AttGrade : 0) + '" readonly>' +
                    '</td>'
                );
                
                // Participation
                row.append(
                    '<td>' +
                    '<input type="number" class="form-control form-control-sm grade-input participation" ' +
                    'name="participation[]" min="0" max="10" step="0.1" ' +
                    'value="' + (trainee.Participation !== null ? trainee.Participation : 0) + '">' +
                    '</td>'
                );
                
                // Quiz 1
                row.append(
                    '<td>' +
                    '<input type="number" class="form-control form-control-sm grade-input quiz1" ' +
                    'name="quiz1[]" min="0" max="30" step="0.1" ' +
                    'value="' + (trainee.Quiz1 !== null ? trainee.Quiz1 : 0) + '">' +
                    '</td>'
                );
                
                // Quiz 2
                row.append(
                    '<td>' +
                    '<input type="number" class="form-control form-control-sm grade-input quiz2" ' +
                    'name="quiz2[]" min="0" max="30" step="0.1" ' +
                    'value="' + (trainee.Quiz2 !== null ? trainee.Quiz2 : '') + '">' +
                    '</td>'
                );
                
                // Quiz 3
                row.append(
                    '<td>' +
                    '<input type="number" class="form-control form-control-sm grade-input quiz3" ' +
                    'name="quiz3[]" min="0" max="30" step="0.1" ' +
                    'value="' + (trainee.Quiz3 !== null ? trainee.Quiz3 : '') + '">' +
                    '</td>'
                );
                
                // Quiz Average (calculated)
                row.append(
                    '<td>' +
                    '<input type="number" class="form-control form-control-sm quiz-avg" ' +
                    'name="quiz_avg[]" min="0" max="30" step="0.1" ' +
                    'value="' + (trainee.QuizAvg !== null ? trainee.QuizAvg : 0) + '" readonly>' +
                    '</td>'
                );
                
                // Final Exam
                row.append(
                    '<td>' +
                    '<input type="number" class="form-control form-control-sm grade-input final-exam" ' +
                    'name="final_exam[]" min="0" max="50" step="0.1" ' +
                    'value="' + (trainee.FinalExam !== null ? trainee.FinalExam : 0) + '">' +
                    '</td>'
                );
                
                // Total (calculated)
                row.append(
                    '<td>' +
                    '<input type="number" class="form-control form-control-sm course-total" ' +
                    'name="course_total[]" min="0" max="100" step="0.1" ' +
                    'value="' + (trainee.CourseTotal !== null ? trainee.CourseTotal : 0) + '" readonly>' +
                    '</td>'
                );
                
                tableBody.append(row);
                
                // Create feedback fields for this trainee
                const feedbackRow = $('<div class="row mb-4 trainee-feedback" id="feedback-' + trainee.TID + '"></div>');
                feedbackRow.append(
                    '<div class="col-12">' +
                    '<h6 class="border-bottom pb-2">' + trainee.FirstName + ' ' + trainee.LastName + ' - Feedback</h6>' +
                    '</div>' +
                    '<div class="col-md-6">' +
                    '<div class="form-group">' +
                    '<label for="positive-feedback-' + trainee.TID + '">Positive Feedback</label>' +
                    '<textarea class="form-control" id="positive-feedback-' + trainee.TID + '" ' +
                    'name="positive_feedback[]" rows="3" placeholder="Enter positive feedback for this trainee">' + 
                    (trainee.PositiveFeedback !== null ? trainee.PositiveFeedback : '') + '</textarea>' +
                    '</div>' +
                    '</div>' +
                    '<div class="col-md-6">' +
                    '<div class="form-group">' +
                    '<label for="areas-to-improve-' + trainee.TID + '">Areas to Improve</label>' +
                    '<textarea class="form-control" id="areas-to-improve-' + trainee.TID + '" ' +
                    'name="areas_to_improve[]" rows="3" placeholder="Enter areas to improve for this trainee">' + 
                    (trainee.AreasToImprove !== null ? trainee.AreasToImprove : '') + '</textarea>' +
                    '</div>' +
                    '</div>'
                );
                
                $('#gradeFeedbackContainer').append(feedbackRow);
                
                // Calculate initial values
                calculateQuizAvg(row);
                calculateTotal(row);
            });
            
            $('#gradesLoading').hide();
            $('#gradeEntryForm').show();
            $('#gradesSaveStatus').text('Ready').removeClass('bg-primary bg-warning bg-success').addClass('bg-secondary');
        },
        error: function(xhr, status, error) {
            $('#gradesErrorText').text('Failed to load trainees: ' + error);
            $('#gradesErrorMessage').show();
            $('#gradesLoading').hide();
        }
    });
}

// Load trainees for attendance entry
function loadTraineesForAttendance(groupCourseId) {
    $.ajax({
        url: 'get_trainees_for_attendance.php',
        data: { group_course_id: groupCourseId },
        dataType: 'json',
        success: function(data) {
            if (data.error) {
                $('#attendanceErrorText').text(data.error);
                $('#attendanceErrorMessage').show();
                $('#attendanceLoading').hide();
                return;
            }
            
            if (data.length === 0) {
                $('#attendanceNoTraineesMessage').show();
                $('#attendanceLoading').hide();
                return;
            }
            
            const tableBody = $('#attendanceTableBody');
            tableBody.empty();
            
            data.forEach(function(trainee) {
                const row = $('<tr></tr>');
                
                // First Name
                row.append(
                    '<td>' +
                    '<input type="text" class="form-control form-control-sm" value="' + trainee.FirstName + '" readonly>' +
                    '<input type="hidden" name="trainee_ids[]" value="' + trainee.TID + '">' +
                    '</td>'
                );
                
                // Last Name
                row.append(
                    '<td>' +
                    '<input type="text" class="form-control form-control-sm" value="' + trainee.LastName + '" readonly>' +
                    '</td>'
                );
                
                // Gov ID
                row.append(
                    '<td>' +
                    '<input type="text" class="form-control form-control-sm" value="' + trainee.GovID + '" readonly>' +
                    '</td>'
                );
                
                // Present Hours (P)
                row.append(
                    '<td>' +
                    '<input type="number" class="form-control form-control-sm attendance-input present-hours" ' +
                    'name="present_hours[]" min="0" step="0.5" ' +
                    'value="' + (trainee.PresentHours !== null ? trainee.PresentHours : 0) + '">' +
                    '</td>'
                );
                
                // Excused Hours (E)
                row.append(
                    '<td>' +
                    '<input type="number" class="form-control form-control-sm attendance-input excused-hours" ' +
                    'name="excused_hours[]" min="0" step="0.5" ' +
                    'value="' + (trainee.ExcusedHours !== null ? trainee.ExcusedHours : 0) + '">' +
                    '</td>'
                );
                
                // Late Hours (L)
                row.append(
                    '<td>' +
                    '<input type="number" class="form-control form-control-sm attendance-input late-hours" ' +
                    'name="late_hours[]" min="0" step="0.5" ' +
                    'value="' + (trainee.LateHours !== null ? trainee.LateHours : 0) + '">' +
                    '</td>'
                );
                
                // Absent Hours (A)
                row.append(
                    '<td>' +
                    '<input type="number" class="form-control form-control-sm attendance-input absent-hours" ' +
                    'name="absent_hours[]" min="0" step="0.5" ' +
                    'value="' + (trainee.AbsentHours !== null ? trainee.AbsentHours : 0) + '">' +
                    '</td>'
                );
                
                // Points (calculated, read-only)
                row.append(
                    '<td>' +
                    '<input type="number" class="form-control form-control-sm points" ' +
                    'name="points[]" min="0" step="1" ' +
                    'value="' + (trainee.MoodlePoints !== null ? parseFloat(trainee.MoodlePoints).toFixed(1) : 0) + '" readonly>' +
                    '</td>'
                );
                
                // Total Sessions (calculated, read-only)
                row.append(
                    '<td>' +
                    '<input type="number" class="form-control form-control-sm taken-sessions" ' +
                    'name="taken_sessions[]" min="1" step="1" ' +
                    'value="' + (trainee.TakenSessions !== null ? trainee.TakenSessions : 1) + '" readonly>' +
                    '</td>'
                );
                
                // Attendance Percentage (calculated, read-only)
                row.append(
                    '<td>' +
                    '<input type="number" class="form-control form-control-sm attendance-percentage" ' +
                    'name="attendance_percentage[]" min="0" max="100" step="1" ' +
                    'value="' + (trainee.AttendancePercentage !== null ? Math.round(trainee.AttendancePercentage) : 0) + '" readonly>' +
                    '</td>'
                );
                
                tableBody.append(row);
                
                // Calculate initial attendance values
                calculateAttendance(row);
            });
            
            $('#attendanceLoading').hide();
            $('#attendanceEntryForm').show();
            $('#attendanceSaveStatus').text('Ready').removeClass('bg-primary bg-warning bg-success').addClass('bg-secondary');
        },
        error: function(xhr, status, error) {
            $('#attendanceErrorText').text('Failed to load trainees: ' + error);
            $('#attendanceErrorMessage').show();
            $('#attendanceLoading').hide();
        }
    });
}

// Set up form submissions via AJAX
function setupFormSubmissions() {
    // Handle Save Grades button click
    $('#saveGradesBtn').on('click', function() {
        submitGradesForm();
    });
    
    // Handle Save Attendance button click
    $('#saveAttendanceBtn').on('click', function() {
        submitAttendanceForm();
    });
    
    // Override form submissions
    $('#gradeEntryForm').on('submit', function(e) {
        e.preventDefault();
        submitGradesForm();
    });
    
    $('#attendanceEntryForm').on('submit', function(e) {
        e.preventDefault();
        submitAttendanceForm();
    });
}

// Validate grade values against maximum limits
function validateGradeValues() {
    let isValid = true;
    let errorMessages = [];
    
    // Check pretest values (max 50)
    $('.pretest').each(function(index) {
        const value = parseFloat($(this).val());
        if ($(this).val() !== '' && (isNaN(value) || value < 0 || value > 50)) {
            isValid = false;
            errorMessages.push('Pre-Test values must be between 0 and 50');
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    
    // Check participation values (max 10)
    $('.participation').each(function() {
        const value = parseFloat($(this).val());
        if (isNaN(value) || value < 0 || value > 10) {
            isValid = false;
            errorMessages.push('Participation values must be between 0 and 10');
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    
    // Check quiz values (max 30)
    $('.quiz1, .quiz2, .quiz3').each(function() {
        if ($(this).val() === '') return; // Skip empty quiz2 and quiz3 fields
        
        const value = parseFloat($(this).val());
        if (isNaN(value) || value < 0 || value > 30) {
            isValid = false;
            errorMessages.push('Quiz values must be between 0 and 30');
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    
    // Check final exam values (max 50)
    $('.final-exam').each(function() {
        const value = parseFloat($(this).val());
        if (isNaN(value) || value < 0 || value > 50) {
            isValid = false;
            errorMessages.push('Final Exam values must be between 0 and 50');
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    
    if (!isValid) {
        // Show error message
        $('#gradesErrorText').html(errorMessages.join('<br>'));
        $('#gradesErrorMessage').show();
        setTimeout(() => {
            $('#gradesErrorMessage').hide();
        }, 5000);
    }
    
    return isValid;
}

// Submit grades form via AJAX
function submitGradesForm() {
    // Validate form values first
    if (!validateGradeValues()) {
        return;
    }
    
    // Hide any previous error messages
    $('#gradesErrorMessage').hide();
    
    $('#gradesSaveStatus').text('Saving...').removeClass('bg-secondary bg-warning bg-danger').addClass('bg-primary');
    
    $.ajax({
        url: 'submit_grades.php',
        type: 'POST',
        data: $('#gradeEntryForm').serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#gradesSaveStatus').text('Saved').removeClass('bg-primary bg-warning bg-danger').addClass('bg-success');
                setTimeout(() => {
                    $('#gradesSaveStatus').text('Ready').removeClass('bg-success').addClass('bg-secondary');
                }, 3000);
            } else {
                $('#gradesErrorText').text(response.message);
                $('#gradesErrorMessage').show();
                $('#gradesSaveStatus').text('Error').removeClass('bg-primary bg-warning bg-success').addClass('bg-danger');
                
                // Auto-hide error message after 5 seconds
                setTimeout(() => {
                    $('#gradesErrorMessage').hide();
                }, 5000);
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", status, error);
            console.log("Response:", xhr.responseText);
            
            let errorMessage = 'Error saving grades. Please try again.';
            
            // Check for redirect or unauthorized response
            if (xhr.responseText && xhr.responseText.includes('UNAUTHORIZED')) {
                errorMessage = 'You do not have permission to save grades. Please check your access rights.';
            } else if (xhr.status === 403) {
                errorMessage = 'Access denied. You do not have permission to perform this action.';
            } else if (xhr.status === 0) {
                errorMessage = 'Connection error. Please check your internet connection and try again.';
            } else if (error) {
                errorMessage = 'Error saving grades: ' + error;
            }
            
            $('#gradesErrorText').text(errorMessage);
            $('#gradesErrorMessage').show();
            $('#gradesSaveStatus').text('Error').removeClass('bg-primary bg-warning bg-success').addClass('bg-danger');
            
            // Auto-hide error message after 5 seconds
            setTimeout(() => {
                $('#gradesErrorMessage').hide();
            }, 5000);
        }
    });
}

// Validate attendance values
function validateAttendanceValues() {
    let isValid = true;
    let errorMessages = [];
    
    // All attendance input fields should be non-negative
    $('.attendance-input').each(function() {
        const value = parseFloat($(this).val());
        if (isNaN(value) || value < 0) {
            isValid = false;
            errorMessages.push('All attendance values must be non-negative numbers');
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    
    if (!isValid) {
        // Show error message
        $('#attendanceErrorText').html(errorMessages.join('<br>'));
        $('#attendanceErrorMessage').show();
        setTimeout(() => {
            $('#attendanceErrorMessage').hide();
        }, 5000);
    }
    
    return isValid;
}

// Submit attendance form via AJAX
function submitAttendanceForm() {
    // Validate form values first
    if (!validateAttendanceValues()) {
        return;
    }
    
    // Recalculate all attendance values before submitting
    $('#attendanceTableBody tr').each(function() {
        calculateAttendance($(this));
    });
    
    // Hide any previous error messages
    $('#attendanceErrorMessage').hide();
    
    $('#attendanceSaveStatus').text('Saving...').removeClass('bg-secondary bg-warning bg-danger').addClass('bg-primary');
    
    $.ajax({
        url: 'submit_attendance.php',
        type: 'POST',
        data: $('#attendanceEntryForm').serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#attendanceSaveStatus').text('Saved').removeClass('bg-primary bg-warning bg-danger').addClass('bg-success');
                setTimeout(() => {
                    $('#attendanceSaveStatus').text('Ready').removeClass('bg-success').addClass('bg-secondary');
                }, 3000);
            } else {
                $('#attendanceErrorText').text(response.message);
                $('#attendanceErrorMessage').show();
                $('#attendanceSaveStatus').text('Error').removeClass('bg-primary bg-warning bg-success').addClass('bg-danger');
                
                // Auto-hide error message after 5 seconds
                setTimeout(() => {
                    $('#attendanceErrorMessage').hide();
                }, 5000);
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", status, error);
            console.log("Response:", xhr.responseText);
            
            let errorMessage = 'Error saving attendance. Please try again.';
            
            // Check for redirect or unauthorized response
            if (xhr.responseText && xhr.responseText.includes('UNAUTHORIZED')) {
                errorMessage = 'You do not have permission to save attendance. Please check your access rights.';
            } else if (xhr.status === 403) {
                errorMessage = 'Access denied. You do not have permission to perform this action.';
            } else if (xhr.status === 0) {
                errorMessage = 'Connection error. Please check your internet connection and try again.';
            } else if (error) {
                errorMessage = 'Error saving attendance: ' + error;
            }
            
            $('#attendanceErrorText').text(errorMessage);
            $('#attendanceErrorMessage').show();
            $('#attendanceSaveStatus').text('Error').removeClass('bg-primary bg-warning bg-success').addClass('bg-danger');
            
            // Auto-hide error message after 5 seconds
            setTimeout(() => {
                $('#attendanceErrorMessage').hide();
            }, 5000);
        }
    });
}

// Set up data handlers
function setupDataHandlers() {
    // Handle input changes to calculate quiz average and total for grades
    $(document).on('input', '.grade-input', function() {
        // Validate input based on max attribute
        const maxValue = parseFloat($(this).attr('max'));
        const currentValue = parseFloat($(this).val());
        
        if (!isNaN(currentValue) && !isNaN(maxValue) && currentValue > maxValue) {
            $(this).addClass('is-invalid');
            $(this).val(maxValue); // Set to max allowed value
            
            // Show a warning toast
            showValidationWarning(`Value exceeds maximum limit of ${maxValue}. Adjusted to maximum.`);
        } else {
            $(this).removeClass('is-invalid');
        }
        
        const row = $(this).closest('tr');
        calculateQuizAvg(row);
        calculateTotal(row);
        
        // Update status to indicate changes
        $('#gradesSaveStatus').text('Unsaved changes').removeClass('bg-secondary bg-success').addClass('bg-warning');
        
        // Schedule auto-save after 30 seconds of inactivity
        scheduleAutoSave('grades');
    });
    
    // Also validate on blur to catch all changes
    $(document).on('blur', '.grade-input', function() {
        // Get the min and max values from attributes
        const minValue = parseFloat($(this).attr('min')) || 0;
        const maxValue = parseFloat($(this).attr('max'));
        const currentValue = parseFloat($(this).val());
        
        // Skip if empty and not required
        if ($(this).val() === '' && !$(this).hasClass('required-field')) {
            return;
        }
        
        // Validate the value
        if (isNaN(currentValue)) {
            $(this).addClass('is-invalid');
            $(this).val(minValue); // Reset to minimum
            showValidationWarning('Invalid value. Please enter a number.');
        } else if (currentValue < minValue) {
            $(this).addClass('is-invalid');
            $(this).val(minValue); // Set to min allowed value
            showValidationWarning(`Value below minimum of ${minValue}. Adjusted to minimum.`);
        } else if (!isNaN(maxValue) && currentValue > maxValue) {
            $(this).addClass('is-invalid');
            $(this).val(maxValue); // Set to max allowed value
            showValidationWarning(`Value exceeds maximum limit of ${maxValue}. Adjusted to maximum.`);
        } else {
            $(this).removeClass('is-invalid');
        }
        
        // Recalculate
        const row = $(this).closest('tr');
        calculateQuizAvg(row);
        calculateTotal(row);
    });
    
    // Handle input changes to calculate attendance values
    $(document).on('input', '.attendance-input', function() {
        const row = $(this).closest('tr');
        calculateAttendance(row);
        
        // Update status to indicate changes
        $('#attendanceSaveStatus').text('Unsaved changes').removeClass('bg-secondary bg-success').addClass('bg-warning');
        
        // Schedule auto-save after 30 seconds of inactivity
        scheduleAutoSave('attendance');
    });
    
    // Set up search functionality for grades table
    $('#gradesSearchInput').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        filterTable('#gradesTableBody', searchTerm);
    });
    
    // Set up search functionality for attendance table
    $('#attendanceSearchInput').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        filterTable('#attendanceTableBody', searchTerm);
    });
    
    // Set up sorting functionality for tables
    $('.grades-table th[data-sort], .attendance-table th[data-sort]').on('click', function() {
        const table = $(this).closest('table');
        const tbody = table.find('tbody');
        const sortField = $(this).data('sort');
        
        // Toggle sort direction
        const currentDir = $(this).hasClass('asc') ? 'desc' : 'asc';
        
        // Remove sort classes from all columns
        table.find('th').removeClass('asc desc');
        $(this).addClass(currentDir);
        
        // Sort the table
        sortTable(tbody, sortField, currentDir);
    });
    
    // Reset grades form to initial values
    $('#resetGradesBtn').on('click', function() {
        if (confirm('Are you sure you want to reset all values to their initial state? Unsaved changes will be lost.')) {
            const groupCourseId = $('#grade_group_course_id').val();
            loadTraineesForGrades(groupCourseId);
            $('#gradesSaveStatus').text('Reset complete').removeClass('bg-warning bg-success bg-danger').addClass('bg-info');
            setTimeout(() => {
                $('#gradesSaveStatus').text('Ready').removeClass('bg-info').addClass('bg-secondary');
            }, 3000);
        }
    });
    
    // Reset attendance form to initial values
    $('#resetAttendanceBtn').on('click', function() {
        if (confirm('Are you sure you want to reset all values to their initial state? Unsaved changes will be lost.')) {
            const groupCourseId = $('#attendance_group_course_id').val();
            loadTraineesForAttendance(groupCourseId);
            $('#attendanceSaveStatus').text('Reset complete').removeClass('bg-warning bg-success bg-danger').addClass('bg-info');
            setTimeout(() => {
                $('#attendanceSaveStatus').text('Ready').removeClass('bg-info').addClass('bg-secondary');
            }, 3000);
        }
    });
    
    // Enable paste functionality for grades table
    setupPasteHandling('#gradesTableBody');
    
    // Enable paste functionality for attendance table
    setupPasteHandling('#attendanceTableBody');
}

// Calculate Quiz Average for a row
function calculateQuizAvg(row) {
    const quiz1 = parseFloat(row.find('.quiz1').val()) || 0;
    const quiz2 = row.find('.quiz2').val() ? parseFloat(row.find('.quiz2').val()) : null;
    const quiz3 = row.find('.quiz3').val() ? parseFloat(row.find('.quiz3').val()) : null;
    
    let quizCount = 1; // Quiz 1 is mandatory
    let quizSum = quiz1;
    
    if (quiz2 !== null) {
        quizCount++;
        quizSum += quiz2;
    }
    
    if (quiz3 !== null) {
        quizCount++;
        quizSum += quiz3;
    }
    
    const quizAvg = quizSum / quizCount;
    row.find('.quiz-avg').val(quizAvg.toFixed(1));
}

// Calculate Total for a row
function calculateTotal(row) {
    const attendance = parseFloat(row.find('.attendance').val()) || 0;
    const participation = parseFloat(row.find('.participation').val()) || 0;
    const quizAvg = parseFloat(row.find('.quiz-avg').val()) || 0;
    const finalExam = parseFloat(row.find('.final-exam').val()) || 0;
    
    const total = attendance + participation + quizAvg + finalExam;
    row.find('.course-total').val(total.toFixed(1));
}

// Show validation warning toast/message
function showValidationWarning(message) {
    // Check if a toast container exists, if not create one
    if ($('#toast-container').length === 0) {
        $('body').append('<div id="toast-container" class="position-fixed top-0 end-0 p-3" style="z-index: 1100;"></div>');
    }
    
    // Create a unique ID for this toast
    const toastId = 'validation-warning-' + Date.now();
    
    // Create the toast HTML
    const toast = `
        <div id="${toastId}" class="toast align-items-center text-white bg-warning border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="ri-error-warning-line me-2"></i> ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    // Add the toast to the container
    $('#toast-container').append(toast);
    
    // Initialize and show the toast
    const toastEl = document.getElementById(toastId);
    if (bootstrap && bootstrap.Toast) {
        const bsToast = new bootstrap.Toast(toastEl, {
            autohide: true,
            delay: 3000
        });
        bsToast.show();
    } else {
        // Fallback if Bootstrap Toast is not available
        $(toastEl).addClass('show');
        setTimeout(() => {
            $(toastEl).removeClass('show');
            setTimeout(() => $(toastEl).remove(), 300);
        }, 3000);
    }
}

// Schedule auto-save after a period of inactivity
function scheduleAutoSave(formType) {
    // Clear existing timeout if any
    if (formType === 'grades' && gradesSaveTimeout) {
        clearTimeout(gradesSaveTimeout);
    } else if (formType === 'attendance' && attendanceSaveTimeout) {
        clearTimeout(attendanceSaveTimeout);
    }
    
    // Set new timeout
    const timeout = setTimeout(() => {
        if (formType === 'grades') {
            $('#gradesSaveStatus').text('Auto-saving...').removeClass('bg-warning').addClass('bg-primary');
            submitGradesForm();
        } else if (formType === 'attendance') {
            $('#attendanceSaveStatus').text('Auto-saving...').removeClass('bg-warning').addClass('bg-primary');
            submitAttendanceForm();
        }
    }, 30000); // 30 seconds of inactivity
    
    // Store the timeout ID
    if (formType === 'grades') {
        gradesSaveTimeout = timeout;
    } else if (formType === 'attendance') {
        attendanceSaveTimeout = timeout;
    }
}

// Set up paste handling for editable tables
function setupPasteHandling(tableBodySelector) {
    $(document).on('paste', tableBodySelector + ' input', function(e) {
        e.preventDefault();
        
        // Get pasted data
        let pastedData = (e.originalEvent.clipboardData || window.clipboardData).getData('text');
        
        // Process the pasted data
        const rows = pastedData.split(/\r\n|\n|\r/);
        if (rows.length <= 1) return; // Not a multi-line paste
        
        const currentCell = $(this);
        const currentRow = currentCell.closest('tr');
        const currentTable = currentRow.closest('tbody');
        const currentCellIndex = currentRow.find('input:visible').index(currentCell);
        
        // Process each row of pasted data
        rows.forEach((rowData, rowIndex) => {
            if (!rowData.trim()) return; // Skip empty rows
            
            const targetRow = currentTable.find('tr').eq(currentRow.index() + rowIndex);
            if (targetRow.length === 0) return; // Skip if row doesn't exist
            
            const cells = rowData.split(/\t/);
            
            // Process each cell in the row
            cells.forEach((cellData, cellIndex) => {
                const targetCellIndex = currentCellIndex + cellIndex;
                const targetInput = targetRow.find('input:visible').eq(targetCellIndex);
                
                // Only update if input is not readonly
                if (targetInput.length && !targetInput.prop('readonly')) {
                    targetInput.val(cellData.trim());
                    targetInput.trigger('input'); // Trigger calculations
                }
            });
        });
    });
}

// Filter table rows based on search term
function filterTable(tableBodySelector, searchTerm) {
    $(tableBodySelector + ' tr').each(function() {
        const row = $(this);
        const firstNameCell = row.find('td:nth-child(1) input').val().toLowerCase();
        const lastNameCell = row.find('td:nth-child(2) input').val().toLowerCase();
        const govIdCell = row.find('td:nth-child(3) input').val().toLowerCase();
        
        // If any cell contains the search term, show the row, otherwise hide it
        if (firstNameCell.includes(searchTerm) || lastNameCell.includes(searchTerm) || govIdCell.includes(searchTerm)) {
            row.show();
        } else {
            row.hide();
        }
    });
}

// Sort table rows based on field and direction
function sortTable(tbody, field, direction) {
    const rows = tbody.find('tr').toArray();
    
    rows.sort((a, b) => {
        let valA, valB;
        
        switch(field) {
            case 'name':
                valA = $(a).find('td:nth-child(1) input').val();
                valB = $(b).find('td:nth-child(1) input').val();
                return direction === 'asc' ? valA.localeCompare(valB) : valB.localeCompare(valA);
            case 'lastname':
                valA = $(a).find('td:nth-child(2) input').val();
                valB = $(b).find('td:nth-child(2) input').val();
                return direction === 'asc' ? valA.localeCompare(valB) : valB.localeCompare(valA);
            case 'govid':
                valA = $(a).find('td:nth-child(3) input').val();
                valB = $(b).find('td:nth-child(3) input').val();
                return direction === 'asc' ? valA.localeCompare(valB) : valB.localeCompare(valA);
            default:
                // For numeric fields (find the correct column index based on field)
                let colIndex;
                switch(field) {
                    case 'pretest': colIndex = 4; break;
                    case 'attgrade': colIndex = 5; break;
                    case 'participation': colIndex = 6; break;
                    case 'quiz1': colIndex = 7; break;
                    case 'quiz2': colIndex = 8; break;
                    case 'quiz3': colIndex = 9; break;
                    case 'quizavg': colIndex = 10; break;
                    case 'finaltest': colIndex = 11; break;
                    case 'total': colIndex = 12; break;
                    case 'present': colIndex = 4; break;
                    case 'excused': colIndex = 5; break;
                    case 'late': colIndex = 6; break;
                    case 'absent': colIndex = 7; break;
                    case 'points': colIndex = 8; break;
                    case 'sessions': colIndex = 9; break;
                    case 'percentage': colIndex = 10; break;
                    default: colIndex = 0;
                }
                
                valA = parseFloat($(a).find('td:nth-child(' + colIndex + ') input').val()) || 0;
                valB = parseFloat($(b).find('td:nth-child(' + colIndex + ') input').val()) || 0;
                return direction === 'asc' ? valA - valB : valB - valA;
        }
    });
    
    // Re-append sorted rows to the tbody
    $.each(rows, function(index, row) {
        tbody.append(row);
    });
}

// Calculate attendance values based on P, E, L, A inputs
function calculateAttendance(row) {
    // Get input values
    const presentHours = parseFloat(row.find('.present-hours').val()) || 0;
    const excusedHours = parseFloat(row.find('.excused-hours').val()) || 0;
    const lateHours = parseFloat(row.find('.late-hours').val()) || 0;
    const absentHours = parseFloat(row.find('.absent-hours').val()) || 0;
    
    // Formula 1: Taken Sessions = Present + Excused + Late + Absent
    const takenSessions = presentHours + excusedHours + lateHours + absentHours;
    row.find('.taken-sessions').val(Math.round(takenSessions));
    
    // Formula 2: Points = 2 * Present + 1 * Excused
    const points = (2 * presentHours) + (1 * excusedHours);
    
    // NO capping at 10, show the actual calculated points with 1 decimal place
    row.find('.points').val(points.toFixed(1));
    
    // Formula 3: Percentage = Points / (Taken Sessions * 2) * 100
    let percentage = 0;
    if (takenSessions > 0) {
        percentage = (points / (takenSessions * 2)) * 100;
    }
    // Ensure percentage is between 0-100, with 1 decimal place
    const clampedPercentage = Math.min(Math.max(percentage, 0), 100);
    row.find('.attendance-percentage').val(clampedPercentage.toFixed(1));
    
    // Mark the attendance status as changed so user knows to save
    $('#attendanceSaveStatus').text('Unsaved changes').removeClass('bg-secondary bg-success').addClass('bg-warning');
}

/**
 * New Group Wizard - Client-side Logic
 * Handles multi-step form navigation, validation, and AJAX submissions.
 */

'use strict';

console.log('assets/dashjs/group_wizard.js: Script loaded and executing.'); // Diagnostic log

// Expose the initialization function globally
window.initGroupWizard = function() {
    console.log('initGroupWizard function called.'); // Diagnostic log
    const wizardForm = document.getElementById('wizard-form');
    const wizardModal = document.getElementById('groupWizardModal'); // Corrected ID to match includes/footer.php
    let bsStepper; // Variable to hold the BS-Stepper instance
    let currentGroupID = null; // To store the GroupID after Step 1

    // Initialize BS-Stepper
    if (typeof Stepper !== 'undefined' && wizardModal) {
        bsStepper = new Stepper(wizardModal, {
            linear: true, // Force linear progression
            animation: true
        });
    } else {
        console.error('BS-Stepper or wizard-modal element not found. Cannot initialize wizard.');
        return; // Exit if essential elements are missing
    }

    // Event listener for modal show to re-initialize stepper and clear form
    // This event listener is now handled by the calling script (dashboards/index.php)
    // and initGroupWizard is called when the modal is shown.
    // So, we just need to ensure the form is reset and state is cleared on init.
    if (bsStepper) {
        bsStepper.to(1); // Go to the first step
        wizardForm.reset(); // Reset the form
        currentGroupID = null; // Clear stored group ID
        $('#traineePreviewTable tbody').empty(); // Clear trainee preview
        $('.course-details-fields').hide(); // Hide course details
        $('.course-checkbox').prop('checked', false); // Uncheck all courses
        // Clear confirmation summary
        $('#confirmGroupName').text('');
        $('#confirmGroupDates').text('');
        $('#confirmCoordinator').text('');
        $('#confirmTraineeList').empty();
        $('#confirmCourseList').empty();
    }


    // Handle Next button clicks
    wizardModal.addEventListener('click', function (event) {
        if (event.target.classList.contains('btn-next')) {
            const currentStep = bsStepper._currentIndex + 1; // Stepper is 0-indexed

            if (currentStep === 1) { // Group Details
                if (!validateStep1()) {
                    return;
                }
                submitStep1();
            } else if (currentStep === 2) { // Add Trainees
                if (!validateStep2()) {
                    return;
                }
                submitStep2();
            } else if (currentStep === 3) { // Assign Courses
                submitStep3(); // Validation for this step is more about data collection than strict blocking
            }
        }
    });

    // Handle Previous button clicks
    wizardModal.addEventListener('click', function (event) {
        if (event.target.classList.contains('btn-prev')) {
            bsStepper.previous();
        }
    });

    // Handle Submit button click (Final step)
    wizardModal.addEventListener('click', function (event) {
        if (event.target.classList.contains('btn-submit')) {
            submitFinal();
        }
    });

    // --- Step 1: Group Details Functions ---
    function validateStep1() {
        const groupName = document.getElementById('groupName').value.trim();
        if (!groupName) {
            showToast('Error', 'Group Name is required.', 'danger');
            return false;
        }
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;
        if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
            showToast('Error', 'Start Date cannot be after End Date.', 'danger');
            return false;
        }
        return true;
    }

    function submitStep1() {
        const formData = new FormData(wizardForm);
        formData.append('action', 'create_group');

        $.ajax({
            url: '../api/group_wizard_api.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function() {
                showLoadingOverlay(true);
            },
            success: function (response) {
                if (response.success) {
                    currentGroupID = response.data.groupID;
                    showToast('Success', response.message, 'success');
                    bsStepper.next();
                    updateConfirmationSummary();
                } else {
                    showToast('Error', response.message, 'danger');
                }
            },
            error: function (xhr, status, error) {
                showToast('Error', 'AJAX Error: ' + error, 'danger');
            },
            complete: function() {
                showLoadingOverlay(false);
            }
        });
    }

    // --- Step 2: Add Trainees Functions ---
    const traineeDataTextarea = document.getElementById('traineeData');
    traineeDataTextarea.addEventListener('input', updateTraineePreview);

    function updateTraineePreview() {
        const data = traineeDataTextarea.value.trim();
        const rows = data.split('\n');
        const tbody = $('#traineePreviewTable tbody');
        tbody.empty();

        if (data === '') {
            return;
        }

        rows.forEach(row => {
            const cols = row.split(',').map(col => col.trim());
            if (cols.length >= 3) { // At least FirstName, LastName, Email
                const firstName = cols[0] || '';
                const lastName = cols[1] || '';
                const email = cols[2] || '';
                const govID = cols[3] || '';
                const phone = cols[4] || '';

                tbody.append(`
                    <tr>
                        <td>${htmlspecialchars(firstName)}</td>
                        <td>${htmlspecialchars(lastName)}</td>
                        <td>${htmlspecialchars(email)}</td>
                        <td>${htmlspecialchars(govID)}</td>
                        <td>${htmlspecialchars(phone)}</td>
                    </tr>
                `);
            }
        });
    }

    function validateStep2() {
        const data = traineeDataTextarea.value.trim();
        if (data === '') {
            showToast('Info', 'No trainees entered. You can proceed without adding trainees or enter data.', 'info');
            return true; // Allow proceeding without trainees
        }

        const rows = data.split('\n');
        let isValid = true;
        rows.forEach((row, index) => {
            const cols = row.split(',').map(col => col.trim());
            if (cols.length < 3 || !cols[0] || !cols[1] || !cols[2]) {
                showToast('Warning', `Row ${index + 1}: Each trainee must have at least First Name, Last Name, and Email.`, 'warning');
                isValid = false;
            }
            if (cols[2] && !isValidEmail(cols[2])) {
                showToast('Warning', `Row ${index + 1}: Invalid email format for "${cols[2]}".`, 'warning');
                isValid = false;
            }
        });
        return isValid;
    }

    function submitStep2() {
        const data = traineeDataTextarea.value.trim();
        if (data === '') {
            bsStepper.next(); // Proceed if no trainees entered
            updateConfirmationSummary();
            return;
        }

        const trainees = [];
        const rows = data.split('\n');
        rows.forEach(row => {
            const cols = row.split(',').map(col => col.trim());
            if (cols.length >= 3) {
                trainees.push({
                    FirstName: cols[0],
                    LastName: cols[1],
                    Email: cols[2],
                    GovID: cols[3] || null,
                    Phone: cols[4] || null
                });
            }
        });

        const formData = new FormData();
        formData.append('action', 'add_new_trainees_to_group');
        formData.append('groupID', currentGroupID);
        formData.append('trainees', JSON.stringify(trainees));

        $.ajax({
            url: '../api/group_wizard_api.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function() {
                showLoadingOverlay(true);
            },
            success: function (response) {
                if (response.success) {
                    showToast('Success', response.message, 'success');
                    bsStepper.next();
                    updateConfirmationSummary();
                } else {
                    showToast('Error', response.message, 'danger');
                }
            },
            error: function (xhr, status, error) {
                showToast('Error', 'AJAX Error: ' + error, 'danger');
            },
            complete: function() {
                showLoadingOverlay(false);
            }
        });
    }

    // --- Step 3: Assign Courses Functions ---
    document.querySelectorAll('.course-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const courseCard = this.closest('.course-assignment-card');
            const detailsFields = courseCard.querySelector('.course-details-fields');
            if (this.checked) {
                $(detailsFields).slideDown();
            } else {
                $(detailsFields).slideUp();
                // Optionally clear fields when unchecked
                detailsFields.querySelectorAll('input, select, textarea').forEach(field => {
                    if (field.type === 'select-one') {
                        field.value = '';
                    } else {
                        field.value = '';
                    }
                });
            }
        });
    });

    function submitStep3() {
        const selectedCourses = [];
        document.querySelectorAll('.course-checkbox:checked').forEach(checkbox => {
            const courseID = checkbox.value;
            const courseCard = checkbox.closest('.course-assignment-card');
            const instructorID = courseCard.querySelector(`[name="course_instructor[${courseID}]"]`).value;
            const startDate = courseCard.querySelector(`[name="course_startDate[${courseID}]"]`).value;
            const endDate = courseCard.querySelector(`[name="course_endDate[${courseID}]"]`).value;
            const location = courseCard.querySelector(`[name="course_location[${courseID}]"]`).value;
            const scheduleDetails = courseCard.querySelector(`[name="course_scheduleDetails[${courseID}]"]`).value;

            selectedCourses.push({
                CourseID: courseID,
                InstructorID: instructorID,
                StartDate: startDate,
                EndDate: endDate,
                Location: location,
                ScheduleDetails: scheduleDetails
            });
        });

        if (selectedCourses.length === 0) {
            showToast('Info', 'No courses selected. You can proceed without assigning courses.', 'info');
            bsStepper.next(); // Allow proceeding without courses
            updateConfirmationSummary();
            return;
        }

        const formData = new FormData();
        formData.append('action', 'assign_course_instances_to_group');
        formData.append('groupID', currentGroupID);
        formData.append('courses', JSON.stringify(selectedCourses));

        $.ajax({
            url: '../api/group_wizard_api.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function() {
                showLoadingOverlay(true);
            },
            success: function (response) {
                if (response.success) {
                    showToast('Success', response.message, 'success');
                    bsStepper.next();
                    updateConfirmationSummary();
                } else {
                    showToast('Error', response.message, 'danger');
                }
            },
            error: function (xhr, status, error) {
                showToast('Error', 'AJAX Error: ' + error, 'danger');
            },
            complete: function() {
                showLoadingOverlay(false);
            }
        });
    }

    // --- Step 4: Confirmation Functions ---
    function updateConfirmationSummary() {
        // Group Details
        const groupName = document.getElementById('groupName').value;
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;
        const coordinatorSelect = document.getElementById('coordinatorID');
        const coordinatorName = coordinatorSelect.options[coordinatorSelect.selectedIndex].text;

        $('#confirmGroupName').text(`Group Name: ${groupName}`);
        $('#confirmGroupDates').text(`Dates: ${startDate} to ${endDate}`);
        $('#confirmCoordinator').text(`Coordinator: ${coordinatorName}`);

        // Trainees
        const traineeList = $('#confirmTraineeList');
        traineeList.empty();
        const traineeData = traineeDataTextarea.value.trim();
        if (traineeData) {
            traineeData.split('\n').forEach(row => {
                const cols = row.split(',').map(col => col.trim());
                if (cols.length >= 3) {
                    traineeList.append(`<li>${htmlspecialchars(cols[0])} ${htmlspecialchars(cols[1])} (${htmlspecialchars(cols[2])})</li>`);
                }
            });
        } else {
            traineeList.append('<li>No new trainees added.</li>');
        }

        // Courses
        const courseList = $('#confirmCourseList');
        courseList.empty();
        document.querySelectorAll('.course-checkbox:checked').forEach(checkbox => {
            const courseCard = checkbox.closest('.course-assignment-card');
            const courseName = courseCard.querySelector('label').textContent.split('(')[0].trim();
            const instructorSelect = courseCard.querySelector(`[name="course_instructor[${checkbox.value}]"]`);
            const instructorName = instructorSelect.options[instructorSelect.selectedIndex].text;
            const courseStartDate = courseCard.querySelector(`[name="course_startDate[${checkbox.value}]"]`).value;
            const courseEndDate = courseCard.querySelector(`[name="course_endDate[${checkbox.value}]"]`).value;

            courseList.append(`<li>${htmlspecialchars(courseName)} (Instructor: ${htmlspecialchars(instructorName)}, Dates: ${courseStartDate} to ${courseEndDate})</li>`);
        });
        if (courseList.children().length === 0) {
            courseList.append('<li>No courses assigned.</li>');
        }
    }

    function submitFinal() {
        // This step is primarily for confirmation. The actual data submission
        // happens in previous steps. Here, we just show a final success message
        // and potentially redirect or close the modal.
        showToast('Success', 'New Group Wizard completed successfully!', 'success');
        // Optionally, close the modal or redirect
        $('#groupWizardModal').modal('hide');
        // Reload the page to reflect changes, or redirect to groups page
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    }

    // --- Utility Functions ---
    function showToast(title, message, type) {
        // Assuming a toast function exists globally or can be implemented here
        // For now, a simple alert or console log
        console.log(`${type.toUpperCase()}: ${title} - ${message}`);
        // Example using a hypothetical toast library:
        // toastr[type](message, title);
        // Or if using Bootstrap toasts:
        // const toastEl = document.getElementById('liveToast');
        // const toast = new bootstrap.Toast(toastEl);
        // toastEl.querySelector('.toast-header strong').textContent = title;
        // toastEl.querySelector('.toast-body').textContent = message;
        // toast.show();
    }

    function showLoadingOverlay(show) {
        const modalBody = $('#groupWizardModal .modal-body');
        const spinner = modalBody.find('.spinner-border');
        if (show) {
            modalBody.children().hide(); // Hide current content
            spinner.show(); // Show spinner
        } else {
            spinner.hide(); // Hide spinner
            modalBody.children().show(); // Show content
        }
    }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function htmlspecialchars(str) {
        var map = {
            '&': '&',
            '<': '<',
            '>': '>',
            '"': '"',
            "'": '&#039;'
        };
        return str.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
}; // End of initGroupWizard function

// Initial call to update preview if there's any pre-filled data (unlikely for new form)
// This will now be called by initGroupWizard when the modal content is loaded.
// updateTraineePreview(); // Removed from global scope

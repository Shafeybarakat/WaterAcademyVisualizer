/**
 * groupModal.js - Handles the group edit modal functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Get references to DOM elements
    const groupDetailModal = document.getElementById('groupDetailModal');
    const groupDetailContent = document.getElementById('groupDetailContent');
    const saveGroupBtn = document.getElementById('saveGroupBtn');
    
    // Add event listener for when the modal is shown
    if (groupDetailModal) {
        groupDetailModal.addEventListener('show.bs.modal', function(event) {
            // Get the button that triggered the modal
            const button = event.relatedTarget;
            
            // Extract group ID from data attribute
            const groupId = button.getAttribute('data-group-id');
            
            // Show loading message
            groupDetailContent.innerHTML = 'Loading group data...';
            
            // Fetch group data
            fetch(`../dashboards/get_group.php?id=${groupId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    // Format dates for display (yyyy-mm-dd to dd/mm/yyyy)
                    const formatDateForDisplay = (dateStr) => {
                        if (!dateStr) return '';
                        const parts = dateStr.split('-');
                        if (parts.length !== 3) return dateStr;
                        return `${parts[2]}/${parts[1]}/${parts[0]}`;
                    };
                    
                    // Format dates for input (dd/mm/yyyy to yyyy-mm-dd)
                    const formatDateForInput = (dateStr) => {
                        if (!dateStr) return '';
                        const parts = dateStr.split('/');
                        if (parts.length !== 3) return dateStr;
                        return `${parts[2]}-${parts[1]}-${parts[0]}`;
                    };
                    
                    // Create form with group data
                    let formHtml = `
                        <form id="editGroupForm">
                            <input type="hidden" name="group_id" value="${data.id}">
                            
                            <h5 class="mb-3">Group Information</h5>
                            
                            <div class="mb-3">
                                <label for="groupName" class="form-label">Group Name</label>
                                <input type="text" class="form-control" id="groupName" name="name" value="${data.name || ''}" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="groupDescription" class="form-label">Description</label>
                                <textarea class="form-control" id="groupDescription" name="description" rows="3">${data.description || ''}</textarea>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="startDate" class="form-label">Start Date (DD/MM/YYYY)</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="startDate" name="start_date" value="${data.start_date || ''}" placeholder="DD/MM/YYYY" required>
                                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="endDate" class="form-label">End Date (DD/MM/YYYY)</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="endDate" name="end_date" value="${data.end_date || ''}" placeholder="DD/MM/YYYY" required>
                                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="roomNumber" class="form-label">Room Number</label>
                                <input type="text" class="form-control" id="roomNumber" name="room_number" value="${data.room_number || ''}">
                            </div>
                            
                            <h5 class="mb-3 mt-4">Courses</h5>
                            
                            <div id="coursesContainer">
                                <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Course</th>
                                            <th>Instructor</th>
                                            <th>Start Date (DD/MM/YYYY)</th>
                                            <th>End Date (DD/MM/YYYY)</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="coursesTableBody">
                    `;
                    
                    // Add existing courses to the table
                    if (data.courses && data.courses.length > 0) {
                        data.courses.forEach((course, index) => {
                            formHtml += `
                                <tr data-index="${index}">
                                    <td>
                                        <input type="hidden" name="courses[${index}][group_course_id]" value="${course.group_course_id}">
                                        <input type="hidden" name="courses[${index}][course_id]" value="${course.CourseID}">
                                        ${course.CourseName} (${course.CourseCode})
                                    </td>
                                    <td>
                                        <select class="form-select" name="courses[${index}][instructor_id]">
                                            <option value="">Select Instructor</option>
                                            ${data.available_instructors.map(instructor => 
                                                `<option value="${instructor.UserID}" ${instructor.UserID == course.InstructorID ? 'selected' : ''}>${instructor.InstructorName}</option>`
                                            ).join('')}
                                        </select>
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="courses[${index}][start_date]" value="${course.course_start_date || ''}" placeholder="DD/MM/YYYY">
                                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="courses[${index}][end_date]" value="${course.course_end_date || ''}" placeholder="DD/MM/YYYY">
                                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                        </div>
                                    </td>
                                    <td>
                                        <select class="form-select" name="courses[${index}][status]">
                                            <option value="Scheduled" ${course.Status === 'Scheduled' ? 'selected' : ''}>Scheduled</option>
                                            <option value="In Progress" ${course.Status === 'In Progress' ? 'selected' : ''}>In Progress</option>
                                            <option value="Completed" ${course.Status === 'Completed' ? 'selected' : ''}>Completed</option>
                                            <option value="Cancelled" ${course.Status === 'Cancelled' ? 'selected' : ''}>Cancelled</option>
                                        </select>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger d-block w-100 remove-course" data-index="${index}">Remove</button>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        formHtml += `
                            <tr>
                                <td colspan="6" class="text-center">No courses assigned to this group yet.</td>
                            </tr>
                        `;
                    }
                    
                    formHtml += `
                                    </tbody>
                                </table>
                                </div>
                                
                                <div class="mb-3">
                                    <button type="button" class="btn btn-sm btn-primary d-block d-md-inline-block w-100 w-md-auto" id="addCourseBtn">Add Course</button>
                                </div>
                                
                                <!-- Template for adding new course (hidden) -->
                                <div id="newCourseTemplate" style="display: none;">
                                    <div class="card mb-3 new-course-card">
                                        <div class="card-body">
                                            <h6 class="card-title">Add New Course</h6>
                                            <div class="mb-3">
                                                <label class="form-label">Course</label>
                                                <select class="form-select new-course-select">
                                                    <option value="">Select Course</option>
                                                    ${data.available_courses.map(course => 
                                                        `<option value="${course.CourseID}">${course.CourseName} (${course.CourseCode})</option>`
                                                    ).join('')}
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Instructor</label>
                                                <select class="form-select new-instructor-select">
                                                    <option value="">Select Instructor</option>
                                                    ${data.available_instructors.map(instructor => 
                                                        `<option value="${instructor.UserID}">${instructor.InstructorName}</option>`
                                                    ).join('')}
                                                </select>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Start Date (DD/MM/YYYY)</label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control new-start-date" placeholder="DD/MM/YYYY">
                                                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">End Date (DD/MM/YYYY)</label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control new-end-date" placeholder="DD/MM/YYYY">
                                                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Status</label>
                                                <select class="form-select new-status-select">
                                                    <option value="Scheduled">Scheduled</option>
                                                    <option value="In Progress">In Progress</option>
                                                    <option value="Completed">Completed</option>
                                                    <option value="Cancelled">Cancelled</option>
                                                </select>
                                            </div>
                                            <div class="d-flex flex-column flex-md-row justify-content-md-end">
                                                <button type="button" class="btn btn-secondary mb-2 mb-md-0 me-md-2 w-100 w-md-auto cancel-add-course">Cancel</button>
                                                <button type="button" class="btn btn-primary w-100 w-md-auto confirm-add-course">Add</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    `;
                    
                    // Update modal content with the form
                    groupDetailContent.innerHTML = formHtml;
                    
                    // Add event listeners for course management
                    setupCourseManagement();
                })
                .catch(error => {
                    console.error('Error fetching group data:', error);
                    groupDetailContent.innerHTML = `<div class="alert alert-danger">Error loading group data: ${error.message}</div>`;
                });
        });
    }
    
    // Add event listener for the save button
    if (saveGroupBtn) {
        saveGroupBtn.addEventListener('click', function() {
            const form = document.getElementById('editGroupForm');
            
            if (form) {
                // Create FormData object from the form
                const formData = new FormData(form);
                
                // Send form data to the server
                fetch('../dashboards/update_group.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Close the modal
                        const modal = bootstrap.Modal.getInstance(groupDetailModal);
                        modal.hide();
                        
                        // Reload the page to show updated data
                        window.location.reload();
                    } else {
                        throw new Error('Failed to update group');
                    }
                })
                .catch(error => {
                    console.error('Error updating group:', error);
                    groupDetailContent.innerHTML += `<div class="alert alert-danger mt-3">Error saving changes: ${error.message}</div>`;
                });
            }
        });
    }
});

/**
 * Sets up event listeners for course management in the group modal
 */
function setupCourseManagement() {
    const coursesTableBody = document.getElementById('coursesTableBody');
    const addCourseBtn = document.getElementById('addCourseBtn');
    const newCourseTemplate = document.getElementById('newCourseTemplate');
    
    // Handle "Add Course" button click
    if (addCourseBtn) {
        addCourseBtn.addEventListener('click', function() {
            // Show the new course form
            const newCourseForm = newCourseTemplate.querySelector('.new-course-card').cloneNode(true);
            newCourseForm.style.display = 'block';
            
            // Insert the form after the table
            document.getElementById('coursesContainer').insertBefore(
                newCourseForm, 
                document.getElementById('newCourseTemplate')
            );
            
            // Add event listeners to the new form's buttons
            setupNewCourseFormEvents(newCourseForm);
        });
    }
    
    // Handle "Remove Course" button clicks
    document.querySelectorAll('.remove-course').forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            const courseName = row.querySelector('td:first-child').textContent.trim();
            
            // Show confirmation dialog
            if (confirm(`Are you sure you want to remove "${courseName}" from this group?`)) {
                const groupCourseId = row.querySelector('input[name*="group_course_id"]').value;
                
                // If this is an existing course (has an ID), mark it for deletion
                if (groupCourseId) {
                    // Create a hidden input to mark this course for deletion
                    const deleteInput = document.createElement('input');
                    deleteInput.type = 'hidden';
                    deleteInput.name = 'delete_courses[]';
                    deleteInput.value = groupCourseId;
                    document.getElementById('editGroupForm').appendChild(deleteInput);
                }
                
                // Remove the row from the table
                row.remove();
                
                // If no courses left, show the "No courses" message
                if (coursesTableBody.querySelectorAll('tr').length === 0) {
                    coursesTableBody.innerHTML = `
                        <tr>
                            <td colspan="6" class="text-center">No courses assigned to this group yet.</td>
                        </tr>
                    `;
                }
            }
        });
    });
}

/**
 * Sets up event listeners for the new course form
 */
function setupNewCourseFormEvents(formElement) {
    const cancelBtn = formElement.querySelector('.cancel-add-course');
    const confirmBtn = formElement.querySelector('.confirm-add-course');
    
    // Cancel button removes the form
    cancelBtn.addEventListener('click', function() {
        formElement.remove();
    });
    
    // Confirm button adds the course to the table
    confirmBtn.addEventListener('click', function() {
        const courseSelect = formElement.querySelector('.new-course-select');
        const instructorSelect = formElement.querySelector('.new-instructor-select');
        const startDateInput = formElement.querySelector('.new-start-date');
        const endDateInput = formElement.querySelector('.new-end-date');
        const statusSelect = formElement.querySelector('.new-status-select');
        
        // Validate inputs
        if (!courseSelect.value) {
            alert('Please select a course');
            return;
        }
        
        if (!startDateInput.value) {
            alert('Please enter a start date');
            startDateInput.focus();
            return;
        }
        
        if (!endDateInput.value) {
            alert('Please enter an end date');
            endDateInput.focus();
            return;
        }
        
        // Validate date format (DD/MM/YYYY)
        const dateRegex = /^(\d{2})\/(\d{2})\/(\d{4})$/;
        if (!dateRegex.test(startDateInput.value)) {
            alert('Please enter a valid start date in DD/MM/YYYY format');
            startDateInput.focus();
            return;
        }
        
        if (!dateRegex.test(endDateInput.value)) {
            alert('Please enter a valid end date in DD/MM/YYYY format');
            endDateInput.focus();
            return;
        }
        
        // Get the course name and code from the selected option
        const courseOption = courseSelect.options[courseSelect.selectedIndex];
        const courseName = courseOption.text;
        
        // Get the instructor name from the selected option
        let instructorName = '';
        if (instructorSelect.value) {
            instructorName = instructorSelect.options[instructorSelect.selectedIndex].text;
        }
        
        // Get the current number of courses to use as the new index
        const coursesTableBody = document.getElementById('coursesTableBody');
        const currentRows = coursesTableBody.querySelectorAll('tr[data-index]');
        const newIndex = currentRows.length > 0 ? 
            Math.max(...Array.from(currentRows).map(row => parseInt(row.getAttribute('data-index')))) + 1 : 0;
        
        // Remove the "No courses" message if it exists
        const noCoursesRow = coursesTableBody.querySelector('tr:not([data-index])');
        if (noCoursesRow) {
            noCoursesRow.remove();
        }
        
        // Create a new row for the course
        const newRow = document.createElement('tr');
        newRow.setAttribute('data-index', newIndex);
        newRow.innerHTML = `
            <td>
                <input type="hidden" name="courses[${newIndex}][group_course_id]" value="">
                <input type="hidden" name="courses[${newIndex}][course_id]" value="${courseSelect.value}">
                ${courseName}
            </td>
            <td>
                <select class="form-select" name="courses[${newIndex}][instructor_id]">
                    <option value="">Select Instructor</option>
                    ${Array.from(instructorSelect.options).map(opt => 
                        `<option value="${opt.value}" ${opt.value === instructorSelect.value ? 'selected' : ''}>${opt.text}</option>`
                    ).join('')}
                </select>
            </td>
            <td>
                <div class="input-group">
                    <input type="text" class="form-control" name="courses[${newIndex}][start_date]" value="${startDateInput.value}" placeholder="DD/MM/YYYY">
                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                </div>
            </td>
            <td>
                <div class="input-group">
                    <input type="text" class="form-control" name="courses[${newIndex}][end_date]" value="${endDateInput.value}" placeholder="DD/MM/YYYY">
                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                </div>
            </td>
            <td>
                <select class="form-select" name="courses[${newIndex}][status]">
                    <option value="Scheduled" ${statusSelect.value === 'Scheduled' ? 'selected' : ''}>Scheduled</option>
                    <option value="In Progress" ${statusSelect.value === 'In Progress' ? 'selected' : ''}>In Progress</option>
                    <option value="Completed" ${statusSelect.value === 'Completed' ? 'selected' : ''}>Completed</option>
                    <option value="Cancelled" ${statusSelect.value === 'Cancelled' ? 'selected' : ''}>Cancelled</option>
                </select>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger d-block w-100 remove-course" data-index="${newIndex}">Remove</button>
            </td>
        `;
        
        // Add the new row to the table
        coursesTableBody.appendChild(newRow);
        
        // Add event listener to the new remove button
        newRow.querySelector('.remove-course').addEventListener('click', function() {
            // Show confirmation dialog
            if (confirm(`Are you sure you want to remove "${courseName}" from this group?`)) {
                newRow.remove();
                
                // If no courses left, show the "No courses" message
                if (coursesTableBody.querySelectorAll('tr').length === 0) {
                    coursesTableBody.innerHTML = `
                        <tr>
                            <td colspan="6" class="text-center">No courses assigned to this group yet.</td>
                        </tr>
                    `;
                }
            }
        });
        
        // Remove the form
        formElement.remove();
    });
}

/**
 * Upcoming Events JavaScript
 * Handles the functionality for the Upcoming Events card on the dashboard
 * - Fetches and displays event details in a modal
 * - Handles sending emails to instructors
 * - Handles extending course end dates
 */

document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const eventDetailsModal = document.getElementById('eventDetailsModal');
    const eventDetailsContent = document.getElementById('eventDetailsContent');
    const eventDetailsLoading = document.getElementById('eventDetailsLoading');
    const eventDetailsError = document.getElementById('eventDetailsError');
    const eventDetailsErrorMessage = document.getElementById('eventDetailsErrorMessage');
    const sendEmailBtn = document.getElementById('sendEmailBtn');
    const extendDateBtn = document.getElementById('extendDateBtn');
    
    // Date Extension Modal Elements
    const dateExtensionModal = document.getElementById('dateExtensionModal');
    const extensionEventId = document.getElementById('extensionEventId');
    const extensionEventType = document.getElementById('extensionEventType');
    const currentEndDate = document.getElementById('currentEndDate');
    const newEndDate = document.getElementById('newEndDate');
    const saveExtensionBtn = document.getElementById('saveExtensionBtn');
    
    // Current event data
    let currentEventData = null;
    
    // If any of the required elements don't exist, exit early
    if (!eventDetailsModal || !eventDetailsContent || !eventDetailsLoading || !eventDetailsError) {
        console.error('Required elements for event details modal not found');
        return;
    }
    
    // Event Listeners
    document.querySelectorAll('.event-details-btn').forEach(button => {
        button.addEventListener('click', function() {
            const eventType = this.getAttribute('data-event-type');
            const eventId = this.getAttribute('data-event-id');
            fetchEventDetails(eventType, eventId);
        });
    });
    
    if (sendEmailBtn) {
        sendEmailBtn.addEventListener('click', function() {
            if (currentEventData) {
                sendInstructorEmail(currentEventData);
            }
        });
    }
    
    if (extendDateBtn) {
        extendDateBtn.addEventListener('click', function() {
            if (currentEventData) {
                showDateExtensionModal(currentEventData);
            }
        });
    }
    
    if (saveExtensionBtn) {
        saveExtensionBtn.addEventListener('click', function() {
            saveEndDateExtension();
        });
    }
    
    /**
     * Fetches event details from the server
     * @param {string} eventType - The type of event (course_instance_starting, course_instance_ending, etc.)
     * @param {number} eventId - The ID of the event
     */
    function fetchEventDetails(eventType, eventId) {
        // Reset modal state
        eventDetailsContent.classList.add('d-none');
        eventDetailsError.classList.add('d-none');
        eventDetailsLoading.classList.remove('d-none');
        
        // Disable action buttons until data is loaded
        if (sendEmailBtn) sendEmailBtn.disabled = true;
        if (extendDateBtn) extendDateBtn.disabled = true;
        
        // Fetch event details
        fetch(`get_event_details.php?type=${encodeURIComponent(eventType)}&id=${encodeURIComponent(eventId)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }
                
                // Store current event data
                currentEventData = {
                    type: eventType,
                    id: eventId,
                    ...data
                };
                
                // Display event details
                displayEventDetails(currentEventData);
                
                // Enable action buttons
                if (sendEmailBtn) {
                    sendEmailBtn.disabled = false;
                    // Only show send email button for course events with an instructor
                    if (eventType.includes('course_instance') && data.InstructorEmail) {
                        sendEmailBtn.classList.remove('d-none');
                    } else {
                        sendEmailBtn.classList.add('d-none');
                    }
                }
                
                if (extendDateBtn) {
                    extendDateBtn.disabled = false;
                    // Only show extend date button for ending events
                    if (eventType.includes('ending')) {
                        extendDateBtn.classList.remove('d-none');
                    } else {
                        extendDateBtn.classList.add('d-none');
                    }
                }
            })
            .catch(error => {
                console.error('Error fetching event details:', error);
                eventDetailsErrorMessage.textContent = error.message || 'Error loading event details.';
                eventDetailsError.classList.remove('d-none');
                eventDetailsContent.classList.add('d-none');
                eventDetailsLoading.classList.add('d-none');
            });
    }
    
    /**
     * Displays event details in the modal
     * @param {Object} data - The event data
     */
    function displayEventDetails(data) {
        // Hide loading indicator
        eventDetailsLoading.classList.add('d-none');
        
        // Create content based on event type
        let content = '<div class="row">';
        
        if (data.type.includes('course_instance')) {
            // Course instance event
            content += `
                <div class="col-md-6">
                    <h6 class="mb-2">Course Details</h6>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Course Name</dt>
                        <dd class="col-sm-8">${escapeHtml(data.CourseName || 'N/A')}</dd>
                        
                        <dt class="col-sm-4">Group</dt>
                        <dd class="col-sm-8">${escapeHtml(data.GroupName || 'N/A')}</dd>
                        
                        <dt class="col-sm-4">Start Date</dt>
                        <dd class="col-sm-8">${formatDate(data.StartDate)}</dd>
                        
                        <dt class="col-sm-4">End Date</dt>
                        <dd class="col-sm-8">${formatDate(data.EndDate)}</dd>
                        
                        <dt class="col-sm-4">Duration</dt>
                        <dd class="col-sm-8">${data.DurationWeeks || 'N/A'} weeks (${data.TotalHours || 'N/A'} hours)</dd>
                    </dl>
                </div>
                <div class="col-md-6">
                    <h6 class="mb-2">Instructor Information</h6>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Instructor</dt>
                        <dd class="col-sm-8">${escapeHtml((data.InstructorFirstName && data.InstructorLastName) ? 
                            `${data.InstructorFirstName} ${data.InstructorLastName}` : 'Not Assigned')}</dd>
                        
                        <dt class="col-sm-4">Email</dt>
                        <dd class="col-sm-8">${escapeHtml(data.InstructorEmail || 'N/A')}</dd>
                        
                        <dt class="col-sm-4">Phone</dt>
                        <dd class="col-sm-8">${escapeHtml(data.InstructorPhone || 'N/A')}</dd>
                    </dl>
                </div>
            `;
        } else if (data.type.includes('group_program')) {
            // Group program event
            content += `
                <div class="col-md-6">
                    <h6 class="mb-2">Group Details</h6>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Group Name</dt>
                        <dd class="col-sm-8">${escapeHtml(data.GroupName || 'N/A')}</dd>
                        
                        <dt class="col-sm-4">Company</dt>
                        <dd class="col-sm-8">${escapeHtml(data.CompanyName || 'N/A')}</dd>
                        
                        <dt class="col-sm-4">Start Date</dt>
                        <dd class="col-sm-8">${formatDate(data.StartDate)}</dd>
                        
                        <dt class="col-sm-4">End Date</dt>
                        <dd class="col-sm-8">${formatDate(data.EndDate)}</dd>
                    </dl>
                </div>
                <div class="col-md-6">
                    <h6 class="mb-2">Coordinator Information</h6>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Coordinator</dt>
                        <dd class="col-sm-8">${escapeHtml((data.CoordinatorFirstName && data.CoordinatorLastName) ? 
                            `${data.CoordinatorFirstName} ${data.CoordinatorLastName}` : 'Not Assigned')}</dd>
                        
                        <dt class="col-sm-4">Email</dt>
                        <dd class="col-sm-8">${escapeHtml(data.CoordinatorEmail || 'N/A')}</dd>
                        
                        <dt class="col-sm-4">Phone</dt>
                        <dd class="col-sm-8">${escapeHtml(data.CoordinatorPhone || 'N/A')}</dd>
                    </dl>
                </div>
            `;
        }
        
        content += '</div>';
        
        // Set content and show
        eventDetailsContent.innerHTML = content;
        eventDetailsContent.classList.remove('d-none');
    }
    
    /**
     * Shows the date extension modal with pre-filled data
     * @param {Object} data - The event data
     */
    function showDateExtensionModal(data) {
        if (!dateExtensionModal) return;
        
        // Set form values
        extensionEventId.value = data.id;
        extensionEventType.value = data.type;
        currentEndDate.value = formatDate(data.EndDate);
        
        // Set min date for new end date (current end date + 1 day)
        const minDate = new Date(data.EndDate);
        minDate.setDate(minDate.getDate() + 1);
        newEndDate.min = formatDateForInput(minDate);
        
        // Set default value for new end date (current end date + 7 days)
        const defaultDate = new Date(data.EndDate);
        defaultDate.setDate(defaultDate.getDate() + 7);
        newEndDate.value = formatDateForInput(defaultDate);
        
        // Show modal using Alpine.js
        if (dateExtensionModal.__x) {
            dateExtensionModal.__x.$data.open = true;
        } else {
            console.error('Alpine.js component not found on dateExtensionModal');
        }
    }
    
    /**
     * Saves the end date extension
     */
    function saveEndDateExtension() {
        const form = document.getElementById('dateExtensionForm');
        if (!form) return;
        
        // Validate form
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        // Get form data
        const formData = new FormData(form);
        
        // Show loading state
        saveExtensionBtn.disabled = true;
        saveExtensionBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
        
        // Send request to update end date
        fetch('update_event_date.php', {
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
            if (data.error) {
                throw new Error(data.error);
            }
            
            // Close modal using Alpine.js
            if (dateExtensionModal.__x) {
                dateExtensionModal.__x.$data.open = false;
            }
            
            // Show success message
            alert('End date updated successfully. The page will now reload to reflect the changes.');
            
            // Reload page to reflect changes
            window.location.reload();
        })
        .catch(error => {
            console.error('Error updating end date:', error);
            alert('Error updating end date: ' + error.message);
            
            // Reset button state
            saveExtensionBtn.disabled = false;
            saveExtensionBtn.innerHTML = 'Save Changes';
        });
    }
    
    /**
     * Sends an email to the instructor
     * @param {Object} data - The event data
     */
    function sendInstructorEmail(data) {
        if (!data.InstructorEmail) {
            alert('No instructor email available.');
            return;
        }
        
        // Determine if it's a starting or ending event
        const isEnding = data.type.includes('ending');
        
        // Create email subject and body
        let subject, body;
        
        if (isEnding) {
            subject = `Action Required: Submit Grades for ${data.CourseName} - ${data.GroupName}`;
            body = `Dear ${data.InstructorFirstName || 'Instructor'},

This is a reminder that the course "${data.CourseName}" for group "${data.GroupName}" is ending on ${formatDate(data.EndDate)}.

Please ensure that all grades and attendance records are submitted by the end of the last day of the course.

Thank you for your cooperation.

Best regards,
Water Academy Administration`;
        } else {
            subject = `Course Starting Soon: ${data.CourseName} - ${data.GroupName}`;
            body = `Dear ${data.InstructorFirstName || 'Instructor'},

This is a reminder that the course "${data.CourseName}" for group "${data.GroupName}" is starting on ${formatDate(data.StartDate)}.

Please ensure that you are prepared for the course and have all necessary materials ready.

Thank you for your cooperation.

Best regards,
Water Academy Administration`;
        }
        
        // Create mailto link
        const mailtoLink = `mailto:${encodeURIComponent(data.InstructorEmail)}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
        
        // Open email client
        window.location.href = mailtoLink;
    }
    
    /**
     * Formats a date string for display
     * @param {string} dateString - The date string to format
     * @returns {string} The formatted date
     */
    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return dateString; // Return original if invalid
        
        return date.toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }
    
    /**
     * Formats a date for input[type="date"]
     * @param {Date} date - The date to format
     * @returns {string} The formatted date (YYYY-MM-DD)
     */
    function formatDateForInput(date) {
        return date.toISOString().split('T')[0];
    }
    
    /**
     * Escapes HTML special characters
     * @param {string} text - The text to escape
     * @returns {string} The escaped text
     */
    function escapeHtml(text) {
        if (!text) return '';
        
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});

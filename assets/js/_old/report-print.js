/**
 * Report Utility System
 * 
 * A comprehensive utility for generating, printing, and emailing
 * PDF reports across all report pages in the Water Academy system.
 */

/**
 * Initialize all report functionality when the DOM is loaded
 */
document.addEventListener('DOMContentLoaded', function() {
    initReportActions();
});

/**
 * Initialize report action buttons and functionality
 */
function initReportActions() {
    // Initialize the print button
    const printBtn = document.getElementById('printReportBtn');
    if (printBtn) {
        printBtn.addEventListener('click', generatePDF);
    }
    
    // Initialize the email button
    const emailBtn = document.getElementById('emailReportBtn');
    if (emailBtn) {
        emailBtn.addEventListener('click', function() {
            // Show email modal
            const emailModal = document.getElementById('emailReportModal');
            if (emailModal) {
                const modalInstance = new bootstrap.Modal(emailModal);
                modalInstance.show();
            } else {
                createEmailModal();
            }
        });
    }
    
    // If neither button exists, add them to the page
    if (!printBtn && !emailBtn) {
        addReportActionButtons();
    }
}

/**
 * Add report action buttons to the page if they don't exist
 */
function addReportActionButtons() {
    // Find the card header to append the buttons to
    const cardHeaders = document.querySelectorAll('.card-header');
    if (cardHeaders.length === 0) return;
    
    // Use the first card header
    const container = cardHeaders[0];
    
    // Create action buttons container
    const actionDiv = document.createElement('div');
    actionDiv.className = 'report-actions';
    
    // Add print button
    const printBtn = document.createElement('button');
    printBtn.id = 'printReportBtn';
    printBtn.type = 'button';
    printBtn.className = 'btn btn-outline-primary btn-sm';
    printBtn.title = 'Generate PDF Report';
    printBtn.innerHTML = '<i class="bi bi-file-pdf"></i> Export PDF';
    actionDiv.appendChild(printBtn);
    
    // Add email button
    const emailBtn = document.createElement('button');
    emailBtn.id = 'emailReportBtn';
    emailBtn.type = 'button';
    emailBtn.className = 'btn btn-outline-secondary btn-sm';
    emailBtn.title = 'Email Report';
    emailBtn.innerHTML = '<i class="bi bi-envelope"></i> Email Report';
    actionDiv.appendChild(emailBtn);
    
    // Append action buttons to container
    container.appendChild(actionDiv);
    
    // Initialize the buttons
    initReportActions();
}

/**
 * Create the email modal if it doesn't exist
 */
function createEmailModal() {
    // Check if the modal already exists
    if (document.getElementById('emailReportModal')) return;
    
    // Create modal HTML
    const modalHTML = `
        <div class="modal fade" id="emailReportModal" tabindex="-1" aria-labelledby="emailReportModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="emailReportModalLabel">Email Report</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="emailReportForm">
                            <div class="form-group mb-3">
                                <label for="recipientEmail">Recipient Email</label>
                                <input type="email" class="form-control" id="recipientEmail" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="emailSubject">Subject</label>
                                <input type="text" class="form-control" id="emailSubject" 
                                       value="Water Academy Report" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="emailMessage">Message</label>
                                <textarea class="form-control" id="emailMessage" rows="4">Please find the attached report from Water Academy.</textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="sendReportEmailBtn">Send</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Append modal to body
    const modalContainer = document.createElement('div');
    modalContainer.innerHTML = modalHTML;
    document.body.appendChild(modalContainer);
    
    // Initialize modal
    const modalElement = document.getElementById('emailReportModal');
    const modal = new bootstrap.Modal(modalElement);
    
    // Add event listener to send button
    const sendBtn = document.getElementById('sendReportEmailBtn');
    if (sendBtn) {
        sendBtn.addEventListener('click', function() {
            const recipientEmail = document.getElementById('recipientEmail').value;
            const emailSubject = document.getElementById('emailSubject').value;
            const emailMessage = document.getElementById('emailMessage').value;
            
            if (recipientEmail && emailSubject) {
                sendReportByEmail(recipientEmail, emailSubject, emailMessage);
                modal.hide();
            } else {
                alert('Please fill all required fields');
            }
        });
    }
    
    // Show the modal
    modal.show();
}

/**
 * Generate PDF from the current report page
 */
function generatePDF(download = true) {
    // Get report title from page
    const pageTitle = document.title || 'Water Academy Report';
    let reportTitle = pageTitle;
    
    // Try to get a more specific report title from the page
    const headingElement = document.querySelector('h1, h2, h3, h4, h5');
    if (headingElement) {
        reportTitle = headingElement.textContent.trim();
    }
    
    // Get the content to print - we'll use the entire container-fluid or main content area
    let contentElement = document.querySelector('.container-fluid');
    if (!contentElement) {
        contentElement = document.querySelector('.main-content');
    }
    
    if (!contentElement) {
        console.error('No content found to generate PDF');
        return null;
    }
    
    // Clone the content to avoid modifying the original
    const content = contentElement.cloneNode(true);
    
    // Remove the report header with buttons from the PDF content
    const reportHeader = content.querySelector('.report-header');
    if (reportHeader) {
        // Extract trainee and course info before removing
        const traineeInfo = reportHeader.querySelector('.col-md-6:first-child');
        const reportInfo = document.createElement('div');
        reportInfo.className = 'report-info mb-4';
        if (traineeInfo) {
            reportInfo.innerHTML = traineeInfo.innerHTML;
            // Remove the buttons
            const buttons = reportInfo.querySelectorAll('button, .report-actions, .btn');
            buttons.forEach(btn => btn.remove());
        }
        
        // Replace the report header with just the info
        reportHeader.parentNode.replaceChild(reportInfo, reportHeader);
    }
    
    // Remove elements that shouldn't be in the PDF
    content.querySelectorAll('.no-print, button, .dropdown, .action-btn').forEach(el => {
        el.remove();
    });
    
    // Get the current date in dd/mm/yyyy format
    const now = new Date();
    const formattedDate = `${String(now.getDate()).padStart(2, '0')}/${String(now.getMonth() + 1).padStart(2, '0')}/${now.getFullYear()}`;
    
    // Create a wrapper for the content with proper styling
    const wrapper = document.createElement('div');
    wrapper.className = 'report-pdf-wrapper';
    
    // Add header
    wrapper.innerHTML = `
        <div class="report-header">
            <div class="report-logo-container">
                <img src="../assets/img/logos/waLogoBlue.png" alt="Water Academy Logo" class="report-logo">
            </div>
            <div class="report-header-text">
                <h1>Water Academy</h1>
                <h3>${reportTitle}</h3>
            </div>
        </div>
        <div class="blue-divider"></div>
    `;
    
    // Create content container
    const reportBody = document.createElement('div');
    reportBody.className = 'report-body';
    reportBody.appendChild(content);
    wrapper.appendChild(reportBody);
    
    // Add footer for each page (will be added by jsPDF)
    wrapper.innerHTML += `
        <div class="pdf-content" id="pdf-footer">
            <div class="blue-divider"></div>
            <div class="report-footer">
                <div>${formattedDate}</div>
                <div>Page {page} of {pages}</div>
            </div>
        </div>
    `;
    
    // Create a temporary container in the document
    const tempContainer = document.createElement('div');
    tempContainer.className = 'pdf-temp-container';
    tempContainer.style.position = 'absolute';
    tempContainer.style.left = '-9999px';
    tempContainer.appendChild(wrapper);
    document.body.appendChild(tempContainer);
    
    // Configure html2pdf options
    const options = {
        margin: [15, 15, 15, 15], // [top, right, bottom, left] in mm
        filename: 'water-academy-report.pdf',
        image: { 
            type: 'jpeg', 
            quality: 0.98 
        },
        html2canvas: { 
            scale: 2,
            useCORS: true,
            letterRendering: true
        },
        jsPDF: { 
            unit: 'mm', 
            format: 'a4', 
            orientation: 'portrait' 
        },
        pagebreak: { mode: ['avoid-all', 'css', 'legacy'] }
    };
    
    // Generate the PDF
    return html2pdf().from(wrapper).set(options).toPdf().get('pdf').then(function(pdf) {
        // Add footer to each page
        const totalPages = pdf.internal.getNumberOfPages();
        
        for (let i = 1; i <= totalPages; i++) {
            pdf.setPage(i);
            
            // Add footer with page numbers
            const footerStr = `Page ${i} of ${totalPages}`;
            const footerX = pdf.internal.pageSize.getWidth() / 2;
            const footerY = pdf.internal.pageSize.getHeight() - 10;
            
            pdf.setFontSize(10);
            pdf.setTextColor(100, 100, 100);
            
            // Add blue line
            pdf.setDrawColor(0, 86, 179);
            pdf.setLineWidth(0.5);
            pdf.line(15, footerY - 5, pdf.internal.pageSize.getWidth() - 15, footerY - 5);
            
            // Add date on left
            pdf.text(formattedDate, 15, footerY);
            
            // Add page numbers on right
            pdf.text(footerStr, pdf.internal.pageSize.getWidth() - 15, footerY, { align: 'right' });
        }
        
        // Clean up the temporary container
        document.body.removeChild(tempContainer);
        
        // If download is true, save the PDF, otherwise return it
        if (download) {
            pdf.save('water-academy-report.pdf');
        }
        
        return pdf;
    }).catch(error => {
        console.error('Error generating PDF:', error);
        alert('An error occurred while generating the PDF. Please try again.');
        
        // Clean up
        if (document.body.contains(tempContainer)) {
            document.body.removeChild(tempContainer);
        }
        
        return null;
    });
}

/**
 * Send the report as an email attachment
 * 
 * @param {string} recipientEmail - Email of the recipient
 * @param {string} subject - Email subject
 * @param {string} message - Email message body
 */
function sendReportByEmail(recipientEmail, subject, message) {
    // Show loading indicator
    const loadingToast = showToast('Preparing email...', 'info');
    
    // Generate the PDF without downloading it
    generatePDF(false).then(pdf => {
        if (!pdf) {
            hideToast(loadingToast);
            showToast('Failed to generate PDF for email', 'error');
            return;
        }
        
        // Convert the PDF to base64
        const pdfBase64 = btoa(pdf.output('datauristring'));
        
        // Send to server
        fetch('../dashboards/send_report_email.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                recipient: recipientEmail,
                subject: subject,
                message: message,
                pdfData: pdfBase64,
                filename: 'water-academy-report.pdf'
            })
        })
        .then(response => response.json())
        .then(data => {
            hideToast(loadingToast);
            
            if (data.success) {
                showToast('Report sent successfully!', 'success');
            } else {
                showToast('Failed to send email: ' + data.message, 'error');
            }
        })
        .catch(error => {
            hideToast(loadingToast);
            showToast('Error sending email', 'error');
            console.error('Error:', error);
        });
    });
}

/**
 * Show a toast notification
 * 
 * @param {string} message - Message to display
 * @param {string} type - Type of toast (success, error, info, warning)
 * @returns {Object} Toast instance
 */
function showToast(message, type = 'info') {
    // Create toast container if it doesn't exist
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }
    
    // Create toast element
    const toastId = 'toast-' + new Date().getTime();
    const toastHTML = `
        <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-${type === 'error' ? 'danger' : type}">
                <strong class="me-auto text-white">${type.charAt(0).toUpperCase() + type.slice(1)}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
    
    // Add toast to container
    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    
    // Initialize and show toast
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { autohide: true, delay: 5000 });
    toast.show();
    
    return { id: toastId, instance: toast };
}

/**
 * Hide a toast notification
 * 
 * @param {Object} toast - Toast object returned by showToast
 */
function hideToast(toast) {
    if (toast && toast.instance) {
        toast.instance.hide();
    }
}

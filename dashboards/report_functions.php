<?php
/**
 * Report Functions
 * 
 * This file contains utility functions for report generation and email functionality.
 */

/**
 * Set up a report page with necessary CSS and JavaScript files
 * 
 * @param string $reportTitle The title of the report
 * @param bool $includeCharts Whether to include Chart.js library
 * @return string The sanitized report title
 */
function setupReportPage($reportTitle = '', $includeCharts = false) {
    // Add required CSS
    echo '<link rel="stylesheet" href="../assets/css/report-print.css">';
    
    // Add required JavaScript
    echo '<script src="../assets/js/report-print.js"></script>';
    
    // Add chart.js if charts are needed
    if ($includeCharts) {
        echo '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
    }
    
    // Return the report title for use in the page
    return htmlspecialchars($reportTitle);
}

/**
 * Send an email using a template
 * 
 * @param string $templateCode The code of the template to use
 * @param string $recipientEmail The email address of the recipient
 * @param array $templateVars Associative array of template variables
 * @param array $attachments Optional array of attachments
 * @return bool Whether the email was sent successfully
 */
function sendTemplateEmail($templateCode, $recipientEmail, $templateVars = [], $attachments = []) {
    global $conn;
    
    // Get the template from the database
    $stmt = $conn->prepare("SELECT * FROM EmailTemplates WHERE TemplateCode = ?");
    $stmt->bind_param("s", $templateCode);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        error_log("Email template not found: $templateCode");
        return false;
    }
    
    $template = $result->fetch_assoc();
    
    // Replace template variables
    $subject = $template['Subject'];
    $htmlContent = $template['HtmlContent'];
    $textContent = $template['TextContent'];
    
    // Add system variables
    $templateVars['logo_url'] = 'https://' . $_SERVER['SERVER_NAME'] . '/assets/img/logos/waLogoBlue.png';
    $templateVars['current_year'] = date('Y');
    
    // Replace all variables
    foreach ($templateVars as $key => $value) {
        $subject = str_replace('{{' . $key . '}}', $value, $subject);
        $htmlContent = str_replace('{{' . $key . '}}', $value, $htmlContent);
        $textContent = str_replace('{{' . $key . '}}', $value, $textContent);
    }
    
    // Set up email headers
    $boundary = md5(time());
    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
        'From: Water Academy <no-reply@wateracademy.example.com>',
        'X-Mailer: PHP/' . phpversion()
    ];
    
    // Create email body
    $body = "--$boundary\r\n" .
            "Content-Type: text/plain; charset=utf-8\r\n" .
            "Content-Transfer-Encoding: 7bit\r\n\r\n" .
            $textContent . "\r\n\r\n";
            
    $body .= "--$boundary\r\n" .
             "Content-Type: text/html; charset=utf-8\r\n" .
             "Content-Transfer-Encoding: 7bit\r\n\r\n" .
             $htmlContent . "\r\n\r\n";
    
    // Add attachments if any
    if (!empty($attachments)) {
        foreach ($attachments as $attachment) {
            if (!isset($attachment['content']) || !isset($attachment['name'])) {
                continue;
            }
            
            $attachmentBoundary = md5(time() . rand(1000, 9999));
            $headers = [
                'MIME-Version: 1.0',
                'Content-Type: multipart/mixed; boundary="' . $attachmentBoundary . '"',
                'From: Water Academy <no-reply@wateracademy.example.com>',
                'X-Mailer: PHP/' . phpversion()
            ];
            
            $attachmentBody = "--$attachmentBoundary\r\n" .
                             "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n\r\n" .
                             $body . "\r\n";
                             
            $attachmentBody .= "--$attachmentBoundary\r\n" .
                              "Content-Type: application/octet-stream; name=\"" . $attachment['name'] . "\"\r\n" .
                              "Content-Transfer-Encoding: base64\r\n" .
                              "Content-Disposition: attachment; filename=\"" . $attachment['name'] . "\"\r\n\r\n" .
                              chunk_split(base64_encode($attachment['content'])) . "\r\n";
                              
            $attachmentBody .= "--$attachmentBoundary--";
            
            $body = $attachmentBody;
            $boundary = $attachmentBoundary;
        }
    } else {
        $body .= "--$boundary--";
    }
    
    // Send the email
    $mailSent = mail($recipientEmail, $subject, $body, implode("\r\n", $headers));
    
    // Log the email sending
    if ($mailSent) {
        $user_id = $_SESSION['UserID'] ?? 0;
        $activityType = 'Email';
        $entityType = 'Template';
        $entityID = $template['TemplateID'];
        $details = "Email template '$templateCode' sent to $recipientEmail";
        
        $sql_log = "INSERT INTO ActivityLog (UserID, ActivityType, EntityType, EntityID, Details, ActivityDate) VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt_log = $conn->prepare($sql_log);
        
        if ($stmt_log) {
            $stmt_log->bind_param("issss", $user_id, $activityType, $entityType, $entityID, $details);
            $stmt_log->execute();
            $stmt_log->close();
        } else {
            error_log("Failed to log email activity: " . $conn->error);
        }
    }
    
    return $mailSent;
}

/**
 * Send a report by email using the report_email template
 * 
 * @param string $recipientEmail The email address of the recipient
 * @param string $subject The email subject
 * @param string $message The email message
 * @param string $pdfContent The PDF content as a string
 * @param string $filename The filename for the attachment
 * @return bool Whether the email was sent successfully
 */
function sendReportEmail($recipientEmail, $subject, $message, $pdfContent, $filename = 'water-academy-report.pdf') {
    // Add timestamp to filename to avoid overwriting
    $filenameWithTimestamp = pathinfo($filename, PATHINFO_FILENAME) . '_' . 
                             date('Ymd_His') . '.' . 
                             pathinfo($filename, PATHINFO_EXTENSION);
    
    // Set up template variables
    $templateVars = [
        'message' => $message
    ];
    
    // Set up attachment
    $attachments = [
        [
            'name' => $filenameWithTimestamp,
            'content' => $pdfContent
        ]
    ];
    
    // Use the template email function with custom subject
    return sendTemplateEmail('report_email', $recipientEmail, $templateVars, $attachments);
}

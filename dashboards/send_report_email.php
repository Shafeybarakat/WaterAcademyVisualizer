<?php
/**
 * API Endpoint: Send Report Email
 * 
 * Receives PDF data and sends it as an email attachment using the email template system.
 */

// Include configuration and helper files
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once 'report_functions.php'; // Updated path to the new location

// Set content type to JSON
header('Content-Type: application/json');

// RBAC guard: Only users with 'send_reports_email' permission can access this page.
if (!hasPermission('send_reports_email')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied. You do not have permission to send reports.']);
    exit;
}

// Get the JSON data from the request
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

// Validate required fields
if (!isset($data['recipient']) || !isset($data['subject']) || !isset($data['pdfData'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Extract data
$recipient = filter_var($data['recipient'], FILTER_SANITIZE_EMAIL);
$subject = htmlspecialchars($data['subject'], ENT_QUOTES, 'UTF-8');
$message = htmlspecialchars($data['message'] ?? 'Please find the attached report from Water Academy.', ENT_QUOTES, 'UTF-8');
$pdfData = $data['pdfData'];
$filename = htmlspecialchars($data['filename'] ?? 'water-academy-report.pdf', ENT_QUOTES, 'UTF-8');

// Validate email
if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

// Decode base64 PDF data
$pdfBinary = base64_decode($pdfData);
if (!$pdfBinary) {
    echo json_encode(['success' => false, 'message' => 'Invalid PDF data']);
    exit;
}

// Send the email using the template system
$mailSent = sendReportEmail($recipient, $subject, $message, $pdfBinary, $filename);

// Return result
if ($mailSent) {
    echo json_encode(['success' => true, 'message' => 'Email sent successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send email']);
}

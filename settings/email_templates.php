<?php
/**
 * Email Templates Management Page
 * 
 * This page allows administrators to manage email templates used throughout the system.
 */

// Include necessary files
$pageTitle = "Email Templates";
// Include config.php and auth.php via header.php
require_once '../includes/header.php';

// Check authorization - only users with manage_email_templates permission can access
if (!require_permission('manage_email_templates', '../login.php')) {
    echo '<div class="container-xxl flex-grow-1 container-p-y"><div class="alert alert-danger" role="alert">' . ($_SESSION['access_denied_message'] ?? 'You do not have permission to access this page.') . '</div></div>';
    require_once '../includes/footer.php'; // Ensure footer is included
    die(); // Terminate script
}

// Handle form submission if applicable
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_template' && isset($_POST['template_id'])) {
        $templateId = intval($_POST['template_id']);
        $subject = trim($_POST['subject'] ?? '');
        $htmlContent = $_POST['html_content'] ?? '';
        $textContent = $_POST['text_content'] ?? '';
        
        // Update the template
        $stmt = $conn->prepare("UPDATE EmailTemplates SET Subject = ?, HtmlContent = ?, TextContent = ?, UpdatedAt = NOW() WHERE TemplateID = ?");
        $stmt->bind_param("sssi", $subject, $htmlContent, $textContent, $templateId);
        
        if ($stmt->execute()) {
            $successMessage = "Template updated successfully";
        } else {
            $errorMessage = "Failed to update template: " . $conn->error;
        }
    }
}

// Get all templates
$templates = [];
$result = $conn->query("SELECT * FROM EmailTemplates ORDER BY TemplateName");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $templates[] = $row;
    }
}
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Settings /</span> Email Templates
    </h4>
    
    <?php if (isset($successMessage)): ?>
    <div class="alert alert-success"><?= $successMessage ?></div>
    <?php endif; ?>
    
    <?php if (isset($errorMessage)): ?>
    <div class="alert alert-danger"><?= $errorMessage ?></div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <h5 class="card-header">Email Templates</h5>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                <?php foreach($templates as $index => $template): ?>
                                <a class="nav-link <?= $index === 0 ? 'active' : '' ?>" 
                                   id="v-pills-<?= $template['TemplateCode'] ?>-tab" 
                                   data-bs-toggle="pill" 
                                   href="#v-pills-<?= $template['TemplateCode'] ?>" 
                                   role="tab" 
                                   aria-controls="v-pills-<?= $template['TemplateCode'] ?>" 
                                   aria-selected="<?= $index === 0 ? 'true' : 'false' ?>">
                                    <?= htmlspecialchars($template['TemplateName']) ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-9">
                            <div class="tab-content" id="v-pills-tabContent">
                                <?php foreach($templates as $index => $template): ?>
                                <div class="tab-pane fade <?= $index === 0 ? 'show active' : '' ?>" 
                                     id="v-pills-<?= $template['TemplateCode'] ?>" 
                                     role="tabpanel" 
                                     aria-labelledby="v-pills-<?= $template['TemplateCode'] ?>-tab">
                                    
                                    <form method="post" action="">
                                        <input type="hidden" name="action" value="update_template">
                                        <input type="hidden" name="template_id" value="<?= $template['TemplateID'] ?>">
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Template Name</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($template['TemplateName']) ?>" readonly>
                                            <div class="form-text">Template Code: <?= htmlspecialchars($template['TemplateCode']) ?></div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Description</label>
                                            <textarea class="form-control" rows="2" readonly><?= htmlspecialchars($template['Description']) ?></textarea>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="subject-<?= $template['TemplateID'] ?>" class="form-label">Subject</label>
                                            <input type="text" class="form-control" id="subject-<?= $template['TemplateID'] ?>" name="subject" value="<?= htmlspecialchars($template['Subject']) ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="html-content-<?= $template['TemplateID'] ?>" class="form-label">HTML Content</label>
                                            <textarea class="form-control code-editor" id="html-content-<?= $template['TemplateID'] ?>" name="html_content" rows="12"><?= htmlspecialchars($template['HtmlContent']) ?></textarea>
                                            <div class="form-text">Available variables: {{user_name}}, {{logo_url}}, etc. (See template description)</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="text-content-<?= $template['TemplateID'] ?>" class="form-label">Plain Text Content</label>
                                            <textarea class="form-control" id="text-content-<?= $template['TemplateID'] ?>" name="text_content" rows="8"><?= htmlspecialchars($template['TextContent']) ?></textarea>
                                            <div class="form-text">Plain text version for email clients that don't support HTML</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Preview</label>
                                            <div class="border p-3 bg-light">
                                                <div class="mb-2">
                                                    <strong>Subject:</strong> <?= htmlspecialchars($template['Subject']) ?>
                                                </div>
                                                <iframe id="preview-<?= $template['TemplateID'] ?>" srcdoc="<?= htmlspecialchars($template['HtmlContent']) ?>" style="width: 100%; height: 400px; border: 1px solid #ddd;"></iframe>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-3">
                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                            <button type="button" class="btn btn-outline-secondary preview-btn" data-template-id="<?= $template['TemplateID'] ?>">Refresh Preview</button>
                                        </div>
                                    </form>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Preview button functionality
    document.querySelectorAll('.preview-btn').forEach(button => {
        button.addEventListener('click', function() {
            const templateId = this.getAttribute('data-template-id');
            const htmlContent = document.getElementById('html-content-' + templateId).value;
            const previewFrame = document.getElementById('preview-' + templateId);
            
            // Replace template variables with sample values
            let previewHtml = htmlContent
                .replace(/{{user_name}}/g, 'John Doe')
                .replace(/{{username}}/g, 'john.doe')
                .replace(/{{user_role}}/g, 'Instructor')
                .replace(/{{temp_password}}/g, 'Temp123!')
                .replace(/{{login_url}}/g, '#')
                .replace(/{{reset_url}}/g, '#')
                .replace(/{{expiry_time}}/g, '24 hours from now')
                .replace(/{{logo_url}}/g, '../assets/img/logos/waLogoBlue.png')
                .replace(/{{instructor_name}}/g, 'John Doe')
                .replace(/{{course_name}}/g, 'Water Chemistry 101')
                .replace(/{{group_name}}/g, 'Group A')
                .replace(/{{start_date}}/g, '01/06/2025')
                .replace(/{{end_date}}/g, '30/06/2025')
                .replace(/{{trainee_count}}/g, '25')
                .replace(/{{course_url}}/g, '#')
                .replace(/{{grades_url}}/g, '#')
                .replace(/{{message}}/g, 'This is a sample message for the report email.')
                .replace(/{{course_list}}/g, '<li>Water Chemistry 101 - Group A (Due: 30/06/2025)</li><li>Water Treatment - Group B (Due: 15/07/2025)</li>');
            
            previewFrame.srcdoc = previewHtml;
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>

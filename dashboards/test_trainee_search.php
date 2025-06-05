<?php
// test_trainee_search.php - Simple test page for AJAX trainee search
// Include the header - this also includes config.php and auth.php
include_once "../includes/header.php";

// Enforce permissions
enforceAnyPermission(['access_group_reports', 'access_trainee_reports', 'access_attendance_reports']);
// If execution continues, permissions are granted.
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Test /</span> Trainee Search
    </h4>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Trainee Search Test</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-12">
                    <h6>Search for a trainee:</h6>
                    <select id="traineeSearch" class="form-control" style="width: 100%;"></select>
                    <div id="searchDebug" class="mt-3">
                        <h6>Debug Info:</h6>
                        <pre id="debugOutput" style="background-color: #f8f9fa; padding: 10px; border-radius: 5px; max-height: 200px; overflow: auto;"></pre>
                    </div>
                </div>
            </div>
            
            <div id="resultsContainer" style="display: none;">
                <h6>Selected Trainee Info:</h6>
                <div id="selectedTraineeInfo" style="background-color: #f8f9fa; padding: 10px; border-radius: 5px;"></div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-12">
            <a href="reports.php" class="btn btn-primary">
                <i class="bx bx-arrow-back me-1"></i> Back to Reports
            </a>
        </div>
    </div>
</div>

<?php include_once "../includes/footer.php"; ?>

<!-- Add Select2 CSS and JS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    function log(message) {
        var now = new Date().toLocaleTimeString();
        $('#debugOutput').prepend('[' + now + '] ' + message + '\n');
    }
    
    log('Document ready, initializing Select2...');
    log('jQuery version: ' + $.fn.jquery);
    log('Select2 version: ' + ($.fn.select2 ? 'Loaded' : 'Not loaded'));
    
    try {
        // Initialize Select2
        $('#traineeSearch').select2({
            placeholder: 'Type to search for a trainee...',
            allowClear: true,
            minimumInputLength: 2,
            width: '100%',
            ajax: {
                url: 'ajax_search_trainees.php',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    log('Search term: ' + params.term);
                    return {
                        term: params.term
                    };
                },
                processResults: function(data) {
                    log('Received ' + (data.results ? data.results.length : 0) + ' results');
                    if (data.results && data.results.length > 0) {
                        log('First result: ' + JSON.stringify(data.results[0]));
                    } else {
                        log('No results found');
                    }
                    return data;
                },
                error: function(xhr, status, error) {
                    log('AJAX Error: ' + status + ' - ' + error);
                    log('Response: ' + xhr.responseText);
                },
                cache: true
            }
        }).on('select2:select', function(e) {
            log('Selected trainee: ' + e.params.data.text);
            
            // Show selected trainee info
            $('#selectedTraineeInfo').html('<strong>ID:</strong> ' + e.params.data.id + '<br><strong>Name:</strong> ' + e.params.data.text);
            $('#resultsContainer').show();
            
            // Fetch trainee's courses
            $.ajax({
                url: 'get_trainee_data.php',
                type: 'GET',
                data: {
                    trainee_id: e.params.data.id
                },
                dataType: 'json',
                success: function(response) {
                    log('Trainee data received: ' + JSON.stringify(response));
                    
                    if (response.success && response.courses.length > 0) {
                        var courseInfo = '<hr><strong>Courses:</strong><ul>';
                        response.courses.forEach(function(course) {
                            courseInfo += '<li>' + course.CourseName + ' (' + course.GroupName + ')</li>';
                        });
                        courseInfo += '</ul>';
                        $('#selectedTraineeInfo').append(courseInfo);
                    } else {
                        $('#selectedTraineeInfo').append('<hr><strong>No courses found for this trainee.</strong>');
                    }
                },
                error: function(xhr, status, error) {
                    log('Error fetching trainee data: ' + status + ' - ' + error);
                    log('Response: ' + xhr.responseText);
                }
            });
        });
        
        log('Select2 initialization complete');
    } catch(e) {
        log('Error initializing Select2: ' + e.message);
    }
});
</script>

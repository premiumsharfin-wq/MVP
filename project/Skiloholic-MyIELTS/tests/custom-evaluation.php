<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custom Evaluation - MyIELTS</title>
    <link rel="stylesheet" href="../assets/css/main.css?v=2.0">
</head>
<body>
    <?php
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../includes/session.php';
    require_once __DIR__ . '/../includes/upload-handler.php';
    require_once __DIR__ . '/../includes/email-functions.php';

    check_auth();
    $user = get_current_user();

    $errors = [];
    $success = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $taskType = sanitize($_POST['task_type'] ?? '');
        $task1Answer = sanitize($_POST['task1_answer'] ?? '');
        $task2Answer = sanitize($_POST['task2_answer'] ?? '');

        // Validation
        if (empty($taskType) || !in_array($taskType, ['task1', 'task2', 'both'])) {
            $errors[] = 'Please select a task type';
        }

        if (($taskType === 'task1' || $taskType === 'both') && empty($task1Answer)) {
            $errors[] = 'Task 1 answer is required';
        }

        if (($taskType === 'task2' || $taskType === 'both') && empty($task2Answer)) {
            $errors[] = 'Task 2 answer is required';
        }

        // Handle Task 1 image uploads (required for Task 1)
        $questionImages = [];
        if ($taskType === 'task1' || $taskType === 'both') {
            if (isset($_FILES['task1_images']) && !empty($_FILES['task1_images']['name'][0])) {
                $result = upload_multiple_submission_images($_FILES['task1_images']);

                if ($result['success']) {
                    $questionImages = $result['urls'];
                } else {
                    $errors = array_merge($errors, $result['errors']);
                }
            } else {
                $errors[] = 'Please upload at least one screenshot of the Task 1 question';
            }
        }

        if (empty($errors)) {
            // Create submission
            $submissionId = db_insert(
                "INSERT INTO submissions (user_id, test_id, task1_answer, task1_question_images, task2_answer, is_custom_test, status, submitted_at)
                 VALUES (?, NULL, ?, ?, ?, TRUE, 'pending', NOW())",
                [
                    $_SESSION['user_id'],
                    $taskType !== 'task2' ? $task1Answer : null,
                    !empty($questionImages) ? json_encode($questionImages) : null,
                    $taskType !== 'task1' ? $task2Answer : null
                ]
            );

            // Send notification to admins
            send_admin_submission_notification($submissionId);

            redirect(BASE_URL . 'tests/evaluation-status.php?id=' . $submissionId);
        }
    }
    ?>

    <!-- Navigation -->
    <?php require_once __DIR__ . '/../includes/header.php'; ?>

    <div class="container" style="margin-top: var(--spacing-lg); margin-bottom: var(--spacing-xl);">
        <div class="card" style="max-width: 800px; margin: 0 auto;">
            <div class="card-header">
                <h1 class="card-title">Custom Answer Evaluation 🎯</h1>
                <p class="card-subtitle">Submit your answer from any platform for expert evaluation</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Which task(s) are you submitting?</label>
                    <div style="display: flex; flex-direction: column; gap: var(--spacing-xs);">
                        <div class="checkbox-group">
                            <input type="radio" id="task_type_task1" name="task_type" value="task1" required>
                            <label for="task_type_task1" style="font-weight: normal;">Task 1 Only</label>
                        </div>
                        <div class="checkbox-group">
                            <input type="radio" id="task_type_task2" name="task_type" value="task2" required>
                            <label for="task_type_task2" style="font-weight: normal;">Task 2 Only</label>
                        </div>
                        <div class="checkbox-group">
                            <input type="radio" id="task_type_both" name="task_type" value="both" required>
                            <label for="task_type_both" style="font-weight: normal;">Both Tasks</label>
                        </div>
                    </div>
                </div>

                <!-- Task 1 Section -->
                <div id="task1_section" style="display: none;">
                    <h3 style="color: var(--primary); margin-top: var(--spacing-lg); margin-bottom: var(--spacing-md);">Task 1</h3>

                    <div class="form-group">
                        <label for="task1_images">Upload Question Screenshots *</label>
                        <input type="file" id="task1_images" name="task1_images[]" accept="image/*" multiple>
                        <small>Upload one or more screenshots of the Task 1 question. Accepted formats: JPG, PNG (Max 5MB each)</small>
                    </div>

                    <div class="form-group">
                        <label for="task1_answer">Your Task 1 Answer *</label>
                        <textarea id="task1_answer" name="task1_answer" rows="12" placeholder="Paste or type your complete Task 1 answer here..."></textarea>
                        <small>Recommended: At least 150 words</small>
                    </div>
                </div>

                <!-- Task 2 Section -->
                <div id="task2_section" style="display: none;">
                    <h3 style="color: var(--secondary); margin-top: var(--spacing-lg); margin-bottom: var(--spacing-md);">Task 2</h3>

                    <div class="form-group">
                        <label for="task2_answer">Your Task 2 Answer *</label>
                        <textarea id="task2_answer" name="task2_answer" rows="15" placeholder="Paste or type your complete Task 2 answer here..."></textarea>
                        <small>Recommended: At least 250 words</small>
                    </div>
                </div>

                <div class="alert alert-info" style="margin-top: var(--spacing-lg);">
                    <p><strong>What happens next?</strong></p>
                    <ul style="margin: 10px 0 0 20px;">
                        <li>Your submission will be reviewed by our expert examiners</li>
                        <li>You'll receive an email once an examiner is assigned</li>
                        <li>Detailed feedback typically arrives within 24-48 hours</li>
                        <li>You'll be notified via email when your results are ready</li>
                    </ul>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top: var(--spacing-md);">Submit for Evaluation</button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <?php require_once __DIR__ . '/../includes/footer.php'; ?>

    <script src="../assets/js/main.js"></script>
    <script>
        // Show/hide sections based on task type selection
        document.querySelectorAll('input[name="task_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const task1Section = document.getElementById('task1_section');
                const task2Section = document.getElementById('task2_section');

                if (this.value === 'task1') {
                    task1Section.style.display = 'block';
                    task2Section.style.display = 'none';
                } else if (this.value === 'task2') {
                    task1Section.style.display = 'none';
                    task2Section.style.display = 'block';
                } else if (this.value === 'both') {
                    task1Section.style.display = 'block';
                    task2Section.style.display = 'block';
                }
            });
        });
    </script>
</body>
</html>

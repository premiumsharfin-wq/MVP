<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluate Submission - MyIELTS Admin</title>
    <link rel="stylesheet" href="../../assets/css/main.css?v=2.0">
</head>
<body>
    <?php
    require_once __DIR__ . '/../../config/config.php';
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../includes/session.php';
    require_once __DIR__ . '/../../includes/email-functions.php';

    require_admin();
    $user = get_current_user();

    $submissionId = intval($_GET['id'] ?? 0);

    if (!$submissionId) {
        redirect(BASE_URL . 'admin/submissions/queue.php');
    }

    // Get submission details
    $submission = db_fetch(
        "SELECT s.*, u.full_name as student_name, u.email as student_email, t.title as test_title,
                e.id as evaluation_id, e.examiner_id
         FROM submissions s
         JOIN users u ON s.user_id = u.id
         LEFT JOIN writing_tests t ON s.test_id = t.id
         LEFT JOIN evaluations e ON s.id = e.submission_id
         WHERE s.id = ?",
        [$submissionId]
    );

    if (!$submission) {
        redirect(BASE_URL . 'admin/submissions/queue.php');
    }

    // Check if current admin is the assigned examiner
    if ($submission['examiner_id'] && $submission['examiner_id'] != $_SESSION['user_id']) {
        redirect(BASE_URL . 'admin/submissions/queue.php');
    }

    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $task1Score = floatval($_POST['task1_score'] ?? 0);
        $task1Feedback = sanitize($_POST['task1_feedback'] ?? '');
        $task2Score = floatval($_POST['task2_score'] ?? 0);
        $task2Feedback = sanitize($_POST['task2_feedback'] ?? '');
        $overallScore = floatval($_POST['overall_score'] ?? 0);
        $overallFeedback = sanitize($_POST['overall_feedback'] ?? '');

        // Validation
        if ($submission['task1_answer'] && ($task1Score < 0 || $task1Score > 9)) {
            $errors[] = 'Task 1 score must be between 0 and 9';
        }

        if ($submission['task2_answer'] && ($task2Score < 0 || $task2Score > 9)) {
            $errors[] = 'Task 2 score must be between 0 and 9';
        }

        if ($overallScore < 0 || $overallScore > 9) {
            $errors[] = 'Overall score must be between 0 and 9';
        }

        if (empty($overallFeedback)) {
            $errors[] = 'Overall feedback is required';
        }

        if (empty($errors)) {
            // Update evaluation
            db_query(
                "UPDATE evaluations
                 SET task1_score = ?, task1_feedback = ?, task2_score = ?, task2_feedback = ?,
                     overall_score = ?, overall_feedback = ?, completed_at = NOW()
                 WHERE id = ?",
                [
                    $submission['task1_answer'] ? $task1Score : null,
                    $submission['task1_answer'] ? $task1Feedback : null,
                    $submission['task2_answer'] ? $task2Score : null,
                    $submission['task2_answer'] ? $task2Feedback : null,
                    $overallScore,
                    $overallFeedback,
                    $submission['evaluation_id']
                ]
            );

            // Update submission status
            db_query("UPDATE submissions SET status = 'completed' WHERE id = ?", [$submissionId]);

            // Send notification to student
            send_results_ready_notification($submissionId);

            redirect(BASE_URL . 'admin/submissions/queue.php?evaluated=1');
        }
    }

    // Decode Task 1 question images if custom test
    $questionImages = [];
    if ($submission['task1_question_images']) {
        $questionImages = json_decode($submission['task1_question_images'], true) ?? [];
    }
    ?>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="<?php echo BASE_URL; ?>" class="navbar-brand">
                <img src="<?php echo LOGO_URL . 'No%20Background%20Skiloholic.png'; ?>" alt="MyIELTS Logo" class="navbar-logo">
                <span>MyIELTS Admin</span>
            </a>

            <ul class="navbar-menu">
                <li><a href="../index.php">Dashboard</a></li>
                <li><a href="../tests/manage.php">Manage Tests</a></li>
                <li><a href="queue.php">Submission Queue</a></li>
                <li><a href="../../dashboard.php">User View</a></li>
                <li><a href="../../auth/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container" style="margin-top: var(--spacing-lg); margin-bottom: var(--spacing-xl);">
        <div style="max-width: 1000px; margin: 0 auto;">
            <!-- Header -->
            <div class="card" style="background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%); color: white; margin-bottom: var(--spacing-lg);">
                <h1 style="color: white; margin-bottom: var(--spacing-sm);">Evaluate Submission 📝</h1>
                <p style="color: rgba(255,255,255,0.9);">Student: <strong><?php echo htmlspecialchars($submission['student_name']); ?></strong></p>
                <p style="color: rgba(255,255,255,0.9); font-size: 0.875rem;">
                    Test: <?php echo $submission['is_custom_test'] ? 'Custom Test' : htmlspecialchars($submission['test_title']); ?>
                </p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <!-- Task 1 Evaluation -->
                <?php if ($submission['task1_answer']): ?>
                    <div class="card" style="margin-bottom: var(--spacing-lg);">
                        <h2 style="color: var(--primary); margin-bottom: var(--spacing-md);">Task 1 Evaluation</h2>

                        <!-- Show question images if custom test -->
                        <?php if (!empty($questionImages)): ?>
                            <div style="margin-bottom: var(--spacing-md);">
                                <h4 style="margin-bottom: var(--spacing-sm);">Question Images:</h4>
                                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: var(--spacing-sm);">
                                    <?php foreach ($questionImages as $imageUrl): ?>
                                        <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="Task 1 Question"
                                             style="width: 100%; max-width: 500px; border: 2px solid var(--border); border-radius: var(--radius-md);">
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div style="background-color: var(--bg-tertiary); padding: var(--spacing-md); border-radius: var(--radius-md); margin-bottom: var(--spacing-md);">
                            <h4 style="margin-bottom: var(--spacing-sm);">Student's Answer:</h4>
                            <p style="white-space: pre-wrap; font-family: 'Courier New', monospace; font-size: 0.875rem;"><?php echo htmlspecialchars($submission['task1_answer']); ?></p>
                            <p style="margin-top: var(--spacing-sm); color: var(--text-secondary); font-size: 0.875rem;">
                                Word count: <?php echo str_word_count($submission['task1_answer']); ?> words
                            </p>
                        </div>

                        <div class="form-group">
                            <label for="task1_score">Task 1 Score (0.0 - 9.0) *</label>
                            <input type="number" id="task1_score" name="task1_score" step="0.5" min="0" max="9" required>
                        </div>

                        <div class="form-group">
                            <label for="task1_feedback">Task 1 Detailed Feedback</label>
                            <textarea id="task1_feedback" name="task1_feedback" rows="8"
                                      placeholder="Provide detailed feedback on:
- Task Achievement
- Coherence and Cohesion
- Lexical Resource
- Grammatical Range and Accuracy

Be specific and provide examples from the student's answer."></textarea>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Task 2 Evaluation -->
                <?php if ($submission['task2_answer']): ?>
                    <div class="card" style="margin-bottom: var(--spacing-lg);">
                        <h2 style="color: var(--secondary); margin-bottom: var(--spacing-md);">Task 2 Evaluation</h2>

                        <div style="background-color: var(--bg-tertiary); padding: var(--spacing-md); border-radius: var(--radius-md); margin-bottom: var(--spacing-md);">
                            <h4 style="margin-bottom: var(--spacing-sm);">Student's Answer:</h4>
                            <p style="white-space: pre-wrap; font-family: 'Courier New', monospace; font-size: 0.875rem;"><?php echo htmlspecialchars($submission['task2_answer']); ?></p>
                            <p style="margin-top: var(--spacing-sm); color: var(--text-secondary); font-size: 0.875rem;">
                                Word count: <?php echo str_word_count($submission['task2_answer']); ?> words
                            </p>
                        </div>

                        <div class="form-group">
                            <label for="task2_score">Task 2 Score (0.0 - 9.0) *</label>
                            <input type="number" id="task2_score" name="task2_score" step="0.5" min="0" max="9" required>
                        </div>

                        <div class="form-group">
                            <label for="task2_feedback">Task 2 Detailed Feedback</label>
                            <textarea id="task2_feedback" name="task2_feedback" rows="10"
                                      placeholder="Provide detailed feedback on:
- Task Response
- Coherence and Cohesion
- Lexical Resource
- Grammatical Range and Accuracy

Be specific and provide examples from the student's answer."></textarea>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Overall Evaluation -->
                <div class="card" style="margin-bottom: var(--spacing-lg); border: 2px solid var(--primary);">
                    <h2 style="color: var(--primary); margin-bottom: var(--spacing-md);">Overall Assessment</h2>

                    <div class="form-group">
                        <label for="overall_score">Overall Band Score (0.0 - 9.0) *</label>
                        <input type="number" id="overall_score" name="overall_score" step="0.5" min="0" max="9" required>
                        <small>This is the overall writing band score for the student</small>
                    </div>

                    <div class="form-group">
                        <label for="overall_feedback">Overall Feedback and Recommendations *</label>
                        <textarea id="overall_feedback" name="overall_feedback" rows="8"
                                  placeholder="Provide overall assessment:
- Strengths
- Areas for improvement
- Specific recommendations for practice
- Overall impression

Write encouraging, constructive feedback to help the student improve." required></textarea>
                    </div>
                </div>

                <div class="alert alert-info">
                    <p><strong>⚠️ Before Submitting:</strong></p>
                    <ul style="margin: 10px 0 0 20px;">
                        <li>Double-check all scores</li>
                        <li>Ensure feedback is constructive and helpful</li>
                        <li>Once submitted, the student will be notified immediately via email</li>
                        <li>You cannot edit the evaluation after submission</li>
                    </ul>
                </div>

                <div style="display: flex; gap: var(--spacing-sm);">
                    <button type="submit" class="btn btn-primary btn-lg" style="flex: 1;"
                            onclick="return confirm('Are you sure? The student will be immediately notified via email.');">
                        Submit Evaluation
                    </button>
                    <a href="queue.php" class="btn btn-secondary btn-lg" style="flex: 1;">Cancel &amp; Return to Queue</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <?php require_once __DIR__ . '/../../includes/footer.php'; ?>

    <script src="../../assets/js/main.js"></script>
</body>
</html>

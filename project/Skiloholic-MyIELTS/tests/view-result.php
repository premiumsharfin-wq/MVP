<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Results - MyIELTS</title>
    <link rel="stylesheet" href="../assets/css/main.css?v=2.0">
</head>
<body>
    <?php
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../includes/session.php';

    check_auth();
    $user = get_authenticated_user();

    // Get submission ID
    $submissionId = intval($_GET['id'] ?? 0);

    if (!$submissionId) {
        redirect(BASE_URL . 'profile/dashboard.php');
    }

    // Get submission and evaluation details
    $result = db_fetch(
        "SELECT s.*, t.title as test_title,
                e.id as evaluation_id, e.task1_score, e.task1_feedback, e.task2_score, e.task2_feedback,
                e.overall_score, e.overall_feedback, e.completed_at,
                u.full_name as examiner_name
         FROM submissions s
         LEFT JOIN writing_tests t ON s.test_id = t.id
         LEFT JOIN evaluations e ON s.id = e.submission_id
         LEFT JOIN users u ON e.examiner_id = u.id
         WHERE s.id = ? AND s.user_id = ?",
        [$submissionId, $_SESSION['user_id']]
    );

    if (!$result) {
        // Submission not found or doesn't belong to user
        redirect(BASE_URL . 'profile/dashboard.php');
    }

    // Check if status is completed but evaluation is missing (Edge case)
    if ($result['status'] === 'completed' && !$result['evaluation_id']) {
        $error = "This test is marked as completed, but the evaluation details are pending. Please contact support.";
    } elseif ($result['status'] !== 'completed') {
        // Not completed yet
        redirect(BASE_URL . 'tests/evaluation-status.php?id=' . $submissionId);
    }

    $testName = $result['is_custom_test'] ? 'Custom Test' : $result['test_title'];
    ?>

    <!-- Navigation -->
    <?php require_once __DIR__ . '/../includes/header.php'; ?>

    <div class="container" style="margin-top: var(--spacing-lg); margin-bottom: var(--spacing-xl);">
        <?php if (isset($error)): ?>
            <div class="alert alert-error" style="max-width: 900px; margin: 0 auto 20px auto;">
                <h3 style="margin-bottom: 10px;">⚠️ Unable to View Results</h3>
                <p><?php echo htmlspecialchars($error); ?></p>
                <div style="margin-top: 15px;">
                    <a href="../profile/dashboard.php" class="btn btn-primary">Return to Dashboard</a>
                </div>
            </div>
        <?php else: ?>
        <div style="max-width: 900px; margin: 0 auto;">
            <!-- Results Header -->
            <div class="card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; text-align: center; margin-bottom: var(--spacing-xl);">
                <h1 style="color: white; font-size: 3rem; margin-bottom: var(--spacing-sm);">🎉</h1>
                <h1 style="color: white; margin-bottom: var(--spacing-sm);">Your Results Are Ready!</h1>
                <p style="color: rgba(255,255,255,0.9); font-size: 1.125rem;"><?php echo htmlspecialchars($testName); ?></p>
            </div>

            <!-- Overall Score -->
            <div class="card" style="text-align: center; margin-bottom: var(--spacing-lg); border: 3px solid var(--primary);">
                <h2 style="color: var(--text-secondary); font-weight: 600; text-transform: uppercase; font-size: 1rem; margin-bottom: var(--spacing-sm);">Overall Band Score</h2>
                <h1 style="color: var(--primary); font-size: 5rem; font-weight: 700; margin: var(--spacing-md) 0;"><?php echo number_format($result['overall_score'], 1); ?></h1>
                <p style="color: var(--text-secondary);">Evaluated by <strong><?php echo htmlspecialchars($result['examiner_name']); ?></strong></p>
                <p style="color: var(--text-muted); font-size: 0.875rem;"><?php echo format_datetime($result['completed_at']); ?></p>
            </div>

            <!-- Individual Task Scores -->
            <div class="grid grid-2" style="margin-bottom: var(--spacing-lg);">
                <?php if ($result['task1_score']): ?>
                    <div class="card" style="text-align: center;">
                        <h3 style="color: var(--primary); margin-bottom: var(--spacing-sm);">Task 1 Score</h3>
                        <h2 style="color: var(--text-primary); font-size: 3rem; font-weight: 700;"><?php echo number_format($result['task1_score'], 1); ?></h2>
                    </div>
                <?php endif; ?>

                <?php if ($result['task2_score']): ?>
                    <div class="card" style="text-align: center;">
                        <h3 style="color: var(--secondary); margin-bottom: var(--spacing-sm);">Task 2 Score</h3>
                        <h2 style="color: var(--text-primary); font-size: 3rem; font-weight: 700;"><?php echo number_format($result['task2_score'], 1); ?></h2>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Task 1 Feedback -->
            <?php if ($result['task1_answer']): ?>
                <div class="card" style="margin-bottom: var(--spacing-lg);">
                    <h2 style="color: var(--primary); margin-bottom: var(--spacing-md);">📝 Task 1 Feedback</h2>

                    <div style="background-color: var(--bg-tertiary); padding: var(--spacing-md); border-radius: var(--radius-md); margin-bottom: var(--spacing-md);">
                        <h4 style="margin-bottom: var(--spacing-sm);">Your Answer:</h4>
                        <p style="white-space: pre-wrap; font-size: 0.875rem; color: var(--text-secondary);"><?php echo htmlspecialchars($result['task1_answer']); ?></p>
                    </div>

                    <?php if ($result['task1_feedback']): ?>
                        <div style="border-left: 4px solid var(--primary); padding-left: var(--spacing-md);">
                            <h4 style="margin-bottom: var(--spacing-sm);">Examiner Feedback:</h4>
                            <div style="white-space: pre-wrap; line-height: 1.8;"><?php echo htmlspecialchars($result['task1_feedback']); ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Task 2 Feedback -->
            <?php if ($result['task2_answer']): ?>
                <div class="card" style="margin-bottom: var(--spacing-lg);">
                    <h2 style="color: var(--secondary); margin-bottom: var(--spacing-md);">📝 Task 2 Feedback</h2>

                    <div style="background-color: var(--bg-tertiary); padding: var(--spacing-md); border-radius: var(--radius-md); margin-bottom: var(--spacing-md);">
                        <h4 style="margin-bottom: var(--spacing-sm);">Your Answer:</h4>
                        <p style="white-space: pre-wrap; font-size: 0.875rem; color: var(--text-secondary);"><?php echo htmlspecialchars($result['task2_answer']); ?></p>
                    </div>

                    <?php if ($result['task2_feedback']): ?>
                        <div style="border-left: 4px solid var(--secondary); padding-left: var(--spacing-md);">
                            <h4 style="margin-bottom: var(--spacing-sm);">Examiner Feedback:</h4>
                            <div style="white-space: pre-wrap; line-height: 1.8;"><?php echo htmlspecialchars($result['task2_feedback']); ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Overall Feedback -->
            <?php if ($result['overall_feedback']): ?>
                <div class="card" style="background-color: #f0f9ff; border-left: 4px solid var(--info); margin-bottom: var(--spacing-lg);">
                    <h2 style="color: var(--info); margin-bottom: var(--spacing-md);">💡 Overall Assessment</h2>
                    <div style="white-space: pre-wrap; line-height: 1.8;"><?php echo htmlspecialchars($result['overall_feedback']); ?></div>
                </div>
            <?php endif; ?>

            <!-- Actions -->
            <div class="grid grid-2" style="gap: var(--spacing-sm);">
                <a href="../profile/dashboard.php" class="btn btn-secondary btn-lg">Back to Dashboard</a>
                <a href="writing-tests.php" class="btn btn-primary btn-lg">Take Another Test</a>
            </div>

            <!-- Print Button -->
            <div style="text-align: center; margin-top: var(--spacing-md);">
                <button onclick="window.print()" class="btn btn-secondary">🖨️ Print Results</button>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php require_once __DIR__ . '/../includes/footer.php'; ?>

    <script src="../assets/js/main.js"></script>

    <style media="print">
        .navbar, .footer, button { display: none !important; }
        body { background: white !important; }
        .card { box-shadow: none !important; border: 1px solid #ccc !important; }
    </style>
</body>
</html>

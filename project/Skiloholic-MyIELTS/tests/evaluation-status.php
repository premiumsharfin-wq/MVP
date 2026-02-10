<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluation Status - MyIELTS</title>
    <link rel="stylesheet" href="../assets/css/main.css?v=2.0">
    <meta http-equiv="refresh" content="30"> <!-- Auto-refresh every 30 seconds -->
</head>
<body>
    <?php
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../includes/session.php';

    check_auth();
    $user = get_current_user();

    // Get submission ID
    $submissionId = intval($_GET['id'] ?? 0);

    if (!$submissionId) {
        redirect(BASE_URL . 'profile/dashboard.php');
    }

    // Get submission details
    $submission = db_fetch(
        "SELECT s.*, t.title as test_title, e.examiner_id, e.estimated_completion_time, e.completed_at,
                u.full_name as examiner_name
         FROM submissions s
         LEFT JOIN writing_tests t ON s.test_id = t.id
         LEFT JOIN evaluations e ON s.id = e.submission_id
         LEFT JOIN users u ON e.examiner_id = u.id
         WHERE s.id = ? AND s.user_id = ?",
        [$submissionId, $_SESSION['user_id']]
    );

    if (!$submission) {
        redirect(BASE_URL . 'profile/dashboard.php');
    }

    // Redirect to results if completed
    if ($submission['status'] === 'completed') {
        redirect(BASE_URL . 'tests/view-result.php?id=' . $submissionId);
    }

    $testName = $submission['is_custom_test'] ? 'Custom Test' : $submission['test_title'];

    $statusInfo = [
        'pending' => [
            'icon' => '⏳',
            'title' => 'Submission Received',
            'description' => 'Your test has been submitted and is waiting to be assigned to an examiner.',
            'color' => 'warning'
        ],
        'assigned' => [
            'icon' => '👨‍🏫',
            'title' => 'Examiner Assigned',
            'description' => 'An examiner has been assigned to evaluate your test.',
            'color' => 'info'
        ],
        'in_evaluation' => [
            'icon' => '📝',
            'title' => 'Under Evaluation',
            'description' => 'Your examiner is currently reviewing your answers and preparing detailed feedback.',
            'color' => 'info'
        ]
    ];

    $currentStatus = $statusInfo[$submission['status']] ?? $statusInfo['pending'];
    ?>

    <!-- Navigation -->
    <?php require_once __DIR__ . '/../includes/header.php'; ?>

    <div class="container" style="margin-top: var(--spacing-lg); margin-bottom: var(--spacing-xl);">
        <div style="max-width: 700px; margin: 0 auto;">
            <!-- Success Message -->
            <div class="card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; text-align: center; margin-bottom: var(--spacing-xl);">
                <h1 style="color: white; font-size: 3rem; margin-bottom: var(--spacing-sm);">✅</h1>
                <h1 style="color: white; margin-bottom: var(--spacing-sm);">Test Submitted Successfully!</h1>
                <p style="color: rgba(255,255,255,0.9); font-size: 1.125rem;">Thank you for your submission</p>
            </div>

            <!-- Current Status -->
            <div class="card" style="text-align: center; margin-bottom: var(--spacing-lg);">
                <h1 style="font-size: 4rem; margin-bottom: var(--spacing-sm);"><?php echo $currentStatus['icon']; ?></h1>
                <h2 style="color: var(--primary); margin-bottom: var(--spacing-sm);"><?php echo $currentStatus['title']; ?></h2>
                <p style="font-size: 1.125rem; color: var(--text-secondary);"><?php echo $currentStatus['description']; ?></p>

                <div style="margin-top: var(--spacing-lg);">
                    <span class="badge badge-<?php echo $currentStatus['color']; ?>" style="font-size: 1rem; padding: 0.5rem 1.5rem;">
                        <?php echo ucwords(str_replace('_', ' ', $submission['status'])); ?>
                    </span>
                </div>
            </div>

            <!-- Submission Details -->
            <div class="card" style="margin-bottom: var(--spacing-lg);">
                <h3 style="margin-bottom: var(--spacing-md);">Submission Details</h3>

                <div style="display: grid; gap: var(--spacing-sm);">
                    <div style="display: flex; justify-content: space-between; padding: var(--spacing-sm) 0; border-bottom: 1px solid var(--border);">
                        <span style="color: var(--text-secondary); font-weight: 600;">Test:</span>
                        <span><?php echo htmlspecialchars($testName); ?></span>
                    </div>

                    <div style="display: flex; justify-content: space-between; padding: var(--spacing-sm) 0; border-bottom: 1px solid var(--border);">
                        <span style="color: var(--text-secondary); font-weight: 600;">Submitted:</span>
                        <span><?php echo format_datetime($submission['submitted_at']); ?></span>
                    </div>

                    <?php if ($submission['examiner_name']): ?>
                        <div style="display: flex; justify-content: space-between; padding: var(--spacing-sm) 0; border-bottom: 1px solid var(--border);">
                            <span style="color: var(--text-secondary); font-weight: 600;">Examiner:</span>
                            <span><?php echo htmlspecialchars($submission['examiner_name']); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($submission['estimated_completion_time']): ?>
                        <div style="display: flex; justify-content: space-between; padding: var(--spacing-sm) 0;">
                            <span style="color: var(--text-secondary); font-weight: 600;">Estimated Completion:</span>
                            <span><?php echo htmlspecialchars($submission['estimated_completion_time']); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Progress Steps -->
            <div class="card" style="margin-bottom: var(--spacing-lg);">
                <h3 style="margin-bottom: var(--spacing-md);">Evaluation Progress</h3>

                <div style="display: flex; flex-direction: column; gap: var(--spacing-md);">
                    <div style="display: flex; align-items: center; gap: var(--spacing-sm);">
                        <div style="width: 30px; height: 30px; border-radius: 50%; background-color: var(--success); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700;">✓</div>
                        <div>
                            <strong>Submitted</strong>
                            <p style="margin: 0; font-size: 0.875rem; color: var(--text-secondary);"><?php echo time_ago($submission['submitted_at']); ?></p>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: var(--spacing-sm);">
                        <?php if ($submission['status'] !== 'pending'): ?>
                            <div style="width: 30px; height: 30px; border-radius: 50%; background-color: var(--success); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700;">✓</div>
                        <?php else: ?>
                            <div style="width: 30px; height: 30px; border-radius: 50%; background-color: var(--border); color: var(--text-secondary); display: flex; align-items: center; justify-content: center;">2</div>
                        <?php endif; ?>
                        <div>
                            <strong>Examiner Assigned</strong>
                            <?php if ($submission['examiner_name']): ?>
                                <p style="margin: 0; font-size: 0.875rem; color: var(--text-secondary);">Assigned to <?php echo htmlspecialchars($submission['examiner_name']); ?></p>
                            <?php else: ?>
                                <p style="margin: 0; font-size: 0.875rem; color: var(--text-secondary);">Waiting for assignment...</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: var(--spacing-sm);">
                        <?php if ($submission['status'] === 'in_evaluation'): ?>
                            <div class="spinner" style="width: 30px; height: 30px;"></div>
                        <?php else: ?>
                            <div style="width: 30px; height: 30px; border-radius: 50%; background-color: var(--border); color: var(--text-secondary); display: flex; align-items: center; justify-content: center;">3</div>
                        <?php endif; ?>
                        <div>
                            <strong>Evaluation</strong>
                            <p style="margin: 0; font-size: 0.875rem; color: var(--text-secondary);">
                                <?php echo $submission['status'] === 'in_evaluation' ? 'In progress...' : 'Pending...'; ?>
                            </p>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: var(--spacing-sm);">
                        <div style="width: 30px; height: 30px; border-radius: 50%; background-color: var(--border); color: var(--text-secondary); display: flex; align-items: center; justify-content: center;">4</div>
                        <div>
                            <strong>Results Ready</strong>
                            <p style="margin: 0; font-size: 0.875rem; color: var(--text-secondary);">You'll be notified via email</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Information Box -->
            <div class="alert alert-info">
                <p><strong>💡 What's Next?</strong></p>
                <ul style="margin: 10px 0 0 20px;">
                    <li>You'll receive an email when an examiner is assigned to your test</li>
                    <li>Another email will be sent when your results are ready</li>
                    <li>Evaluation typically takes 24-48 hours</li>
                    <li>This page auto-refreshes every 30 seconds to show latest status</li>
                </ul>
            </div>

            <!-- Actions -->
            <div style="display: flex; gap: var(--spacing-sm); margin-top: var(--spacing-lg);">
                <a href="../profile/dashboard.php" class="btn btn-secondary" style="flex: 1;">Back to Dashboard</a>
                <a href="writing-tests.php" class="btn btn-primary" style="flex: 1;">Take Another Test</a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php require_once __DIR__ . '/../includes/footer.php'; ?>

    <script src="../assets/js/main.js"></script>
</body>
</html>

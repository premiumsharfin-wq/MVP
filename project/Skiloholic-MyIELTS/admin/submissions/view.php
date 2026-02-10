<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/session.php';

require_admin();

$submissionId = intval($_GET['id'] ?? 0);
if (!$submissionId) {
    redirect('queue.php');
}

// Get submission details
$submission = db_fetch(
    "SELECT s.*, t.title as test_title, t.task1_image_url, t.task1_description, t.task2_prompt,
            u.full_name as student_name, u.email as student_email,
            e.overall_score, e.task1_score, e.task2_score, e.task1_feedback, e.task2_feedback, e.overall_feedback,
            ex.full_name as examiner_name
     FROM submissions s
     LEFT JOIN writing_tests t ON s.test_id = t.id
     LEFT JOIN users u ON s.user_id = u.id
     LEFT JOIN evaluations e ON s.id = e.submission_id
     LEFT JOIN users ex ON e.examiner_id = ex.id
     WHERE s.id = ?",
    [$submissionId]
);

if (!$submission) {
    redirect('queue.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Submission - MyIELTS Admin</title>
    <link rel="stylesheet" href="../../assets/css/main.css?v=2.0">
</head>
<body>
    <!-- Navigation -->
    <?php require_once __DIR__ . '/../../includes/header.php'; ?>

    <div class="container" style="margin-top: var(--spacing-lg); margin-bottom: var(--spacing-xl);">
        <div class="card" style="margin-bottom: var(--spacing-lg);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1 style="margin-bottom: var(--spacing-xs);">Submission #<?php echo $submission['id']; ?></h1>
                    <p style="color: var(--text-secondary);">
                        Student: <strong><?php echo htmlspecialchars($submission['student_name']); ?></strong>
                        (<?php echo htmlspecialchars($submission['student_email']); ?>)
                    </p>
                </div>
                <div>
                    <span class="badge badge-<?php
                        echo match($submission['status']) {
                            'pending' => 'warning',
                            'assigned', 'in_evaluation' => 'info',
                            'completed' => 'success',
                            default => 'secondary'
                        };
                    ?>">
                        <?php echo ucwords(str_replace('_', ' ', $submission['status'])); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Task 1 -->
        <?php if ($submission['task1_answer']): ?>
        <div class="card" style="margin-bottom: var(--spacing-lg);">
            <h2 style="color: var(--primary); margin-bottom: var(--spacing-md);">Task 1 Answer</h2>

            <?php if ($submission['task1_question_images']): ?>
                <?php
                $images = json_decode($submission['task1_question_images'], true);
                if ($images) {
                    foreach ($images as $img) {
                        echo '<img src="' . htmlspecialchars($img) . '" style="max-width: 100%; margin-bottom: 1rem;"><br>';
                    }
                }
                ?>
            <?php endif; ?>

            <div style="background: var(--bg-tertiary); padding: var(--spacing-md); border-radius: var(--radius-md); white-space: pre-wrap;">
                <?php echo htmlspecialchars($submission['task1_answer']); ?>
            </div>

            <?php if ($submission['task1_score'] !== null): ?>
                <div style="margin-top: var(--spacing-md); padding-top: var(--spacing-md); border-top: 1px solid var(--border);">
                    <h3>Score: <span style="color: var(--primary);"><?php echo $submission['task1_score']; ?></span></h3>
                    <?php if ($submission['task1_feedback']): ?>
                        <p><strong>Feedback:</strong></p>
                        <p style="white-space: pre-wrap;"><?php echo htmlspecialchars($submission['task1_feedback']); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Task 2 -->
        <?php if ($submission['task2_answer']): ?>
        <div class="card" style="margin-bottom: var(--spacing-lg);">
            <h2 style="color: var(--secondary); margin-bottom: var(--spacing-md);">Task 2 Answer</h2>

            <div style="background: var(--bg-tertiary); padding: var(--spacing-md); border-radius: var(--radius-md); white-space: pre-wrap;">
                <?php echo htmlspecialchars($submission['task2_answer']); ?>
            </div>

            <?php if ($submission['task2_score'] !== null): ?>
                <div style="margin-top: var(--spacing-md); padding-top: var(--spacing-md); border-top: 1px solid var(--border);">
                    <h3>Score: <span style="color: var(--secondary);"><?php echo $submission['task2_score']; ?></span></h3>
                    <?php if ($submission['task2_feedback']): ?>
                        <p><strong>Feedback:</strong></p>
                        <p style="white-space: pre-wrap;"><?php echo htmlspecialchars($submission['task2_feedback']); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Overall -->
        <?php if ($submission['overall_score'] !== null): ?>
        <div class="card" style="border: 2px solid var(--success);">
            <h2 style="color: var(--success); margin-bottom: var(--spacing-md);">Overall Assessment</h2>
            <h1 style="font-size: 3rem; margin-bottom: var(--spacing-sm);"><?php echo $submission['overall_score']; ?></h1>

            <?php if ($submission['overall_feedback']): ?>
                <div style="background: rgba(16, 185, 129, 0.1); padding: var(--spacing-md); border-radius: var(--radius-md);">
                    <p style="white-space: pre-wrap;"><?php echo htmlspecialchars($submission['overall_feedback']); ?></p>
                </div>
            <?php endif; ?>

            <p style="margin-top: var(--spacing-md); color: var(--text-secondary);">
                Evaluated by <?php echo htmlspecialchars($submission['examiner_name']); ?> on <?php echo format_datetime($submission['evaluation_date'] ?? $submission['created_at']); ?>
            </p>
        </div>
        <?php endif; ?>

        <div style="margin-top: var(--spacing-lg);">
            <a href="queue.php" class="btn btn-secondary">Back to Queue</a>
        </div>
    </div>

    <!-- Footer -->
    <?php require_once __DIR__ . '/../../includes/footer.php'; ?>
    <script src="../../assets/js/main.js"></script>
</body>
</html>

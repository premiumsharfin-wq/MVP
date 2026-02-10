<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submission Queue - MyIELTS Admin</title>
    <link rel="stylesheet" href="../../assets/css/main.css?v=2.2">
</head>
<body>
    <?php
    require_once __DIR__ . '/../../config/config.php';
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../includes/session.php';
    require_once __DIR__ . '/../../includes/email-functions.php';

    require_admin();
    $user = get_current_user();

    // Handle examiner assignment
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_examiner'])) {
        $submissionId = intval($_POST['submission_id']);
        $examinerId = intval($_POST['examiner_id']);
        $estimatedTime = sanitize($_POST['estimated_time']);
        // Check if already assigned
        $exists = db_fetch("SELECT * FROM evaluations WHERE submission_id = ?", [$submissionId]);

        if ($exists) {
            db_execute(
                "UPDATE evaluations SET examiner_id = ?, estimated_completion_time = ?, assigned_at = NOW() WHERE submission_id = ?",
                [$examinerId, $estimatedTime, $submissionId]
            );
        } else {
            db_insert(
                "INSERT INTO evaluations (submission_id, examiner_id, estimated_completion_time, assigned_at)
                 VALUES (?, ?, ?, NOW())",
                [$submissionId, $examinerId, $estimatedTime]
            );
        }
        db_query("UPDATE submissions SET status = 'in_evaluation' WHERE id = ?", [$submissionId]);
        send_examiner_assigned_notification($submissionId, $examinerId);
        redirect(BASE_URL . 'admin/submissions/queue.php?assigned=1');
    }

    // Data Fetching
    $examiners = db_fetch_all("SELECT id, full_name, email FROM users WHERE role = 'admin'");

    // Pending
    $pendingSubmissions = db_fetch_all(
        "SELECT s.*, u.full_name, u.email, t.title as test_title
         FROM submissions s
         JOIN users u ON s.user_id = u.id
         LEFT JOIN writing_tests t ON s.test_id = t.id
         WHERE s.status = 'pending'
         ORDER BY s.submitted_at ASC"
    );

    // In Evaluation
    $inEvalSubmissions = db_fetch_all(
        "SELECT s.*, u.full_name as student_name, t.title as test_title,
                e.examiner_id, ex.full_name as examiner_name, e.estimated_completion_time
         FROM submissions s
         JOIN users u ON s.user_id = u.id
         LEFT JOIN writing_tests t ON s.test_id = t.id
         LEFT JOIN evaluations e ON s.id = e.submission_id
         LEFT JOIN users ex ON e.examiner_id = ex.id
         WHERE s.status IN ('assigned', 'in_evaluation')
         ORDER BY s.submitted_at ASC"
    );
    ?>

    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <a href="<?php echo BASE_URL; ?>admin/index.php" class="sidebar-brand">
                    <img src="<?php echo LOGO_URL . 'No%20Background%20Skiloholic.png'; ?>" alt="Logo" style="height: 32px;">
                    MyIELTS Admin
                </a>
            </div>
            <ul class="sidebar-nav">
                <li><a href="../index.php" class="sidebar-link">📊 Dashboard</a></li>
                <li><a href="../tests/manage.php" class="sidebar-link">📝 Manage Tests</a></li>
                <li><a href="queue.php" class="sidebar-link active">📋 Submission Queue</a></li>
                <li><a href="../users/index.php" class="sidebar-link">👥 Manage Users</a></li>
                <li><hr style="border-color: #374151; margin: 1rem 1.5rem;"></li>
                <li><a href="../../dashboard.php" class="sidebar-link">🏠 User View</a></li>
                <li><a href="../../auth/logout.php" class="sidebar-link">🚪 Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Topbar -->
            <header class="admin-topbar">
                <h1 class="admin-page-title">Submission Queue</h1>
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <span class="status-badge status-pending"><?php echo count($pendingSubmissions); ?> Pending</span>
                    <span class="status-badge status-assigned"><?php echo count($inEvalSubmissions); ?> In Progress</span>
                    <div style="font-weight: 600; font-size: 0.9rem;">
                        👤 <?php echo htmlspecialchars($user['full_name']); ?>
                    </div>
                </div>
            </header>

            <div class="admin-content">
                <?php if (isset($_GET['assigned'])): ?>
                    <div class="alert alert-success" style="margin-bottom: 2rem;">
                         ✅ Examiner assigned successfully!
                    </div>
                <?php endif; ?>

                <!-- Pending Section -->
                <section style="margin-bottom: 3rem;">
                    <div class="card-header">
                        <h2 class="card-title">⏳ Pending Assignments</h2>
                        <span class="status-badge status-pending"><?php echo count($pendingSubmissions); ?> Waiting</span>
                    </div>

                    <?php if (empty($pendingSubmissions)): ?>
                        <div class="admin-card text-center" style="padding: 3rem;">
                            <h3>No pending submissions 🎉</h3>
                            <p style="color: #6b7280;">All caught up! Great job.</p>
                        </div>
                    <?php else: ?>
                        <div style="display: grid; gap: 1.5rem;">
                            <?php foreach ($pendingSubmissions as $submission): ?>
                                <div id="submission-<?php echo $submission['id']; ?>" class="assignment-card">
                                    <div class="assignment-header">
                                        <div>
                                            <h3 style="font-size: 1.1rem; font-weight: 700; margin: 0 0 0.5rem 0;">
                                                <?php echo htmlspecialchars($submission['full_name']); ?>
                                            </h3>
                                            <div style="color: #6b7280; font-size: 0.9rem;">
                                                <?php echo htmlspecialchars($submission['email']); ?>
                                            </div>
                                        </div>
                                        <div style="text-align: right;">
                                            <div style="font-weight: 600; color: var(--primary);">
                                                <?php echo $submission['is_custom_test'] ? 'Custom Test' : htmlspecialchars($submission['test_title']); ?>
                                            </div>
                                            <small style="color: #9ca3af;">
                                                Submitted: <?php echo time_ago($submission['submitted_at']); ?>
                                            </small>
                                        </div>
                                    </div>

                                    <form method="POST" action="" class="assignment-form">
                                        <input type="hidden" name="submission_id" value="<?php echo $submission['id']; ?>">

                                        <div style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
                                            <div style="flex: 1; min-width: 200px;">
                                                <label for="examiner_<?php echo $submission['id']; ?>" style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.25rem;">Assign Examiner</label>
                                                <select id="examiner_<?php echo $submission['id']; ?>" name="examiner_id" required style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                                                    <option value="">Select Examiner...</option>
                                                    <?php foreach ($examiners as $examiner): ?>
                                                        <option value="<?php echo $examiner['id']; ?>">
                                                            <?php echo htmlspecialchars($examiner['full_name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div style="flex: 1; min-width: 200px;">
                                                <label for="estimated_time_<?php echo $submission['id']; ?>" style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.25rem;">Estimated Completion</label>
                                                <input type="text" id="estimated_time_<?php echo $submission['id']; ?>"
                                                       name="estimated_time"
                                                       placeholder="e.g., 24 hours"
                                                       required
                                                       style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                                            </div>

                                            <div style="display: flex; gap: 0.5rem;">
                                                <button type="submit" name="assign_examiner" class="btn btn-primary" style="padding: 0.6rem 1.5rem;">Assign</button>
                                                <a href="view.php?id=<?php echo $submission['id']; ?>" class="btn btn-secondary" style="padding: 0.6rem 1rem;">View</a>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <!-- In Progress Section -->
                <section>
                    <div class="card-header">
                        <h2 class="card-title">📝 In Progress</h2>
                    </div>

                    <div class="admin-card" style="padding: 0;">
                        <?php if (empty($inEvalSubmissions)): ?>
                            <div style="padding: 2rem; text-align: center; color: #6b7280;">No active evaluations.</div>
                        <?php else: ?>
                            <div class="admin-table-wrapper">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Test</th>
                                            <th>Examiner</th>
                                            <th>ETA</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($inEvalSubmissions as $submission): ?>
                                            <tr>
                                                <td>
                                                    <div style="font-weight: 600;"><?php echo htmlspecialchars($submission['student_name']); ?></div>
                                                </td>
                                                <td><?php echo $submission['is_custom_test'] ? 'Custom' : htmlspecialchars($submission['test_title']); ?></td>
                                                <td>
                                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                        <span style="width: 24px; height: 24px; background: #e5e7eb; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem;">👤</span>
                                                        <?php echo htmlspecialchars($submission['examiner_name']); ?>
                                                    </div>
                                                </td>
                                                <td><span class="status-badge status-assigned"><?php echo htmlspecialchars($submission['estimated_completion_time']); ?></span></td>
                                                <td>
                                                    <?php if ($submission['examiner_id'] == $_SESSION['user_id']): ?>
                                                        <a href="evaluate.php?id=<?php echo $submission['id']; ?>" class="btn btn-sm btn-primary">Evaluate</a>
                                                    <?php else: ?>
                                                        <a href="view.php?id=<?php echo $submission['id']; ?>" class="btn btn-sm btn-secondary">details</a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script src="../../assets/js/main.js"></script>
</body>
</html>

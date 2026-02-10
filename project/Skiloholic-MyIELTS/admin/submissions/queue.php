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

        // CSRF Verification (Basic placeholder)

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
        db_query("UPDATE submissions SET status = 'assigned' WHERE id = ?", [$submissionId]);
        send_examiner_assigned_notification($submissionId, $examinerId);
        redirect(BASE_URL . 'admin/submissions/queue.php?assigned=1');
    }

    // Handle Claim (Assign to self)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['claim_submission'])) {
        $submissionId = intval($_POST['submission_id']);
        $examinerId = $_SESSION['user_id'];
        $estimatedTime = "24 Hours"; // Default

        // Check if already assigned
        $exists = db_fetch("SELECT * FROM evaluations WHERE submission_id = ?", [$submissionId]);

        if (!$exists) {
            db_insert(
                "INSERT INTO evaluations (submission_id, examiner_id, estimated_completion_time, assigned_at)
                 VALUES (?, ?, ?, NOW())",
                [$submissionId, $examinerId, $estimatedTime]
            );
            db_query("UPDATE submissions SET status = 'in_evaluation' WHERE id = ?", [$submissionId]);
            redirect(BASE_URL . 'admin/submissions/evaluate.php?id=' . $submissionId);
        }
    }

    // Filter & Search
    $statusFilter = sanitize($_GET['status'] ?? '');

    // Data Fetching
    $examiners = db_fetch_all("SELECT id, full_name, email FROM users WHERE role = 'admin' OR role = 'examiner'");

    // Submissions Query
    $query = "SELECT s.*, u.full_name, u.email, t.title as test_title,
                     e.examiner_id, ex.full_name as examiner_name, e.estimated_completion_time
              FROM submissions s
              JOIN users u ON s.user_id = u.id
              LEFT JOIN writing_tests t ON s.test_id = t.id
              LEFT JOIN evaluations e ON s.id = e.submission_id
              LEFT JOIN users ex ON e.examiner_id = ex.id";

    $where = [];
    $params = [];

    if ($statusFilter) {
        $where[] = "s.status = ?";
        $params[] = $statusFilter;
    } else {
        // Default view: Pending and In Progress
        $where[] = "s.status IN ('pending', 'assigned', 'in_evaluation')";
    }

    if (!empty($where)) {
        $query .= " WHERE " . implode(' AND ', $where);
    }

    $query .= " ORDER BY CASE WHEN s.status = 'pending' THEN 0 ELSE 1 END, s.submitted_at ASC";

    $submissions = db_fetch_all($query, $params);
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
            <header class="admin-topbar">
                <h1 class="admin-page-title">Submission Queue</h1>
            </header>

            <div class="admin-content">
                <?php if (isset($_GET['assigned'])): ?>
                    <div class="alert alert-success" style="margin-bottom: 2rem;">
                         ✅ Examiner assigned successfully!
                    </div>
                <?php endif; ?>

                <!-- Filters -->
                <div class="admin-card" style="margin-bottom: 2rem; display: flex; gap: 1rem;">
                    <a href="queue.php" class="btn <?php echo !$statusFilter ? 'btn-primary' : 'btn-secondary'; ?>">Active Queue</a>
                    <a href="queue.php?status=pending" class="btn <?php echo $statusFilter === 'pending' ? 'btn-primary' : 'btn-secondary'; ?>">Pending Only</a>
                    <a href="queue.php?status=completed" class="btn <?php echo $statusFilter === 'completed' ? 'btn-primary' : 'btn-secondary'; ?>">Completed History</a>
                </div>

                <!-- Queue List -->
                <?php if (empty($submissions)): ?>
                    <div class="admin-card text-center" style="padding: 3rem;">
                        <h3>No submissions found 🎉</h3>
                        <p style="color: #6b7280;">There are no submissions matching your criteria.</p>
                    </div>
                <?php else: ?>
                    <div style="display: grid; gap: 1.5rem;">
                        <?php foreach ($submissions as $submission): ?>
                            <div id="submission-<?php echo $submission['id']; ?>" class="assignment-card" style="<?php echo $submission['status'] === 'completed' ? 'border-left-color: var(--success);' : ($submission['status'] === 'pending' ? 'border-left-color: var(--warning);' : 'border-left-color: var(--info);'); ?>">
                                <div class="assignment-header">
                                    <div>
                                        <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.5rem;">
                                            <span class="status-badge status-<?php echo $submission['status']; ?>">
                                                <?php echo ucwords(str_replace('_', ' ', $submission['status'])); ?>
                                            </span>
                                            <span style="color: #6b7280; font-size: 0.85rem;">#<?php echo $submission['id']; ?></span>
                                        </div>
                                        <h3 style="font-size: 1.1rem; font-weight: 700; margin: 0 0 0.25rem 0;">
                                            <?php echo htmlspecialchars($submission['full_name']); ?>
                                        </h3>
                                        <div style="color: #6b7280; font-size: 0.9rem;">
                                            <?php echo $submission['is_custom_test'] ? 'Custom Test' : htmlspecialchars($submission['test_title']); ?>
                                        </div>
                                    </div>
                                    <div style="text-align: right;">
                                        <div style="font-size: 0.9rem; margin-bottom: 0.5rem;">
                                            Submitted: <strong><?php echo time_ago($submission['submitted_at']); ?></strong>
                                        </div>
                                        <?php if ($submission['examiner_name']): ?>
                                            <div style="font-size: 0.9rem; color: #6b7280;">
                                                Examiner: <strong><?php echo htmlspecialchars($submission['examiner_name']); ?></strong>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if ($submission['status'] === 'pending'): ?>
                                    <div style="background: #f9fafb; padding: 1rem; border-top: 1px solid #e5e7eb; display: flex; gap: 1rem; align-items: center; justify-content: space-between;">
                                        <!-- Quick Claim -->
                                        <form method="POST">
                                            <input type="hidden" name="submission_id" value="<?php echo $submission['id']; ?>">
                                            <input type="hidden" name="claim_submission" value="1">
                                            <button type="submit" class="btn btn-primary">⚡ Claim & Evaluate</button>
                                        </form>

                                        <span style="color: #9ca3af;">— OR —</span>

                                        <!-- Assign Others -->
                                        <form method="POST" action="" style="display: flex; gap: 0.5rem; align-items: center;">
                                            <input type="hidden" name="submission_id" value="<?php echo $submission['id']; ?>">
                                            <select name="examiner_id" required style="padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                                                <option value="">Assign to...</option>
                                                <?php foreach ($examiners as $examiner): ?>
                                                    <option value="<?php echo $examiner['id']; ?>">
                                                        <?php echo htmlspecialchars($examiner['full_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="text" name="estimated_time" placeholder="ETA (e.g. 24h)" required style="width: 100px; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                                            <button type="submit" name="assign_examiner" class="btn btn-secondary">Assign</button>
                                        </form>
                                    </div>
                                <?php elseif ($submission['status'] === 'completed'): ?>
                                    <div style="background: #f9fafb; padding: 1rem; border-top: 1px solid #e5e7eb; text-align: right;">
                                        <a href="view.php?id=<?php echo $submission['id']; ?>" class="btn btn-secondary">View Result</a>
                                    </div>
                                <?php else: ?>
                                    <div style="background: #f9fafb; padding: 1rem; border-top: 1px solid #e5e7eb; text-align: right;">
                                        <?php if ($submission['examiner_id'] == $_SESSION['user_id']): ?>
                                            <a href="evaluate.php?id=<?php echo $submission['id']; ?>" class="btn btn-primary">Continue Evaluation</a>
                                        <?php else: ?>
                                            <a href="view.php?id=<?php echo $submission['id']; ?>" class="btn btn-secondary">View Status</a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="../../assets/js/main.js"></script>
</body>
</html>

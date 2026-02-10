<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MyIELTS</title>
    <link rel="stylesheet" href="../assets/css/main.css?v=2.0">
</head>
<body>
    <?php
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../includes/session.php';

    require_admin();
    $user = get_authenticated_user();

    // Get statistics
    $totalUsers = db_fetch("SELECT COUNT(*) as count FROM users WHERE role = 'user'")['count'];
    $totalSubmissions = db_fetch("SELECT COUNT(*) as count FROM submissions")['count'];
    $pendingSubmissions = db_fetch("SELECT COUNT(*) as count FROM submissions WHERE status = 'pending'")['count'];
    $completedEvaluations = db_fetch("SELECT COUNT(*) as count FROM submissions WHERE status = 'completed'")['count'];
    $totalTests = db_fetch("SELECT COUNT(*) as count FROM writing_tests WHERE is_active = TRUE")['count'];

    // Get recent submissions
    $recentSubmissions = db_fetch_all(
        "SELECT s.*, u.full_name, t.title as test_title
         FROM submissions s
         JOIN users u ON s.user_id = u.id
         LEFT JOIN writing_tests t ON s.test_id = t.id
         ORDER BY s.submitted_at DESC
         LIMIT 10"
    );
    ?>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="<?php echo BASE_URL; ?>" class="navbar-brand">
                <img src="<?php echo LOGO_URL . 'No%20Background%20Skiloholic.png'; ?>" alt="MyIELTS Logo" class="navbar-logo">
                <span>MyIELTS Admin</span>
            </a>

            <ul class="navbar-menu">
                <li><a href="index.php" style="color: var(--primary); font-weight: 700;">Dashboard</a></li>
                <li><a href="tests/manage.php">Manage Tests</a></li>
                <li><a href="submissions/queue.php">Submission Queue</a></li>
                <li><a href="users/index.php">Users</a></li>
                <li><a href="../dashboard.php">User View</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container" style="margin-top: var(--spacing-lg); margin-bottom: var(--spacing-xl);">
        <!-- Welcome Header -->
        <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; margin-bottom: var(--spacing-lg);">
            <h1 style="color: white; margin-bottom: var(--spacing-sm);">Admin Dashboard 👨‍💼</h1>
            <p style="color: rgba(255,255,255,0.9); font-size: 1.125rem;">Welcome, <?php echo htmlspecialchars($user['full_name']); ?></p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-3" style="margin-bottom: var(--spacing-xl);">
            <div class="card">
                <h3 style="color: var(--text-secondary); font-size: 0.875rem; font-weight: 600; text-transform: uppercase; margin-bottom: var(--spacing-sm);">Total Users</h3>
                <h1 style="color: var(--primary); font-size: 3rem; margin-bottom: 0;"><?php echo $totalUsers; ?></h1>
            </div>

            <div class="card">
                <h3 style="color: var(--text-secondary); font-size: 0.875rem; font-weight: 600; text-transform: uppercase; margin-bottom: var(--spacing-sm);">Total Submissions</h3>
                <h1 style="color: var(--secondary); font-size: 3rem; margin-bottom: 0;"><?php echo $totalSubmissions; ?></h1>
            </div>

            <div class="card">
                <h3 style="color: var(--text-secondary); font-size: 0.875rem; font-weight: 600; text-transform: uppercase; margin-bottom: var(--spacing-sm);">Available Tests</h3>
                <h1 style="color: var(--info); font-size: 3rem; margin-bottom: 0;"><?php echo $totalTests; ?></h1>
            </div>
        </div>

        <div class="grid grid-2" style="margin-bottom: var(--spacing-xl);">
            <div class="card">
                <h3 style="color: var(--text-secondary); font-size: 0.875rem; font-weight: 600; text-transform: uppercase; margin-bottom: var(--spacing-sm);">Pending Evaluations</h3>
                <h1 style="color: var(--warning); font-size: 3rem; margin-bottom: var(--spacing-sm);"><?php echo $pendingSubmissions; ?></h1>
                <a href="submissions/queue.php" class="btn btn-warning btn-sm">Review Queue</a>
            </div>

            <div class="card">
                <h3 style="color: var(--text-secondary); font-size: 0.875rem; font-weight: 600; text-transform: uppercase; margin-bottom: var(--spacing-sm);">completed Evaluations</h3>
                <h1 style="color: var(--success); font-size: 3rem; margin-bottom: 0;"><?php echo $completedEvaluations; ?></h1>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card" style="margin-bottom: var(--spacing-lg);">
            <h2 style="margin-bottom: var(--spacing-md);">Quick Actions</h2>
            <div class="grid grid-3">
                <a href="tests/add.php" class="btn btn-primary btn-lg" style="text-decoration: none;">➕ Add New Test</a>
                <a href="submissions/queue.php" class="btn btn-secondary btn-lg" style="text-decoration: none;">📋 View Submissions</a>
                <a href="users/index.php" class="btn btn-secondary btn-lg" style="text-decoration: none;">👥 Manage Users</a>
            </div>
        </div>

        <!-- Recent Submissions -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Recent Submissions</h2>
                <p class="card-subtitle">Latest test submissions from users</p>
            </div>

            <?php if (empty($recentSubmissions)): ?>
                <div class="text-center" style="padding: var(--spacing-xl); color: var(--text-secondary);">
                    <h3>No submissions yet</h3>
                    <p>Submissions will appear here as users take tests</p>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--border); text-align: left;">
                                <th style="padding: var(--spacing-sm); font-weight: 600;">Student</th>
                                <th style="padding: var(--spacing-sm); font-weight: 600;">Test</th>
                                <th style="padding: var(--spacing-sm); font-weight: 600;">Submitted</th>
                                <th style="padding: var(--spacing-sm); font-weight: 600;">Status</th>
                                <th style="padding: var(--spacing-sm); font-weight: 600;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentSubmissions as $submission): ?>
                                <tr style="border-bottom: 1px solid var(--border);">
                                    <td style="padding: var(--spacing-sm);">
                                        <?php echo htmlspecialchars($submission['full_name']); ?>
                                    </td>
                                    <td style="padding: var(--spacing-sm);">
                                        <?php
                                        $testName = $submission['is_custom_test'] ? 'Custom Test' : htmlspecialchars($submission['test_title']);
                                        echo $testName;
                                        ?>
                                    </td>
                                    <td style="padding: var(--spacing-sm); color: var(--text-secondary); font-size: 0.875rem;">
                                        <?php echo time_ago($submission['submitted_at']); ?>
                                    </td>
                                    <td style="padding: var(--spacing-sm);">
                                        <?php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'assigned' => 'info',
                                            'in_evaluation' => 'info',
                                            'completed' => 'success'
                                        ];
                                        $statusLabels = [
                                            'pending' => 'Pending',
                                            'assigned' => 'Assigned',
                                            'in_evaluation' => 'In Review',
                                            'completed' => 'Completed'
                                        ];
                                        $badgeClass = $statusColors[$submission['status']] ?? 'primary';
                                        echo '<span class="badge badge-' . $badgeClass . '">' . $statusLabels[$submission['status']] . '</span>';
                                        ?>
                                    </td>
                                    <td style="padding: var(--spacing-sm);">
                                        <?php if ($submission['status'] === 'pending'): ?>
                                            <a href="submissions/queue.php#submission-<?php echo $submission['id']; ?>" class="btn btn-sm btn-primary">Assign Examiner</a>
                                        <?php elseif ($submission['status'] === 'completed'): ?>
                                            <a href="submissions/view.php?id=<?php echo $submission['id']; ?>" class="btn btn-sm btn-secondary">View</a>
                                        <?php else: ?>
                                            <a href="submissions/evaluate.php?id=<?php echo $submission['id']; ?>" class="btn btn-sm btn-info">Evaluate</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div style="text-align: center; margin-top: var(--spacing-md);">
                    <a href="submissions/queue.php" class="btn btn-secondary">View All Submissions</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <!-- Footer -->
    <?php require_once __DIR__ . '/../includes/footer.php'; ?>

    <script src="../assets/js/main.js"></script>
</body>
</html>

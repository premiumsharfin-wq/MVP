<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details - MyIELTS Admin</title>
    <link rel="stylesheet" href="../../assets/css/main.css?v=2.0">
</head>
<body>
    <?php
    require_once __DIR__ . '/../../config/config.php';
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../includes/session.php';

    require_admin();
    $currentUser = get_current_user();

    $userId = intval($_GET['id'] ?? 0);
    if (!$userId) {
        redirect(BASE_URL . 'admin/users/index.php');
    }

    // Handle role update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
        $newRole = sanitize($_POST['role']);
        if (in_array($newRole, ['user', 'examiner', 'admin'])) {
            db_query("UPDATE users SET role = ? WHERE id = ?", [$newRole, $userId]);
            $success = "User role updated successfully!";
        }
    }

    // Get user details
    $user = db_fetch(
        "SELECT * FROM users WHERE id = ?",
        [$userId]
    );

    if (!$user) {
        redirect(BASE_URL . 'admin/users/index.php');
    }

    // Get user statistics
    $stats = db_fetch(
        "SELECT
            COUNT(DISTINCT s.id) as total_submissions,
            COUNT(DISTINCT CASE WHEN s.status = 'completed' THEN s.id END) as completed_submissions,
            COUNT(DISTINCT CASE WHEN s.status = 'pending' THEN s.id END) as pending_submissions,
            AVG(e.overall_score) as avg_score,
            MAX(e.overall_score) as best_score,
            MIN(e.overall_score) as lowest_score
         FROM submissions s
         LEFT JOIN evaluations e ON s.id = e.submission_id
         WHERE s.user_id = ?",
        [$userId]
    );

    // Get recent submissions
    $submissions = db_fetch_all(
        "SELECT s.*, t.title as test_title, e.overall_score, e.completed_at as evaluation_date
         FROM submissions s
         LEFT JOIN writing_tests t ON s.test_id = t.id
         LEFT JOIN evaluations e ON s.id = e.submission_id
         WHERE s.user_id = ?
         ORDER BY s.submitted_at DESC
         LIMIT 10",
        [$userId]
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
                <li><a href="../index.php">Dashboard</a></li>
                <li><a href="../tests/manage.php">Manage Tests</a></li>
                <li><a href="../submissions/queue.php">Submission Queue</a></li>
                <li><a href="index.php">Users</a></li>
                <li><a href="../../dashboard.php">User View</a></li>
                <li><a href="../../auth/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container" style="margin-top: var(--spacing-lg); margin-bottom: var(--spacing-xl);">
        <!-- Header -->
        <div class="card" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white; margin-bottom: var(--spacing-lg);">
            <h1 style="color: white; margin-bottom: var(--spacing-sm);"><?php echo htmlspecialchars($user['full_name']); ?></h1>
            <p style="color: rgba(255,255,255,0.9);"><?php echo htmlspecialchars($user['email']); ?></p>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success" style="margin-bottom: var(--spacing-lg);">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-2" style="gap: var(--spacing-lg);">
            <!-- User Information -->
            <div>
                <div class="card" style="margin-bottom: var(--spacing-lg);">
                    <h2 style="margin-bottom: var(--spacing-md);">User Information</h2>

                    <table style="width: 100%;">
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: var(--spacing-sm); font-weight: 600;">Full Name:</td>
                            <td style="padding: var(--spacing-sm);"><?php echo htmlspecialchars($user['full_name']); ?></td>
                        </tr>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: var(--spacing-sm); font-weight: 600;">Email:</td>
                            <td style="padding: var(--spacing-sm);"><?php echo htmlspecialchars($user['email']); ?></td>
                        </tr>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: var(--spacing-sm); font-weight: 600;">WhatsApp:</td>
                            <td style="padding: var(--spacing-sm);"><?php echo htmlspecialchars($user['whatsapp_number']); ?></td>
                        </tr>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: var(--spacing-sm); font-weight: 600;">Role:</td>
                            <td style="padding: var(--spacing-sm);">
                                <?php
                                $roleColors = ['admin' => 'danger', 'examiner' => 'warning', 'user' => 'success'];
                                $roleLabels = ['admin' => 'Admin', 'examiner' => 'Examiner', 'user' => 'Student'];
                                $badgeClass = $roleColors[$user['role']] ?? 'primary';
                                echo '<span class="badge badge-' . $badgeClass . '">' . $roleLabels[$user['role']] . '</span>';
                                ?>
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: var(--spacing-sm); font-weight: 600;">Email Verified:</td>
                            <td style="padding: var(--spacing-sm);">
                                <?php if ($user['email_verified']): ?>
                                    <span class="badge badge-success">✓ Verified</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Not Verified</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: var(--spacing-sm); font-weight: 600;">Registered:</td>
                            <td style="padding: var(--spacing-sm);"><?php echo format_datetime($user['created_at']); ?></td>
                        </tr>
                        <tr>
                            <td style="padding: var(--spacing-sm); font-weight: 600;">Last Updated:</td>
                            <td style="padding: var(--spacing-sm);"><?php echo format_datetime($user['updated_at']); ?></td>
                        </tr>
                    </table>
                </div>

                <!-- Change Role -->
                <div class="card">
                    <h2 style="margin-bottom: var(--spacing-md);">Manage Role</h2>

                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="role">Change User Role</label>
                            <select id="role" name="role" required>
                                <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>Student</option>
                                <option value="examiner" <?php echo $user['role'] === 'examiner' ? 'selected' : ''; ?>>Examiner</option>
                                <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </div>

                        <button type="submit" name="update_role" class="btn btn-primary"
                                onclick="return confirm('Are you sure you want to change this user\'s role?');">
                            Update Role
                        </button>
                    </form>
                </div>
            </div>

            <!-- Statistics -->
            <div>
                <div class="card" style="margin-bottom: var(--spacing-lg);">
                    <h2 style="margin-bottom: var(--spacing-md);">Performance Statistics</h2>

                    <div class="grid grid-2" style="gap: var(--spacing-sm); margin-bottom: var(--spacing-md);">
                        <div style="text-align: center; padding: var(--spacing-md); background: var(--bg-tertiary); border-radius: var(--radius-md);">
                            <h3 style="color: var(--primary); font-size: 2.5rem; margin: 0;"><?php echo $stats['total_submissions']; ?></h3>
                            <p style="color: var(--text-secondary); font-size: 0.875rem; margin: 0.5rem 0 0 0;">Total Submissions</p>
                        </div>

                        <div style="text-align: center; padding: var(--spacing-md); background: var(--bg-tertiary); border-radius: var(--radius-md);">
                            <h3 style="color: var(--success); font-size: 2.5rem; margin: 0;"><?php echo $stats['completed_submissions']; ?></h3>
                            <p style="color: var(--text-secondary); font-size: 0.875rem; margin: 0.5rem 0 0 0;">Evaluated</p>
                        </div>

                        <div style="text-align: center; padding: var(--spacing-md); background: var(--bg-tertiary); border-radius: var(--radius-md);">
                            <h3 style="color: var(--secondary); font-size: 2.5rem; margin: 0;">
                                <?php echo $stats['avg_score'] ? number_format($stats['avg_score'], 1) : 'N/A'; ?>
                            </h3>
                            <p style="color: var(--text-secondary); font-size: 0.875rem; margin: 0.5rem 0 0 0;">Average Score</p>
                        </div>

                        <div style="text-align: center; padding: var(--spacing-md); background: var(--bg-tertiary); border-radius: var(--radius-md);">
                            <h3 style="color: var(--warning); font-size: 2.5rem; margin: 0;"><?php echo $stats['pending_submissions']; ?></h3>
                            <p style="color: var(--text-secondary); font-size: 0.875rem; margin: 0.5rem 0 0 0;">Pending</p>
                        </div>
                    </div>

                    <?php if ($stats['best_score']): ?>
                        <div style="display: flex; justify-content: space-around; padding-top: var(--spacing-md); border-top: 1px solid var(--border);">
                            <div style="text-align: center;">
                                <p style="font-size: 0.875rem; color: var(--text-secondary); margin: 0;">Best Score</p>
                                <h3 style="color: var(--success); margin: 0.5rem 0 0 0;"><?php echo number_format($stats['best_score'], 1); ?></h3>
                            </div>
                            <div style="text-align: center;">
                                <p style="font-size: 0.875rem; color: var(--text-secondary); margin: 0;">Lowest Score</p>
                                <h3 style="color: var(--danger); margin: 0.5rem 0 0 0;"><?php echo number_format($stats['lowest_score'], 1); ?></h3>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Submissions -->
        <div class="card">
            <h2 style="margin-bottom: var(--spacing-md);">Recent Submissions</h2>

            <?php if (empty($submissions)): ?>
                <p class="text-center" style="color: var(--text-secondary); padding: var(--spacing-xl);">No submissions yet</p>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--border); text-align: left;">
                                <th style="padding: var(--spacing-sm); font-weight: 600;">Test</th>
                                <th style="padding: var(--spacing-sm); font-weight: 600;">Submitted</th>
                                <th style="padding: var(--spacing-sm); font-weight: 600;">Status</th>
                                <th style="padding: var(--spacing-sm); font-weight: 600;">Score</th>
                                <th style="padding: var(--spacing-sm); font-weight: 600;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($submissions as $sub): ?>
                                <tr style="border-bottom: 1px solid var(--border);">
                                    <td style="padding: var(--spacing-sm);">
                                        <?php echo $sub['is_custom_test'] ? 'Custom Test' : htmlspecialchars($sub['test_title']); ?>
                                    </td>
                                    <td style="padding: var(--spacing-sm); color: var(--text-secondary); font-size: 0.875rem;">
                                        <?php echo time_ago($sub['submitted_at']); ?>
                                    </td>
                                    <td style="padding: var(--spacing-sm);">
                                        <?php
                                        $statusColors = ['pending' => 'warning', 'assigned' => 'info', 'in_evaluation' => 'info', 'completed' => 'success'];
                                        $statusLabels = ['pending' => 'Pending', 'assigned' => 'Assigned', 'in_evaluation' => 'In Review', 'completed' => 'Completed'];
                                        $badgeClass = $statusColors[$sub['status']] ?? 'primary';
                                        echo '<span class="badge badge-' . $badgeClass . '">' . $statusLabels[$sub['status']] . '</span>';
                                        ?>
                                    </td>
                                    <td style="padding: var(--spacing-sm); font-weight: 600;">
                                        <?php echo $sub['overall_score'] ? number_format($sub['overall_score'], 1) : '-'; ?>
                                    </td>
                                    <td style="padding: var(--spacing-sm);">
                                        <?php if ($sub['status'] === 'completed'): ?>
                                            <a href="../../tests/view-result.php?id=<?php echo $sub['id']; ?>" class="btn btn-sm btn-primary" target="_blank">View Results</a>
                                        <?php else: ?>
                                            <a href="../submissions/evaluate.php?id=<?php echo $sub['id']; ?>" class="btn btn-sm btn-secondary">Evaluate</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div style="margin-top: var(--spacing-lg);">
            <a href="index.php" class="btn btn-secondary">← Back to All Users</a>
        </div>
    </div>

    <!-- Footer -->
    <?php require_once __DIR__ . '/../../includes/footer.php'; ?>

    <script src="../../assets/js/main.js"></script>
</body>
</html>

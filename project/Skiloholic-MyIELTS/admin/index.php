<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MyIELTS</title>
    <link rel="stylesheet" href="../assets/css/main.css?v=2.2">
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
    $activeTests = db_fetch("SELECT COUNT(*) as count FROM writing_tests WHERE is_active = TRUE")['count'];

    // Get recent submissions
    $recentSubmissions = db_fetch_all(
        "SELECT s.*, u.full_name, t.title as test_title
         FROM submissions s
         JOIN users u ON s.user_id = u.id
         LEFT JOIN writing_tests t ON s.test_id = t.id
         ORDER BY s.submitted_at DESC
         LIMIT 5"
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
                <li><a href="index.php" class="sidebar-link active">📊 Dashboard</a></li>
                <li><a href="tests/manage.php" class="sidebar-link">📝 Manage Tests</a></li>
                <li><a href="submissions/queue.php" class="sidebar-link">📋 Submission Queue</a></li>
                <li><a href="users/index.php" class="sidebar-link">👥 Manage Users</a></li>
                <li><hr style="border-color: #374151; margin: 1rem 1.5rem;"></li>
                <li><a href="../profile/dashboard.php" class="sidebar-link">🏠 User View</a></li>
                <li><a href="../auth/logout.php" class="sidebar-link">🚪 Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Topbar -->
            <header class="admin-topbar">
                <h1 class="admin-page-title">Dashboard</h1>
                <div style="font-weight: 600;">
                    Welcome, <?php echo htmlspecialchars($user['full_name']); ?> 👋
                </div>
            </header>

            <div class="admin-content">
                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-title">Total Users</div>
                        <div class="stat-value" style="color: var(--primary);"><?php echo $totalUsers; ?></div>
                        <div class="stat-desc">Registered students</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title">Pending Evaluations</div>
                        <div class="stat-value" style="color: var(--warning);"><?php echo $pendingSubmissions; ?></div>
                        <div class="stat-desc">Waiting for review</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title">Completed Reviews</div>
                        <div class="stat-value" style="color: var(--success);"><?php echo $completedEvaluations; ?></div>
                        <div class="stat-desc">All time evaluations</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title">Active Tests</div>
                        <div class="stat-value" style="color: var(--info);"><?php echo $activeTests; ?></div>
                        <div class="stat-desc">Available for students</div>
                    </div>
                </div>

                <!-- Quick Actions & Recent Activity -->
                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; margin-top: 2rem;">
                    <!-- Actions -->
                    <div class="admin-card">
                        <h2 class="card-title">🚀 Quick Actions</h2>
                        <div style="display: grid; gap: 1rem;">
                            <a href="submissions/queue.php" class="btn btn-warning btn-block" style="text-align: center;">
                                Review Pending (<?php echo $pendingSubmissions; ?>)
                            </a>
                            <a href="tests/add.php" class="btn btn-primary btn-block" style="text-align: center;">
                                ➕ Add New Test
                            </a>
                            <a href="users/index.php" class="btn btn-secondary btn-block" style="text-align: center;">
                                👥 Manage Users
                            </a>
                        </div>
                    </div>

                    <!-- Recent Submissions -->
                    <div class="admin-card">
                        <div class="card-header">
                            <h2 class="card-title">📝 Recent Submissions</h2>
                            <a href="submissions/queue.php" style="color: var(--primary); font-size: 0.9rem;">View All</a>
                        </div>

                        <?php if (empty($recentSubmissions)): ?>
                            <p style="color: #6b7280; text-align: center; padding: 2rem;">No submissions yet.</p>
                        <?php else: ?>
                            <div class="admin-table-wrapper">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Test</th>
                                            <th>Status</th>
                                            <th>Time</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentSubmissions as $sub): ?>
                                            <tr>
                                                <td style="font-weight: 500;">
                                                    <?php echo htmlspecialchars($sub['full_name']); ?>
                                                </td>
                                                <td>
                                                    <?php echo $sub['is_custom_test'] ? 'Custom Test' : htmlspecialchars($sub['test_title']); ?>
                                                </td>
                                                <td>
                                                    <span class="status-badge status-<?php echo $sub['status']; ?>">
                                                        <?php echo ucwords(str_replace('_', ' ', $sub['status'])); ?>
                                                    </span>
                                                </td>
                                                <td style="color: #6b7280; font-size: 0.85rem;">
                                                    <?php echo time_ago($sub['submitted_at']); ?>
                                                </td>
                                                <td>
                                                    <a href="submissions/view.php?id=<?php echo $sub['id']; ?>" class="btn btn-sm btn-secondary">View</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>

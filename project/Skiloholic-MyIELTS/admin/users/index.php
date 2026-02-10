<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - MyIELTS Admin</title>
    <link rel="stylesheet" href="../../assets/css/main.css?v=2.0">
</head>
<body>
    <?php
    require_once __DIR__ . '/../../config/config.php';
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../includes/session.php';

    require_admin();
    $user = get_current_user();

    // Get total users count
    $totalUsers = db_fetch("SELECT COUNT(*) as count FROM users")['count'];

    // User Search
    $search = sanitize($_GET['search'] ?? '');

    $query = "SELECT u.*,
                COUNT(DISTINCT s.id) as total_submissions,
                COUNT(DISTINCT CASE WHEN s.status = 'completed' THEN s.id END) as completed_submissions,
                AVG(e.overall_score) as avg_score,
                MAX(s.submitted_at) as last_submission
         FROM users u
         LEFT JOIN submissions s ON u.id = s.user_id
         LEFT JOIN evaluations e ON s.id = e.submission_id";

    $params = [];

    if ($search) {
        $query .= " WHERE u.full_name LIKE ? OR u.email LIKE ?";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    $query .= " GROUP BY u.id ORDER BY u.created_at DESC";

    // Get filterd users
    $users = db_fetch_all($query, $params);
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
                <li><a href="index.php" style="color: var(--primary); font-weight: 700;">Users</a></li>
                <li><a href="../../dashboard.php">User View</a></li>
                <li><a href="../../auth/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container" style="margin-top: var(--spacing-lg); margin-bottom: var(--spacing-xl);">
        <!-- Header -->
        <div class="card" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white; margin-bottom: var(--spacing-lg);">
            <h1 style="color: white; margin-bottom: var(--spacing-sm);">User Management 👥</h1>
            <p style="color: rgba(255,255,255,0.9); font-size: 1.125rem;">Manage all platform users</p>
        </div>

        <!-- Statistics -->
        <div class="grid grid-4" style="margin-bottom: var(--spacing-xl);">
            <div class="card">
                <h3 style="color: var(--text-secondary); font-size: 0.875rem; font-weight: 600; text-transform: uppercase; margin-bottom: var(--spacing-sm);">Total Users</h3>
                <h1 style="color: var(--primary); font-size: 3rem; margin-bottom: 0;"><?php echo $totalUsers; ?></h1>
            </div>

            <div class="card">
                <h3 style="color: var(--text-secondary); font-size: 0.875rem; font-weight: 600; text-transform: uppercase; margin-bottom: var(--spacing-sm);">Admins</h3>
                <h1 style="color: var(--danger); font-size: 3rem; margin-bottom: 0;">
                    <?php echo db_fetch("SELECT COUNT(*) as count FROM users WHERE role = 'admin'")['count']; ?>
                </h1>
            </div>

            <div class="card">
                <h3 style="color: var(--text-secondary); font-size: 0.875rem; font-weight: 600; text-transform: uppercase; margin-bottom: var(--spacing-sm);">Examiners</h3>
                <h1 style="color: var(--warning); font-size: 3rem; margin-bottom: 0;">
                    <?php echo db_fetch("SELECT COUNT(*) as count FROM users WHERE role = 'examiner'")['count']; ?>
                </h1>
            </div>

            <div class="card">
                <h3 style="color: var(--text-secondary); font-size: 0.875rem; font-weight: 600; text-transform: uppercase; margin-bottom: var(--spacing-sm);">Students</h3>
                <h1 style="color: var(--success); font-size: 3rem; margin-bottom: 0;">
                    <?php echo db_fetch("SELECT COUNT(*) as count FROM users WHERE role = 'user'")['count']; ?>
                </h1>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h2 class="card-title">All Users</h2>
                    <p class="card-subtitle">View and manage user accounts</p>
                </div>
                <form method="GET" action="" style="display: flex; gap: 0.5rem;">
                    <input type="text" name="search" placeholder="Search name or email..." value="<?php echo htmlspecialchars($search); ?>" style="padding: 8px; border: 1px solid var(--border); border-radius: var(--radius-sm); min-width: 250px;">
                    <button type="submit" class="btn btn-primary btn-sm">Search</button>
                    <?php if ($search): ?>
                        <a href="index.php" class="btn btn-secondary btn-sm">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border); text-align: left;">
                            <th style="padding: var(--spacing-sm); font-weight: 600;">Name & Email</th>
                            <th style="padding: var(--spacing-sm); font-weight: 600;">Role</th>
                            <th style="padding: var(--spacing-sm); font-weight: 600;">Verified</th>
                            <th style="padding: var(--spacing-sm); font-weight: 600;">Submissions</th>
                            <th style="padding: var(--spacing-sm); font-weight: 600;">Avg Score</th>
                            <th style="padding: var(--spacing-sm); font-weight: 600;">Registered</th>
                            <th style="padding: var(--spacing-sm); font-weight: 600; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="padding: var(--spacing-sm);">
                                    <strong><?php echo htmlspecialchars($u['full_name']); ?></strong><br>
                                    <small style="color: var(--text-secondary);"><?php echo htmlspecialchars($u['email']); ?></small>
                                </td>
                                <td style="padding: var(--spacing-sm);">
                                    <?php
                                    $roleColors = [
                                        'admin' => 'danger',
                                        'examiner' => 'warning',
                                        'user' => 'success'
                                    ];
                                    $roleLabels = [
                                        'admin' => 'Admin',
                                        'examiner' => 'Examiner',
                                        'user' => 'Student'
                                    ];
                                    $badgeClass = $roleColors[$u['role']] ?? 'primary';
                                    echo '<span class="badge badge-' . $badgeClass . '">' . $roleLabels[$u['role']] . '</span>';
                                    ?>
                                </td>
                                <td style="padding: var(--spacing-sm);">
                                    <?php if ($u['email_verified']): ?>
                                        <span class="badge badge-success">✓ Verified</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: var(--spacing-sm); font-weight: 600;">
                                    <?php echo $u['total_submissions']; ?>
                                    <?php if ($u['completed_submissions']): ?>
                                        <small style="color: var(--text-secondary);">(<?php echo $u['completed_submissions']; ?> evaluated)</small>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: var(--spacing-sm); font-weight: 600;">
                                    <?php echo $u['avg_score'] ? number_format($u['avg_score'], 1) : '-'; ?>
                                </td>
                                <td style="padding: var(--spacing-sm); color: var(--text-secondary); font-size: 0.875rem;">
                                    <?php echo time_ago($u['created_at']); ?>
                                </td>
                                <td style="padding: var(--spacing-sm); text-align: center;">
                                    <a href="view.php?id=<?php echo $u['id']; ?>" class="btn btn-sm btn-primary">View Details</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php require_once __DIR__ . '/../../includes/footer.php'; ?>

    <script src="../../assets/js/main.js"></script>
</body>
</html>

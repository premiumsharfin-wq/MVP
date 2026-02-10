<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - MyIELTS Admin</title>
    <link rel="stylesheet" href="../../assets/css/main.css?v=2.2">
</head>
<body>
    <?php
    require_once __DIR__ . '/../../config/config.php';
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../includes/session.php';

    require_admin();
    $user = get_current_user();

    // Handle status change
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['action'])) {
        // TODO: Add CSRF check
        $targetId = intval($_POST['user_id']);
        $action = $_POST['action'];

        // Prevent modifying own account
        if ($targetId !== $_SESSION['user_id']) {
            if ($action === 'verify') {
                db_query("UPDATE users SET email_verified = TRUE WHERE id = ?", [$targetId]);
                $msg = "User verified successfully.";
            } elseif ($action === 'ban') {
                // Assuming we have an is_active or similar column, if not, maybe verify = false?
                // For now, let's just toggle verification as a placeholder for ban/unban logic or add an is_banned column later
                // Let's assume verifying manually is the main action needed.
            }
        }
    }

    // Get total users count
    $totalUsers = db_fetch("SELECT COUNT(*) as count FROM users")['count'];

    // User Search
    $search = sanitize($_GET['search'] ?? '');
    $roleFilter = sanitize($_GET['role'] ?? '');

    $query = "SELECT u.*,
                COUNT(DISTINCT s.id) as total_submissions,
                COUNT(DISTINCT CASE WHEN s.status = 'completed' THEN s.id END) as completed_submissions,
                AVG(e.overall_score) as avg_score,
                MAX(s.submitted_at) as last_submission
         FROM users u
         LEFT JOIN submissions s ON u.id = s.user_id
         LEFT JOIN evaluations e ON s.id = e.submission_id";

    $params = [];
    $where = [];

    if ($search) {
        $where[] = "(u.full_name LIKE ? OR u.email LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    if ($roleFilter) {
        $where[] = "u.role = ?";
        $params[] = $roleFilter;
    }

    if (!empty($where)) {
        $query .= " WHERE " . implode(' AND ', $where);
    }

    $query .= " GROUP BY u.id ORDER BY u.created_at DESC";

    // Get filtered users
    $users = db_fetch_all($query, $params);
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
                <li><a href="../submissions/queue.php" class="sidebar-link">📋 Submission Queue</a></li>
                <li><a href="index.php" class="sidebar-link active">👥 Manage Users</a></li>
                <li><hr style="border-color: #374151; margin: 1rem 1.5rem;"></li>
                <li><a href="../../dashboard.php" class="sidebar-link">🏠 User View</a></li>
                <li><a href="../../auth/logout.php" class="sidebar-link">🚪 Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-topbar">
                <h1 class="admin-page-title">User Management</h1>
            </header>

            <div class="admin-content">
                <!-- Statistics -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-title">Total Users</div>
                        <div class="stat-value" style="color: var(--primary);"><?php echo $totalUsers; ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title">Students</div>
                        <div class="stat-value" style="color: var(--success);">
                            <?php echo db_fetch("SELECT COUNT(*) as count FROM users WHERE role = 'user'")['count']; ?>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title">Examiners</div>
                        <div class="stat-value" style="color: var(--warning);">
                            <?php echo db_fetch("SELECT COUNT(*) as count FROM users WHERE role = 'examiner'")['count']; ?>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title">Admins</div>
                        <div class="stat-value" style="color: var(--danger);">
                            <?php echo db_fetch("SELECT COUNT(*) as count FROM users WHERE role = 'admin'")['count']; ?>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="admin-card" style="margin-top: 2rem; margin-bottom: 2rem;">
                    <form method="GET" action="" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 250px;">
                            <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.25rem;">Search</label>
                            <input type="text" name="search" placeholder="Search name or email..." value="<?php echo htmlspecialchars($search); ?>" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                        </div>
                        <div style="flex: 0 0 200px;">
                            <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.25rem;">Role</label>
                            <select name="role" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                                <option value="">All Roles</option>
                                <option value="user" <?php echo $roleFilter === 'user' ? 'selected' : ''; ?>>Student</option>
                                <option value="examiner" <?php echo $roleFilter === 'examiner' ? 'selected' : ''; ?>>Examiner</option>
                                <option value="admin" <?php echo $roleFilter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </div>
                        <div style="display: flex; gap: 0.5rem;">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <?php if ($search || $roleFilter): ?>
                                <a href="index.php" class="btn btn-secondary">Clear</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- Users Table -->
                <div class="admin-card">
                    <div class="card-header">
                        <h2 class="card-title">All Users</h2>
                    </div>

                    <div class="admin-table-wrapper">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Stats</th>
                                    <th>Registered</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                    <tr>
                                        <td>
                                            <div style="font-weight: 600;"><?php echo htmlspecialchars($u['full_name']); ?></div>
                                            <div style="font-size: 0.85rem; color: #6b7280;"><?php echo htmlspecialchars($u['email']); ?></div>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo $u['role'] === 'admin' ? 'pending' : ($u['role'] === 'examiner' ? 'assigned' : 'completed'); ?>">
                                                <?php echo ucfirst($u['role']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($u['email_verified']): ?>
                                                <span class="status-badge status-completed">Verified</span>
                                            <?php else: ?>
                                                <span class="status-badge status-pending">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div style="font-size: 0.85rem;">
                                                <div>Tests: <strong><?php echo $u['total_submissions']; ?></strong></div>
                                                <div>Avg: <strong><?php echo $u['avg_score'] ? number_format($u['avg_score'], 1) : '-'; ?></strong></div>
                                            </div>
                                        </td>
                                        <td style="color: #6b7280; font-size: 0.85rem;">
                                            <?php echo date('M j, Y', strtotime($u['created_at'])); ?>
                                        </td>
                                        <td>
                                            <div style="display: flex; gap: 0.5rem;">
                                                <a href="view.php?id=<?php echo $u['id']; ?>" class="btn btn-sm btn-secondary">Profile</a>
                                                <?php if (!$u['email_verified']): ?>
                                                    <form method="POST" onsubmit="return confirm('Manually verify this user?');">
                                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                        <input type="hidden" name="action" value="verify">
                                                        <button type="submit" class="btn btn-sm btn-success">Verify</button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../../assets/js/main.js"></script>
</body>
</html>

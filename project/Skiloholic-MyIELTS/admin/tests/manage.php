<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tests - MyIELTS Admin</title>
    <link rel="stylesheet" href="../../assets/css/main.css?v=2.2">
</head>
<body>
    <?php
    require_once __DIR__ . '/../../config/config.php';
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../includes/session.php';

    require_admin();

    // Handle delete/activate via POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action']) && isset($_POST['test_id'])) {
            $testId = intval($_POST['test_id']);
            $action = $_POST['action'];

            // Verify CSRF (basic check since we haven't implemented full CSRF yet, but good practice)
            // TODO: Implement proper CSRF token verification

            if ($action === 'deactivate') {
                db_query("UPDATE writing_tests SET is_active = FALSE WHERE id = ?", [$testId]);
                $message = "Test deactivated successfully.";
                $msgType = "success";
            } elseif ($action === 'activate') {
                db_query("UPDATE writing_tests SET is_active = TRUE WHERE id = ?", [$testId]);
                $message = "Test activated successfully.";
                $msgType = "success";
            } elseif ($action === 'delete') {
                // Hard delete (careful!)
                // Check if any submissions exist
                $count = db_fetch("SELECT COUNT(*) as count FROM submissions WHERE test_id = ?", [$testId])['count'];
                if ($count > 0) {
                    $message = "Cannot delete test with existing submissions. Deactivate it instead.";
                    $msgType = "error";
                } else {
                    db_query("DELETE FROM writing_tests WHERE id = ?", [$testId]);
                    $message = "Test deleted permanently.";
                    $msgType = "success";
                }
            }
        }
    }

    // Filtering
    $search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
    $typeFilter = isset($_GET['type']) ? sanitize($_GET['type']) : '';
    $sourceFilter = isset($_GET['source']) ? sanitize($_GET['source']) : '';
    $statusFilter = isset($_GET['status']) ? sanitize($_GET['status']) : '';

    $where = ["1=1"];
    $params = [];

    if ($search) {
        $where[] = "title LIKE ?";
        $params[] = "%$search%";
    }

    if ($typeFilter) {
        $where[] = "test_type = ?";
        $params[] = $typeFilter;
    }

    if ($sourceFilter) {
        $where[] = "source = ?";
        $params[] = $sourceFilter;
    }

    if ($statusFilter === 'active') {
        $where[] = "is_active = TRUE";
    } elseif ($statusFilter === 'inactive') {
        $where[] = "is_active = FALSE";
    }

    $whereClause = implode(' AND ', $where);
    $tests = db_fetch_all("SELECT * FROM writing_tests WHERE $whereClause ORDER BY created_at DESC", $params);
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
                <li><a href="manage.php" class="sidebar-link active">📝 Manage Tests</a></li>
                <li><a href="../submissions/queue.php" class="sidebar-link">📋 Submission Queue</a></li>
                <li><a href="../users/index.php" class="sidebar-link">👥 Manage Users</a></li>
                <li><hr style="border-color: #374151; margin: 1rem 1.5rem;"></li>
                <li><a href="../../dashboard.php" class="sidebar-link">🏠 User View</a></li>
                <li><a href="../../auth/logout.php" class="sidebar-link">🚪 Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-topbar">
                <h1 class="admin-page-title">Manage Tests</h1>
                <a href="add.php" class="btn btn-primary">➕ Add New Test</a>
            </header>

            <div class="admin-content">
                <?php if (isset($message)): ?>
                    <div class="alert alert-<?php echo $msgType; ?>"><?php echo $message; ?></div>
                <?php endif; ?>

                <!-- Filters -->
                <div class="admin-card" style="margin-bottom: 2rem;">
                    <form method="GET" action="" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
                        <div>
                            <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.25rem;">Search</label>
                            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Test title..." style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.25rem;">Type</label>
                            <select name="type" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                                <option value="">All Types</option>
                                <option value="full" <?php echo $typeFilter === 'full' ? 'selected' : ''; ?>>Full Test</option>
                                <option value="task1" <?php echo $typeFilter === 'task1' ? 'selected' : ''; ?>>Task 1</option>
                                <option value="task2" <?php echo $typeFilter === 'task2' ? 'selected' : ''; ?>>Task 2</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.25rem;">Source</label>
                            <select name="source" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                                <option value="">All Sources</option>
                                <option value="Cambridge" <?php echo $sourceFilter === 'Cambridge' ? 'selected' : ''; ?>>Cambridge</option>
                                <option value="MyIELTS Practice Series" <?php echo $sourceFilter === 'MyIELTS Practice Series' ? 'selected' : ''; ?>>MyIELTS</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.25rem;">Status</label>
                            <select name="status" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                                <option value="">All Status</option>
                                <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $statusFilter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-secondary" style="width: 100%;">Filter</button>
                        </div>
                    </form>
                </div>

                <!-- Tests Table -->
                <div class="admin-card">
                    <div class="card-header">
                        <h2 class="card-title">Test Library (<?php echo count($tests); ?>)</h2>
                    </div>

                    <?php if (empty($tests)): ?>
                        <div style="text-align: center; padding: 3rem; color: #6b7280;">
                            <p>No tests found matching your criteria.</p>
                        </div>
                    <?php else: ?>
                        <div class="admin-table-wrapper">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Details</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tests as $test): ?>
                                        <tr style="<?php echo !$test['is_active'] ? 'opacity: 0.6; background: #f9fafb;' : ''; ?>">
                                            <td>
                                                <div style="font-weight: 600;"><?php echo htmlspecialchars($test['title']); ?></div>
                                            </td>
                                            <td>
                                                <div style="display: flex; gap: 0.5rem;">
                                                    <span class="status-badge status-assigned"><?php echo htmlspecialchars($test['test_type']); ?></span>
                                                    <span class="status-badge status-pending"><?php echo htmlspecialchars($test['source']); ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($test['is_active']): ?>
                                                    <span class="status-badge status-completed">Active</span>
                                                <?php else: ?>
                                                    <span class="status-badge status-pending">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="font-size: 0.85rem; color: #6b7280;">
                                                <?php echo date('M j, Y', strtotime($test['created_at'])); ?>
                                            </td>
                                            <td>
                                                <div style="display: flex; gap: 0.5rem;">
                                                    <a href="edit.php?id=<?php echo $test['id']; ?>" class="btn btn-sm btn-secondary">Edit</a>

                                                    <form method="POST" action="" onsubmit="return confirm('Are you sure?');" style="display: inline;">
                                                        <input type="hidden" name="test_id" value="<?php echo $test['id']; ?>">
                                                        <?php if ($test['is_active']): ?>
                                                            <input type="hidden" name="action" value="deactivate">
                                                            <button type="submit" class="btn btn-sm btn-warning">Deactivate</button>
                                                        <?php else: ?>
                                                            <input type="hidden" name="action" value="activate">
                                                            <button type="submit" class="btn btn-sm btn-success">Activate</button>
                                                        <?php endif; ?>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="../../assets/js/main.js"></script>
</body>
</html>

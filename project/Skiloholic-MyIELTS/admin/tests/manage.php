<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tests - MyIELTS Admin</title>
    <link rel="stylesheet" href="../../assets/css/main.css?v=2.0">
</head>
<body>
    <?php
    require_once __DIR__ . '/../../config/config.php';
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../includes/session.php';

    require_admin();

    // Handle delete
    if (isset($_GET['delete'])) {
        $testId = intval($_GET['delete']);
        db_query("UPDATE writing_tests SET is_active = FALSE WHERE id = ?", [$testId]);
        redirect(BASE_URL . 'admin/tests/manage.php?deleted=1');
    }

    // Handle activate
    if (isset($_GET['activate'])) {
        $testId = intval($_GET['activate']);
        db_query("UPDATE writing_tests SET is_active = TRUE WHERE id = ?", [$testId]);
        redirect(BASE_URL . 'admin/tests/manage.php?activated=1');
    }

    // Get all tests
    $tests = db_fetch_all("SELECT * FROM writing_tests ORDER BY created_at DESC");
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
                <li><a href="manage.php" style="color: var(--primary); font-weight: 700;">Manage Tests</a></li>
                <li><a href="../submissions/queue.php">Submission Queue</a></li>
                <li><a href="../../dashboard.php">User View</a></li>
                <li><a href="../../auth/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container" style="margin-top: var(--spacing-lg); margin-bottom: var(--spacing-xl);">
        <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; margin-bottom: var(--spacing-lg);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1 style="color: white; margin-bottom: var(--spacing-sm);">Manage Tests 📚</h1>
                    <p style="color: rgba(255,255,255,0.9);">Add, edit, or remove writing tests</p>
                </div>
                <a href="add.php" class="btn" style="background-color: white; color: var(--primary);">➕ Add New Test</a>
            </div>
        </div>

        <?php if (isset($_GET['added'])): ?>
            <div class="alert alert-success">Test added successfully!</div>
        <?php endif; ?>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success">Test deactivated successfully!</div>
        <?php endif; ?>

        <?php if (isset($_GET['activated'])): ?>
            <div class="alert alert-success">Test activated successfully!</div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">All Tests (<?php echo count($tests); ?>)</h2>
            </div>

            <?php if (empty($tests)): ?>
                <div class="text-center" style="padding: var(--spacing-xl); color: var(--text-secondary);">
                    <h3>No tests created yet</h3>
                    <p>Click "Add New Test" to create your first test</p>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--border); text-align: left;">
                                <th style="padding: var(--spacing-sm); font-weight: 600;">Title</th>
                                <th style="padding: var(--spacing-sm); font-weight: 600;">Source</th>
                                <th style="padding: var(--spacing-sm); font-weight: 600;">Type</th>
                                <th style="padding: var(--spacing-sm); font-weight: 600;">Status</th>
                                <th style="padding: var(--spacing-sm); font-weight: 600;">Created</th>
                                <th style="padding: var(--spacing-sm); font-weight: 600;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tests as $test): ?>
                                <tr style="border-bottom: 1px solid var(--border); <?php echo !$test['is_active'] ? 'opacity: 0.5;' : ''; ?>">
                                    <td style="padding: var(--spacing-sm);">
                                        <?php echo htmlspecialchars($test['title']); ?>
                                    </td>
                                    <td style="padding: var(--spacing-sm);">
                                        <span class="badge badge-primary"><?php echo htmlspecialchars($test['source']); ?></span>
                                    </td>
                                    <td style="padding: var(--spacing-sm);">
                                        <?php
                                        $typeLabels = [
                                            'full' => 'Full Test',
                                            'task1' => 'Task 1',
                                            'task2' => 'Task 2'
                                        ];
                                        echo $typeLabels[$test['test_type']];
                                        ?>
                                    </td>
                                    <td style="padding: var(--spacing-sm);">
                                        <?php if ($test['is_active']): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-error">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: var(--spacing-sm); font-size: 0.875rem; color: var(--text-secondary);">
                                        <?php echo format_datetime($test['created_at']); ?>
                                    </td>
                                    <td style="padding: var(--spacing-sm);">
                                        <div style="display: flex; gap: var(--spacing-xs);">
                                            <?php if ($test['is_active']): ?>
                                                <a href="?delete=<?php echo $test['id']; ?>"
                                                   class="btn btn-sm btn-error"
                                                   onclick="return confirm('Are you sure you want to deactivate this test? Students will no longer be able to take it.');">
                                                    Deactivate
                                                </a>
                                            <?php else: ?>
                                                <a href="?activate=<?php echo $test['id']; ?>"
                                                   class="btn btn-sm btn-success">
                                                    Activate
                                                </a>
                                            <?php endif; ?>
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

    <!-- Footer -->
    <?php require_once __DIR__ . '/../../includes/footer.php'; ?>

    <script src="../../assets/js/main.js"></script>
</body>
</html>

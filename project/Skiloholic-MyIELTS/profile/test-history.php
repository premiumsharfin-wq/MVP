<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test History - MyIELTS</title>
    <link rel="stylesheet" href="../assets/css/main.css?v=2.3">
</head>
<body>
    <?php
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../includes/session.php';

    require_login();
    $user = get_authenticated_user();

    // Pagination
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $perPage = 10;
    $offset = ($page - 1) * $perPage;

    // Filters
    $statusFilter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
    $typeFilter = isset($_GET['type']) ? sanitize($_GET['type']) : '';
    $search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

    // Build query
    $where = ["s.user_id = ?"];
    $params = [$user['id']];

    if ($statusFilter) {
        $where[] = "s.status = ?";
        $params[] = $statusFilter;
    }

    if ($typeFilter === 'custom') {
        $where[] = "s.is_custom_test = TRUE";
    } elseif ($typeFilter === 'practice') {
        $where[] = "s.is_custom_test = FALSE";
    }

    if ($search) {
        $where[] = "(t.title LIKE ? OR s.id = ?)";
        $params[] = "%$search%";
        $params[] = $search;
    }

    $whereClause = implode(' AND ', $where);

    // Get total count
    $totalQuery = "SELECT COUNT(*) as count FROM submissions s
                   LEFT JOIN writing_tests t ON s.test_id = t.id
                   WHERE $whereClause";
    $total = db_fetch($totalQuery, $params)['count'];
    $totalPages = ceil($total / $perPage);

    // Get submissions
    $submissions = db_fetch_all(
        "SELECT s.*, t.title as test_title, t.test_type,
                e.overall_band_task1, e.overall_band_task2, e.feedback
         FROM submissions s
         LEFT JOIN writing_tests t ON s.test_id = t.id
         LEFT JOIN evaluations e ON s.id = e.submission_id
         WHERE $whereClause
         ORDER BY s.submitted_at DESC
         LIMIT $perPage OFFSET $offset",
        $params
    );
    ?>

    <div class="user-layout">
        <?php include __DIR__ . '/../includes/user-sidebar.php'; ?>

        <main class="user-main">
            <header class="page-header">
                <h1 class="page-title">Test History</h1>
                <p class="page-subtitle">View all your submissions and results</p>
            </header>

            <div class="page-content">
                <!-- Filters & Search -->
                <div class="widget-card" style="margin-bottom: 1.5rem;">
                    <form method="GET" action="">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
                            <div>
                                <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.25rem;">Search</label>
                                <input type="text"
                                       name="search"
                                       value="<?php echo htmlspecialchars($search); ?>"
                                       placeholder="Test name or ID..."
                                       style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                            </div>
                            <div>
                                <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.25rem;">Status</label>
                                <select name="status" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                                    <option value="">All Status</option>
                                    <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="in_evaluation" <?php echo $statusFilter === 'in_evaluation' ? 'selected' : ''; ?>>In Review</option>
                                    <option value="completed" <?php echo $statusFilter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                </select>
                            </div>
                            <div>
                                <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.25rem;">Type</label>
                                <select name="type" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                                    <option value="">All Types</option>
                                    <option value="practice" <?php echo $typeFilter === 'practice' ? 'selected' : ''; ?>>Practice Tests</option>
                                    <option value="custom" <?php echo $typeFilter === 'custom' ? 'selected' : ''; ?>>Custom Tests</option>
                                </select>
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="test-history.php" class="btn btn-secondary">Clear</a>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Results -->
                <?php if (empty($submissions)): ?>
                    <div class="widget-card" style="text-align: center; padding: 3rem;">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">📚</div>
                        <h3 style="color: #374151;">No tests found</h3>
                        <p style="color: #6b7280;">Start practicing to see your test history here.</p>
                        <a href="../tests/writing-tests.php" class="btn btn-primary" style="margin-top: 1rem;">Browse Tests</a>
                    </div>
                <?php else:?>
                    <?php foreach ($submissions as $submission): ?>
                        <div class="widget-card" style="margin-bottom: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                                <div>
                                    <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.25rem;">
                                        <?php if ($submission['is_custom_test']): ?>
                                            <span style="color: var(--primary);">Custom Test #<?php echo $submission['id']; ?></span>
                                        <?php else: ?>
                                            <?php echo htmlspecialchars($submission['test_title']); ?>
                                        <?php endif; ?>
                                    </h3>
                                    <div style="font-size: 0.875rem; color: #6b7280;">
                                        Submitted: <?php echo date('F j, Y \a\t g:i A', strtotime($submission['submitted_at'])); ?>
                                    </div>
                                </div>
                                <div>
                                    <?php
                                    $badges = [
                                        'pending' => '<span style="background: #fef3c7; color: #92400e; padding: 0.375rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">⏳ Pending</span>',
                                        'in_evaluation' => '<span style="background: #dbeafe; color: #1e40af; padding: 0.375rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">📝 In Review</span>',
                                        'completed' => '<span style="background: #d1fae5; color: #065f46; padding: 0.375rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">✅ Completed</span>'
                                    ];
                                    echo $badges[$submission['status']] ?? $submission['status'];
                                    ?>
                                </div>
                            </div>

                            <?php if ($submission['status'] === 'completed' && $submission['overall_band_task1']): ?>
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 1rem; padding: 1rem; background: #f9fafb; border-radius: 0.375rem;">
                                    <div>
                                        <div style="font-size: 0.75rem; color: #6b7280; text-transform: uppercase; font-weight: 600;">Task 1 Band</div>
                                        <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary);">
                                            <?php echo $submission['overall_band_task1']; ?>
                                        </div>
                                    </div>
                                    <?php if ($submission['overall_band_task2']): ?>
                                        <div>
                                            <div style="font-size: 0.75rem; color: #6b7280; text-transform: uppercase; font-weight: 600;">Task 2 Band</div>
                                            <div style="font-size: 1.5rem; font-weight: 700; color: var(--secondary);">
                                                <?php echo $submission['overall_band_task2']; ?>
                                            </div>
                                        </div>
                                        <div>
                                            <div style="font-size: 0.75rem; color: #6b7280; text-transform: uppercase; font-weight: 600;">Overall Writing</div>
                                            <div style="font-size: 1.5rem; font-weight: 700; color: #10b981;">
                                                <?php echo round(($submission['overall_band_task1'] + ($submission['overall_band_task2'] * 2)) / 3, 1); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <?php if ($submission['feedback']): ?>
                                    <details style="margin-top: 1rem;">
                                        <summary style="cursor: pointer; font-weight: 600; color: var(--primary); padding: 0.5rem 0;">
                                            View Feedback
                                        </summary>
                                        <div style="padding: 1rem; background: #f3f4f6; border-radius: 0.375rem; margin-top: 0.5rem; white-space: pre-wrap;">
                                            <?php echo htmlspecialchars($submission['feedback']); ?>
                                        </div>
                                    </details>
                                <?php endif; ?>
                            <?php elseif ($submission['status'] === 'pending'): ?>
                                <div style="padding: 1rem; background: #fffbeb; border-left: 4px solid #f59e0b; border-radius: 0.375rem; font-size: 0.875rem; color: #92400e;">
                                    ⏳ Your submission is in the queue and will be evaluated soon.
                                </div>
                            <?php elseif ($submission['status'] === 'in_evaluation'): ?>
                                <div style="padding: 1rem; background: #eff6ff; border-left: 4px solid #3b82f6; border-radius: 0.375rem; font-size: 0.875rem; color: #1e40af;">
                                    📝 Your test is currently being evaluated by an examiner.
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 2rem;">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>&status=<?php echo $statusFilter; ?>&type=<?php echo $typeFilter; ?>&search=<?php echo urlencode($search); ?>"
                                   class="btn btn-secondary btn-sm">Previous</a>
                            <?php endif; ?>

                            <span style="padding: 0.5rem 1rem; background: white; border: 1px solid #e5e7eb; border-radius: 0.375rem;">
                                Page <?php echo $page; ?> of <?php echo $totalPages; ?>
                            </span>

                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?php echo $page + 1; ?>&status=<?php echo $statusFilter; ?>&type=<?php echo $typeFilter; ?>&search=<?php echo urlencode($search); ?>"
                                   class="btn btn-secondary btn-sm">Next</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Writing Practice - MyIELTS</title>
    <link rel="stylesheet" href="../assets/css/main.css?v=2.3">
</head>
<body>
    <?php
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../includes/session.php';

    require_login();
    $user = get_authenticated_user();

    // Search and filter
    $search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
    $sourceFilter = isset($_GET['source']) ? sanitize($_GET['source']) : '';

    $where = ["is_active = TRUE"];
    $params = [];

    if ($search) {
        $query .= " AND title LIKE ?";
        $params[] = "%$search%";
    }

    if ($type) {
        $query .= " AND test_type = ?";
        $params[] = $type;
    }

    if ($source) {
        $query .= " AND source = ?";
        $params[] = $source;
    }

    $query .= " ORDER BY created_at DESC";

    // Get filtered tests
    $tests = db_fetch_all($query, $params);
    ?>

    <!-- Navigation -->
    <?php require_once __DIR__ . '/../includes/header.php'; ?>

    <div class="container dashboard-container">
        <div class="card test-header">
            <h1>IELTS Writing Tests 📝</h1>
            <p>Practice IELTS writing with expert evaluation and detailed feedback</p>
        </div>

        <!-- Test Options -->
        <div class="section-title">
            <h2>Choose Your Practice Mode</h2>
        </div>

        <!-- Search and Filter -->
        <div class="card mb-4">
            <form method="GET" action="" class="search-filter-form">
                <div class="search-input-wrapper">
                    <input type="text" name="search" placeholder="Search test title..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="filter-select-wrapper">
                    <select name="type">
                        <option value="">All Types</option>
                        <option value="full" <?php echo $type === 'full' ? 'selected' : ''; ?>>Full Test</option>
                        <option value="task1" <?php echo $type === 'task1' ? 'selected' : ''; ?>>Task 1 Only</option>
                        <option value="task2" <?php echo $type === 'task2' ? 'selected' : ''; ?>>Task 2 Only</option>
                    </select>
                </div>
                <div class="filter-select-wrapper-lg">
                    <select name="source">
                        <option value="">All Sources</option>
                        <option value="Cambridge" <?php echo $source === 'Cambridge' ? 'selected' : ''; ?>>Cambridge</option>
                        <option value="MyIELTS Practice Series" <?php echo $source === 'MyIELTS Practice Series' ? 'selected' : ''; ?>>MyIELTS Practice Series</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Search</button>
                <?php if ($search || $type || $source): ?>
                    <a href="writing-tests.php" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="grid grid-2 mb-5">
            <!-- Full Tests -->
            <div class="card">
                <h3 style="color: var(--primary);">📋 Full Writing Tests</h3>
                <p>Complete both Task 1 and Task 2 in one session, just like the real IELTS exam.</p>
                <p><strong>Duration:</strong> 60 minutes recommended</p>
            </div>

            <!-- Individual Tasks -->
            <div class="card">
                <h3 style="color: var(--secondary);">✍️ Individual Tasks</h3>
                <p>Practice Task 1 or Task 2 separately to focus on specific skills.</p>
                <p><strong>Duration:</strong> 20-40 minutes per task</p>
            </div>
        </div>

        <!-- Custom Evaluation -->
        <div id="custom" class="card custom-eval-card">
            <h2 class="mb-3">🎯 Custom Answer Evaluation</h2>
            <p class="mb-3">Already have an answer from another platform? Get it evaluated by our examiners!</p>
            <a href="custom-evaluation.php" class="btn btn-warning">Submit Custom Answer for Evaluation</a>
        </div>

        <!-- Available Tests -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Available Tests</h2>
                <p class="card-subtitle">Select a test to begin your practice</p>
            </div>
                    <!-- Tests Grid -->
                    <?php if (empty($tests)): ?>
                        <div class="widget-card" style="text-align: center; padding: 3rem;">
                            <div style="font-size: 3rem; margin-bottom: 1rem;">📚</div>
                            <h3>No tests found</h3>
                            <p style="color: #6b7280;">Try adjusting your search or filters.</p>
                        </div>
                    <?php else: ?>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem;">
                            <?php foreach ($tests as $test): ?>
                                <div class="widget-card" style="display: flex; flex-direction: column; transition: transform 0.2s, box-shadow 0.2s;"
                                     onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 10px 15px -3px rgba(0,0,0,0.1)'"
                                     onmouseout="this.style.transform=''; this.style.boxShadow=''">
                                    <div style="margin-bottom: 1rem;">
                                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                                            <h3 style="font-size: 1.125rem; font-weight: 600; margin: 0; flex: 1;">
                                                <?php echo htmlspecialchars($test['title']); ?>
                                            </h3>
                                            <?php
                                            $typeColors = [
                                                'full' => 'background: #dbeafe; color: #1e40af;',
                                                'task1' => 'background: #fef3c7; color: #92400e;',
                                                'task2' => 'background: #d1fae5; color: #065f46;'
                                            ];
                                            $typeLabels = ['full' => 'Full Test', 'task1' => 'Task 1', 'task2' => 'Task 2'];
                                            ?>
                                            <span style="padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; <?php echo $typeColors[$test['test_type']] ?? ''; ?>">
                                                <?php echo $typeLabels[$test['test_type']] ?? $test['test_type']; ?>
                                            </span>
                                        </div>
                                        <div style="font-size: 0.875rem; color: #6b7280;">
                                            <?php echo htmlspecialchars($test['source']); ?>
                                        </div>
                                    </div>

                                    <div style="flex: 1;">
                                        <?php if ($test['task1_description']): ?>
                                            <div style="font-size: 0.875rem; color: #374151; margin-bottom: 0.5rem; line-height: 1.5;">
                                                <?php echo substr(htmlspecialchars($test['task1_description']), 0, 100) . (strlen($test['task1_description']) > 100 ? '...' : ''); ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($test['task2_prompt']): ?>
                                            <div style="font-size: 0.875rem; color: #374151; line-height: 1.5;">
                                                <?php echo substr(htmlspecialchars($test['task2_prompt']), 0, 100) . (strlen($test['task2_prompt']) > 100 ? '...' : ''); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                                        <a href="take-test.php?test_id=<?php echo $test['id']; ?>"
                                           class="btn btn-primary"
                                           style="width: 100%; text-align: center;">
                                            Start Test →
                                        </a>
                                    </div>
                                </div>
                            <div class="test-actions">
                                <a href="take-test.php?id=<?php echo $test['id']; ?>" class="btn btn-primary">Start This Test</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- View History -->
        <div class="text-center mt-5">
            <a href="history.php" class="btn btn-secondary btn-lg">View Test History</a>
        </div>
    </div>

    <!-- Footer -->
    <!-- Footer -->
    <?php require_once __DIR__ . '/../includes/footer.php'; ?>

    <script src="../assets/js/main.js"></script>
</body>
</html>

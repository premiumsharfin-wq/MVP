<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MyIELTS</title>
    <link rel="stylesheet" href="../assets/css/main.css?v=2.3">
</head>
<body>
    <?php
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../includes/session.php';

    require_login();
    $user = get_authenticated_user();

    // Get user stats
    $totalSubmissions = db_fetch("SELECT COUNT(*) as count FROM submissions WHERE user_id = ?", [$user['id']])['count'];
    $completedTests = db_fetch("SELECT COUNT(*) as count FROM submissions WHERE user_id = ? AND status = 'completed'", [$user['id']])['count'];
    $pendingTests = db_fetch("SELECT COUNT(*) as count FROM submissions WHERE user_id = ? AND status = 'pending'", [$user['id']])['count'];

    // Get average score if available
    $avgScore = db_fetch("SELECT AVG((overall_band_task1 + overall_band_task2) / 2) as avg FROM evaluations e
                          JOIN submissions s ON e.submission_id = s.id
                          WHERE s.user_id = ? AND e.overall_band_task1 IS NOT NULL", [$user['id']]);
    $averageBand = $avgScore['avg'] ? round($avgScore['avg'], 1) : null;

    // Get recent submissions
    $recentSubmissions = db_fetch_all(
        "SELECT s.*, t.title as test_title, e.overall_band_task1, e.overall_band_task2
         FROM submissions s
         LEFT JOIN writing_tests t ON s.test_id = t.id
         LEFT JOIN evaluations e ON s.id = e.submission_id
         WHERE s.user_id = ?
         ORDER BY s.submitted_at DESC
         LIMIT 5",
        [$user['id']]
    );

    // Calculate days until exam
    $daysUntilExam = null;
    if ($user['exam_date']) {
        $examDate = new DateTime($user['exam_date']);
        $today = new DateTime();
        $interval = $today->diff($examDate);
        $daysUntilExam = $interval->days;
        if ($today > $examDate) $daysUntilExam = -$daysUntilExam;
    }
    ?>

    <div class="user-layout">
        <?php include __DIR__ . '/../includes/user-sidebar.php'; ?>

        <main class="user-main">
            <header class="page-header">
                <div>
                    <h1 class="page-title">Dashboard</h1>
                    <p class="page-subtitle">Track your progress and stay focused on your IELTS goals</p>
                </div>
            </header>

            <div class="page-content">
                <!-- Stats Grid -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                    <div class="widget-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;">
                        <div style="font-size: 0.875rem; font-weight: 600; text-transform: uppercase; opacity: 0.9;">Total Tests</div>
                        <div style="font-size: 2.5rem; font-weight: 700; margin-top: 0.5rem;"><?php echo $totalSubmissions; ?></div>
                    </div>

                    <div class="widget-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border: none;">
                        <div style="font-size: 0.875rem; font-weight: 600; text-transform: uppercase; opacity: 0.9;">Completed</div>
                        <div style="font-size: 2.5rem; font-weight: 700; margin-top: 0.5rem;"><?php echo $completedTests; ?></div>
                    </div>

                    <div class="widget-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; border: none;">
                        <div style="font-size: 0.875rem; font-weight: 600; text-transform: uppercase; opacity: 0.9;">Pending</div>
                        <div style="font-size: 2.5rem; font-weight: 700; margin-top: 0.5rem;"><?php echo $pendingTests; ?></div>
                    </div>

                    <div class="widget-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; border: none;">
                        <div style="font-size: 0.875rem; font-weight: 600; text-transform: uppercase; opacity: 0.9;">Avg Band</div>
                        <div style="font-size: 2.5rem; font-weight: 700; margin-top: 0.5rem;">
                            <?php echo $averageBand ? $averageBand : 'N/A'; ?>
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem;">
                    <!-- Exam Countdown -->
                    <div class="widget-card">
                        <h3 class="widget-title">🎯 Exam Countdown</h3>
                        <?php if ($user['exam_date']): ?>
                            <div style="text-align: center; padding: 1rem;">
                                <div style="font-size: 3rem; font-weight: 700; color: var(--primary);">
                                    <?php echo abs($daysUntilExam); ?>
                                </div>
                                <div style="font-size: 1.125rem; color: #6b7280; margin-top: 0.5rem;">
                                    days <?php echo $daysUntilExam >= 0 ? 'until' : 'since'; ?> exam
                                </div>
                                <div style="font-size: 0.875rem; color: #9ca3af; margin-top: 0.25rem;">
                                    <?php echo date('F j, Y', strtotime($user['exam_date'])); ?>
                                </div>
                            </div>
                            <?php if ($user['target_band']): ?>
                                <div style="background: #f3f4f6; padding: 1rem; border-radius: 0.375rem; margin-top: 1rem;">
                                    <div style="font-size: 0.875rem; color: #6b7280;">Target Band</div>
                                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary);">
                                        <?php echo $user['target_band']; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div style="text-align: center; padding: 2rem; color: #6b7280;">
                                <p>Set your exam date to start tracking!</p>
                                <a href="settings.php" class="btn btn-primary" style="margin-top: 1rem;">Go to Settings</a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Score Calculator -->
                    <div class="widget-card">
                        <h3 class="widget-title">🧮 Band Score Calculator</h3>
                        <div class="widget-content">
                            <div style="margin-bottom: 1rem;">
                                <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.25rem;">Task 1 Band</label>
                                <input type="number" id="task1Band" min="0" max="9" step="0.5" placeholder="0.0 - 9.0"
                                       style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                            </div>
                            <div style="margin-bottom: 1rem;">
                                <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.25rem;">Task 2 Band</label>
                                <input type="number" id="task2Band" min="0" max="9" step="0.5" placeholder="0.0 - 9.0"
                                       style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                            </div>
                            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 1.5rem; border-radius: 0.5rem; text-align: center; color: white;">
                                <div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.5rem;">Overall Writing Band</div>
                                <div style="font-size: 2.5rem; font-weight: 700;" id="overallBand">-</div>
                            </div>
                            <p style="font-size: 0.75rem; color: #6b7280; margin-top: 1rem; text-align: center;">
                                Writing = (Task 1 + Task 2 × 2) ÷ 3
                            </p>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="widget-card" style="grid-column: span 2;">
                        <h3 class="widget-title">📚 Recent Activity</h3>
                        <?php if (empty($recentSubmissions)): ?>
                            <div style="text-align: center; padding: 2rem; color: #6b7280;">
                                <p>No tests submitted yet.</p>
                                <a href="../tests/writing-tests.php" class="btn btn-primary" style="margin-top: 1rem;">Start Practicing</a>
                            </div>
                        <?php else: ?>
                            <div style="overflow-x: auto;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <thead>
                                        <tr style="border-bottom: 2px solid #e5e7eb; text-align: left;">
                                            <th style="padding: 0.75rem; font-size: 0.75rem; font-weight: 600; color: #6b7280; text-transform: uppercase;">Test</th>
                                            <th style="padding: 0.75rem; font-size: 0.75rem; font-weight: 600; color: #6b7280; text-transform: uppercase;">Date</th>
                                            <th style="padding: 0.75rem; font-size: 0.75rem; font-weight: 600; color: #6b7280; text-transform: uppercase;">Status</th>
                                            <th style="padding: 0.75rem; font-size: 0.75rem; font-weight: 600; color: #6b7280; text-transform: uppercase;">Score</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentSubmissions as $sub): ?>
                                            <tr style="border-bottom: 1px solid #e5e7eb;">
                                                <td style="padding: 0.75rem;">
                                                    <?php echo $sub['is_custom_test'] ? '<em>Custom Test</em>' : htmlspecialchars($sub['test_title']); ?>
                                                </td>
                                                <td style="padding: 0.75rem; font-size: 0.875rem; color: #6b7280;">
                                                    <?php echo date('M j, Y', strtotime($sub['submitted_at'])); ?>
                                                </td>
                                                <td style="padding: 0.75rem;">
                                                    <?php
                                                    $badges = [
                                                        'pending' => '<span style="background: #fef3c7; color: #92400e; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">Pending</span>',
                                                        'in_evaluation' => '<span style="background: #dbeafe; color: #1e40af; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">In Review</span>',
                                                        'completed' => '<span style="background: #d1fae5; color: #065f46; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">Completed</span>'
                                                    ];
                                                    echo $badges[$sub['status']] ?? $sub['status'];
                                                    ?>
                                                </td>
                                                <td style="padding: 0.75rem; font-weight: 600;">
                                                    <?php
                                                    if ($sub['status'] === 'completed' && $sub['overall_band_task1']) {
                                                        echo round(($sub['overall_band_task1'] + $sub['overall_band_task2']) / 2, 1);
                                                    } else {
                                                        echo '-';
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div style="text-align: center; margin-top: 1rem;">
                                <a href="test-history.php" class="btn btn-secondary">View All Tests</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Band Score Calculator
        const task1Input = document.getElementById('task1Band');
        const task2Input = document.getElementById('task2Band');
        const overallDisplay = document.getElementById('overallBand');

        function calculateOverall() {
            const task1 = parseFloat(task1Input.value) || 0;
            const task2 = parseFloat(task2Input.value) || 0;

            if (task1 === 0 && task2 === 0) {
                overallDisplay.textContent = '-';
                return;
            }

            // IELTS Writing band calculation: (Task 1 + Task 2 × 2) ÷ 3
            const overall = (task1 + (task2 * 2)) / 3;

            // Round to nearest 0.5
            const rounded = Math.round(overall * 2) / 2;

            overallDisplay.textContent = rounded.toFixed(1);
        }

        task1Input.addEventListener('input', calculateOverall);
        task2Input.addEventListener('input', calculateOverall);
    </script>
</body>
</html>

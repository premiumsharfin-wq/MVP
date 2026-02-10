<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Test - MyIELTS Admin</title>
    <link rel="stylesheet" href="../../assets/css/main.css?v=2.0">
</head>
<body>
    <?php
    require_once __DIR__ . '/../../config/config.php';
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../includes/session.php';
    require_once __DIR__ . '/../../includes/upload-handler.php';

    require_admin();

    $errors = [];
    $success = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = sanitize($_POST['title'] ?? '');
        $source = sanitize($_POST['source'] ?? '');
        $testType = sanitize($_POST['test_type'] ?? '');
        $task1Description = sanitize($_POST['task1_description'] ?? '');
        $task2Prompt = sanitize($_POST['task2_prompt'] ?? '');

        // Validation
        if (empty($title)) $errors[] = 'Test title is required';
        if (empty($source)) $errors[] = 'Source is required';
        if (empty($testType) || !in_array($testType, ['full', 'task1', 'task2'])) {
            $errors[] = 'Valid test type is required';
        }

        if (($testType === 'full' || $testType === 'task1') && empty($task1Description)) {
            $errors[] = 'Task 1 description is required';
        }

        if (($testType === 'full' || $testType === 'task2') && empty($task2Prompt)) {
            $errors[] = 'Task 2 prompt is required';
        }

        // Handle Task 1 image upload
        $task1ImageUrl = null;
        if (isset($_FILES['task1_image']) && $_FILES['task1_image']['size'] > 0) {
            $result = upload_test_image($_FILES['task1_image']);
            if ($result['success']) {
                $task1ImageUrl = $result['url'];
            } else {
                $errors = array_merge($errors, $result['errors']);
            }
        }

        if (empty($errors)) {
            // Prepare values with strict NULLs for optional fields
            $task1DescValue = ($testType === 'full' || $testType === 'task1') ? $task1Description : null;
            $task2PromptValue = ($testType === 'full' || $testType === 'task2') ? $task2Prompt : null;
            $task1ImageValue = !empty($task1ImageUrl) ? $task1ImageUrl : null;

            db_insert(
                "INSERT INTO writing_tests (title, source, test_type, task1_description, task1_image_url, task2_prompt, created_by, is_active, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, TRUE, NOW())",
                [
                    $title,
                    $source,
                    $testType,
                    $task1DescValue,
                    $task1ImageValue,
                    $task2PromptValue,
                    $_SESSION['user_id']
                ]
            );

            redirect(BASE_URL . 'admin/tests/manage.php?added=1');
        }
    }
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
        <div style="max-width: 800px; margin: 0 auto;">
            <div class="card" style="margin-bottom: var(--spacing-lg);">
                <h1 style="margin-bottom: var(--spacing-sm);">➕ Add New Writing Test</h1>
                <p style="color: var(--text-secondary);">Create a new IELTS writing test for students to practice</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data" class="card">
                <h3 style="margin-bottom: var(--spacing-md);">Basic Information</h3>

                <div class="form-group">
                    <label for="title">Test Title *</label>
                    <input type="text" id="title" name="title" placeholder="e.g., Cambridge IELTS 18 - Test 1" required>
                </div>

                <div class="form-group">
                    <label for="source">Source *</label>
                    <input type="text" id="source" name="source" placeholder="e.g., Cambridge 18, MyIELTS Practice Series" required>
                </div>

                <div class="form-group">
                    <label>Test Type *</label>
                    <div style="display: flex; flex-direction: column; gap: var(--spacing-xs);">
                        <div class="checkbox-group">
                            <input type="radio" id="type_full" name="test_type" value="full" required>
                            <label for="type_full" style="font-weight: normal;">Full Test (Task 1 + Task 2)</label>
                        </div>
                        <div class="checkbox-group">
                            <input type="radio" id="type_task1" name="test_type" value="task1" required>
                            <label for="type_task1" style="font-weight: normal;">Task 1 Only</label>
                        </div>
                        <div class="checkbox-group">
                            <input type="radio" id="type_task2" name="test_type" value="task2" required>
                            <label for="type_task2" style="font-weight: normal;">Task 2 Only</label>
                        </div>
                    </div>
                </div>

                <hr style="margin: var(--spacing-lg) 0;">

                <!-- Task 1 Section -->
                <div id="task1_section" style="display: none;">
                    <h3 style="color: var(--primary); margin-bottom: var(--spacing-md);">Task 1</h3>

                    <div class="form-group">
                        <label for="task1_image">Task 1 Question Image (Optional)</label>
                        <input type="file" id="task1_image" name="task1_image" accept="image/*">
                        <small>Upload a chart, graph, diagram, or process illustration (JPG, PNG - Max 5MB)</small>
                    </div>

                    <div class="form-group">
                        <label for="task1_description">Task 1 Description *</label>
                        <textarea id="task1_description" name="task1_description" rows="6"
                                  placeholder="Describe the task, e.g.: The chart below shows the percentage of households in owned and rented accommodation in England and Wales between 1918 and 2011.

Summarise the information by selecting and reporting the main features, and make comparisons where relevant."></textarea>
                        <small>Include the full task instructions and question prompt</small>
                    </div>
                </div>

                <!-- Task 2 Section -->
                <div id="task2_section" style="display: none;">
                    <h3 style="color: var(--secondary); margin-bottom: var(--spacing-md);">Task 2</h3>

                    <div class="form-group">
                        <label for="task2_prompt">Task 2 Essay Question *</label>
                        <textarea id="task2_prompt" name="task2_prompt" rows="8"
                                  placeholder="Enter the full Task 2 question, e.g.: Some people believe that unpaid community service should be a compulsory part of high school programmes.

To what extent do you agree or disagree?

Give reasons for your answer and include any relevant examples from your own knowledge or experience."></textarea>
                        <small>Include the full question and any additional instructions</small>
                    </div>
                </div>

                <div style="display: flex; gap: var(--spacing-sm); margin-top: var(--spacing-lg);">
                    <button type="submit" class="btn btn-primary btn-lg" style="flex: 1;">Create Test</button>
                    <a href="manage.php" class="btn btn-secondary btn-lg" style="flex: 1;">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <?php require_once __DIR__ . '/../../includes/footer.php'; ?>

    <script src="../../assets/js/main.js"></script>
    <script>
        // Show/hide task sections based on test type
        document.querySelectorAll('input[name="test_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const task1Section = document.getElementById('task1_section');
                const task2Section = document.getElementById('task2_section');

                if (this.value === 'full') {
                    task1Section.style.display = 'block';
                    task2Section.style.display = 'block';
                } else if (this.value === 'task1') {
                    task1Section.style.display = 'block';
                    task2Section.style.display = 'none';
                } else if (this.value === 'task2') {
                    task1Section.style.display = 'none';
                    task2Section.style.display = 'block';
                }
            });
        });
    </script>
</body>
</html>

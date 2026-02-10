<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custom Evaluation - MyIELTS</title>
    <link rel="stylesheet" href="../assets/css/main.css?v=2.3">
</head>
<body>
    <?php
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../includes/session.php';

    require_login();
    $user = get_authenticated_user();

    $success = '';
    $error = '';

    // Get test type from URL
    $type = isset($_GET['type']) ? sanitize($_GET['type']) : '';
    if (!in_array($type, ['full', 'task1', 'task2'])) {
        $type = '';
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_custom'])) {
        $task1Question = isset($_POST['task1_question']) ? sanitize($_POST['task1_question']) : null;
        $task2Question = isset($_POST['task2_question']) ? sanitize($_POST['task2_question']) : null;
        $task1Answer = isset($_POST['task1_answer']) ? sanitize($_POST['task1_answer']) : null;
        $task2Answer = isset($_POST['task2_answer']) ? sanitize($_POST['task2_answer']) : null;

        $task1ImagePath = null;

        // Handle image upload if provided
        if (isset($_FILES['task1_image']) && $_FILES['task1_image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['task1_image'];

            // Validate file size (10 MB max)
            if ($file['size'] > 10 * 1024 * 1024) {
                $error = 'Image file is too large. Maximum size is 10 MB.';
            } else {
                // Validate file type
                $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                if (in_array($mimeType, $allowedTypes)) {
                    // Create upload directory
                    $uploadDir = __DIR__ . '/../uploads/custom-tests/';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    // Generate unique filename
                    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = 'task1_' . $user['id'] . '_' . time() . '.' . $extension;
                    $filepath = $uploadDir . $filename;

                    if (move_uploaded_file($file['tmp_name'], $filepath)) {
                        $task1ImagePath = BASE_URL . 'uploads/custom-tests/' . $filename;
                    } else {
                        $error = 'Failed to upload image.';
                    }
                } else {
                    $error = 'Invalid image type. Only JPG and PNG are allowed.';
                }
            }
        }

        // Validate required fields based on type
        if (!$error) {
            if (($type === 'full' || $type === 'task1') && empty($task1Question)) {
                $error = 'Task 1 question is required.';
            } elseif (($type === 'full' || $type === 'task1') && empty($task1Answer)) {
                $error = 'Task 1 answer is required.';
            } elseif (($type === 'full' || $type === 'task2') && empty($task2Question)) {
                $error = 'Task 2 question is required.';
            } elseif (($type === 'full' || $type === 'task2') && empty($task2Answer)) {
                $error = 'Task 2 answer is required.';
            }
        }

        // Insert submission if no errors
        if (!$error) {
            $submissionId = db_insert(
                "INSERT INTO submissions (user_id, is_custom_test, custom_task1_question, custom_task1_image,
                                         custom_task2_question, task1_answer, task2_answer, status, submitted_at)
                 VALUES (?, TRUE, ?, ?, ?, ?, ?, 'pending', NOW())",
                [$user['id'], $task1Question, $task1ImagePath, $task2Question, $task1Answer, $task2Answer]
            );

            if ($submissionId) {
                $success = 'Custom test submitted successfully! Our examiners will evaluate it soon.';
                // Redirect to history or show success
                // Clear form
                $_POST = [];
            } else {
                $error = 'Failed to submit test. Please try again.';
            }
        }
    }
    ?>

    <div class="user-layout">
        <?php include __DIR__ . '/../includes/user-sidebar.php'; ?>

        <main class="user-main">
            <header class="page-header">
                <h1 class="page-title">Custom Evaluation</h1>
                <p class="page-subtitle">Submit your own writing for professional evaluation</p>
            </header>

            <div class="page-content">
                <?php if ($success): ?>
                    <div class="alert alert-success" style="margin-bottom: 1.5rem;">
                        ✅ <?php echo $success; ?>
                        <div style="margin-top: 1rem;">
                            <a href="test-history.php" class="btn btn-primary btn-sm">View History</a>
                            <a href="custom-evaluation.php" class="btn btn-secondary btn-sm">Submit Another</a>
                        </div>
                    </div>
                <?php elseif (!$type): ?>
                    <!-- Step 1: Select Type -->
                    <div class="section-title">
                        <h2>Select Evaluation Type</h2>
                        <p>Choose what you would like to submit for evaluation</p>
                    </div>

                    <div class="grid grid-3" style="gap: 1.5rem;">
                        <!-- Full Test -->
                        <a href="?type=full" class="widget-card" style="text-decoration: none; color: inherit; transition: transform 0.2s; display: block;"
                           onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform=''">
                            <div style="font-size: 2.5rem; margin-bottom: 1rem;">📋</div>
                            <h3 style="color: var(--primary); margin-bottom: 0.5rem;">Full Writing Test</h3>
                            <p style="color: #6b7280; font-size: 0.875rem;">Submit both Task 1 and Task 2 for a complete evaluation and overall band score.</p>
                            <div style="margin-top: 1rem; color: var(--primary); font-weight: 600;">Select →</div>
                        </a>

                        <!-- Task 1 Only -->
                        <a href="?type=task1" class="widget-card" style="text-decoration: none; color: inherit; transition: transform 0.2s; display: block;"
                           onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform=''">
                            <div style="font-size: 2.5rem; margin-bottom: 1rem;">📊</div>
                            <h3 style="color: var(--secondary); margin-bottom: 0.5rem;">Task 1 Only</h3>
                            <p style="color: #6b7280; font-size: 0.875rem;">Submit only Academic Task 1 (Report) or General Task 1 (Letter).</p>
                            <div style="margin-top: 1rem; color: var(--secondary); font-weight: 600;">Select →</div>
                        </a>

                        <!-- Task 2 Only -->
                        <a href="?type=task2" class="widget-card" style="text-decoration: none; color: inherit; transition: transform 0.2s; display: block;"
                           onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform=''">
                            <div style="font-size: 2.5rem; margin-bottom: 1rem;">✍️</div>
                            <h3 style="color: #10b981; margin-bottom: 0.5rem;">Task 2 Only</h3>
                            <p style="color: #6b7280; font-size: 0.875rem;">Submit only Task 2 (Essay) for detailed feedback and score.</p>
                            <div style="margin-top: 1rem; color: #10b981; font-weight: 600;">Select →</div>
                        </a>
                    </div>

                <?php else: ?>
                    <!-- Step 2: Form -->
                    <div style="margin-bottom: 1.5rem;">
                        <a href="custom-evaluation.php" style="color: #6b7280; text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
                            ← Back to Selection
                        </a>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-error" style="margin-bottom: 1.5rem;"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <!-- Info Card -->
                    <div class="widget-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; margin-bottom: 2rem; border: none;">
                        <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.75rem;">
                            Submit <?php echo $type === 'full' ? 'Full Test' : ($type === 'task1' ? 'Task 1' : 'Task 2'); ?>
                        </h3>
                        <ul style="margin: 0; padding-left: 1.5rem;">
                            <li style="margin-bottom: 0.5rem;">Provide your own writing questions and answers</li>
                            <?php if ($type !== 'task2'): ?>
                                <li style="margin-bottom: 0.5rem;">For Task 1, you can attach an image (chart, graph, diagram)</li>
                            <?php endif; ?>
                            <li>You'll receive detailed feedback from our examiners within 24-48 hours</li>
                        </ul>
                    </div>

                    <form method="POST" action="" enctype="multipart/form-data">
                        <!-- Task 1 -->
                        <?php if ($type === 'full' || $type === 'task1'): ?>
                            <div class="widget-card" style="margin-bottom: 2rem;">
                                <h3 class="widget-title">📊 Task 1 (Academic/General)</h3>

                                <div style="margin-bottom: 1.5rem;">
                                    <label for="task1_question" style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">
                                        Task 1 Question/Prompt <span style="color: #ef4444;">*</span>
                                    </label>
                                    <textarea id="task1_question"
                                              name="task1_question"
                                              rows="4"
                                              required
                                              placeholder="Enter the Task 1 question or describe the chart/graph/diagram..."
                                              style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-family: inherit;"><?php echo isset($_POST['task1_question']) ? htmlspecialchars($_POST['task1_question']) : ''; ?></textarea>
                                </div>

                                <div style="margin-bottom: 1.5rem;">
                                    <label for="task1_image" style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">
                                        Attach Image (Optional)
                                    </label>
                                    <input type="file"
                                           id="task1_image"
                                           name="task1_image"
                                           accept="image/jpeg,image/png,image/jpg"
                                           style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                                    <p style="font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem;">
                                        JPG or PNG. Maximum 10 MB total.
                                    </p>
                                    <div id="imagePreview" style="margin-top: 1rem; display: none;">
                                        <img id="previewImg" src="" alt="Preview" style="max-width: 100%; max-height: 400px; border: 2px solid #e5e7eb; border-radius: 0.375rem;">
                                    </div>
                                </div>

                                <div>
                                    <label for="task1_answer" style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">
                                        Your Answer <span style="color: #ef4444;">*</span>
                                    </label>
                                    <textarea id="task1_answer"
                                              name="task1_answer"
                                              rows="10"
                                              required
                                              placeholder="Paste or type your Task 1 answer here..."
                                              style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-family: inherit;"><?php echo isset($_POST['task1_answer']) ? htmlspecialchars($_POST['task1_answer']) : ''; ?></textarea>
                                    <div style="font-size: 0.875rem; color: #6b7280; margin-top: 0.25rem;">
                                        Word count: <span id="task1WordCount" style="font-weight: 600;">0</span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Task 2 -->
                        <?php if ($type === 'full' || $type === 'task2'): ?>
                            <div class="widget-card" style="margin-bottom: 2rem;">
                                <h3 class="widget-title">✍️ Task 2 (Essay)</h3>

                                <div style="margin-bottom: 1.5rem;">
                                    <label for="task2_question" style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">
                                        Task 2 Question/Prompt <span style="color: #ef4444;">*</span>
                                    </label>
                                    <textarea id="task2_question"
                                              name="task2_question"
                                              rows="4"
                                              required
                                              placeholder="Enter the Task 2 essay question..."
                                              style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-family: inherit;"><?php echo isset($_POST['task2_question']) ? htmlspecialchars($_POST['task2_question']) : ''; ?></textarea>
                                </div>

                                <div>
                                    <label for="task2_answer" style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">
                                        Your Answer <span style="color: #ef4444;">*</span>
                                    </label>
                                    <textarea id="task2_answer"
                                              name="task2_answer"
                                              rows="12"
                                              required
                                              placeholder="Paste or type your Task 2 essay here..."
                                              style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-family: inherit;"><?php echo isset($_POST['task2_answer']) ? htmlspecialchars($_POST['task2_answer']) : ''; ?></textarea>
                                    <div style="font-size: 0.875rem; color: #6b7280; margin-top: 0.25rem;">
                                        Word count: <span id="task2WordCount" style="font-weight: 600;">0</span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div style="display: flex; gap: 1rem;">
                            <button type="submit" name="submit_custom" class="btn btn-primary btn-lg">
                                Submit for Evaluation
                            </button>
                            <a href="custom-evaluation.php" class="btn btn-secondary btn-lg">Cancel</a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        // Word counters
        function countWords(text) {
            return text.trim().split(/\s+/).filter(word => word.length > 0).length;
        }

        const task1Answer = document.getElementById('task1_answer');
        const task2Answer = document.getElementById('task2_answer');

        if (task1Answer) {
            task1Answer.addEventListener('input', function() {
                document.getElementById('task1WordCount').textContent = countWords(this.value);
            });
            document.getElementById('task1WordCount').textContent = countWords(task1Answer.value);
        }

        if (task2Answer) {
            task2Answer.addEventListener('input', function() {
                document.getElementById('task2WordCount').textContent = countWords(this.value);
            });
            document.getElementById('task2WordCount').textContent = countWords(task2Answer.value);
        }

        // Image preview
        const task1Image = document.getElementById('task1_image');
        if (task1Image) {
            task1Image.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Check file size
                    if (file.size > 10 * 1024 * 1024) {
                        alert('File is too large. Maximum size is 10 MB.');
                        this.value = '';
                        document.getElementById('imagePreview').style.display = 'none';
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('previewImg').src = e.target.result;
                        document.getElementById('imagePreview').style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                } else {
                    document.getElementById('imagePreview').style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>

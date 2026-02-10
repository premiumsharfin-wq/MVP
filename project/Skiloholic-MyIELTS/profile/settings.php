<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - MyIELTS</title>
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

    // Handle target/exam date update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_goals'])) {
        $targetBand = floatval($_POST['target_band']);
        $examDate = sanitize($_POST['exam_date']);

        if ($targetBand >= 0 && $targetBand <= 9) {
            db_execute("UPDATE users SET target_band = ?, exam_date = ? WHERE id = ?",
                      [$targetBand, $examDate ?: null, $user['id']]);
            $success = 'Goals updated successfully!';
            $user = get_authenticated_user(); // Refresh user data
        }
    }

    // Handle password change
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        if (password_verify($currentPassword, $user['password'])) {
            if ($newPassword === $confirmPassword) {
                if (strlen($newPassword) >= 6) {
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    db_execute("UPDATE users SET password = ? WHERE id = ?", [$hashedPassword, $user['id']]);
                    $success = 'Password changed successfully!';
                } else {
                    $error = 'New password must be at least 6 characters.';
                }
            } else {
                $error = 'New passwords do not match.';
            }
        } else {
            $error = 'Current password is incorrect.';
        }
    }
    ?>

    <div class="user-layout">
        <?php include __DIR__ . '/../includes/user-sidebar.php'; ?>

        <main class="user-main">
            <header class="page-header">
                <h1 class="page-title">Settings</h1>
                <p class="page-subtitle">Manage your account preferences</p>
            </header>

            <div class="page-content">
                <?php if ($success): ?>
                    <div class="alert alert-success" style="margin-bottom: 1.5rem;"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-error" style="margin-bottom: 1.5rem;"><?php echo $error; ?></div>
                <?php endif; ?>

                <div style="display: grid; gap: 1.5rem; max-width: 800px;">
                    <!-- Profile Picture -->
                    <div class="widget-card">
                        <h3 class="widget-title">Profile Picture</h3>
                        <div style="display: flex; align-items: center; gap: 2rem;">
                            <div>
                                <?php if ($user['avatar_url']): ?>
                                    <img src="<?php echo htmlspecialchars($user['avatar_url']); ?>"
                                         alt="Profile"
                                         style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid #e5e7eb;"
                                         id="avatarPreview">
                                <?php else: ?>
                                    <?php
                                    $nameParts = explode(' ', $user['full_name']);
                                    $initials = strtoupper(substr($nameParts[0], 0, 1));
                                    if (count($nameParts) > 1) $initials .= strtoupper(substr($nameParts[count($nameParts) - 1], 0, 1));
                                    ?>
                                    <div style="width: 100px; height: 100px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--secondary)); display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 700; color: white;" id="avatarPreview">
                                        <?php echo $initials; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div style="flex: 1;">
                                <form id="avatarForm" enctype="multipart/form-data">
                                    <input type="file"
                                           id="avatarInput"
                                           name="avatar"
                                           accept="image/jpeg,image/png,image/jpg"
                                           style="margin-bottom: 0.5rem;">
                                    <p style="font-size: 0.75rem; color: #6b7280;">JPG or PNG. Max file size 2 MB.</p>
                                    <button type="submit" class="btn btn-primary btn-sm" style="margin-top: 0.5rem;">Upload Picture</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Account Information -->
                    <div class="widget-card">
                        <h3 class="widget-title">Account Information</h3>
                        <div style="display: grid; gap: 1rem;">
                            <div>
                                <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #6b7280; margin-bottom: 0.25rem;">Full Name</label>
                                <div style="padding: 0.75rem; background: #f9fafb; border-radius: 0.375rem; color: #374151;">
                                    <?php echo htmlspecialchars($user['full_name']); ?>
                                </div>
                            </div>
                            <div>
                                <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #6b7280; margin-bottom: 0.25rem;">Email</label>
                                <div style="padding: 0.75rem; background: #f9fafb; border-radius: 0.375rem; color: #374151;">
                                    <?php echo htmlspecialchars($user['email']); ?>
                                </div>
                            </div>
                            <div>
                                <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #6b7280; margin-bottom: 0.25rem;">Role</label>
                                <div style="padding: 0.75rem; background: #f9fafb; border-radius: 0.375rem; color: #374151; text-transform: capitalize;">
                                    <?php echo htmlspecialchars($user['role']); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Goals & Targets -->
                    <div class="widget-card">
                        <h3 class="widget-title">Goals & Targets</h3>
                        <form method="POST" action="">
                            <div style="display: grid; gap: 1rem; margin-bottom: 1rem;">
                                <div>
                                    <label for="target_band" style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.25rem;">
                                        Target Band Score
                                    </label>
                                    <input type="number"
                                           id="target_band"
                                           name="target_band"
                                           min="0"
                                           max="9"
                                           step="0.5"
                                           value="<?php echo $user['target_band'] ?? ''; ?>"
                                           placeholder="e.g., 7.5"
                                           style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                                </div>
                                <div>
                                    <label for="exam_date" style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.25rem;">
                                        Exam Date
                                    </label>
                                    <input type="date"
                                           id="exam_date"
                                           name="exam_date"
                                           value="<?php echo $user['exam_date'] ?? ''; ?>"
                                           style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                                </div>
                            </div>
                            <button type="submit" name="update_goals" class="btn btn-primary">Save Goals</button>
                        </form>
                    </div>

                    <!-- Change Password -->
                    <div class="widget-card">
                        <h3 class="widget-title">Change Password</h3>
                        <form method="POST" action="">
                            <div style="display: grid; gap: 1rem; margin-bottom: 1rem;">
                                <div>
                                    <label for="current_password" style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.25rem;">
                                        Current Password
                                    </label>
                                    <input type="password"
                                           id="current_password"
                                           name="current_password"
                                           required
                                           style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                                </div>
                                <div>
                                    <label for="new_password" style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.25rem;">
                                        New Password
                                    </label>
                                    <input type="password"
                                           id="new_password"
                                           name="new_password"
                                           required
                                           minlength="6"
                                           style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                                </div>
                                <div>
                                    <label for="confirm_password" style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.25rem;">
                                        Confirm New Password
                                    </label>
                                    <input type="password"
                                           id="confirm_password"
                                           name="confirm_password"
                                           required
                                           minlength="6"
                                           style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                                </div>
                            </div>
                            <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Avatar upload
        document.getElementById('avatarForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData();
            const fileInput = document.getElementById('avatarInput');
            const file = fileInput.files[0];

            if (!file) {
                alert('Please select an image file.');
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                alert('File size must be less than 2 MB.');
                return;
            }

            formData.append('avatar', file);

            try {
                const response = await fetch('upload-avatar.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    location.reload();
                } else {
                    alert(result.error || 'Upload failed');
                }
            } catch (error) {
                alert('Upload failed. Please try again.');
            }
        });

        // Preview avatar before upload
        document.getElementById('avatarInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatarPreview').outerHTML =
                        '<img src="' + e.target.result + '" alt="Preview" id="avatarPreview" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid #e5e7eb;">';
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>

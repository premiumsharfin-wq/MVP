<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - MyIELTS</title>
    <link rel="stylesheet" href="../assets/css/main.css?v=2.0">
</head>
<body class="auth-page">
    <?php
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../includes/session.php';

    $errors = [];
    $success = '';
    $step = 1; // 1: Enter code, 2: Set new password

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['verify_code'])) {
            // Step 1: Verify code
            $code = sanitize($_POST['code'] ?? '');

            if (empty($code)) {
                $errors[] = 'Please enter the verification code';
            } else {
                $verification = db_fetch(
                    "SELECT * FROM email_verification_codes
                     WHERE code = ? AND type = 'password_reset' AND used_at IS NULL
                     ORDER BY created_at DESC LIMIT 1",
                    [$code]
                );

                if (!$verification) {
                    $errors[] = 'Invalid verification code';
                } elseif (strtotime($verification['expires_at']) < time()) {
                    $errors[] = 'Verification code has expired. Please request a new one.';
                } else {
                    $_SESSION['reset_code_id'] = $verification['id'];
                    $_SESSION['reset_user_id'] = $verification['user_id'];
                    $step = 2;
                }
            }
        } elseif (isset($_POST['reset_password'])) {
            // Step 2: Reset password
            if (!isset($_SESSION['reset_code_id']) || !isset($_SESSION['reset_user_id'])) {
                redirect(BASE_URL . 'auth/forgot-password.php');
            }

            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (empty($newPassword)) {
                $errors[] = 'New password is required';
            } elseif (strlen($newPassword) < 8) {
                $errors[] = 'Password must be at least 8 characters long';
            }

            if ($newPassword !== $confirmPassword) {
                $errors[] = 'Passwords do not match';
            }

            if (empty($errors)) {
                $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

                // Update password
                db_execute(
                    "UPDATE users SET password_hash = ? WHERE id = ?",
                    [$passwordHash, $_SESSION['reset_user_id']]
                );

                // Mark code as used
                db_execute(
                    "UPDATE email_verification_codes SET used_at = NOW() WHERE id = ?",
                    [$_SESSION['reset_code_id']]
                );

                // Clear reset session data
                unset($_SESSION['reset_code_id']);
                unset($_SESSION['reset_user_id']);

                $success = 'Your password has been reset successfully. You can now login with your new password.';
            } else {
                $step = 2;
            }
        }
    }

    // Determine current step
    if (isset($_SESSION['reset_code_id']) && !isset($_POST['verify_code'])) {
        $step = 2;
    }
    ?>

    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <img src="<?php echo LOGO_URL . 'No%20Background%20Skiloholic.png'; ?>" alt="MyIELTS Logo" class="auth-logo">
                <h1>Reset Password</h1>
                <?php if ($step === 1): ?>
                    <p>Enter the verification code sent to your email</p>
                <?php else: ?>
                    <p>Enter your new password</p>
                <?php endif; ?>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <p><?php echo htmlspecialchars($success); ?></p>
                    <p><a href="login.php">Go to Login →</a></p>
                </div>
            <?php elseif ($step === 1): ?>
                <form method="POST" action="" class="auth-form">
                    <div class="form-group">
                        <label for="code">Verification Code</label>
                        <input type="text" id="code" name="code"
                               maxlength="6"
                               pattern="\d{6}"
                               placeholder="Enter 6-digit code"
                               required autofocus>
                        <small>Code expires in 15 minutes</small>
                    </div>

                    <button type="submit" name="verify_code" class="btn btn-primary btn-block">Verify Code</button>
                </form>
            <?php elseif ($step === 2): ?>
                <form method="POST" action="" class="auth-form">
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" required autofocus>
                        <small>Minimum 8 characters</small>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>

                    <button type="submit" name="reset_password" class="btn btn-primary btn-block">Reset Password</button>
                </form>
            <?php endif; ?>

            <?php if (!$success): ?>
                <div class="auth-footer">
                    <p>Remember your password? <a href="login.php">Login here</a></p>
                </div>
            <?php endif; ?>
        </div>

        <div class="auth-info">
            <p class="powered-by">Powered by Skiloholic</p>
            <p class="nonprofit">Non-Profit Organization</p>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>

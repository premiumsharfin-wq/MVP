<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - MyIELTS</title>
    <link rel="stylesheet" href="../assets/css/main.css?v=2.0">
</head>
<body class="auth-page">
    <?php
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../includes/session.php';
    require_once __DIR__ . '/../includes/email-functions.php';

    // Require user to be logged in
    require_login();

    // Redirect if already verified
    if ($_SESSION['email_verified']) {
        redirect(BASE_URL . 'profile/dashboard.php');
    }

    $errors = [];
    $success = '';
    $resendMessage = '';

    // Handle code verification
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_code'])) {
        $code = sanitize($_POST['code'] ?? '');

        if (empty($code)) {
            $errors[] = 'Please enter the verification code';
        } else {
            $verification = db_fetch(
                "SELECT * FROM email_verification_codes
                 WHERE user_id = ? AND code = ? AND type = 'registration' AND used_at IS NULL
                 ORDER BY created_at DESC LIMIT 1",
                [$_SESSION['user_id'], $code]
            );

            if (!$verification) {
                $errors[] = 'Invalid verification code';
            } elseif (strtotime($verification['expires_at']) < time()) {
                $errors[] = 'Verification code has expired. Please request a new one.';
            } else {
                // Mark code as used
                db_execute(
                    "UPDATE email_verification_codes SET used_at = NOW() WHERE id = ?",
                    [$verification['id']]
                );

                // Update user email_verified status
                db_execute(
                    "UPDATE users SET email_verified = TRUE WHERE id = ?",
                    [$_SESSION['user_id']]
                );

                // Send welcome email
                send_welcome_email($_SESSION['user_id']);

                $_SESSION['email_verified'] = true;
                redirect(BASE_URL . 'profile/dashboard.php');
            }
        }
    }

    // Handle resend code
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend_code'])) {
        $user = get_current_user();

        // Check if there's a recent code (within last 2 minutes)
        $recentCode = db_fetch(
            "SELECT * FROM email_verification_codes
             WHERE user_id = ? AND type = 'registration' AND created_at > DATE_SUB(NOW(), INTERVAL 2 MINUTE)
             ORDER BY created_at DESC LIMIT 1",
            [$_SESSION['user_id']]
        );

        if ($recentCode) {
            $resendMessage = 'Please wait a moment before requesting a new code';
        } else {
            // Generate new verification code
            $code = generate_verification_code();
            $expiresAt = date('Y-m-d H:i:s', strtotime('+' . VERIFICATION_CODE_EXPIRY . ' minutes'));

            db_insert(
                "INSERT INTO email_verification_codes (user_id, code, type, expires_at, created_at)
                 VALUES (?, ?, 'registration', ?, NOW())",
                [$_SESSION['user_id'], $code, $expiresAt]
            );

            if (send_verification_email($user['email'], $user['full_name'], $code, $expiresAt)) {
                $resendMessage = 'A new verification code has been sent to your email';
            } else {
                $errors[] = 'Failed to send verification email. Please try again.';
            }
        }
    }
    ?>

    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <img src="<?php echo LOGO_URL . 'No%20Background%20Skiloholic.png'; ?>" alt="MyIELTS Logo" class="auth-logo">
                <h1>Verify Your Email</h1>
                <p>We've sent a 6-digit verification code to<br><strong><?php echo htmlspecialchars($_SESSION['email']); ?></strong></p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($resendMessage): ?>
                <div class="alert alert-info">
                    <p><?php echo htmlspecialchars($resendMessage); ?></p>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="auth-form">
                <div class="form-group">
                    <label for="code">Verification Code</label>
                    <input type="text" id="code" name="code"
                           maxlength="6"
                           pattern="\d{6}"
                           placeholder="Enter 6-digit code"
                           required
                           autofocus>
                    <small>Code expires in 15 minutes</small>
                </div>

                <button type="submit" name="verify_code" class="btn btn-primary btn-block">Verify Email</button>
            </form>

            <form method="POST" action="" style="margin-top: 20px;">
                <button type="submit" name="resend_code" class="btn btn-secondary btn-block">Resend Code</button>
            </form>

            <div class="auth-footer">
                <p><a href="logout.php">Logout</a></p>
            </div>
        </div>

        <div class="auth-info">
            <p class="powered-by">Powered by Skiloholic</p>
            <p class="nonprofit">Non-Profit Organization</p>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - MyIELTS</title>
    <link rel="stylesheet" href="../assets/css/main.css?v=2.0">
</head>
<body class="auth-page">
    <?php
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../includes/session.php';
    require_once __DIR__ . '/../includes/email-functions.php';

    $errors = [];
    $success = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = sanitize($_POST['email'] ?? '');

        if (empty($email)) {
            $errors[] = 'Email address is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address';
        } else {
            $user = db_fetch("SELECT * FROM users WHERE email = ?", [$email]);

            if ($user) {
                // Generate password reset code
                $code = generate_verification_code();
                $expiresAt = date('Y-m-d H:i:s', strtotime('+' . VERIFICATION_CODE_EXPIRY . ' minutes'));

                db_insert(
                    "INSERT INTO email_verification_codes (user_id, code, type, expires_at, created_at)
                     VALUES (?, ?, 'password_reset', ?, NOW())",
                    [$user['id'], $code, $expiresAt]
                );

                send_password_reset_email($email, $user['full_name'], $code, $expiresAt);
            }

            // Always show success message (security best practice)
            $success = 'If an account exists with this email, a password reset code has been sent.';
        }
    }
    ?>

    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <img src="<?php echo LOGO_URL . 'No%20Background%20Skiloholic.png'; ?>" alt="MyIELTS Logo" class="auth-logo">
                <h1>Forgot Password?</h1>
                <p>Enter your email address and we'll send you a verification code to reset your password</p>
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
                    <p><a href="reset-password.php">Enter verification code →</a></p>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="auth-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           required autofocus>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Send Reset Code</button>
            </form>

            <div class="auth-footer">
                <p>Remember your password? <a href="login.php">Login here</a></p>
                <p>Don't have an account? <a href="register.php">Register here</a></p>
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

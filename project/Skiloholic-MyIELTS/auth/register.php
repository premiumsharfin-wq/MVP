<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/email-functions.php';

// Redirect if already logged in
if (is_logged_in()) {
    redirect(BASE_URL . 'profile/dashboard.php');
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = sanitize($_POST['full_name'] ?? '');
    $whatsappNumber = sanitize($_POST['whatsapp_number'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($fullName)) {
        $errors[] = 'Full name is required';
    }

    if (empty($whatsappNumber)) {
        $errors[] = 'WhatsApp number is required';
    } elseif (!preg_match('/^\+?\d{10,15}$/', $whatsappNumber)) {
        $errors[] = 'Please enter a valid WhatsApp number';
    }

    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address';
    }

    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match';
    }

    // Check if email already exists
    if (empty($errors)) {
        $existingUser = db_fetch("SELECT id FROM users WHERE email = ?", [$email]);
        if ($existingUser) {
            $errors[] = 'An account with this email already exists';
        }
    }

    // Create user if no errors
    if (empty($errors)) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $userId = db_insert(
                "INSERT INTO users (full_name, email, whatsapp_number, password_hash, role, email_verified, created_at)
                 VALUES (?, ?, ?, ?, 'user', FALSE, NOW())",
                [$fullName, $email, $whatsappNumber, $passwordHash]
            );

            // Generate verification code
            $code = generate_verification_code();
            $expiresAt = date('Y-m-d H:i:s', strtotime('+' . VERIFICATION_CODE_EXPIRY . ' minutes'));

            db_insert(
                "INSERT INTO email_verification_codes (user_id, code, type, expires_at, created_at)
                 VALUES (?, ?, 'registration', ?, NOW())",
                [$userId, $code, $expiresAt]
            );

            // Send verification email
            if (send_verification_email($email, $fullName, $code, $expiresAt)) {
                // Auto-login the user
                $user = db_fetch("SELECT * FROM users WHERE id = ?", [$userId]);
                login_user($user, false);

                redirect(BASE_URL . 'auth/verify-email.php');
            } else {
                $errors[] = 'Account created but failed to send verification email. Please contact support.';
            }
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            $errors[] = 'An error occurred during registration. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - MyIELTS</title>
    <link rel="stylesheet" href="../assets/css/main.css?v=2.0">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <img src="<?php echo LOGO_URL . 'No%20Background%20Skiloholic.png'; ?>" alt="MyIELTS Logo" class="auth-logo">
                <h1>Create Your Account</h1>
                <p>Join MyIELTS and start your IELTS preparation journey</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="auth-form" id="registerForm">
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name"
                           value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>"
                           required>
                </div>

                <div class="form-group">
                    <label for="whatsapp_number">WhatsApp Number *</label>
                    <input type="tel" id="whatsapp_number" name="whatsapp_number"
                           placeholder="+8801XXXXXXXXX"
                           value="<?php echo htmlspecialchars($_POST['whatsapp_number'] ?? ''); ?>"
                           required>
                    <small>Include country code (e.g., +8801533869234)</small>
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           required>
                </div>

                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required>
                    <small>Minimum 8 characters</small>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Create Account</button>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Login here</a></p>
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

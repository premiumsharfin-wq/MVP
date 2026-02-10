<?php
/**
 * Email Template Functions
 * MyIELTS - All email content templates
 */

require_once __DIR__ . '/../config/email.php';
require_once __DIR__ . '/../config/config.php';

/**
 * Send registration verification email
 */
function send_verification_email($email, $name, $code, $expiresAt) {
    $expiryTime = date('h:i A', strtotime($expiresAt));

    $content = <<<HTML
    <h2 style="color: #333;">Welcome to MyIELTS!</h2>
    <p>Dear <strong>{$name}</strong>,</p>
    <p>Thank you for registering with MyIELTS, your comprehensive IELTS preparation platform.</p>
    <p>To complete your registration and verify your email address, please use the verification code below:</p>

    <div class="code-box">
        <p style="margin: 0; font-size: 14px; color: #666;">Your Verification Code</p>
        <div class="verification-code">{$code}</div>
        <p style="margin: 10px 0 0 0; font-size: 12px; color: #999;">
            This code will expire at {$expiryTime}
        </p>
    </div>

    <div class="info-box">
        <p style="margin: 0; font-size: 14px;">
            <strong>⏰ Important:</strong> This verification code is valid for 15 minutes only.
        </p>
    </div>

    <p>If you did not create an account with MyIELTS, please ignore this email or contact our support team.</p>

    <p style="margin-top: 30px;">
        Best regards,<br>
        <strong>The MyIELTS Team</strong><br>
        Powered by Skiloholic
    </p>
HTML;

    $html = get_email_template($content, 'Verify Your MyIELTS Account');
    $subject = 'Verify Your MyIELTS Account - Action Required';

    return send_email($email, $name, $subject, $html);
}

/**
 * Send password reset email
 */
function send_password_reset_email($email, $name, $code, $expiresAt) {
    $expiryTime = date('h:i A', strtotime($expiresAt));

    $content = <<<HTML
    <h2 style="color: #333;">Password Reset Request</h2>
    <p>Dear <strong>{$name}</strong>,</p>
    <p>We received a request to reset the password for your MyIELTS account.</p>
    <p>To proceed with resetting your password, please use the verification code below:</p>

    <div class="code-box">
        <p style="margin: 0; font-size: 14px; color: #666;">Your Password Reset Code</p>
        <div class="verification-code">{$code}</div>
        <p style="margin: 10px 0 0 0; font-size: 12px; color: #999;">
            This code will expire at {$expiryTime}
        </p>
    </div>

    <div class="info-box">
        <p style="margin: 0; font-size: 14px;">
            <strong>🔒 Security Notice:</strong> This code is valid for 15 minutes only. Never share this code with anyone.
        </p>
    </div>

    <p>If you did not request a password reset, please ignore this email. Your password will remain unchanged.</p>

    <p style="margin-top: 30px;">
        Best regards,<br>
        <strong>The MyIELTS Team</strong><br>
        Powered by Skiloholic
    </p>
HTML;

    $html = get_email_template($content, 'Reset Your MyIELTS Password');
    $subject = 'MyIELTS Password Reset Request';

    return send_email($email, $name, $subject, $html);
}

/**
 * Send result ready notification
 */
function send_result_ready_email($userId, $submissionId, $examinerName) {
    require_once __DIR__ . '/../config/database.php';

    $user = db_fetch("SELECT full_name, email FROM users WHERE id = ?", [$userId]);
    if (!$user) return false;

    $resultUrl = BASE_URL . "tests/view-result.php?id=" . $submissionId;

    $content = <<<HTML
    <h2 style="color: #333;">Your Writing Test Results Are Ready! 🎉</h2>
    <p>Dear <strong>{$user['full_name']}</strong>,</p>
    <p>Great news! Your IELTS writing test has been evaluated and your detailed results are now available.</p>

    <div class="success-box">
        <p style="margin: 0; font-size: 14px;">
            <strong>✓ Evaluated by:</strong> {$examinerName}<br>
            <strong>📊 Status:</strong> Evaluation Complete
        </p>
    </div>

    <p style="text-align: center;">
        <a href="{$resultUrl}" class="button">View Your Results</a>
    </p>

    <p>Your results include:</p>
    <ul>
        <li>Overall score and individual task scores</li>
        <li>Detailed feedback from your examiner</li>
        <li>Strengths and areas for improvement</li>
        <li>Personalized recommendations</li>
    </ul>

    <p>We encourage you to review the feedback carefully to improve your writing skills.</p>

    <p style="margin-top: 30px;">
        Best regards,<br>
        <strong>The MyIELTS Team</strong><br>
        Powered by Skiloholic
    </p>
HTML;

    $html = get_email_template($content, 'Your Test Results Are Ready');
    $subject = 'Your MyIELTS Writing Test Results Are Ready!';

    return send_email($user['email'], $user['full_name'], $subject, $html);
}

/**
 * Send notification to all admins about new submission
 */
function send_admin_submission_notification($submissionId) {
    require_once __DIR__ . '/../config/database.php';

    // Get submission details
    $submission = db_fetch(
        "SELECT s.*, u.full_name, u.email as user_email, t.title as test_title
         FROM submissions s
         JOIN users u ON s.user_id = u.id
         LEFT JOIN writing_tests t ON s.test_id = t.id
         WHERE s.id = ?",
        [$submissionId]
    );

    if (!$submission) return false;

    // Get all admins
    $admins = db_fetch_all("SELECT id, full_name, email FROM users WHERE role = 'admin'");

    $adminUrl = ADMIN_URL . "submissions/queue.php";
    $testType = $submission['is_custom_test'] ? "Custom Test" : $submission['test_title'];
    $submittedTime = format_datetime($submission['submitted_at']);

    $successCount = 0;

    foreach ($admins as $admin) {
        $content = <<<HTML
        <h2 style="color: #333;">New Test Submission Received 📝</h2>
        <p>Dear <strong>{$admin['full_name']}</strong>,</p>
        <p>A new writing test submission has been received and is awaiting evaluation.</p>

        <div class="info-box">
            <p style="margin: 0; font-size: 14px;">
                <strong>📌 Submission Details:</strong><br>
                <strong>Student:</strong> {$submission['full_name']}<br>
                <strong>Email:</strong> {$submission['user_email']}<br>
                <strong>Test:</strong> {$testType}<br>
                <strong>Submitted:</strong> {$submittedTime}
            </p>
        </div>

        <p style="text-align: center;">
            <a href="{$adminUrl}" class="button">Go to Admin Panel</a>
        </p>

        <p>Please assign an examiner to evaluate this submission at your earliest convenience.</p>

        <p style="margin-top: 30px;">
            Best regards,<br>
            <strong>MyIELTS System</strong>
        </p>
HTML;

        $html = get_email_template($content, 'New Test Submission');
        $subject = 'MyIELTS - New Writing Test Submission Received';

        if (send_email($admin['email'], $admin['full_name'], $subject, $html)) {
            $successCount++;

            // Insert notification record
            db_insert(
                "INSERT INTO admin_notifications (submission_id, admin_id, created_at)
                 VALUES (?, ?, NOW())",
                [$submissionId, $admin['id']]
            );
        }
    }

    return $successCount > 0;
}

/**
 * Send examiner assignment notification to student
 */
function send_examiner_assignment_notification($userId, $examinerName, $testTitle) {
    require_once __DIR__ . '/../config/database.php';

    $user = db_fetch("SELECT full_name, email FROM users WHERE id = ?", [$userId]);
    if (!$user) return false;

    $content = <<<HTML
    <h2 style="color: #333;">Your Test Is Being Evaluated 📋</h2>
    <p>Dear <strong>{$user['full_name']}</strong>,</p>
    <p>Good news! Your writing test submission has been assigned to an examiner for evaluation.</p>

    <div class="success-box">
        <p style="margin: 0; font-size: 14px;">
            <strong>✓ Examiner:</strong> {$examinerName}<br>
            <strong>📝 Test:</strong> {$testTitle}<br>
            <strong>⏱️ Status:</strong> In Evaluation
        </p>
    </div>

    <p>Your examiner is now reviewing your submission and providing detailed feedback. You will receive another notification once your results are ready.</p>

    <div class="info-box">
        <p style="margin: 0; font-size: 14px;">
            <strong>⏰ Estimated Time:</strong> Most evaluations are completed within 24-48 hours.
        </p>
    </div>

    <p>Thank you for your patience!</p>

    <p style="margin-top: 30px;">
        Best regards,<br>
        <strong>The MyIELTS Team</strong><br>
        Powered by Skiloholic
    </p>
HTML;

    $html = get_email_template($content, 'Examiner Assigned');
    $subject = 'MyIELTS - Your Test Is Being Evaluated';

    return send_email($user['email'], $user['full_name'], $subject, $html);
}

/**
 * Send results ready notification (wrapper function called by evaluate.php)
 */
function send_results_ready_notification($submissionId) {
    require_once __DIR__ . '/../config/database.php';

    // Get submission and examiner details
    $submission = db_fetch(
        "SELECT s.user_id, u.full_name as examiner_name
         FROM submissions s
         LEFT JOIN evaluations e ON s.id = e.submission_id
         LEFT JOIN users u ON e.examiner_id = u.id
         WHERE s.id = ?",
        [$submissionId]
    );

    if (!$submission) {
        error_log("Failed to send results notification: Submission $submissionId not found");
        return false;
    }

    return send_result_ready_email(
        $submission['user_id'],
        $submissionId,
        $submission['examiner_name'] ?? 'Your Examiner'
    );
}

/**
 * Send welcome email to new user
 */
function send_welcome_email($userId) {
    require_once __DIR__ . '/../config/database.php';

    $user = db_fetch("SELECT full_name, email FROM users WHERE id = ?", [$userId]);
    if (!$user) return false;

    $dashboardUrl = BASE_URL . "dashboard.php";
    $testsUrl = BASE_URL . "tests/writing-tests.php";

    $content = <<<HTML
    <h2>Welcome to the MyIELTS Community! 🌟</h2>
    <p>Dear <strong>{$user['full_name']}</strong>,</p>
    <p>We're thrilled to have you on board! Your account has been successfully verified, and you're now ready to start your journey towards IELTS success.</p>

    <div class="success-box">
        <h3>🚀 Get Started Right Away</h3>
        <p>Explore our library of writing tests and start practicing today.</p>
    </div>

    <h3>What you can do with MyIELTS:</h3>
    <ul>
        <li><strong>Take Writing Tests:</strong> Practice with real exam-style questions.</li>
        <li><strong>Get Expert Feedback:</strong> Our examiners provide detailed scoring and advice.</li>
        <li><strong>Track Your Progress:</strong> See your improvements over time.</li>
    </ul>

    <p style="text-align: center;">
        <a href="{$dashboardUrl}" class="button">Go to Dashboard</a>
    </p>

    <div class="info-box">
        <p><strong>💡 Pro Tip:</strong> Consistent practice is key! Try to complete at least one writing task per week.</p>
    </div>

    <p>If you have any questions, feel free to reply to this email. We're here to help!</p>

    <p>Happy Learning!</p>

    <p>
        Warm regards,<br>
        <strong>The MyIELTS Team</strong>
    </p>
HTML;

    $html = get_email_template($content, 'Welcome to MyIELTS!');
    $subject = 'Welcome to MyIELTS! Let\'s Get Started 🚀';

    return send_email($user['email'], $user['full_name'], $subject, $html);
}

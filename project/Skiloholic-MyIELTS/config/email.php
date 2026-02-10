<?php
/**
 * Email Configuration and Sender
 * MyIELTS - PHPMailer Setup for Skiloholic Email Server
 */

// Load PHPMailer classes
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Email server configuration
define('MAIL_HOST', getenv('MAIL_HOST') ?: 'mail.skiloholic.com');
define('MAIL_PORT', getenv('MAIL_PORT') ?: 465);
define('MAIL_USERNAME', getenv('MAIL_USERNAME') ?: 'no_reply@skiloholic.com');
define('MAIL_PASSWORD', getenv('MAIL_PASSWORD') ?: 'DevNerds@Sharfin9090');
define('MAIL_FROM_EMAIL', getenv('MAIL_FROM_EMAIL') ?: 'no_reply@skiloholic.com');
define('MAIL_FROM_NAME', getenv('MAIL_FROM_NAME') ?: 'MyIELTS - Skiloholic');
define('MAIL_ENCRYPTION', PHPMailer::ENCRYPTION_SMTPS); // SSL/TLS

/**
 * Create configured PHPMailer instance
 *
 * @return PHPMailer
 */
function get_mailer() {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = MAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USERNAME;
        $mail->Password = MAIL_PASSWORD;
        $mail->SMTPSecure = MAIL_ENCRYPTION;
        $mail->Port = MAIL_PORT;

        // Sender
        $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);

        // Content type
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';

        return $mail;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $e->getMessage());
        return null;
    }
}

/**
 * Send email using configured mailer
 *
 * @param string $to Recipient email
 * @param string $toName Recipient name
 * @param string $subject Email subject
 * @param string $body HTML body
 * @param string $altBody Plain text alternative
 * @return bool Success status
 */
function send_email($to, $toName, $subject, $body, $altBody = '') {
    $mail = get_mailer();

    if (!$mail) {
        return false;
    }

    try {
        $mail->addAddress($to, $toName);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = $altBody ?: strip_tags($body);

        $result = $mail->send();

        if ($result) {
            error_log("Email sent successfully to: $to");
        }

        return $result;
    } catch (Exception $e) {
        error_log("Email send failed to $to: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Get email template wrapper
 *
 * @param string $content Main content HTML
 * @param string $title Email title
 * @return string Complete HTML email
 */
function get_email_template($content, $title = 'MyIELTS Notification') {
    $year = date('Y');
    $siteName = 'MyIELTS';
    $siteUrl = BASE_URL;
    $logoUrl = LOGO_URL . 'No%20Background%20Skiloholic.png';

    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f3f4f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; -webkit-font-smoothing: antialiased; }
        table { border-collapse: collapse; width: 100%; }
        .wrapper { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); margin-top: 40px; margin-bottom: 40px; }
        .header { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); padding: 30px 20px; text-align: center; }
        .header img { height: 50px; width: auto; background: rgba(255, 255, 255, 0.9); padding: 5px 15px; border-radius: 8px; }
        .header h1 { color: #ffffff; margin: 15px 0 0 0; font-size: 24px; font-weight: 700; text-shadow: 0 1px 2px rgba(0,0,0,0.1); }
        .content { padding: 40px 30px; color: #374151; line-height: 1.6; font-size: 16px; }
        .footer { background-color: #f9fafb; padding: 30px; text-align: center; color: #6b7280; font-size: 13px; border-top: 1px solid #e5e7eb; }
        .footer a { color: #6366f1; text-decoration: none; }
        .button { display: inline-block; padding: 12px 24px; background-color: #6366f1; color: #ffffff !important; text-decoration: none; border-radius: 8px; font-weight: 600; text-align: center; margin: 20px 0; transition: background-color 0.2s; }
        .button:hover { background-color: #4f46e5; }
        .info-box { background-color: #eff6ff; border-left: 4px solid #3b82f6; padding: 15px; border-radius: 4px; margin: 20px 0; }
        .success-box { background-color: #ecfdf5; border-left: 4px solid #10b981; padding: 15px; border-radius: 4px; margin: 20px 0; }
        .warning-box { background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 15px; border-radius: 4px; margin: 20px 0; }
        .code-box { background-color: #f3f4f6; border: 1px solid #d1d5db; padding: 20px; text-align: center; border-radius: 8px; margin: 20px 0; }
        .verification-code { font-family: 'Courier New', monospace; font-size: 32px; font-weight: 700; letter-spacing: 4px; color: #111827; margin: 10px 0; }
        @media only screen and (max-width: 600px) {
            .wrapper { margin: 0; border-radius: 0; width: 100% !important; }
            .content { padding: 30px 20px; }
        }
    </style>
</head>
<body>
    <div style="background-color: #f3f4f6; padding: 20px 0;">
        <div class="wrapper">
            <div class="header">
                <img src="{$logoUrl}" alt="{$siteName}">
                <h1>{$title}</h1>
            </div>

            <div class="content">
                {$content}
            </div>

            <div class="footer">
                <p>&copy; {$year} {$siteName}. All rights reserved.</p>
                <p style="margin: 10px 0;">
                    <a href="{$siteUrl}">Visit Website</a> &bull;
                    <a href="{$siteUrl}privacy.php">Privacy Policy</a> &bull;
                    <a href="{$siteUrl}contact.php">Contact Support</a>
                </p>
                <p>Powered by Skiloholic - A Non-Profit Organization</p>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
}

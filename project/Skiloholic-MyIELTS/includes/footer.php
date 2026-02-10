<?php
// Ensure SITE_NAME and other constants are available if not already
if (!defined('SITE_NAME')) require_once __DIR__ . '/../config/config.php';
?>
<!-- Modern Footer -->
<footer class="modern-footer">
    <div class="footer-content">
        <div class="footer-grid">
            <!-- Brand Section -->
            <div class="footer-brand-col">
                <div class="footer-brand">
                    <img src="<?php echo LOGO_URL . 'No%20Background%20Skiloholic.png'; ?>" alt="MyIELTS" class="footer-logo">
                    <div class="footer-brand-text">
                        <h3>MyIELTS</h3>
                        <p>Powered by Skiloholic</p>
                    </div>
                </div>
                <p class="footer-about">
                    Empowering IELTS aspirants with free, expert-evaluated writing practice. Join our community to achieve your target band score.
                </p>
                <div class="social-links">
                    <a href="#" target="_blank" class="social-icon youtube" title="YouTube">
                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"></path><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"></polygon></svg>
                    </a>
                    <a href="#" target="_blank" class="social-icon facebook" title="Facebook">
                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>
                    </a>
                    <a href="#" target="_blank" class="social-icon whatsapp" title="WhatsApp">
                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo BASE_URL; ?>">Home</a></li>
                    <?php if (!is_logged_in()): ?>
                        <li><a href="<?php echo BASE_URL; ?>auth/login.php">Login</a></li>
                        <li><a href="<?php echo BASE_URL; ?>auth/register.php">Register</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo BASE_URL; ?>profile/dashboard.php">Dashboard</a></li>
                    <?php endif; ?>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Terms of Service</a></li>
                </ul>
            </div>

            <!-- Official IELTS -->
            <div class="footer-section">
                <h4>Official IELTS</h4>
                <ul class="footer-links">
                    <li><a href="https://www.ielts.org" target="_blank">IELTS.org</a></li>
                    <li><a href="https://www.britishcouncil.org/exam/ielts" target="_blank">British Council IELTS</a></li>
                    <li><a href="https://www.idp.com/global/ielts/" target="_blank">IDP IELTS</a></li>
                    <li><a href="https://www.cambridgeenglish.org/exams-and-tests/ielts/" target="_blank">Cambridge English</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2026 Copyright reserved. <strong>MyIELTS Powered By Skiloholic</strong></p>
            <div class="footer-credits">
                <span>A Non-Profit Organization</span>
                <span class="separator">•</span>
                <span>Developed by <a href="#" class="dev-link">Sharfin Hossain</a></span>
            </div>
        </div>
    </div>
</footer>

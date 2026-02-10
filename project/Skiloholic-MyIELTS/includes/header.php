<!-- Modern Header -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/main.css?v=2.0">

<header class="modern-header">
    <div class="header-content">
        <a href="<?php echo BASE_URL; ?>" class="brand-section">
            <img src="<?php echo LOGO_URL . 'No%20Background%20Skiloholic.png'; ?>" alt="MyIELTS Logo" class="brand-logo">
            <div class="brand-text">
                <div class="brand-name">MyIELTS</div>
                <div class="brand-tagline">Powered by Skiloholic</div>
            </div>
        </a>

        <ul class="nav-menu">
            <?php if (is_logged_in()): ?>
                <li><a href="<?php echo BASE_URL; ?>profile/dashboard.php">Dashboard</a></li>
                <li><a href="<?php echo BASE_URL; ?>tests/writing-tests.php">Writing Tests</a></li>
                <?php if (is_admin()): ?>
                    <li><a href="<?php echo BASE_URL; ?>admin/index.php">Admin</a></li>
                <?php endif; ?>
                <li><a href="<?php echo BASE_URL; ?>auth/logout.php" class="btn btn-secondary btn-sm">Logout</a></li>
            <?php else: ?>
                <li><a href="<?php echo BASE_URL; ?>auth/login.php">Login</a></li>
                <li><a href="<?php echo BASE_URL; ?>auth/register.php" class="btn btn-primary btn-sm">Get Started</a></li>
            <?php endif; ?>
        </ul>
    </div>
</header>

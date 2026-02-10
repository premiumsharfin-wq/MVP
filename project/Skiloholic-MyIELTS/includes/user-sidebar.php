<?php
// User Sidebar Component
// Requires: $user variable with user data
// Requires: $currentPage variable to set active state

$currentPath = $_SERVER['PHP_SELF'];
$basePath = BASE_URL;

// Get user initials for avatar placeholder
$initials = '';
if (isset($user['full_name'])) {
    $nameParts = explode(' ', $user['full_name']);
    $initials = strtoupper(substr($nameParts[0], 0, 1));
    if (count($nameParts) > 1) {
        $initials .= strtoupper(substr($nameParts[count($nameParts) - 1], 0, 1));
    }
}

$avatarUrl = $user['avatar_url'] ?? null;
$isAdmin = ($user['role'] === 'admin');
?>

<aside class="user-sidebar" id="userSidebar">
    <!-- Brand -->
    <div class="sidebar-header">
        <a href="<?php echo $basePath; ?>" class="sidebar-brand">
            <img src="<?php echo LOGO_URL . 'No%20Background%20Skiloholic.png'; ?>" alt="MyIELTS Logo">
            <span>MyIELTS</span>
        </a>
    </div>

    <!-- User Profile -->
    <div class="sidebar-profile">
        <?php if ($avatarUrl): ?>
            <img src="<?php echo htmlspecialchars($avatarUrl); ?>" alt="Profile" class="profile-avatar">
        <?php else: ?>
            <div class="profile-avatar-placeholder"><?php echo $initials; ?></div>
        <?php endif; ?>

        <div class="profile-info">
            <div class="profile-name"><?php echo htmlspecialchars($user['full_name']); ?></div>
            <div class="profile-role"><?php echo htmlspecialchars($user['role']); ?></div>
        </div>
    </div>

    <!-- Navigation -->
    <ul class="sidebar-nav">
        <li>
            <a href="<?php echo $basePath; ?>profile/dashboard.php"
               class="sidebar-link <?php echo (strpos($currentPath, 'dashboard.php') !== false) ? 'active' : ''; ?>">
                <span class="icon">📊</span>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="<?php echo $basePath; ?>tests/writing-tests.php"
               class="sidebar-link <?php echo (strpos($currentPath, 'writing-tests.php') !== false || strpos($currentPath, 'take-test.php') !== false) ? 'active' : ''; ?>">
                <span class="icon">✍️</span>
                <span>Writing Practice</span>
            </a>
        </li>
        <li>
            <a href="<?php echo $basePath; ?>profile/custom-evaluation.php"
               class="sidebar-link <?php echo (strpos($currentPath, 'custom-evaluation.php') !== false) ? 'active' : ''; ?>">
                <span class="icon">📝</span>
                <span>Custom Evaluation</span>
            </a>
        </li>
        <li>
            <a href="<?php echo $basePath; ?>profile/test-history.php"
               class="sidebar-link <?php echo (strpos($currentPath, 'test-history.php') !== false) ? 'active' : ''; ?>">
                <span class="icon">📚</span>
                <span>Test History</span>
            </a>
        </li>
        <li>
            <a href="<?php echo $basePath; ?>profile/settings.php"
               class="sidebar-link <?php echo (strpos($currentPath, 'settings.php') !== false) ? 'active' : ''; ?>">
                <span class="icon">⚙️</span>
                <span>Settings</span>
            </a>
        </li>

        <?php if ($isAdmin): ?>
            <li><hr class="sidebar-divider"></li>
            <li>
                <a href="<?php echo $basePath; ?>admin/index.php" class="sidebar-link">
                    <span class="icon">🔧</span>
                    <span>Admin Panel</span>
                </a>
            </li>
        <?php endif; ?>

        <li><hr class="sidebar-divider"></li>
        <li>
            <a href="<?php echo $basePath; ?>auth/logout.php" class="sidebar-link">
                <span class="icon">🚪</span>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</aside>

<!-- Mobile Toggle Button -->
<button class="sidebar-toggle" onclick="toggleSidebar()">
    <span style="font-size: 1.5rem;">☰</span>
</button>

<script>
function toggleSidebar() {
    document.getElementById('userSidebar').classList.toggle('open');
}

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('userSidebar');
    const toggle = document.querySelector('.sidebar-toggle');

    if (window.innerWidth <= 1024 && sidebar.classList.contains('open')) {
        if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
            sidebar.classList.remove('open');
        }
    }
});
</script>

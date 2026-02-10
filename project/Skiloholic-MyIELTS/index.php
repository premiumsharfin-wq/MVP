<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyIELTS - Your Path to IELTS Success</title>
    <link rel="stylesheet" href="assets/css/main.css?v=2.3">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <?php
    require_once __DIR__ . '/config/config.php';
    require_once __DIR__ . '/includes/session.php';
    check_remember_me();
    ?>

    <?php include __DIR__ . '/includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero-modern">
        <div class="container">
            <div class="hero-badge">
                <span>🎯</span> <?php echo ORG_TYPE; ?>
            </div>
            <h1>Master Your IELTS Writing</h1>
            <p class="subtitle">Get expert feedback, track your progress, and achieve your target band score with personalized practice.</p>

            <div class="hero-buttons">
                <?php if (is_logged_in()): ?>
                    <a href="profile/dashboard.php" class="btn btn-hero-primary">Go to Dashboard →</a>
                    <a href="tests/writing-tests.php" class="btn btn-hero-secondary">Start Practicing</a>
                <?php else: ?>
                    <a href="auth/register.php" class="btn btn-hero-primary">Get Started Free →</a>
                    <a href="auth/login.php" class="btn btn-hero-secondary">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="container">
        <div class="stats-section">
            <div class="stats-grid">
                <div class="stat-item">
                    <h2>📝</h2>
                    <p>Expert Evaluation</p>
                </div>
                <div class="stat-item">
                    <h2>🎯</h2>
                    <p>Personalized Feedback</p>
                </div>
                <div class="stat-item">
                    <h2>🆓</h2>
                    <p>Completely Free</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="section container">
        <h2 class="section-title" style="font-size: 2.5rem; font-weight: 800;">Complete IELTS Preparation</h2>
        <p class="text-center" style="color: #6b7280; max-width: 600px; margin: 1rem auto 0;">
            Practice all four modules with expert guidance and detailed feedback
        </p>

        <div class="features-grid">
            <!-- Writing Tests -->
            <div class="feature-card">
                <span class="feature-icon">✍️</span>
                <div class="feature-header">
                    <h3 class="feature-title">Writing Tests</h3>
                    <span class="badge badge-success">Available</span>
                </div>
                <p style="color: #6b7280; margin-bottom: 1rem;">Master IELTS Writing with expert evaluation and personalized feedback.</p>
                <ul class="feature-list">
                    <li>Full writing tests</li>
                    <li>Task 1 & Task 2 practice</li>
                    <li>Custom answer evaluation</li>
                    <li>Expert examiner feedback</li>
                    <li>Detailed band scores</li>
                </ul>
                <?php if (is_logged_in()): ?>
                    <a href="tests/writing-tests.php" class="btn btn-primary btn-block">Start Writing Practice</a>
                <?php else: ?>
                    <a href="auth/register.php" class="btn btn-primary btn-block">Register to Access</a>
                <?php endif; ?>
            </div>

            <!-- Speaking Tests -->
            <div class="feature-card coming-soon-card">
                <span class="feature-icon">🎤</span>
                <div class="feature-header">
                    <h3 class="feature-title">Speaking Tests</h3>
                    <span class="badge badge-warning">Soon</span>
                </div>
                <p style="color: #6b7280;">AI-powered speaking practice with instant feedback</p>
                <div class="coming-soon-overlay">
                    <div class="coming-soon-title">Coming Soon</div>
                    <div class="coming-soon-date"><?php echo OTHER_TESTS_AVAILABLE_DATE; ?></div>
                    <p style="color: #6b7280; text-align: center;">Practice all three Speaking parts with detailed feedback</p>
                </div>
            </div>

            <!-- Reading Tests -->
            <div class="feature-card coming-soon-card">
                <span class="feature-icon">📖</span>
                <div class="feature-header">
                    <h3 class="feature-title">Reading Tests</h3>
                    <span class="badge badge-warning">Soon</span>
                </div>
                <p style="color: #6b7280;">Authentic reading materials with explanations</p>
                <div class="coming-soon-overlay">
                    <div class="coming-soon-title">Coming Soon</div>
                    <div class="coming-soon-date"><?php echo OTHER_TESTS_AVAILABLE_DATE; ?></div>
                    <p style="color: #6b7280; text-align: center;">Comprehensive reading practice with detailed answer keys</p>
                </div>
            </div>

            <!-- Listening Tests -->
            <div class="feature-card coming-soon-card">
                <span class="feature-icon">🎧</span>
                <div class="feature-header">
                    <h3 class="feature-title">Listening Tests</h3>
                    <span class="badge badge-warning">Soon</span>
                </div>
                <p style="color: #6b7280;">Authentic IELTS listening materials</p>
                <div class="coming-soon-overlay">
                    <div class="coming-soon-title">Coming Soon</div>
                    <div class="coming-soon-date"><?php echo OTHER_TESTS_AVAILABLE_DATE; ?></div>
                    <p style="color: #6b7280; text-align: center;">Interactive listening tests with transcripts</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="section" style="background-color: #f9fafb; padding: 5rem 0;">
        <div class="container">
            <h2 class="section-title text-center" style="margin-bottom: 3rem; font-size: 2.5rem; font-weight: 800;">How MyIELTS Works</h2>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; text-align: center;">
                <div class="step-card">
                    <div style="background: white; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: bold; color: var(--primary); margin: 0 auto 1.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">1</div>
                    <h3 style="margin-bottom: 1rem; font-size: 1.5rem;">Choose a Test</h3>
                    <p style="color: #6b7280; font-size: 1.1rem;">Select from our extensive library of Academic Writing Task 1 and Task 2 questions.</p>
                </div>

                <div class="step-card">
                    <div style="background: white; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: bold; color: var(--primary); margin: 0 auto 1.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">2</div>
                    <h3 style="margin-bottom: 1rem; font-size: 1.5rem;">Take the Test</h3>
                    <p style="color: #6b7280; font-size: 1.1rem;">Practice in our real exam-like interface with timer and word count tools.</p>
                </div>

                <div class="step-card">
                    <div style="background: white; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: bold; color: var(--primary); margin: 0 auto 1.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">3</div>
                    <h3 style="margin-bottom: 1rem; font-size: 1.5rem;">Get Feedback</h3>
                    <p style="color: #6b7280; font-size: 1.1rem;">Receive detailed evaluation, band scores, and correction usage from expert examiners.</p>
                </div>
            </div>

            <div style="text-align: center; margin-top: 3rem;">
                <?php if (is_logged_in()): ?>
                    <a href="tests/writing-tests.php" class="btn btn-primary btn-lg">Start Practicing Now</a>
                <?php else: ?>
                    <a href="auth/register.php" class="btn btn-primary btn-lg">Get Started Free</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
</body>
</html>

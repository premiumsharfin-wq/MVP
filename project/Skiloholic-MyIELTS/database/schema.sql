-- MyIELTS Database Schema
-- Created: 2026-02-09
-- Description: Complete database structure for MyIELTS MVP
--
-- IMPORTANT: Select your database 'mpkhydsc_myielts' in phpMyAdmin BEFORE importing
--

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    whatsapp_number VARCHAR(20) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    avatar_url VARCHAR(255) NULL,
    target_band DECIMAL(2,1) NULL,
    exam_date DATE NULL,
    email_verified BOOLEAN DEFAULT FALSE,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Email Verification Codes Table
CREATE TABLE IF NOT EXISTS email_verification_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    code VARCHAR(6) NOT NULL,
    type ENUM('registration', 'password_reset') NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_code (user_id, code),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Writing Tests Table
CREATE TABLE IF NOT EXISTS writing_tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    test_type ENUM('full', 'task1', 'task2') NOT NULL,
    source VARCHAR(255) DEFAULT 'Other',
    task1_description TEXT NULL,
    task1_image_url VARCHAR(500) NULL,
    task2_prompt TEXT NULL,
    created_by INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_test_type (test_type),
    INDEX idx_source (source),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Submissions Table
CREATE TABLE IF NOT EXISTS submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    test_id INT NULL,
    is_custom_test BOOLEAN DEFAULT FALSE,
    custom_task1_question TEXT NULL,
    custom_task1_image VARCHAR(255) NULL,
    custom_task2_question TEXT NULL,
    task1_answer TEXT NULL,
    task2_answer TEXT NULL,
    status ENUM('pending', 'assigned', 'in_evaluation', 'completed') DEFAULT 'pending',
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    assigned_examiner_id INT NULL,
    assigned_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (test_id) REFERENCES writing_tests(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_examiner_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_examiner (assigned_examiner_id),
    INDEX idx_submitted (submitted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Evaluations Table
CREATE TABLE IF NOT EXISTS evaluations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    submission_id INT NOT NULL UNIQUE,
    examiner_id INT NOT NULL,
    task1_score DECIMAL(3,1) NULL,
    task1_feedback TEXT NULL,
    task2_score DECIMAL(3,1) NULL,
    task2_feedback TEXT NULL,
    overall_score DECIMAL(3,1) NULL,
    overall_feedback TEXT NULL,
    estimated_completion_time VARCHAR(50) NULL,
    assigned_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
    FOREIGN KEY (examiner_id) REFERENCES users(id),
    INDEX idx_submission (submission_id),
    INDEX idx_examiner (examiner_id),
    INDEX idx_completed (completed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin Notifications Table
CREATE TABLE IF NOT EXISTS admin_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    submission_id INT NOT NULL,
    admin_id INT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_admin_read (admin_id, is_read),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert initial admin account
-- Password: DevNerds@Sharfin9090 (will be hashed by PHP)
INSERT INTO users (full_name, email, whatsapp_number, password_hash, role, email_verified, created_at)
VALUES (
    'Sharfin Hossain',
    'sharfinhossain50@gmail.com',
    '+8801533869234',
    '$2y$10$placeholder_will_be_replaced_by_install_script',
    'admin',
    TRUE,
    NOW()
) ON DUPLICATE KEY UPDATE email = email;

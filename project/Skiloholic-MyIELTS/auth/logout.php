<?php
/**
 * Logout
 * MyIELTS - Destroy session and redirect to login
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/session.php';

logout_user();
redirect(BASE_URL . 'auth/login.php');

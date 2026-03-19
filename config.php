<?php

// ===== DATABASE =====
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'student_db');

// ===== APP =====
define('APP_NAME', 'EduPro');
define('APP_URL', 'http://localhost/student-management-system');
define('APP_VERSION', '2.0');

// ===== UPLOADS =====
define('UPLOAD_PATH', 'C:/xampp/htdocs/student-management-system/uploads/students/');
define('UPLOAD_URL', APP_URL . '/uploads/students/');
define('MAX_FILE_SIZE', 2 * 1024 * 1024);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// ===== DATABASE CONNECTION =====
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// ===== SESSION =====
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ===== HELPER FUNCTIONS =====

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function redirect($url) {
    header("Location: " . APP_URL . "/" . $url);
    exit();
}

function logActivity($conn, $action) {
    $stmt = mysqli_prepare($conn, "INSERT INTO activity_log (action) VALUES (?)");
    mysqli_stmt_bind_param($stmt, "s", $action);
    mysqli_stmt_execute($stmt);
}

function timeAgo($datetime) {
    $diff = abs(time() - strtotime($datetime));
    if ($diff < 60)    return $diff . ' seconds ago';
    if ($diff < 3600)  return floor($diff/60) . ' minutes ago';
    if ($diff < 86400) return floor($diff/3600) . ' hours ago';
    return floor($diff/86400) . ' days ago';
}

function generateStudentID($conn) {
    $year = date('Y');
    // Get the highest existing number to avoid duplicates
    $result = mysqli_fetch_assoc(mysqli_query($conn, "SELECT student_id FROM students WHERE student_id LIKE 'STU-$year-%' ORDER BY id DESC LIMIT 1"));
    
    if ($result && !empty($result['student_id'])) {
        // Extract the number from the last ID and increment
        $parts  = explode('-', $result['student_id']);
        $number = (int) end($parts) + 1;
    } else {
        $number = 1;
    }
    
    return "STU-$year-" . str_pad($number, 4, '0', STR_PAD_LEFT);
}

function calculateGrade($marks) {
    if ($marks >= 90) return 'A+';
    if ($marks >= 80) return 'A';
    if ($marks >= 70) return 'B';
    if ($marks >= 60) return 'C';
    if ($marks >= 50) return 'D';
    return 'F';
}

function calculateGPA($marks) {
    if ($marks >= 90) return 4.0;
    if ($marks >= 80) return 3.7;
    if ($marks >= 70) return 3.0;
    if ($marks >= 60) return 2.0;
    if ($marks >= 50) return 1.0;
    return 0.0;
}

// ===== AUTH FUNCTIONS =====

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function isStudentLoggedIn() {
    return isset($_SESSION['student_id']);
}

function requireAdmin() {
    if (!isAdminLoggedIn()) {
        redirect('login.php');
    }
}

function requireStudent() {
    if (!isStudentLoggedIn()) {
        redirect('student/login.php');
    }
}

// Update student last login
function updateStudentLastLogin($conn, $student_id) {
    $now  = date('Y-m-d H:i:s');
    $stmt = mysqli_prepare($conn, "UPDATE students SET last_login = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "si", $now, $student_id);
    mysqli_stmt_execute($stmt);
}

// Get student attendance percentage
function getAttendancePercentage($conn, $student_id) {
    $total   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM attendance WHERE student_id = $student_id"))['count'];
    $present = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM attendance WHERE student_id = $student_id AND status = 'present'"))['count'];
    if ($total == 0) return 0;
    return round(($present / $total) * 100);
}

// Get student average marks
function getAverageMarks($conn, $student_id) {
    $result = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(marks) as avg FROM grades WHERE student_id = $student_id"));
    return round($result['avg'] ?? 0, 1);
}

// Get student fee percentage paid
function getFeePercentage($conn, $student_id) {
    $result = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as total, SUM(paid_amount) as paid FROM fees WHERE student_id = $student_id"));
    if (!$result['total'] || $result['total'] == 0) return 100;
    return round(($result['paid'] / $result['total']) * 100);
}
?>
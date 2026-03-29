<?php
require_once 'config.php';
requireAdmin();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('students.php');
}

$id = (int) $_GET['id'];

// Get student name for log
$stmt    = mysqli_prepare($conn, "SELECT name, student_id, profile_image FROM students WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$student = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if ($student) {
    // Delete profile image if exists
    if (!empty($student['profile_image']) && file_exists(UPLOAD_PATH . $student['profile_image'])) {
        unlink(UPLOAD_PATH . $student['profile_image']);
    }

    // Delete student
    $stmt = mysqli_prepare($conn, "DELETE FROM students WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    logActivity($conn, "Deleted student: {$student['name']} (ID: {$student['student_id']})");
}

redirect('students.php?success=deleted');
?>
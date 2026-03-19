<?php
require_once '../config.php';

if (isStudentLoggedIn()) redirect('student/dashboard.php');

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = sanitize($_POST['student_id'] ?? '');
    $password   = $_POST['password'] ?? '';

    if (empty($student_id) || empty($password)) {
        $error = "All fields are required.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT * FROM students WHERE student_id = ?");
        mysqli_stmt_bind_param($stmt, "s", $student_id);
        mysqli_stmt_execute($stmt);
        $student = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if ($student && !empty($student['password']) && password_verify($password, $student['password'])) {
            $_SESSION['student_id']    = $student['id'];
            $_SESSION['student_name']  = $student['name'];
            $_SESSION['student_sid']   = $student['student_id'];
            $_SESSION['student_image'] = $student['profile_image'];
            updateStudentLastLogin($conn, $student['id']);
            logActivity($conn, "Student logged in: {$student['name']} ({$student['student_id']})");
            redirect('student/dashboard.php');
        } else {
            $error = "Invalid Student ID or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login — <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/style.css">
    <style>
        .auth-body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 50%, #1c1917 100%);
            padding: 20px;
        }

        .auth-container {
            background: white;
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        .auth-logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .auth-logo .logo-icon {
            font-size: 50px;
            display: block;
            margin-bottom: 10px;
        }

        .auth-logo h1 {
            font-size: 28px;
            color: var(--primary);
            font-weight: 700;
        }

        .auth-logo p {
            color: var(--gray);
            font-size: 14px;
            margin-top: 5px;
        }

        .auth-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: var(--gray);
        }

        .auth-footer a {
            color: var(--primary);
            font-weight: 600;
        }
    </style>
</head>
<body class="auth-body">

<div class="auth-container">
    <div class="auth-logo">
        <span class="logo-icon">👨‍🎓</span>
        <h1><?php echo APP_NAME; ?></h1>
        <p>Student Portal — Sign in to continue</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger">❌ <?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['logout'])): ?>
        <div class="alert alert-success">✅ Logged out successfully!</div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="form-group">
            <label class="form-label">Student ID</label>
            <input type="text" name="student_id" class="form-control"
                placeholder="e.g. STU-2026-0001"
                value="<?php echo isset($_POST['student_id']) ? sanitize($_POST['student_id']) : ''; ?>"
                required>
        </div>
        <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control"
                placeholder="Enter your password" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block btn-lg">
            👨‍🎓 Sign In as Student
        </button>
    </form>

    <div class="auth-footer">
        Are you an admin? <a href="<?php echo APP_URL; ?>/login.php">Admin Login</a>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
<?php
require_once 'config.php';

if (isAdminLoggedIn()) redirect('dashboard.php');

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email    = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "All fields are required.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT * FROM admins WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $admin = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_email']= $admin['email'];
            logActivity($conn, "Admin logged in: {$admin['name']}");
            redirect('dashboard.php');
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/style.css">
    <style>
        .auth-body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #1c1917 0%, #292524 50%, #f97316 100%);
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
        <span class="logo-icon">🎓</span>
        <h1><?php echo APP_NAME; ?></h1>
        <p>Admin Portal — Sign in to continue</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger">❌ <?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['logout'])): ?>
        <div class="alert alert-success">✅ Logged out successfully!</div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="form-group">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control"
                placeholder="admin@edupro.com"
                value="<?php echo isset($_POST['email']) ? sanitize($_POST['email']) : ''; ?>"
                required>
        </div>
        <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control"
                placeholder="Enter password" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block btn-lg">
            🔐 Sign In as Admin
        </button>
    </form>

    <div class="auth-footer">
        Are you a student? <a href="<?php echo APP_URL; ?>/student/login.php">Login here</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
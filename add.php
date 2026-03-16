<?php
include 'db.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name   = trim($_POST['name']);
    $email  = trim($_POST['email']);
    $course = trim($_POST['course']);

    if (empty($name) || empty($email) || empty($course)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO students (name, email, course) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sss", $name, $email, $course);

        if (mysqli_stmt_execute($stmt)) {
            header('Location: index.php');
            exit();
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Student</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h1>Add New Student</h1>

    <?php if ($error): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST" action="add.php">
        <label>Full Name</label>
        <input type="text" name="name" placeholder="Enter full name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">

        <label>Email Address</label>
        <input type="email" name="email" placeholder="Enter email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">

        <label>Course</label>
        <input type="text" name="course" placeholder="Enter course name" value="<?php echo isset($_POST['course']) ? htmlspecialchars($_POST['course']) : ''; ?>">

        <button type="submit" class="btn btn-add">Add Student</button>
        <a href="index.php" class="btn btn-edit">Cancel</a>
    </form>
</div>

</body>
</html>
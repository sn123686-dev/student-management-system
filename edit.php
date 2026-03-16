<?php
include 'db.php';

$error = "";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$id = (int) $_GET['id'];

$stmt = mysqli_prepare($conn, "SELECT * FROM students WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result  = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);

if (!$student) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name   = trim($_POST['name']);
    $email  = trim($_POST['email']);
    $course = trim($_POST['course']);

    if (empty($name) || empty($email) || empty($course)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE students SET name=?, email=?, course=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "sssi", $name, $email, $course, $id);

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
    <title>Edit Student</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h1>Edit Student</h1>

    <?php if ($error): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST" action="edit.php?id=<?php echo $id; ?>">
        <label>Full Name</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($student['name']); ?>">

        <label>Email Address</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>">

        <label>Course</label>
        <input type="text" name="course" value="<?php echo htmlspecialchars($student['course']); ?>">

        <button type="submit" class="btn btn-add">Update Student</button>
        <a href="index.php" class="btn btn-edit">Cancel</a>
    </form>
</div>

</body>
</html>
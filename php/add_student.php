<?php
session_start();

if (
    !isset($_SESSION["admin_id"]) ||
    ($_SESSION["role"] ?? "") !== "admin"
) {
    header("Location: ../index.php");
    exit();
}

require "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $roll_number = trim($_POST["roll_number"] ?? "");
    $password = $_POST["password"] ?? "";
    $name = trim($_POST["name"] ?? "");
    $course = trim($_POST["course"] ?? "");
    $email = trim($_POST["email"] ?? "");

    if (
        $roll_number === "" ||
        $password === "" ||
        $name === "" ||
        $course === "" ||
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {
        $message = "Please enter all details correctly.";
    } else {
        // Prevent duplicate roll numbers.
        $check = $conn->prepare(
            "SELECT id FROM students WHERE roll_number = ?"
        );
        $check->bind_param("s", $roll_number);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $message = "This roll number already exists.";
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO students
                (roll_number, password, name, course, email)
                VALUES (?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "sssss",
                $roll_number,
                $password,
                $name,
                $course,
                $email
            );

            if ($stmt->execute()) {
                $message = "Student registered successfully!";
            } else {
                $message = "Unable to register the student.";
            }

            $stmt->close();
        }

        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Student</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eef3f8;
            padding: 20px;
        }

        .container {
            max-width: 500px;
            margin: 25px auto;
            padding: 25px;
            background: white;
            border-radius: 10px;
        }

        h2 {
            text-align: center;
            color: #174a7e;
        }

        label {
            display: block;
            margin-top: 14px;
            font-weight: bold;
        }

        input, select {
            width: 100%;
            padding: 11px;
            margin-top: 7px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 22px;
            background: #174a7e;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .message {
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>

<body>
<div class="container">
    <h2>Register New Student</h2>

    <p class="message">
        <?php echo htmlspecialchars($message); ?>
    </p>

    <form method="POST" action="add_student.php">
        <label for="roll_number">Roll Number</label>
        <input type="text" id="roll_number" name="roll_number" required>

        <label for="name">Student Name</label>
        <input type="text" id="name" name="name" required>

        <label for="course">Course / Branch</label>
        <input type="text" id="course" name="course" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Temporary Password</label>
        <input type="password" id="password" name="password"
               minlength="6" required>

        <button type="submit">Register Student</button>
    </form>

    <p><a href="admin_dashboard.php">Back to Admin Dashboard</a></p>
</div>
</body>
</html>

<?php $conn->close(); ?>
<?php
session_start();
require "db.php";

$role = $_POST["role"] ?? "";
$username = trim($_POST["username"] ?? "");
$password = $_POST["password"] ?? "";

if ($role === "student") {

    $sql = "SELECT * FROM students WHERE roll_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($student = $result->fetch_assoc()) {
        // Existing demo database stores student passwords as plain text.
        if ($password === $student["password"]) {
            session_regenerate_id(true);

            $_SESSION["student_id"] = $student["id"];
            $_SESSION["roll_number"] = $student["roll_number"];
            $_SESSION["student_name"] = $student["name"];
            $_SESSION["course"] = $student["course"];
            $_SESSION["role"] = "student";

            header("Location: dashboard.php");
            exit();
        }
    }

} elseif ($role === "admin") {

    $sql = "SELECT * FROM admins WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($admin = $result->fetch_assoc()) {
        // For the initial classroom demo only.
        if ($password === $admin["password"]) {
            session_regenerate_id(true);

            $_SESSION["admin_id"] = $admin["id"];
            $_SESSION["admin_username"] = $admin["username"];
            $_SESSION["role"] = "admin";

            header("Location: admin_dashboard.php");
            exit();
        }
    }

}

if (isset($stmt)) {
    $stmt->close();
}

$conn->close();

echo "<h3>Invalid username or password.</h3>";
echo '<a href="../index.php">Try Again</a>';
?>
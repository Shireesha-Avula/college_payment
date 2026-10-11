<?php
session_start();
require "db.php";

// Allow only logged-in admins
if (
    !isset($_SESSION["admin_id"]) ||
    $_SESSION["role"] !== "admin"
) {
    header("Location: ../index.php");
    exit();
}

$message = "";
$messageType = "";
$roll_number = "";

// Handle the delete request
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $roll_number = trim($_POST["roll_number"] ?? "");

    if ($roll_number === "") {
        $message = "Please enter a student roll number.";
        $messageType = "error";
    } else {

        // Check whether the student exists
        $stmt = $conn->prepare(
            "SELECT id FROM students WHERE roll_number = ?"
        );
        $stmt->bind_param("s", $roll_number);
        $stmt->execute();
        $student = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$student) {
            $message = "Student not found.";
            $messageType = "error";
        } else {

            // Check for pending bills
            $stmt = $conn->prepare(
                "SELECT COUNT(*) AS pending_count
                 FROM bills
                 WHERE roll_number = ? AND status = 'Pending'"
            );
            $stmt->bind_param("s", $roll_number);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ((int)$result["pending_count"] > 0) {

                $message = "Deletion not allowed! This student has pending fees. Please clear all dues first.";
                $messageType = "error";

            } else {

                // Delete bill records and student together
                $conn->begin_transaction();

                try {
                    $stmt = $conn->prepare(
                        "DELETE FROM bills WHERE roll_number = ?"
                    );
                    $stmt->bind_param("s", $roll_number);
                    $stmt->execute();
                    $stmt->close();

                    $stmt = $conn->prepare(
                        "DELETE FROM students WHERE roll_number = ?"
                    );
                    $stmt->bind_param("s", $roll_number);
                    $stmt->execute();

                    if ($stmt->affected_rows !== 1) {
                        throw new Exception("Student could not be deleted.");
                    }

                    $stmt->close();
                    $conn->commit();

                    $message = "Student " . htmlspecialchars($roll_number) .
                               " and their bill records have been permanently deleted.";
                    $messageType = "success";
                    $roll_number = "";

                } catch (Throwable $e) {
                    $conn->rollback();
                    $message = "Deletion failed. Please try again.";
                    $messageType = "error";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Student</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f4f9;
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 500px;
            margin: 40px auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 3px 12px #0002;
        }

        h2 {
            text-align: center;
            color: #b42318;
        }

        label {
            display: block;
            margin-top: 18px;
            font-weight: bold;
        }

        input {
            box-sizing: border-box;
            width: 100%;
            padding: 12px;
            margin-top: 8px;
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            background: #c62828;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .message {
            padding: 12px;
            margin: 15px 0;
            border-radius: 5px;
        }

        .error {
            background: #ffebee;
            color: #b71c1c;
        }

        .success {
            background: #e8f5e9;
            color: #1b5e20;
        }

        a {
            display: block;
            text-align: center;
            margin-top: 20px;
        }

        .warning {
            color: #b42318;
            font-size: 14px;
            line-height: 1.5;
        }
    </style>
</head>

<body>
<div class="container">

    <h2>Delete Student</h2>

    <p class="warning">
        Warning: Deletion is permanent. Students with pending fees
        cannot be deleted. Paid bill records will also be removed.
    </p>

    <?php if ($message !== ""): ?>
        <div class="message <?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form method="POST"
          onsubmit="return confirm('Are you sure? This action permanently deletes the student and their bill records if no fees are pending.');">

        <label for="roll_number">Student Roll Number</label>

        <input
            type="text"
            id="roll_number"
            name="roll_number"
            placeholder="Enter student roll number"
            value="<?php echo htmlspecialchars($roll_number); ?>"
            required
        >

        <button type="submit">Check and Delete Student</button>
    </form>

    <a href="admin_dashboard.php">Back to Admin Dashboard</a>

</div>
</body>
</html>
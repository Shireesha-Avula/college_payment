<?php
session_start();
require "db.php";

// Allow only admin
if (
    !isset($_SESSION["admin_id"]) ||
    ($_SESSION["role"] ?? "") !== "admin"
) {
    header("Location: ../index.php");
    exit();
}

// Fetch all students
$sql = "SELECT id, roll_number, name, course, email
        FROM students
        ORDER BY roll_number ASC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View All Students</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f4f9;
            margin: 0;
            padding: 25px;
        }

        .container {
            max-width: 1100px;
            margin: 20px auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 3px 12px #0002;
            overflow-x: auto;
        }

        h2 {
            text-align: center;
            color: #243b64;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #243b64;
            color: white;
        }

        tr:nth-child(even) {
            background: #f5f7fb;
        }

        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 16px;
            background: #243b64;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .empty {
            text-align: center;
            padding: 20px;
        }
    </style>
</head>

<body>
<div class="container">

    <h2>All Registered Students</h2>

    <table>
        <tr>
            <th>S.No.</th>
            <th>Roll Number</th>
            <th>Name</th>
            <th>Course</th>
            <th>Email</th>
        </tr>

        <?php if ($result && $result->num_rows > 0): ?>
            <?php $i = 1; ?>

            <?php while ($student = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo htmlspecialchars($student["roll_number"]); ?></td>
                    <td><?php echo htmlspecialchars($student["name"]); ?></td>
                    <td><?php echo htmlspecialchars($student["course"] ?? ""); ?></td>
                    <td><?php echo htmlspecialchars($student["email"] ?? ""); ?></td>
                </tr>
            <?php endwhile; ?>

        <?php else: ?>
            <tr>
                <td colspan="5" class="empty">No students found.</td>
            </tr>
        <?php endif; ?>
    </table>

    <a href="admin_dashboard.php">Back to Admin Dashboard</a>

</div>
</body>
</html>

<?php
$conn->close();
?>
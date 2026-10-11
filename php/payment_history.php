<?php
session_start();

if (!isset($_SESSION["student_id"])) {
    header("Location: ../index.php");
    exit();
}

include "db.php";

$roll_number = $_SESSION["roll_number"];

$sql = "SELECT * FROM bills
        WHERE roll_number = ?
        AND status='paid'
        ORDER BY due_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $roll_number);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment History</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f4f8;
            padding: 25px;
        }

        h2 {
            text-align: center;
            color: #174a7e;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 12px;
            text-align: center;
        }

        th {
            background: #174a7e;
            color: white;
        }
    </style>
</head>
<body>

<h2>Payment History</h2>

<table>
    <tr>
        <th>Bill ID</th>
        <th>Bill Type</th>
        <th>Amount</th>
        <th>Due Date</th>
        <th>Status</th>
    </tr>

    <?php while ($bill = $result->fetch_assoc()) { ?>
    <tr>
        <td><?php echo (int)$bill["id"]; ?></td>
        <td><?php echo htmlspecialchars($bill["bill_type"]); ?></td>
        <td>₹<?php echo number_format((float)$bill["amount"], 2); ?></td>
        <td><?php echo htmlspecialchars($bill["due_date"]); ?></td>
        <td><?php echo htmlspecialchars($bill["status"]); ?></td>
    </tr>
    <?php } ?>
</table>

<br>
<a href="dashboard.php">Back to Dashboard</a>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
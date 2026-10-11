<?php

session_start();

if (!isset($_SESSION["student_id"])) {
    header("Location: ../index.php");
    exit();
}

$student_name = $_SESSION["student_name"];
$roll_number = $_SESSION["roll_number"];
$course = $_SESSION["course"];
include "db.php";

$sql = "SELECT
        SUM(CASE WHEN status = 'Pending' THEN amount ELSE 0 END) AS pending_total,
        SUM(CASE WHEN status = 'Paid' THEN amount ELSE 0 END) AS paid_total
        FROM bills
        WHERE roll_number = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $roll_number);
$stmt->execute();
$totals = $stmt->get_result()->fetch_assoc();

$pending_total = $totals["pending_total"] ?? 0;
$paid_total = $totals["paid_total"] ?? 0;

$stmt->close();

?>

<!DOCTYPE html>
<html>

<head>

    <title>Student Dashboard</title>

    <style>

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #eef3f8;
        }

        .header {
            background-color: #1f4e78;
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 25px;
            font-weight: bold;
        }

        .welcome {
            text-align: center;
            margin: 30px;
        }

        .welcome h2 {
            color: #1f4e78;
        }

        .student-info {
            width: 80%;
            margin: auto;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 8px #aaa;
        }

        .student-info p {
            font-size: 17px;
        }

        .menu {
            width: 80%;
            margin: 30px auto;
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .menu a {
            text-decoration: none;
            background-color: #1f4e78;
            color: white;
            padding: 15px 30px;
            border-radius: 5px;
        }

        .menu a:hover {
            background-color: #163a5a;
        }

    </style>

</head>

<body>

    <div class="header">
        ONLINE BILL PAYMENT
    </div>

    <div class="welcome">

        <h2>Welcome, <?php echo htmlspecialchars($student_name); ?>!</h2>

        <div class="student-info">

            <p>
                <strong>Roll Number:</strong>
                <?php echo htmlspecialchars($roll_number); ?>
            </p>

            <p>
                <strong>Course:</strong>
                <?php echo htmlspecialchars($course); ?>
            </p>

        </div>

    </div>
    <div style="display:flex; gap:20px; flex-wrap:wrap; margin:20px 0;">

    <div style="background:#fff3cd; padding:20px; border-radius:10px; flex:1;">
        <h3>Pending Amount</h3>
        <h2>₹<?php echo number_format((float)$pending_total, 2); ?></h2>
    </div>

    <div style="background:#d1e7dd; padding:20px; border-radius:10px; flex:1;">
        <h3>Paid Amount</h3>
        <h2>₹<?php echo number_format((float)$paid_total, 2); ?></h2>
    </div>

</div>

    <div class="menu">

        <a href="view_bills.php">View Bills</a>

        <a href="pay_bill.php">Pay Bill</a>

        <a href="payment_history.php">Payment History</a>

        <a href="logout.php">Logout</a>

    </div>

</body>

</html>
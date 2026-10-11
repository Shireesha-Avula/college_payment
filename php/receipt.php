<?php
session_start();

if (
    !isset($_SESSION["student_id"]) ||
    ($_SESSION["role"] ?? "") !== "student"
) {
    header("Location: ../index.php");
    exit();
}

require "db.php";

$roll_number = $_SESSION["roll_number"];
$receipt_number = trim($_GET["receipt_number"] ?? "");

if ($receipt_number === "") {
    exit("Invalid receipt request.");
}

// Fetch receipt and matching bill details
$sql = "SELECT
            r.receipt_number,
            r.roll_number,
            r.amount,
            r.payment_date,
            b.bill_type,
            s.name,
            s.course
        FROM payment_receipts r
        INNER JOIN bills b ON r.bill_id = b.id
        INNER JOIN students s ON r.roll_number = s.roll_number
        WHERE r.receipt_number = ?
        AND r.roll_number = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $receipt_number, $roll_number);
$stmt->execute();

$receipt = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if (!$receipt) {
    exit("Receipt not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Payment Receipt</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f4f8;
            padding: 25px;
        }

        .receipt {
            max-width: 650px;
            margin: 20px auto;
            background: white;
            padding: 30px;
            border: 2px solid #174a7e;
            border-radius: 10px;
        }

        h2 {
            text-align: center;
            color: #174a7e;
        }

        .subtitle {
            text-align: center;
            color: #555;
        }

        .success {
            text-align: center;
            color: #198754;
            font-weight: bold;
            margin: 25px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 13px 8px;
            border-bottom: 1px solid #ddd;
        }

        td:first-child {
            font-weight: bold;
            width: 45%;
        }

        .amount {
            color: #198754;
            font-size: 22px;
            font-weight: bold;
        }

        .buttons {
            text-align: center;
            margin-top: 25px;
        }

        button, .link-button {
            display: inline-block;
            padding: 11px 16px;
            margin: 5px;
            border: none;
            border-radius: 5px;
            background: #174a7e;
            color: white;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .receipt {
                border: 1px solid #777;
                margin: 0 auto;
                box-shadow: none;
            }

            .buttons {
                display: none;
            }
        }
    </style>
</head>

<body>

<div class="receipt">

    <h2>COLLEGE ONLINE BILL PAYMENT</h2>
    <p class="subtitle">Official Fee Payment Receipt</p>

    <hr>

    <p class="success">✓ DEMO PAYMENT SUCCESSFUL</p>

    <table>
        <tr>
            <td>Receipt Number</td>
            <td><?php echo htmlspecialchars($receipt["receipt_number"]); ?></td>
        </tr>

        <tr>
            <td>Student Name</td>
            <td><?php echo htmlspecialchars($receipt["name"]); ?></td>
        </tr>

        <tr>
            <td>Roll Number</td>
            <td><?php echo htmlspecialchars($receipt["roll_number"]); ?></td>
        </tr>

        <tr>
            <td>Course</td>
            <td><?php echo htmlspecialchars($receipt["course"] ?? ""); ?></td>
        </tr>

        <tr>
            <td>Bill Name</td>
            <td><?php echo htmlspecialchars($receipt["bill_type"]); ?></td>
        </tr>

        <tr>
            <td>Amount Paid</td>
            <td class="amount">
                ₹<?php echo number_format((float)$receipt["amount"], 2); ?>
            </td>
        </tr>

        <tr>
            <td>Payment Date</td>
            <td><?php echo htmlspecialchars($receipt["payment_date"]); ?></td>
        </tr>
    </table>

    <p class="subtitle">
        This receipt records a simulated payment for a college project.
    </p>

    <div class="buttons">
        <button onclick="window.print()">Print / Save as PDF</button>

        <a class="link-button" href="payment_history.php">
            Payment History
        </a>

        <a class="link-button" href="dashboard.php">
            Dashboard
        </a>
    </div>

</div>

</body>
</html>
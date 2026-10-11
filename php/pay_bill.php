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
$message = "";
$messageType = "";

// Process demo payment
if ($_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["bill_id"])) {

    $bill_id = (int) $_POST["bill_id"];

    if ($bill_id <= 0) {
        $message = "Invalid bill selected.";
        $messageType = "error";
    } else {
        try {
            $conn->begin_transaction();

            // Lock and verify the pending bill
            $sql = "SELECT id, bill_type, amount
                    FROM bills
                    WHERE id = ?
                    AND roll_number = ?
                    AND status = 'Pending'
                    FOR UPDATE";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $bill_id, $roll_number);
            $stmt->execute();

            $bill = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$bill) {
                throw new Exception(
                    "Bill not found or already paid."
                );
            }

            // Update bill status
            $sql = "UPDATE bills
                    SET status = 'Paid'
                    WHERE id = ?
                    AND roll_number = ?
                    AND status = 'Pending'";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $bill_id, $roll_number);
            $stmt->execute();

            if ($stmt->affected_rows !== 1) {
                $stmt->close();
                throw new Exception("Payment could not be completed.");
            }

            $stmt->close();

            // Generate a unique receipt number
            $receipt_number = "RCP-" .
                date("YmdHis") . "-" .
                strtoupper(bin2hex(random_bytes(4)));

            // Save receipt details
            $sql = "INSERT INTO payment_receipts
                    (receipt_number, bill_id, roll_number, amount)
                    VALUES (?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "sisd",
                $receipt_number,
                $bill_id,
                $roll_number,
                $bill["amount"]
            );
            $stmt->execute();
            $stmt->close();

            // Commit payment and receipt together
            $conn->commit();

            // Open the generated receipt
            header(
                "Location: receipt.php?receipt_number=" .
                urlencode($receipt_number)
            );
            exit();

        } catch (Throwable $e) {
            $conn->rollback();

            $message = "Payment could not be completed. " .
                       "Please check the bill and try again.";
            $messageType = "error";
        }
    }
}

// Display only pending bills
$sql = "SELECT id, bill_type, amount, due_date, status
        FROM bills
        WHERE roll_number = ?
        AND status = 'Pending'
        ORDER BY due_date ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $roll_number);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Pay College Bills</title>

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

        .container {
            max-width: 1000px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            overflow-x: auto;
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

        button {
            background: #198754;
            color: white;
            border: none;
            padding: 9px 14px;
            cursor: pointer;
            border-radius: 4px;
        }

        .message {
            padding: 12px;
            text-align: center;
            font-weight: bold;
        }

        .error {
            color: #b42318;
        }

        .empty {
            text-align: center;
            padding: 20px;
        }

        a {
            color: #174a7e;
        }
    </style>
</head>

<body>
<div class="container">

    <h2>Pay College Bills</h2>

    <?php if ($message !== ""): ?>
        <p class="message <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <p>
        Note: This is a demo payment system.
        No real money will be transferred.
    </p>

    <table>
        <tr>
            <th>Bill Name</th>
            <th>Amount</th>
            <th>Due Date</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        <?php if ($result->num_rows > 0): ?>

            <?php while ($bill = $result->fetch_assoc()): ?>
                <tr>
                    <td>
                        <?php echo htmlspecialchars($bill["bill_type"]); ?>
                    </td>

                    <td>
                        ₹<?php echo number_format(
                            (float)$bill["amount"], 2
                        ); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($bill["due_date"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($bill["status"]); ?>
                    </td>

                    <td>
                        <form method="POST"
                              action="pay_bill.php"
                              onsubmit="return confirm(
                                'Confirm this demo payment?'
                              );">

                            <input type="hidden"
                                   name="bill_id"
                                   value="<?php echo (int)$bill["id"]; ?>">

                            <button type="submit">Pay Now</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>

        <?php else: ?>

            <tr>
                <td colspan="5" class="empty">
                    No pending bills available.
                </td>
            </tr>

        <?php endif; ?>
    </table>

    <br>
    <a href="dashboard.php">Back to Dashboard</a>

</div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
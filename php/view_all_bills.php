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

$roll_number = trim($_GET["roll_number"] ?? "");
$bills = null;
$message = "";

if (isset($_GET["roll_number"])) {
    if ($roll_number === "") {
        $message = "Please enter a student roll number.";
    } else {
        $check = $conn->prepare(
            "SELECT id FROM students WHERE roll_number = ?"
        );
        $check->bind_param("s", $roll_number);
        $check->execute();
        $student_result = $check->get_result();

        if ($student_result->num_rows === 0) {
            $message = "No student found with this roll number.";
        } else {
            $sql = "SELECT id, bill_type, amount, due_date, status
                    FROM bills
                    WHERE roll_number = ?
                    ORDER BY due_date DESC";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $roll_number);
            $stmt->execute();
            $bills = $stmt->get_result();
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
    <title>View Student Bills</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eef3f8;
            margin: 0;
            padding: 25px;
        }

        .container {
            max-width: 1000px;
            margin: 20px auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            overflow-x: auto;
        }

        h2 {
            text-align: center;
            color: #174a7e;
        }

        form {
            display: flex;
            gap: 10px;
            margin: 20px 0;
        }

        input {
            flex: 1;
            min-width: 0;
            padding: 11px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background: #174a7e;
            color: white;
            padding: 11px 18px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }

        th {
            background: #174a7e;
            color: white;
        }

        .pending {
            color: #b45309;
            font-weight: bold;
        }

        .paid {
            color: #198754;
            font-weight: bold;
        }

        .message {
            text-align: center;
            font-weight: bold;
        }

        @media (max-width: 550px) {
            form {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>

<div class="container">
    <h2>View Student Bills</h2>

    <form method="GET" action="view_all_bills.php">
        <input
            type="text"
            name="roll_number"
            placeholder="Enter student roll number"
            value="<?php echo htmlspecialchars($roll_number); ?>"
            required
        >
        <button type="submit">Search</button>
    </form>

    <?php if ($message !== "") { ?>
        <p class="message">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php } ?>

    <?php if ($bills !== null) { ?>
        <h3>
            Bills for <?php echo htmlspecialchars($roll_number); ?>
        </h3>

        <table>
            <tr>
                <th>Bill ID</th>
                <th>Bill Type</th>
                <th>Amount</th>
                <th>Due Date</th>
                <th>Status</th>
            </tr>

            <?php if ($bills->num_rows > 0) { ?>
                <?php while ($bill = $bills->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo (int)$bill["id"]; ?></td>
                        <td><?php echo htmlspecialchars($bill["bill_type"]); ?></td>
                        <td>₹<?php echo number_format((float)$bill["amount"], 2); ?></td>
                        <td><?php echo htmlspecialchars($bill["due_date"]); ?></td>
                        <td class="<?php echo $bill["status"] === "Paid" ? "paid" : "pending"; ?>">
                            <?php echo htmlspecialchars($bill["status"]); ?>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="5">No bills found for this student.</td>
                </tr>
            <?php } ?>
        </table>
    <?php } ?>

    <br>
    <a href="admin_dashboard.php">Back to Admin Dashboard</a>
</div>

</body>
</html>

<?php
if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
?>
<?php

session_start();

if (!isset($_SESSION["student_id"])) {
    header("Location: ../index.html");
    exit();
}

include "db.php";

$roll_number = $_SESSION["roll_number"];

$sql = "SELECT * FROM bills
        WHERE roll_number = ?
        ORDER BY due_date ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $roll_number);
$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html>

<head>

    <title>My Bills</title>

    <style>

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #eef3f8;
        }

        .header {
            background-color: #1f4e78;
            color: white;
            text-align: center;
            padding: 20px;
            font-size: 25px;
            font-weight: bold;
        }

        .container {
            width: 90%;
            margin: 40px auto;
        }

        h2 {
            text-align: center;
            color: #1f4e78;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 0 8px #aaa;
        }

        th {
            background-color: #1f4e78;
            color: white;
            padding: 14px;
        }

        td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        .pending {
            color: red;
            font-weight: bold;
        }

        .paid {
            color: green;
            font-weight: bold;
        }

        .back {
            display: block;
            width: 150px;
            margin: 30px auto;
            padding: 12px;
            text-align: center;
            background-color: #1f4e78;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .back:hover {
            background-color: #163a5a;
        }

    </style>

</head>

<body>

    <div class="header">
        ONLINE BILL PAYMENT
    </div>

    <div class="container">

        <h2>My Bills</h2>

        <?php if ($result->num_rows > 0) { ?>

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

                        <td>
                            <?php echo $bill["id"]; ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($bill["bill_type"]); ?>
                        </td>

                        <td>
                            ₹<?php echo number_format($bill["amount"], 2); ?>
                        </td>

                        <td>
                            <?php echo $bill["due_date"]; ?>
                        </td>

                        <td>

                            <?php if ($bill["status"] == "Paid") { ?>

                                <span class="paid">Paid</span>

                            <?php } else { ?>

                                <span class="pending">Pending</span>

                            <?php } ?>

                        </td>

                    </tr>

                <?php } ?>

            </table>

        <?php } else { ?>

            <p style="text-align:center;">
                No bills found.
            </p>

        <?php } ?>

        <a class="back" href="dashboard.php">
            Back to Dashboard
        </a>

    </div>

</body>

</html>
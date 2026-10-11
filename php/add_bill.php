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
$roll_number = trim($_GET["roll_number"] ?? "");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $roll_number = trim($_POST["roll_number"] ?? "");
    $bill_type = trim($_POST["bill_type"] ?? "");
    $amount = $_POST["amount"] ?? "";
    $due_date = $_POST["due_date"] ?? "";

    if (
        $roll_number === "" ||
        $bill_type === "" ||
        !is_numeric($amount) ||
        (float)$amount <= 0 ||
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $due_date)
    ) {
        $message = "Please enter valid bill details.";
    } else {
        // Confirm that the student exists.
        $check = $conn->prepare(
            "SELECT id FROM students WHERE roll_number = ?"
        );
        $check->bind_param("s", $roll_number);
        $check->execute();
        $student_result = $check->get_result();

        if ($student_result->num_rows === 0) {
            $message = "Student roll number not found.";
        } else {
            $amount_value = (float)$amount;

            $insert = $conn->prepare(
                "INSERT INTO bills
                (roll_number, bill_type, amount, due_date, status)
                VALUES (?, ?, ?, ?, 'Pending')"
            );

            $insert->bind_param(
                "ssds",
                $roll_number,
                $bill_type,
                $amount_value,
                $due_date
            );

            if ($insert->execute()) {
                $message = "Bill added successfully!";
            } else {
                $message = "Unable to add the bill.";
            }

            $insert->close();
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
    <title>Add Bill for Student</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eef3f8;
            margin: 0;
            padding: 25px;
        }

        .container {
            background: white;
            max-width: 500px;
            margin: 30px auto;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 3px 12px #00000015;
        }

        h2 {
            color: #174a7e;
            text-align: center;
        }

        label {
            display: block;
            margin-top: 15px;
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
    <h2>Add Bill for Student</h2>

    <p class="message">
        <?php echo htmlspecialchars($message); ?>
    </p>

    <form method="POST" action="add_bill.php">
        <label for="roll_number">Student Roll Number</label>
        <input
            type="text"
            id="roll_number"
            name="roll_number"
            value="<?php echo htmlspecialchars($roll_number); ?>"
            required
        >

        <label for="bill_type">Bill Type</label>
        <select id="bill_type" name="bill_type" required>
            <option value="">Select Bill Type</option>
            <option value="Tuition Fee">Tuition Fee</option>
            <option value="Examination Fee">Examination Fee</option>
            <option value="Library Fee">Library Fee</option>
            <option value="Hostel Fee">Hostel Fee</option>
            <option value="Other Fee">Other Fee</option>
        </select>

        <label for="amount">Amount (₹)</label>
        <input
            type="number"
            id="amount"
            name="amount"
            min="0.01"
            step="0.01"
            required
        >

        <label for="due_date">Due Date</label>
        <input
            type="date"
            id="due_date"
            name="due_date"
            required
        >

        <button type="submit">Add Bill</button>
    </form>

    <p><a href="admin_dashboard.php">Back to Admin Dashboard</a></p>
</div>

</body>
</html>

<?php $conn->close(); ?>
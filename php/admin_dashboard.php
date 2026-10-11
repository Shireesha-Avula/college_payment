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

$student = null;
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "GET"
    && isset($_GET["roll_number"])) {

    $roll_number = trim($_GET["roll_number"]);

    if ($roll_number === "") {
        $message = "Please enter a roll number.";
    } else {
        $sql = "SELECT id, roll_number, name, course, email
                FROM students
                WHERE roll_number = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $roll_number);
        $stmt->execute();

        $result = $stmt->get_result();
        $student = $result->fetch_assoc();

        if (!$student) {
            $message = "No student found with this roll number.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #eef3f8;
        }

        header {
            background: #174a7e;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .layout {
            display: flex;
            gap: 25px;
            padding: 25px;
            flex-wrap: wrap;
        }

        .menu, .content {
            background: white;
            padding: 22px;
            border-radius: 10px;
            box-shadow: 0 3px 10px #00000012;
        }

        .menu {
            flex: 1;
            min-width: 220px;
        }

        .content {
            flex: 2;
            min-width: 280px;
        }

        .menu a {
            display: block;
            padding: 12px;
            margin: 8px 0;
            background: #eaf1f8;
            color: #174a7e;
            text-decoration: none;
            border-radius: 5px;
        }

        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #aaa;
            border-radius: 5px;
        }

        button, .action {
            display: inline-block;
            padding: 10px 14px;
            background: #174a7e;
            color: white;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            margin: 4px 2px;
        }

        .message {
            color: #b42318;
            font-weight: bold;
        }

        .details {
            line-height: 2;
        }
    </style>
</head>

<body>

<header>
    <h1>Admin Dashboard</h1>
    <p>Welcome, <?php
        echo htmlspecialchars($_SESSION["admin_username"]);
    ?></p>
</header>

<div class="layout">

    <aside class="menu">
        <h2>Admin Menu</h2>

        <a href="add_bill.php">Add Bill for Student</a>
        <a href="view_all_bills.php">View Student Bills</a>
        <a href="view_students.php">View All Students</a>
        <a href="add_student.php">Add New Student</a>
        <a href="delete_student.php">Delete Student</a>
        <a href="admin_logout.php">Logout</a>
    </aside>

    <main class="content">
        <h2>Find Student</h2>

        <form method="GET" action="admin_dashboard.php">
            <label for="roll_number">Enter Student Roll Number</label>

            <input
                type="text"
                id="roll_number"
                name="roll_number"
                placeholder="Example: CSE001"
                value="<?php echo htmlspecialchars(
                    $_GET["roll_number"] ?? ""
                ); ?>"
                required
            >

            <button type="submit">Search Student</button>
        </form>

        <?php if ($message !== "") { ?>
            <p class="message">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php } ?>

        <?php if ($student) { ?>
            <hr>

            <h2>Student Details</h2>

            <div class="details">
                <strong>Roll Number:</strong>
                <?php echo htmlspecialchars($student["roll_number"]); ?>
                <br>

                <strong>Name:</strong>
                <?php echo htmlspecialchars($student["name"]); ?>
                <br>

                <strong>Course:</strong>
                <?php echo htmlspecialchars($student["course"] ?? ""); ?>
                <br>

                <strong>Email:</strong>
                <?php echo htmlspecialchars($student["email"] ?? ""); ?>
            </div>

            <h3>Student Operations</h3>

            <a class="action"
               href="add_bill.php?roll_number=<?php
                   echo urlencode($student["roll_number"]);
               ?>">
                Add Bill
            </a>

            <a class="action"
               href="view_all_bills.php?roll_number=<?php
                   echo urlencode($student["roll_number"]);
               ?>">
                View Bills
            </a>
        <?php } ?>
    </main>

</div>

</body>
</html>

<?php $conn->close(); ?>
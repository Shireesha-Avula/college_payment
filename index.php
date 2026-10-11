<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>College Online Bill Payment</title>

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
            text-align: center;
            padding: 22px;
        }

        .login-box {
            background: white;
            width: 90%;
            max-width: 400px;
            margin: 45px auto;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px #00000020;
        }

        h2 {
            text-align: center;
            color: #174a7e;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        input, select {
            width: 100%;
            padding: 12px;
            margin-top: 7px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            width: 100%;
            margin-top: 22px;
            padding: 12px;
            background: #174a7e;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #10375d;
        }
    </style>
</head>

<body>

<header>
    <h1>ONLINE BILL PAYMENT</h1>
    <p>College Fee Management System</p>
</header>

<div class="login-box">
    <h2>Login</h2>

    <form action="php/login.php" method="POST">

        <label for="role">Login As</label>
        <select id="role" name="role" required>
            <option value="student">Student</option>
            <option value="admin">Admin</option>
        </select>

        <label for="username">Roll Number / Username</label>
        <input type="text" id="username" name="username"
               placeholder="Enter roll number or admin username"
               required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password"
               placeholder="Enter password" required>

        <button type="submit">Login</button>

    </form>
</div>

</body>
</html>
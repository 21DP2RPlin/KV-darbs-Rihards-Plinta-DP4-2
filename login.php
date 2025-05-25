<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Your CSS styles */
        body {
            margin: 0;
            padding: 0;
            background-color: #f2f2f2;
        }
        .login-container {
            width: 300px;
            margin: 1px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .login-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .password-toggle {
            position: absolute;
            right: 5px;
            top: 65%;
            transform: translateY(-50%);
            cursor: pointer;
        }

        .form-group button {
            width: 100%;
            padding: 10px;
            background-color: #4caf50;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .form-group button:hover {
            background-color: #45a049;
        }

        .error-container {
            width: 300px; /* same as .login-container */
            margin: 1px auto; /* match vertical spacing */
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            box-sizing: border-box; /* ensures padding doesn't increase width */
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Your navigation bar -->
    <ul id="navbar">
        <li><a href="index.html">Home</a></li>
        <li><a href="contactinfo.html">Kontaktinformācija</a></li>
        <li><a href="ParVietni.html">Par vietni</a></li>
        <li><a href="Map.html">Laukumu karte</a></li>
        <li><a href="profile.php">Profils</a></li>
    </ul>

    <?php
    session_start();
    $servername = "localhost";
    $db_username = "root";
    $db_password = "";
    $database = "noslegumadarbs";

    $conn = new mysqli($servername, $db_username, $db_password, $database);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $error = "";

    // Handle Registration
    if (isset($_POST['register'])) {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Check if user exists
        $stmt = $conn->prepare("SELECT ID FROM user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Username already taken.";
        } else {
            $stmt = $conn->prepare("INSERT INTO user (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $password);
            if ($stmt->execute()) {
                $_SESSION['username'] = $username;
                header("Location: index.html");
                exit();
            } else {
                $error = "Registration failed.";
            }
        }
        $stmt->close();
    }

    // Handle Login
    if (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT password FROM user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($hashed_password);

        if ($stmt->fetch() && password_verify($password, $hashed_password)) {
            $_SESSION['username'] = $username;
            if ($username === "admin") {
                header("Location: data.php"); // admin redirection
            } else {
                header("Location: index.html");
            }
            exit();
        } else {
            $error = "Invalid username or password.";
        }
        $stmt->close();
    }

    $conn->close();
    ?>

    <div class="login-container">
        <h2>Login or Register</h2>
        <form method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <span class="password-toggle" onclick="togglePassword()">👁️</span>
            </div>

            <div class="form-group">
                <button type="submit" name="login">Login</button>
            </div>
            <div class="form-group">
                <button type="submit" name="register">Register</button>
            </div>
        </form>

        <?php if (!empty($error)): ?>
            <div class="error-container">
                <p><?= htmlspecialchars($error) ?></p>
            </div>
        <?php endif; ?>
    </div>


    <!-- Your JavaScript code -->
    <script>
        function togglePassword() {
            var passwordField = document.getElementById("password");
            if (passwordField.type === "password") {
                passwordField.type = "text";
            } else {
                passwordField.type = "password";
            }
        }
    </script>

</body>
</html>

<?php
session_start();

// 1. Not logged in, redirect to login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// 2. Logged in as admin, redirect to data.php
if ($_SESSION['username'] === 'admin') {
    header("Location: data.php");
    exit();
}

// 3. Any other logged-in user: Show message
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Rediģešana</title>
    <link rel="stylesheet" href="style.css">
    <style>
          body {
            background-color: #f8d7da;
            color: #721c24;
            margin: 0;
            padding: 0;
            height: 100vh; /* Ensures the body takes full height of the viewport */
            position: relative; /* This is needed for absolute positioning */
        }

        .message-box {
            background-color: #f5c6cb;
            padding: 30px;
            border: 1px solid #f5c2c7;
            border-radius: 8px;
            text-align: center;
            max-width: 400px;
            position: absolute; /* Allows absolute centering */
            top: 20%; /* Centers vertically */
            left: 50%; /* Centers horizontally */
            transform: translate(-50%, -50%); /* Perfectly center the box */
        }

        .message-box h2 {
            margin-bottom: 15px;
        }

        a {
            display: inline-block;
            text-decoration: underline;
        }

    </style>
</head>
<body>
<div class = "page-container">
    <ul id="navbar">
     <li><a href="index.html">Home</a></li>
     <li><a href="contactinfo.html">Kontaktinformācija</a></li>
     <li><a href="ParVietni.html">Par vietni</a></li>
     <li><a href="Map.html">Laukumu karte</a></li>
     <li><a href="profile.php">Profils</a></li>
    </ul>
    <div class="message-box">
        <h2>Tikai administrators var rediģēt datus</h2>
        <p>Jums nav atļaujas skatīt šo lapu.</p>
        <a href="index.html">Atgriezties uz sākumlapu</a>
    </div>
</body>
</html>

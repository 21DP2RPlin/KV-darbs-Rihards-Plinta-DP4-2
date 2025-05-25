<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$db_username = "root";
$db_password = "";
$database = "noslegumadarbs";
$conn = new mysqli($servername, $db_username, $db_password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$username = $_SESSION['username'];

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save'])) {
    $vards = $_POST['vards'];
    $uzvards = $_POST['uzvards'];
    $vecums = $_POST['vecums'];
    $epasts = $_POST['epasts'];

    $stmt = $conn->prepare("UPDATE user SET `Vārds` = ?, `Uzvārds` = ?, `Vecums` = ?, `E-pasts` = ? WHERE username = ?");
    $stmt->bind_param("ssiss", $vards, $uzvards, $vecums, $epasts, $username);
    $stmt->execute();
}

// Handle account deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    $stmt = $conn->prepare("DELETE FROM user WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    session_destroy();
    header("Location: index.html");
    exit();
}

// Fetch latest user data
$stmt = $conn->prepare("SELECT username, `Vārds`, `Uzvārds`, `Vecums`, `E-pasts` FROM user WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$conn->close();
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Profils</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Styles remain the same as in your original CSS */
        body { background-color: #f2f2f2; margin: 0; padding: 0; }
        .profile-container {
            max-width: 400px; margin: auto; background-color: #fff;
            padding: 30px; border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 { text-align: center; margin-bottom: 20px; }
        .profile-data { margin-bottom: 10px; font-size: 18px; }
        .label { font-weight: bold; font-size: 19px; }
        input[type="text"], input[type="number"], input[type="email"] {
            width: 100%; padding: 8px; margin-top: 5px; margin-bottom: 15px; border-radius: 5px; border: 1px solid #ccc;
        }
        .btn {
            padding: 10px; width: 100%; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;
        }
        .edit-button { background-color: #3498db; color: white; }
        .edit-button:hover { background-color: #2980b9; }
        .save-button { background-color: #2ecc71; color: white; }
        .save-button:hover { background-color: #27ae60; }
        .delete-button { background-color: #e74c3c; color: white; }
        .delete-button:hover { background-color: #c0392b; }
    </style>
    <script>
        function enableEdit() {
            document.getElementById("readonlyView").style.display = "none";
            document.getElementById("editForm").style.display = "block";
            document.getElementById("editButtonWrapper").style.display = "none";
            document.getElementById("deleteBtn").style.display = "block";
            document.getElementById("logoutBtn").style.display = "none";
        }

        function confirmDelete() {
            return confirm("Vai jūs tiešam gribat izdzēst savu kontu?");
        }
        </script>
</head>
<body>
<div class="page-container">
    <ul id="navbar">
        <li><a href="index.html">Home</a></li>
        <li><a href="contactinfo.html">Kontaktinformācija</a></li>
        <li><a href="ParVietni.html">Par vietni</a></li>
        <li><a href="Map.html">Laukumu karte</a></li>
        <li><a href="profile.php">Profils</a></li>
    </ul>

    <div class="profile-container">
        <h2>Jūsu profils</h2>
        <!-- READ-ONLY VIEW -->
        <div id="readonlyView">
            <div class="profile-data"><span class="label">Lietotājvārds:</span> <?= htmlspecialchars($user['username'] ?? '') ?></div>
            <div class="profile-data"><span class="label">Vārds:</span>
                <?= !empty($user['Vārds']) ? htmlspecialchars($user['Vārds']) : 'Nav informācijas' ?>
            </div>
            <div class="profile-data"><span class="label">Uzvārds:</span>
                <?= !empty($user['Uzvārds']) ? htmlspecialchars($user['Uzvārds']) : 'Nav informācijas' ?>
            </div>
            <div class="profile-data"><span class="label">Vecums:</span>
                <?= !empty($user['Vecums']) ? htmlspecialchars($user['Vecums']) : 'Nav informācijas' ?>
            </div>
            <div class="profile-data"><span class="label">E-pasts:</span>
                <?= !empty($user['E-pasts']) ? htmlspecialchars($user['E-pasts']) : 'Nav informācijas' ?>
            </div>
        </div>

        <!-- EDIT FORM (Hidden by default) -->
        <form method="POST" id="editForm" style="display: none;">
            <label class="label">Vārds:</label>
            <input type="text" name="vards" maxlength="50" value="<?= htmlspecialchars($user['Vārds'] ?? '') ?>">

            <label class="label">Uzvārds:</label>
            <input type="text" name="uzvards" maxlength="50" value="<?= htmlspecialchars($user['Uzvārds'] ?? '') ?>">

            <label class="label">Vecums:</label>
            <input type="number" name="vecums" min="0" max="100" value="<?= htmlspecialchars($user['Vecums'] ?? '') ?>">

            <label class="label">E-pasts:</label>
            <input type="email" name="epasts" maxlength="50" value="<?= htmlspecialchars($user['E-pasts'] ?? '') ?>">

            <button type="submit" name="save" class="btn save-button">Saglabāt</button>
        </form>

        <!-- BUTTONS -->
        <div id="editButtonWrapper">
            <button class="btn edit-button" onclick="enableEdit()">Rediģēt</button>
        </div>

        <!-- DELETE ACCOUNT BUTTON (Hidden initially) -->
        <div id="deleteBtn" style="display: none;">
            <form method="POST" onsubmit="return confirmDelete();">
                <button type="submit" name="delete" class="btn delete-button" style="margin-top: 10px;">Izdzēst kontu</button>
            </form>
        </div>

        <!-- LOGOUT BUTTON (Visible only in view mode) -->
        <div id="logoutBtn">
            <form action="logout.php" method="POST">
                <button type="submit" class="btn delete-button" style="margin-top: 10px;">Iziet</button>
            </form>
        </div>

</div>
</body>
</html>

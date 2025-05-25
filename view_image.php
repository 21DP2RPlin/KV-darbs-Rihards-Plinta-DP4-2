<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "noslegumadarbs";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "SELECT photo FROM programmas WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($image);
    $stmt->fetch();

    if ($stmt->num_rows > 0) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($image);

        header("Content-Type: $mime");
        echo $image;
    } else {
        echo "Attēls nav atrasts.";
    }

    $stmt->close();
}

$conn->close();
?>

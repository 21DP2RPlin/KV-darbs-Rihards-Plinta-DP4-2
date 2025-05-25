<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "noslegumadarbs";

// Подключение
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Получаем изображение
    $sql = "SELECT photo FROM programmas WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($image);
    $stmt->fetch();

    if ($stmt->num_rows > 0) {
        // Определяем MIME-тип из бинарных данных
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($image);

        // Получаем расширение из MIME-типа
        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            default => 'bin', // если не удалось определить тип
        };

        // Отдаём файл с корректными заголовками
        header("Content-Type: $mime");
        header("Content-Disposition: attachment; filename=\"downloaded_image.$ext\"");
        echo $image;
    } else {
        echo "No image found.";
    }

    $stmt->close();
}

$conn->close();
?>

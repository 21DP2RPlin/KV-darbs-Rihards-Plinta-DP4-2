<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laukumi pēc rajona</title>
    <link rel="stylesheet" href="../../style.css">
    <style>
        
        .laukumu_container {
            width: 80%;
            margin: 0 auto;
            padding-top: 20px;
        }
        .laukums {
            display: flex;
            background-color: white;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            overflow: hidden;
            min-height: 250px;
        }
        .laukums img {
            width: 350px;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .no-image {
            width: 350px;
            height: 100%;
            background-color: #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            font-size: 16px;
        }
        .info-block {
            padding: 20px;
            flex: 1;
        }
        .info-block h3 {
            margin-top: 0;
            color: #333;
            font-size: 25px; /* header text size */
        }
        .info-block p {
            margin: 10px 0;
            color: #555;
            font-size: 21px; /* info text size */
        }

        
        
    </style>
</head>
<body>
    <ul id="navbar">
        <li><a href="../../index.html">Home</a></li>
        <li><a href="../../contactinfo.html">Kontaktinformācija</a></li>
        <li><a href="../../ParVietni.html">Par vietni</a></li>
        <li><a href="../../Map.html">Laukumu karte</a></li>
        <li><a href="../../admin_check.php">Rediģēt</a></li>
        <li><a href="../../profile.php">Profils</a></li>
    </ul>

    <form method="GET" style="text-align: left; padding-left: 140px; margin-bottom: 5px; padding-top: 50px; font-size: 20px;">
        <label for="sort">Kārtot pēc izmēra:</label>
        <select name="sort" id="sort" onchange="this.form.submit()" style="font-size: 15px; padding: 5px 10px;">
            <option value="">--- Izvēlēties ---</option>
            <option value="asc" <?php if (isset($_GET['sort']) && $_GET['sort'] == 'asc') echo 'selected'; ?>>No mazākā uz lielāko</option>
            <option value="desc" <?php if (isset($_GET['sort']) && $_GET['sort'] == 'desc') echo 'selected'; ?>>No lielākā uz mazāko</option>
        </select>
    </form>

    <div class="laukumu_container">
        <?php
        $servername = "localhost";
        $username = "root";
        $password = "";
        $database = "noslegumadarbs";

        $conn = new mysqli($servername, $username, $password, $database);
        if ($conn->connect_error) {
            die("Savienojuma kļūda: " . $conn->connect_error);
        }

        // Здесь указываем ID нужного района:
        $rajonsID = 33;

        $sortOrder = "";
        if (isset($_GET['sort'])) {
            if ($_GET['sort'] == 'asc') {
                $sortOrder = "ORDER BY FIELD(li.izmers, 'Ļoti maza', 'Maza', 'Vidēja', 'Liela')";
            } elseif ($_GET['sort'] == 'desc') {
                $sortOrder = "ORDER BY FIELD(li.izmers, 'Liela', 'Vidēja', 'Maza', 'Ļoti maza')";
            }
        }


        $sql = "SELECT l.id, p.nosaukums AS pilseta, r.nosaukums AS rajons, l.adrese, li.izmers, l.apraksts, l.bildes
            FROM laukumi l
            JOIN pilseta p ON l.pilseta = p.id
            JOIN rajoni r ON l.rajons = r.id
            JOIN laukumu_izmers li ON l.izmers = li.id
            WHERE l.rajons = $rajonsID
            $sortOrder";


        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='laukums'>";
                
                if (!empty($row['bildes'])) {
                    $imageData = base64_encode($row['bildes']);
                    echo "<img src='data:image/jpeg;base64," . $imageData . "' alt='Laukuma bilde'>";
                } else {
                    echo "<div class='no-image'>Attēls nav pieejams</div>";
                }

                echo "<div class='info-block'>";
                echo "<h3>Laukuma ID: " . $row['id'] . "</h3>";
                echo "<p><strong>Adrese:</strong> " . $row['adrese'] . "</p>";
                echo "<p><strong>Pilsēta:</strong> " . $row['pilseta'] . "</p>";
                echo "<p><strong>Rajons:</strong> " . $row['rajons'] . "</p>";
                echo "<p><strong>Izmērs:</strong> " . $row['izmers'] . "</p>";
                echo "<p><strong>Apraksts:</strong> " . $row['apraksts'] . "</p>";
                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<div style='text-align: center; margin-top: 50px; font-size: 24px; color: #555; font-weight: bold;'>
                    Nav sporta laukumu šajā rajonā.
                  </div>";
        }
        

        $conn->close();
        ?>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Datu ievade</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .data-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 50px;
        }

        .input-group {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .input-group label {
            flex: 1;
        }

        .input-group select, .input-group input {
            flex: 2;
            margin-right: 10px;
            padding: 5px;
        }

        .input-group button {
            padding: 5px 10px;
        }

        .message {
            padding: 10px 15px;
            border-radius: 5px;
            margin-top: -6px;
            font-weight: bold;
            max-width: 500px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .message.success {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
        }

    </style>
</head>
<body>
    <ul id="navbar">
        <li><a href="index.html">Home</a></li>
        <li><a href="contactinfo.html">Kontaktinformācija</a></li>
        <li><a href="ParVietni.html">Par vietni</a></li>
        <li><a href="Map.html">Laukumu karte</a></li>
        <li><a href="profile.php">Profils</a></li>
    </ul>

    <?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "noslegumadarbs";

    $conn = new mysqli($servername, $username, $password, $database);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $message = "";
    $errorMessage = "";

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_image'])) {
        $id = $_POST['id'];
        $stmt = $conn->prepare("UPDATE laukumi SET bildes = NULL WHERE ID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $message = "Attēls ir izdzēsts.";
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_row'])) {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM laukumi WHERE ID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $message = "Ieraksts ar ID: $id ir veiksmīgi izdzēsts.";
        $selectedId = '';
    }

    $selectedId = $_POST['id'] ?? '';
    $adrese = '';
    $apraksts = '';
    $pilseta = '';
    $rajons = '';
    $izmers = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
        $id = $_POST['id'];
        $adrese = $_POST['adrese'];
        $apraksts = $_POST['apraksts'];
        $pilseta = $_POST['pilseta'];
        $rajons = $_POST['rajons'];
        $izmers = $_POST['izmers'];
        $bilde = null;

        $maxFileSize = 5 * 1024 * 1024;

        if (isset($_FILES['bilde']) && $_FILES['bilde']['error'] === UPLOAD_ERR_OK) {
            if ($_FILES['bilde']['size'] > $maxFileSize) {
                $errorMessage = "Kļūda: faila izmērs ir vairāk par 5MB.";
            } else {
                $bilde = file_get_contents($_FILES['bilde']['tmp_name']);
            }
        }

        if ($_FILES['bilde']['error'] === UPLOAD_ERR_NO_FILE || $bilde !== null) {
            if ($bilde) {
                $stmt = $conn->prepare("INSERT INTO laukumi (ID, adrese, apraksts, pilseta, rajons, izmers, bildes)
                                        VALUES (?, ?, ?, ?, ?, ?, ?)
                                        ON DUPLICATE KEY UPDATE adrese = VALUES(adrese), apraksts = VALUES(apraksts), pilseta = VALUES(pilseta), rajons = VALUES(rajons), izmers = VALUES(izmers), bildes = VALUES(bildes)");
                $stmt->bind_param("issssss", $id, $adrese, $apraksts, $pilseta, $rajons, $izmers, $bilde);
            } else {
                $stmt = $conn->prepare("INSERT INTO laukumi (ID, adrese, apraksts, pilseta, rajons, izmers)
                                        VALUES (?, ?, ?, ?, ?, ?)
                                        ON DUPLICATE KEY UPDATE adrese = VALUES(adrese), apraksts = VALUES(apraksts), pilseta = VALUES(pilseta), rajons = VALUES(rajons), izmers = VALUES(izmers)");
                $stmt->bind_param("isssss", $id, $adrese, $apraksts, $pilseta, $rajons, $izmers);
            }

            $stmt->execute();
            $message = "Saglabāts veiksmīgi";
        }
    }

    if (!empty($selectedId)) {
        $stmt = $conn->prepare("SELECT * FROM laukumi WHERE ID = ?");
        $stmt->bind_param("i", $selectedId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $adrese = $row['adrese'];
            $apraksts = $row['apraksts'];

            $stmtPilseta = $conn->prepare("SELECT nosaukums FROM pilseta WHERE ID = ?");
            $stmtPilseta->bind_param("i", $row['pilseta']);
            $stmtPilseta->execute();
            $resultPilseta = $stmtPilseta->get_result();
            $rowPilseta = $resultPilseta->fetch_assoc();
            $pilseta = $rowPilseta ? $rowPilseta['nosaukums'] : '';

            $stmtRajons = $conn->prepare("SELECT nosaukums FROM rajoni WHERE ID = ?");
            $stmtRajons->bind_param("i", $row['rajons']);
            $stmtRajons->execute();
            $resultRajons = $stmtRajons->get_result();
            $rowRajons = $resultRajons->fetch_assoc();
            $rajons = $rowRajons ? $rowRajons['nosaukums'] : '';

            $stmtIzmers = $conn->prepare("SELECT izmers FROM laukumu_izmers WHERE ID = ?");
            $stmtIzmers->bind_param("i", $row['izmers']);
            $stmtIzmers->execute();
            $resultIzmers = $stmtIzmers->get_result();
            $rowIzmers = $resultIzmers->fetch_assoc();
            $izmers = $rowIzmers ? $rowIzmers['izmers'] : '';
        }
    }

    function fetchOptions($conn, $column) {
        $sql = "SELECT DISTINCT $column FROM laukumi ORDER BY $column ASC";
        $result = $conn->query($sql);
        $options = [];
        while ($row = $result->fetch_assoc()) {
            $options[] = $row[$column];
        }
        return $options;
    }
    ?>

    <div class="data-container">
        <form method="post" enctype="multipart/form-data">
            <div class="input-group">
                <label for="id">ID</label>
                <select name="id" onchange="this.form.submit()">
                    <option value="">Izveleties ID</option>
                    <?php
                    $ids = fetchOptions($conn, 'ID');
                    foreach ($ids as $option) {
                        echo "<option" . ($option == $selectedId ? " selected" : "") . ">$option</option>";
                    }
                    ?>
                </select>
                <button type="submit" name="new">Jauns</button>
                <div style="width: 7px;"></div> <!-- replaced button, align data input fields -->
                <?php
                function getNextID($conn) {
                    $stmt = $conn->prepare("SELECT MAX(ID) AS max_id FROM laukumi");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    return $row['max_id'] + 1;
                }

                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    if (isset($_POST['new'])) {
                        $selectedId = getNextID($conn);
                        $stmt = $conn->prepare("INSERT INTO laukumi (ID) VALUES (?)");
                        $stmt->bind_param("i", $selectedId);
                        $stmt->execute();
                        $message = "Jauns ieraksts ir izveidots ar ID: $selectedId";
                    }
                }
                ?>
            </div>
            <div class="input-group">
                <label for="pilseta">Pilseta</label>
                <select name="pilseta">
                    <?php
                    $stmtPilsetaAll = $conn->prepare("SELECT ID, nosaukums FROM pilseta");
                    $stmtPilsetaAll->execute();
                    $resultPilsetaAll = $stmtPilsetaAll->get_result();
                    while ($rowPilsetaAll = $resultPilsetaAll->fetch_assoc()) {
                        $selected = ($rowPilsetaAll['nosaukums'] == $pilseta) ? "selected" : "";
                        echo "<option value='" . $rowPilsetaAll['ID'] . "' $selected>" . $rowPilsetaAll['nosaukums'] . "</option>";
                    }
                    ?>
                </select>
                <div style="width: 66px;"></div> <!-- replaced button, align data input fields -->
            </div>
            <div class="input-group">
                <label for="rajons">Rajons</label>
                <select name="rajons">
                    <?php
                    $stmtRajonsAll = $conn->prepare("SELECT ID, nosaukums FROM rajoni");
                    $stmtRajonsAll->execute();
                    $resultRajonsAll = $stmtRajonsAll->get_result();
                    while ($rowRajonsAll = $resultRajonsAll->fetch_assoc()) {
                        $selected = ($rowRajonsAll['nosaukums'] == $rajons) ? "selected" : "";
                        echo "<option value='" . $rowRajonsAll['ID'] . "' $selected>" . $rowRajonsAll['nosaukums'] . "</option>";
                    }
                    ?>
                </select>
                <div style="width: 66px;"></div> <!-- replaced button, align data input fields -->
            </div>
            <div class="input-group">
                <label for="adrese">Adrese</label>
                <input type="text" name="adrese" maxlength="50" value="<?php echo htmlspecialchars($adrese ?? ''); ?>">
                <button type="button" onclick="clearField('adrese')">Izdzēst</button>
            </div>
            <div class="input-group">
                <label for="izmers">Izmers</label>
                <select name="izmers">
                    <?php
                    $stmtIzmersAll = $conn->prepare("SELECT ID, izmers FROM laukumu_izmers");
                    $stmtIzmersAll->execute();
                    $resultIzmersAll = $stmtIzmersAll->get_result();
                    while ($rowIzmersAll = $resultIzmersAll->fetch_assoc()) {
                        $selected = ($rowIzmersAll['izmers'] == $izmers) ? "selected" : "";
                        echo "<option value='" . $rowIzmersAll['ID'] . "' $selected>" . $rowIzmersAll['izmers'] . "</option>";
                    }
                    ?>
                </select>
                <div style="width: 66px;"></div> <!-- replaced the button, align data input fields -->
            </div>
            <div class="input-group">
                <label for="apraksts">Apraksts</label>
                <input type="text" name="apraksts" maxlength="500" value="<?php echo htmlspecialchars($apraksts ?? ''); ?>">
                <button type="button" onclick="clearField('apraksts')">Izdzēst</button>
            </div>
            <div class="input-group">
                <label for="bilde">Augšupielādēt bildi</label>
                <input type="file" name="bilde" accept="image/*" style="margin-left: -43px;">
            </div>
            <div class="input-group" style="align-items: flex-start;">
                <div>
                    <button type="submit" name="save">Saglabāt</button>
                    <button type="submit" name="delete_row" onclick="return confirm('Vai jus tiešam gribat izdzēst šo ierakstu?');">Dzēst ierakstu</button>
                    <button type="button" onclick="window.location.href='index.html'">Turpināt</button>
                </div>
                <div style="margin-left: 20px;" id="message-box">
                    <?php if (!empty($message)) echo "<div class='message success'>$message</div>"; ?>
                    <?php if (!empty($errorMessage)) echo "<div class='message error'>$errorMessage</div>"; ?>
                </div>
            </div>
        </form>

        <?php
        if (!empty($selectedId)) {
            $stmt = $conn->prepare("SELECT bildes FROM laukumi WHERE ID = ?");
            $stmt->bind_param("i", $selectedId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                if (!empty($row['bildes'])) {
                    $imgData = base64_encode($row['bildes']);
                    echo "<div style='margin-top:10px;'><img src='data:image/jpeg;base64,{$imgData}' style='max-width:300px;'></div>";
                    echo "<form method='post'><input type='hidden' name='id' value='$selectedId'><button type='submit' name='delete_image'>Dzēst attēlu</button></form>";
                }
            }
        }
        ?>
    </div>
    <script>
        function clearField(fieldId) {
            document.getElementsByName(fieldId)[0].value = "";
        }
    </script>
</body>
</html>
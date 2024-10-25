<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "coffee_shop";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$result = $conn->query("SELECT * FROM products");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Menu</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<h1>Coffee Shop Menu</h1>

<form action="order.php" method="POST">
    <div class="menu-container">
        <?php
        while ($row = $result->fetch_assoc()) {
            echo "<div class='menu-item'>
                    <img src='uploads/" . htmlspecialchars($row['image_filename']) . "' alt='" . htmlspecialchars($row['name']) . "' class='menu-image'>
                    <div class='menu-details'>
                        <h2>" . htmlspecialchars($row['name']) . "</h2>
                        <p class='price'>Rp " . number_format($row['price'], 0, ',', '.') . "</p>
                        <p class='stock'>Stock: " . htmlspecialchars($row['stock']) . "</p>
                        <label>
                            <input type='checkbox' name='products[{$row['id']}][selected]' value='1'>
                            Pilih
                        </label>
                        <input type='number' name='products[{$row['id']}][quantity]' placeholder='Jumlah' min='1' max='" . $row['stock'] . "'>
                        <input type='hidden' name='products[{$row['id']}][name]' value='" . htmlspecialchars($row['name']) . "'>
                        <input type='hidden' name='products[{$row['id']}][price]' value='" . $row['price'] . "'>
                        <input type='hidden' name='products[{$row['id']}][id]' value='" . $row['id'] . "'>
                    </div>
                </div>";
        }
        ?>
    </div>
    <button type="submit" class="order-button">Pesan Sekarang</button>
</form>

</body>
</html>

<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "coffee_shop";

// Membuat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Cek apakah produk ada di POST
if (!isset($_POST['products']) || empty($_POST['products'])) {
    die("Tidak ada produk yang dipilih.");
}

$products = $_POST['products'];
$totalOrder = 0;
$orderedItems = [];
$updatedStocks = []; // Array untuk menyimpan stok yang akan diupdate

// Proses setiap produk yang dipesan
foreach ($products as $productId => $product) {
    if (isset($product['selected']) && $product['selected'] == '1' && isset($product['quantity']) && (int)$product['quantity'] > 0) {
        $name = htmlspecialchars($product['name']);
        $price = isset($product['price']) ? (float)$product['price'] : 0;
        $quantity = (int)$product['quantity'];
        $total = $price * $quantity;

        // Cek stok produk
        $stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row && $row['stock'] >= $quantity) {
            $totalOrder += $total;

            $orderedItems[] = [
                'name' => $name,
                'price' => $price,
                'quantity' => $quantity,
                'total' => $total,
            ];

            // Simpan informasi untuk mengupdate stok
            $updatedStocks[$productId] = $quantity;
        } else {
            echo "Stok tidak cukup untuk produk: $name. Produk ini tidak akan diproses.<br>";
        }
    }
}

// Update stok di database jika produk valid
foreach ($updatedStocks as $productId => $quantity) {
    $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
    $stmt->bind_param("ii", $quantity, $productId);
    $stmt->execute();
}

// Menutup koneksi ke database
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Rincian</title>
    <link rel="stylesheet" href="styles.css">

</head>
<body>
<div class="container">
    <h1>Rincian Pesanan</h1>
    <table>
        <tr>
            <th>Nama Produk</th>
            <th>Harga</th>
            <th>Jumlah</th>
            <th>Total</th>
        </tr>
        <?php if (empty($orderedItems)): ?>
            <tr>
                <td colspan="4" style="text-align: center;">Tidak ada produk yang berhasil dipesan.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($orderedItems as $item): ?>
            <tr>
                <td><?php echo $item['name']; ?></td>
                <td>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                <td><?php echo $item['quantity']; ?></td>
                <td>Rp <?php echo number_format($item['total'], 0, ',', '.'); ?></td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="3" style="text-align: right;"><strong>Total Keseluruhan:</strong></td>
                <td>Rp <?php echo number_format($totalOrder, 0, ',', '.'); ?></td>
            </tr>
        <?php endif; ?>
    </table>
    <div style="text-align: center; margin-top: 20px;">
        <button onclick="window.history.back();">Kembali</button>
        <button onclick="alert('Pesanan telah dibuat!');">Konfirmasi Pesanan</button>
    </div>
</div>
</body>
</html>

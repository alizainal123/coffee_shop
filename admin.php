<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "coffee_shop";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Pesan untuk konfirmasi operasi
$message = '';

// Handle delete action
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM products WHERE id = $id");
    $message = "Produk berhasil dihapus.";
    header("Location: admin.php?message=$message");
    exit();
}

// Handle form submission for adding or updating products
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    // Menghilangkan titik dari input harga
    $price = str_replace('.', '', $_POST['price']);
    $stock = $_POST['stock'];
    $image_filename = $_FILES['image']['name']; // Menyimpan nama file gambar
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    // Upload gambar jika ada gambar baru
    if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_filename);
    } else {
        // Jika tidak ada gambar baru, ambil gambar yang sudah ada
        $existing_product = $conn->query("SELECT image_filename FROM products WHERE id = $id")->fetch_assoc();
        $image_filename = $existing_product['image_filename'];
    }

    if ($id) {
        // Update product
        $conn->query("UPDATE products SET name = '$name', price = $price, stock = $stock, image_filename = '$image_filename' WHERE id = $id");
        $message = "Produk berhasil diperbarui.";
    } else {
        // Add new product
        $conn->query("INSERT INTO products (name, price, stock, image_filename) VALUES ('$name', $price, $stock, '$image_filename')");
        $message = "Produk berhasil ditambahkan.";
    }

    // Redirect to the same page after processing
    header("Location: admin.php?message=$message");
    exit();
}

// Fetch products
$result = $conn->query("SELECT * FROM products");

// Handle edit action
$product = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $product = $conn->query("SELECT * FROM products WHERE id = $id")->fetch_assoc();
}

// Menampilkan pesan konfirmasi jika ada
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Daftar Produk</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<h1>Tambah/Edit Produk</h1>

<!-- Tampilkan pesan konfirmasi -->
<?php if ($message): ?>
    <div class="alert"><?php echo $message; ?></div>
<?php endif; ?>

<!-- Form untuk Menambah/Edit Produk -->
<form action="admin.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo isset($product['id']) ? $product['id'] : ''; ?>">
    <input type="text" name="name" placeholder="Nama Produk" value="<?php echo isset($product['name']) ? $product['name'] : ''; ?>" required>
    <input type="text" name="price" placeholder="Harga (contoh: 25.000)" value="<?php echo isset($product['price']) ? number_format($product['price'], 0, ',', '.') : ''; ?>" required>
    <input type="number" name="stock" placeholder="Stok" value="<?php echo isset($product['stock']) ? $product['stock'] : ''; ?>" required>
    <input type="file" name="image" <?php echo isset($product['image_filename']) ? '' : 'required'; ?>>
    <?php if (isset($product['image_filename'])): ?>
        <p>Gambar saat ini: <img src="uploads/<?php echo $product['image_filename']; ?>" alt="<?php echo $product['name']; ?>" style="width: 100px; height: auto;"></p>
    <?php endif; ?>
    <button type="submit">Simpan</button>
</form>

<table>
    <tr>
        <th>ID</th>
        <th>Nama</th>
        <th>Harga</th>
        <th>Stok</th>
        <th>Gambar</th>
        <th>Aksi</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['name']; ?></td>
            <td>Rp <?php echo number_format($row['price'], 0, ',', '.'); ?></td>
            <td><?php echo $row['stock']; ?></td>
            <td>
                <img src="uploads/<?php echo $row['image_filename']; ?>" alt="<?php echo $row['name']; ?>" style="width: 100px; height: auto;">
            </td>
            <td>
                <a href="admin.php?edit=<?php echo $row['id']; ?>">Edit</a> |
                <a href="admin.php?delete=<?php echo $row['id']; ?>">Hapus</a>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

</body>
</html>

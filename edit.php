<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "coffee_shop";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Mengambil data produk berdasarkan ID
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $result = $conn->query("SELECT * FROM products WHERE id=$id");
    $product = $result->fetch_assoc();
}

// Fungsi Update Produk
if (isset($_POST['update'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    // Jika gambar diupload, update gambar
    if ($_FILES['image']['name']) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));

        if (in_array($file_extension, $allowed_types)) {
            // Hapus gambar lama
            if (file_exists("uploads/" . $product['image_filename'])) {
                unlink("uploads/" . $product['image_filename']);
            }

            // Upload gambar baru
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_filename = basename($_FILES["image"]["name"]);
                $sql = "UPDATE products SET name='$name', price='$price', stock='$stock', image_filename='$image_filename' WHERE id=$id";
                $conn->query($sql);
            } else {
                echo "Gagal mengupload gambar.";
            }
        } else {
            echo "Format gambar tidak diperbolehkan.";
        }
    } else {
        // Jika tidak ada gambar baru, hanya update nama, harga, dan stock
        $sql = "UPDATE products SET name='$name', price='$price', stock='$stock' WHERE id=$id";
        $conn->query($sql);
    }
    header("Location: admin.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<h1>Edit Produk</h1>

<form action="edit.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data" class="form-edit">
    <input type="text" name="name" placeholder="Nama Produk" value="<?php echo $product['name']; ?>" required>
    <input type="text" name="price" placeholder="Harga" value="<?php echo $product['price']; ?>" required>
    <input type="number" name="stock" placeholder="Stock" value="<?php echo $product['stock']; ?>" required>
    <input type="file" name="image">
    <button type="submit" name="update">Update Produk</button>
</form>

</body>
</html>

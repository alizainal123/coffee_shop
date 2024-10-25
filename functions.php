<?php
// Koneksi ke database
function getConnection() {
    $connection = new mysqli("localhost", "root", "", "coffee_shop");
    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }
    return $connection;
}

// Mendapatkan daftar menu
function getMenuItems() {
    $connection = getConnection();
    $query = "SELECT * FROM products"; // Pastikan nama tabelnya benar
    $result = $connection->query($query);
    $menuItems = [];
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $menuItems[] = $row;
        }
    }
    
    $connection->close();
    return $menuItems;
}

// Membuat pesanan untuk satu produk
function createOrder($customerName, $menu_id, $quantity) {
    $connection = getConnection();

    // Ambil data produk
    $menu_query = "SELECT * FROM products WHERE id = ?"; // Pastikan nama tabelnya benar
    $stmt = $connection->prepare($menu_query);
    $stmt->bind_param("i", $menu_id);
    $stmt->execute();
    $menu_result = $stmt->get_result();
    $menu = $menu_result->fetch_assoc();

    if ($menu && $menu['stock'] >= $quantity) {
        $total_price = $menu['price'] * $quantity;

        // Masukkan customer
        $customer_query = "INSERT INTO customers (name) VALUES (?)";
        $stmt = $connection->prepare($customer_query);
        $stmt->bind_param("s", $customerName);
        $stmt->execute();
        $customer_id = $stmt->insert_id;

        // Masukkan order
        $order_query = "INSERT INTO orders (customer_id, total_price) VALUES (?, ?)";
        $stmt = $connection->prepare($order_query);
        $stmt->bind_param("id", $customer_id, $total_price);
        $stmt->execute();
        $order_id = $stmt->insert_id;

        // Masukkan detail order
        $order_detail_query = "INSERT INTO order_details (order_id, menu_id, quantity, price) VALUES (?, ?, ?, ?)";
        $stmt = $connection->prepare($order_detail_query);
        $stmt->bind_param("iiid", $order_id, $menu_id, $quantity, $total_price);
        $stmt->execute();

        // Kurangi stok
        $new_stock = $menu['stock'] - $quantity;
        $update_stock_query = "UPDATE products SET stock = ? WHERE id = ?"; // Pastikan nama tabelnya benar
        $stmt = $connection->prepare($update_stock_query);
        $stmt->bind_param("ii", $new_stock, $menu_id);
        $stmt->execute();

        $connection->close();
        return true; // Pesanan berhasil
    } else {
        $connection->close();
        return false; // Stok tidak cukup atau produk tidak ditemukan
    }
}
?>

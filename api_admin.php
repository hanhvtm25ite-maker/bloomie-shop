<?php
include 'db.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$data = json_decode(file_get_contents('php://input'), true);

// Thêm hoa mới
if ($action === 'add_product' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $data['product_name'];
    $price = $data['price'];
    $stock = $data['stock'] ?? 0;
    $desc = $data['description'] ?? '';

    try {
        $stmt = $conn->prepare("INSERT INTO products (product_name, price, stock, description) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $price, $stock, $desc]);
        echo json_encode(["status" => "success", "message" => "Đã thêm hoa mới thành công."]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}
// Sửa thông tin hoa
elseif ($action === 'update_product' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $data['product_id'];
    $name = $data['product_name'];
    $price = $data['price'];
    $stock = $data['stock'];

    try {
        $stmt = $conn->prepare("UPDATE products SET product_name = ?, price = ?, stock = ? WHERE product_id = ?");
        $stmt->execute([$name, $price, $stock, $id]);
        echo json_encode(["status" => "success", "message" => "Cập nhật thành công."]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}
// Xóa hoa
elseif ($action === 'delete_product' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $data['product_id'];
    try {
        $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->execute([$id]);
        echo json_encode(["status" => "success", "message" => "Đã xóa sản phẩm."]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}
?>
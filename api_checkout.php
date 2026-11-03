<?php
include 'db.php';
header('Content-Type: application/json');

// Nhận dữ liệu JSON từ file app_2.js
$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($data['items'])) {
    $name = $data['name'];
    $phone = $data['phone'];
    $address = $data['address'];
    $express = $data['express'];
    $coupon = $data['coupon'];
    $items = $data['items'];

    try {
        $conn->beginTransaction();
        
        $subtotal = 0;
        // 1. Tính toán giá tiền thực tế từ Database để chống gian lận
        foreach ($items as &$item) {
            $stmt = $conn->prepare("SELECT price FROM products WHERE product_id = ?");
            $stmt->execute([$item['id']]);
            $db_price = $stmt->fetchColumn();
            
            // Xử lý hệ số giá theo size (giả lập giống logic JS của bạn)
            $multiplier = ($item['size'] === 'L') ? 1.4 : (($item['size'] === 'S') ? 0.8 : 1);
            $unit_price = round($db_price * $multiplier / 10000) * 10000;
            
            $subtotal += ($unit_price * $item['q']);
            $item['unit_price'] = $unit_price; // Lưu lại giá đã tính
        }

        // 2. Xử lý tính toán phí giao hàng & Mã giảm giá
        $discount = ($coupon === 'BLOOM10') ? ($subtotal * 0.1) : 0;
        $after_discount = $subtotal - $discount;
        $free_ship = ($after_discount >= 500000 || $coupon === 'FREESHIP');
        $shipping_fee = ($subtotal == 0 || $free_ship) ? 0 : 30000;
        $express_fee = ($express && $subtotal > 0) ? 20000 : 0;
        
        $total_amount = $after_discount + $shipping_fee + $express_fee;

        // 3. Lưu vào bảng orders
        $stmt = $conn->prepare("INSERT INTO orders (total_amount, status, shipping_address, phone) VALUES (?, 'Pending', ?, ?)");
        $stmt->execute([$total_amount, $address, $phone]);
        $order_id = $conn->lastInsertId();

        // 4. Lưu vào bảng order_details
        $stmt = $conn->prepare("INSERT INTO order_details (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
        foreach ($items as $item) {
            $stmt->execute([$order_id, $item['id'], $item['q'], $item['unit_price']]);
        }

        $conn->commit();
        echo json_encode(["status" => "success", "order_id" => $order_id, "total_paid" => $total_amount]);
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Dữ liệu không hợp lệ"]);
}
?>
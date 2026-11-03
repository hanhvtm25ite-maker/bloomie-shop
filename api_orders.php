<?php
include 'db.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

// Khách hàng tra cứu 1 đơn hàng cụ thể bằng ID (Mã đơn)
if ($action === 'lookup' && isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];
    try {
        $stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ?");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($order) {
            // Lấy thêm chi tiết các hoa trong đơn
            $detail_stmt = $conn->prepare("SELECT od.*, p.product_name FROM order_details od LEFT JOIN products p ON od.product_id = p.product_id WHERE od.order_id = ?");
            $detail_stmt->execute([$order_id]);
            $order['items'] = $detail_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(["status" => "success", "data" => $order]);
        } else {
            echo json_encode(["status" => "error", "message" => "Không tìm thấy mã đơn hàng này."]);
        }
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}
// Lấy toàn bộ lịch sử đơn hàng của 1 user
elseif ($action === 'history' && isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    try {
        $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["status" => "success", "data" => $orders]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}
?>
<?php
include 'db.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$data = json_decode(file_get_contents('php://input'), true);

// Lấy danh sách toàn bộ đơn hàng trong hệ thống (Sắp xếp mới nhất lên đầu)
if ($action === 'get_all_orders') {
    try {
        $stmt = $conn->prepare("SELECT * FROM orders ORDER BY created_at DESC");
        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["status" => "success", "data" => $orders]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}
// Cập nhật trạng thái đơn (Pending, Processing, Shipping, Completed, Cancelled)
elseif ($action === 'update_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $data['order_id'] ?? null;
    $status = $data['status'] ?? null;

    if ($order_id && $status) {
        try {
            $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
            $stmt->execute([$status, $order_id]);
            echo json_encode(["status" => "success", "message" => "Đã cập nhật trạng thái đơn hàng."]);
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Thiếu thông tin đơn hàng."]);
    }
}
?>
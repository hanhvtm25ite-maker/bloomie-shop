<?php
include 'db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? '';

if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $data['name'] ?? '';
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    // Băm mật khẩu (Dùng thuật toán chuẩn của PHP thay cho SHA-256 trên JS)
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Mặc định role_id = 3 (Customer)
        $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, email, role_id) VALUES (?, ?, ?, ?, 3)");
        // Dùng email làm username luôn
        $stmt->execute([$email, $hashed_password, $name, $email]);
        
        echo json_encode(["status" => "success", "message" => "Đăng ký thành công!"]);
    } catch (PDOException $e) {
        // Lỗi 23000 thường là do trùng email (UNIQUE KEY)
        if ($e->getCode() == 23000) {
            echo json_encode(["status" => "error", "message" => "Email này đã được đăng ký!"]);
        } else {
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    }
} 
elseif ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    try {
        // Kết hợp bảng roles để lấy luôn tên quyền (role_name)
        $stmt = $conn->prepare("SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.role_id WHERE u.email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Trả về thông tin an toàn (không kèm password)
            unset($user['password']);
            echo json_encode(["status" => "success", "user" => $user]);
        } else {
            echo json_encode(["status" => "error", "message" => "Email hoặc mật khẩu chưa đúng!"]);
        }
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}
?>
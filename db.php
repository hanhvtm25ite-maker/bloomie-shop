<?php
$host = 'localhost';
$dbname = 'bloomie_shop';
$username = 'root';
$password = ''; // Mặc định XAMPP không có mật khẩu

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Thiết lập chế độ hiển thị lỗi PDO
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Kết nối cơ sở dữ liệu thất bại: " . $e->getMessage());
}
?>
```[cite: 2]

---

### Bước 2: Tạo các file xử lý API cho CRUD (Ví dụ: Quản lý Sản phẩm - `products`)
Tạo file `api_products.php` ở thư mục gốc để xử lý các yêu cầu **Xem (Read), Thêm (Create), Sửa (Update), Xóa (Delete)** từ giao diện web[cite: 1, 2].

```php
<?php
include 'db.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

// 1. READ: Lấy danh sách sản phẩm
if ($action == 'get') {
    try {
        $stmt = $conn->prepare("SELECT * FROM products ORDER BY product_id DESC");
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($products);
    } catch (Exception $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
}

// 2. CREATE: Thêm sản phẩm mới
elseif ($action == 'add' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_name = $_POST['product_name'] ?? '';
    $category_id = $_POST['category_id'] ?? null;
    $price = $_POST['price'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $description = $_POST['description'] ?? '';

    try {
        $stmt = $conn->prepare("INSERT INTO products (product_name, category_id, price, stock, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$product_name, $category_id, $price, $stock, $description]);
        echo json_encode(["status" => "success", "message" => "Thêm sản phẩm thành công!"]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}

// 3. UPDATE: Cập nhật sản phẩm
elseif ($action == 'update' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'] ?? '';
    $product_name = $_POST['product_name'] ?? '';
    $price = $_POST['price'] ?? 0;
    $stock = $_POST['stock'] ?? 0;

    try {
        $stmt = $conn->prepare("UPDATE products SET product_name = ?, price = ?, stock = ? WHERE product_id = ?");
        $stmt->execute([$product_name, $price, $stock, $product_id]);
        echo json_encode(["status" => "success", "message" => "Cập nhật thành công!"]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}

// 4. DELETE: Xóa sản phẩm
elseif ($action == 'delete' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'] ?? '';

    try {
        $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->execute([$product_id]);
        echo json_encode(["status" => "success", "message" => "Xóa thành công!"]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}
?>
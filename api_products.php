<?php
include 'db.php';
header('Content-Type: application/json');

try {
    $stmt = $conn->prepare("SELECT product_id as id, product_name as name, price, stock FROM products");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(["status" => "success", "data" => $products]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
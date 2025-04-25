<?php
require_once 'db.php';
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$cart = $data['cart'];
$paid = intval($data['paid']); // tiền khách đưa

$order_id = time();
$success = false;

if (!empty($cart)) {
    $now = date("Y-m-d H:i:s");

    // Tính tổng tiền trước thuế
    $subtotal = 0;
    foreach ($cart as $item) {
        $subtotal += $item['qty'] * $item['price'];
    }

    $tax_total = floor($subtotal * 0.08);
    $total_with_tax = $subtotal + $tax_total;
    $change = $paid - $total_with_tax;

    foreach ($cart as $item) {
        $name = mysqli_real_escape_string($conn, $item['name']);
        $qty = intval($item['qty']);
        $price = intval($item['price']);
        $total = $qty * $price;
        $tax = floor($total * 0.08);
        $total_with_tax = $total + $tax;

        // Chèn vào bảng sales_history với đúng cột
        $sql = "INSERT INTO sales_history 
           (order_id, created_at, product_name, quantity, total, tax, paid, `change`)
           VALUES 
           ($order_id, '$now', '$name', $qty, $total_with_tax, $tax, $paid, $change)";
    mysqli_query($conn, $sql);
    }

    $success = true;
}

echo json_encode([
    "success" => $success,
    "order_id" => $order_id
]);



<?php 
// index.php - コンビニPOSメイン画面 (Màn hình chính POS combini)

// データベース接続ファイルをインクルード (Nhúng file kết nối CSDL)
require_once "db.php"; 

// 商品リストを取得 (Truy vấn danh sách sản phẩm từ CSDL)
$product_list = [];
$sql = "SELECT * FROM products";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $product_list[] = $row;
    }
}
// 注文完了処理 (Xử lý khi hoàn tất thanh toán - lưu lịch sử)
$message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["cart_data"])) {
    $cart_json = $_POST["cart_data"];
    // JSONデータを配列にデコード (Giải mã dữ liệu JSON thành mảng PHP)
    $cart_items = json_decode($cart_json, true);
    if (!empty($cart_items)) {
        // 現在時刻を取得 (Lấy thời điểm hiện tại)
        $now = date("Y-m-d H:i:s");
        // 各商品を売上履歴に挿入 (Chèn từng sản phẩm vào lịch sử bán hàng)
        foreach ($cart_items as $item) {
            $product_id = intval($item["id"]);
            $quantity   = intval($item["quantity"]);
            // 安全のため、サーバー側でも商品価格を取得 (Lấy giá sản phẩm từ server để đảm bảo tính đúng)
            $res = mysqli_query($conn, "SELECT price FROM products WHERE product_id=$product_id");
            $price_row = mysqli_fetch_assoc($res);
            $price = intval($price_row["price"]);
            // 金額計算 (Tính toán số tiền cho sản phẩm này)
            $subtotal = $price * $quantity;              // 小計 = đơn giá * số lượng
            $tax      = floor($subtotal * 0.08);         // 税額 = 8% của nhỏ tổng (làm tròn xuống Yên)
            $total    = $subtotal + $tax;                // 合計 = 小計 + 税額
            // 売上履歴テーブルに挿入 (Thêm vào bảng lịch sử bán hàng)
            $insert_sql = "INSERT INTO sales_history (sale_time, product_id, quantity, total, tax)
                           VALUES ('$now', $product_id, $quantity, $total, $tax)";
            mysqli_query($conn, $insert_sql);
        }
        $message = "注文を保存しました。";  // Thông báo "Đã lưu đơn hàng."
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>POS システム</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 0;
            background: #f0f0f0;
        }
        .container {
            display: flex;
            height: 100vh;
        }
        .left-panel {
            width: 65%;
            padding: 10px;
            background: #e0f7ff;
        }
        .right-panel {
            width: 35%;
            padding: 10px;
            background: #ffffff;
            border-left: 2px solid #ccc;
        }
        .product-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        .product-item {
            background: #f9f9f9;
            border: 1px solid #ccc;
            border-radius: 8px;
            text-align: center;
            padding: 10px;
            cursor: pointer;
        }
        .product-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
        }
        .product-item p {
            margin: 5px 0;
            font-size: 16px;
        }
        .order-list table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
        }
        .order-list th, .order-list td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
        }
        .total {
            margin-top: 10px;
            text-align: right;
            font-size: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="left-panel">
        <h2>商品リスト</h2>
        <div class="product-grid">
            <div class="product-item" onclick="addToOrder('コーヒー', 150)">
                <img src="images/coffee.jpg" alt="コーヒー">
                <p>コーヒー</p>
                <p>150 円</p>
            </div>
            <div class="product-item" onclick="addToOrder('アイスクリーム', 200)">
                <img src="images/icecream.jpg" alt="アイスクリーム">
                <p>アイスクリーム</p>
                <p>200 円</p>
            </div>
            <div class="product-item" onclick="addToOrder('おにぎり', 120)">
                <img src="images/onigiri.jpg" alt="おにぎり">
                <p>おにぎり</p>
                <p>120 円</p>
            </div>
            <div class="product-item" onclick="addToOrder('サンドイッチ', 250)">
                <img src="images/sandwich.jpg" alt="サンドイッチ">
                <p>サンドイッチ</p>
                <p>250 円</p>
            </div>
        </div>
    </div>
    <div class="right-panel">
        <h2>注文一覧</h2>
        <div class="order-list">
            <table id="orderTable">
                <thead>
                    <tr>
                        <th>商品名</th>
                        <th>数量</th>
                        <th>価格</th>
                        <th>合計</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <div class="total" id="totalAmount">合計: 0 円</div>
    </div>
</div>
<script>
    const order = {};

    function addToOrder(name, price) {
        if (!order[name]) {
            order[name] = { quantity: 1, price: price };
        } else {
            order[name].quantity += 1;
        }
        renderOrder();
    }

    function renderOrder() {
        const tbody = document.querySelector('#orderTable tbody');
        tbody.innerHTML = '';
        let total = 0;
        for (const [name, item] of Object.entries(order)) {
            const tr = document.createElement('tr');
            const itemTotal = item.quantity * item.price;
            total += itemTotal;
            tr.innerHTML = `
                <td>${name}</td>
                <td>${item.quantity}</td>
                <td>${item.price}</td>
                <td>${itemTotal}</td>
            `;
            tbody.appendChild(tr);
        }
        document.getElementById('totalAmount').innerText = `合計: ${total} 円`;
    }
</script>
</body>
</html>



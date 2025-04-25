<?php
require_once 'db.php';
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$paid = isset($_GET['paid']) ? intval($_GET['paid']) : 0;

$sql = "SELECT * FROM sales_history WHERE order_id = $order_id ORDER BY sale_id ASC";


$result = mysqli_query($conn, $sql);

$items = [];
$total = 0;
$tax = 0;
$date = '';

while ($row = mysqli_fetch_assoc($result)) {
    $items[] = $row;
    $total += $row['total'];
    $tax += $row['tax'];
    $date = $row['created_at'];
}
$change = $paid - $total;
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>領収書</title>
    <style>
        body { font-family: monospace; padding: 30px; }
        h2 { text-align: center; }
        .receipt { max-width: 350px; margin: auto; }
        .line { border-top: 1px dashed #000; margin: 10px 0; }
        .right { text-align: right; }
    </style>
</head>
<body>
<div class="receipt">
    <h2>レシート</h2>
    <p>日付: <?= date("Y年n月j日（D） H:i", strtotime($date)) ?></p>
    <div class="line"></div>
    <?php foreach ($items as $item): ?>
        <p><?= $item['product_name'] ?> × <?= $item['quantity'] ?>　<?= number_format($item['total']) ?>円</p>
    <?php endforeach; ?>
    <div class="line"></div>
    <p>消費税 (8%): <?= number_format($tax) ?>円</p>
    <p><strong>合計: <?= number_format($total) ?>円</strong></p>
    <p>お預り: <?= number_format($paid) ?>円</p>
    <p>お釣り: <?= number_format($change) ?>円</p>
    <div class="line"></div>
    <p class="right">ありがとうございました！</p>
    <p style="text-align: center; margin-top: 20px;">
    <a href="index.php" style="text-decoration: none; background: #43a047; color: white; padding: 10px 20px; border-radius: 6px;">← 戻る</a>
</p>
</div>
</body>
</html>


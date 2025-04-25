<?php
require_once 'db.php';
date_default_timezone_set('Asia/Tokyo');
$today = date('Y-m-d');

$result = mysqli_query($conn, "SELECT * FROM sales_history WHERE DATE(created_at) = '$today' ORDER BY sale_id DESC");

$total = 0;
$tax_total = 0;
$paid_total = 0;

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>日別売上</title>
    <style>
        body { font-family: sans-serif; padding: 30px; background: #f5f5f5; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { border: 1px solid #ccc; padding: 8px; font-size: 14px; text-align: center; }
        th { background: #e3f2fd; }
        h2 { margin-bottom: 10px; }
        .back-btn {
            margin-top: 20px;
            display: inline-block;
            padding: 8px 14px;
            background: #43a047;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <h2>📊 本日の売上（<?= date("Y年n月j日（D）") ?>）</h2>
    <table>
        <tr>
            <th>商品名</th>
            <th>数量</th>
            <th>合計（税込）</th>
            <th>税額</th>
            <th>お預り</th>
            <th>お釣り</th>
            <th>時間</th>
        </tr>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= htmlspecialchars($row['product_name']) ?></td>
                <td><?= $row['quantity'] ?></td>
                <td><?= number_format($row['total']) ?>円</td>
                <td><?= number_format($row['tax']) ?>円</td>
                <td><?= number_format($row['paid']) ?>円</td>
                <td><?= number_format($row['change']) ?>円</td>
                <td><?= date("Y年m月d日（D）H:i", strtotime($row['created_at'])) ?></td>
            </tr>
            <?php
                $total += $row['total'];
                $tax_total += $row['tax'];
                $paid_total += $row['paid'];
            ?>
        <?php endwhile; ?>
    </table>

    <h3>💰 総売上（税込）: <?= number_format($total) ?>円</h3>
    <h4>🧾 総税額: <?= number_format($tax_total) ?>円</h4>
    <h4>🧍 総お預り: <?= number_format($paid_total) ?>円</h4>

    <a href="index.php" class="back-btn">← 戻る</a>
</body>
</html>


<?php
require_once 'db.php';
date_default_timezone_set('Asia/Tokyo');
$now = date('Y年m月d日（D） H:i');
$result = mysqli_query($conn, "SELECT * FROM products");
$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>POSシステム</title>
    <link rel="stylesheet" href="style.css?v=2">
</head>
<body>
<div class="container">
    <!-- 左：商品一覧 -->
    <div class="left-panel">
        <h2>全商品</h2>
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-item" onclick="addToCart('<?= $product['name'] ?>', <?= $product['price'] ?>)">
                    <img src="images/<?= $product['image'] ?>" alt="<?= $product['name'] ?>">
                    <p><?= $product['name'] ?></p>
                    <p class="price"><?= number_format($product['price']) ?> 円</p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 右：レジかご -->
<div class="right-panel">
    <h2>レジかご</h2>
    <p class="time-now"><?= $now ?></p>
           
    <div id="cart-list"></div>

    <div class="cart-summary">
        <p>総数量: <span id="total-qty">0</span> 点</p>
        <p><strong>合計金額: <span id="total-amount">0</span> 円</strong></p>
    </div>

    <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 20px;">
        <button onclick="handleCheckout()">支払い</button>
        <button onclick="window.location.href='daily_sales.php'">日別売上を確認</button>
    </div>

</div>


<script>
let cart = [];

function addToCart(name, price) {
    const found = cart.find(item => item.name === name);
    if (found) {
        found.qty += 1;
    } else {
        cart.push({ name, price, qty: 1 });
    }
    renderCart();
}

function increaseQty(name) {
    const item = cart.find(i => i.name === name);
    if (item) item.qty += 1;
    renderCart();
}

function decreaseQty(name) {
    const item = cart.find(i => i.name === name);
    if (item && item.qty > 1) item.qty -= 1;
    renderCart();
}

function removeItem(name) {
    cart = cart.filter(item => item.name !== name);
    renderCart();
}

function renderCart() {
    const cartList = document.getElementById("cart-list");
    cartList.innerHTML = "";
    let total = 0;
    let qtyTotal = 0;

    cart.forEach(item => {
        const itemTotal = item.qty * item.price;
        qtyTotal += item.qty;
        total += itemTotal;

        const div = document.createElement("div");
        div.className = "cart-item";
        div.innerHTML = `
            <div><span class="delete" onclick="removeItem('${item.name}')">❌</span> <strong>${item.name}</strong></div>
            <div>単価: ${item.price}円</div>
            <div>
                数量:
                <button onclick="decreaseQty('${item.name}')">−</button>
                ${item.qty}
                <button onclick="increaseQty('${item.name}')">＋</button>
            </div>
            <div><strong>小計: ${itemTotal}円</strong></div>
            <hr>
        `;
        cartList.appendChild(div);
    });

    document.getElementById("total-qty").innerText = qtyTotal;
    document.getElementById("total-amount").innerText = total;
}


function handleCheckout() {
    if (cart.length === 0) {
        alert("カートが空です。");
        return;
    }
    const amount = prompt("お預り金額を入力してください（円）:");
    if (!amount || isNaN(amount)) {
        alert("金額が無効です。");
        return;
    }

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "save_order.php");
    xhr.setRequestHeader("Content-Type", "application/json");
    xhr.onload = function () {
        const res = JSON.parse(this.responseText);
        if (res.success && res.order_id) {
            window.location.href = "receipt.php?id=" + res.order_id + "&paid=" + amount;
        } else {
            alert("エラーが発生しました。");
        }
    };
    xhr.send(JSON.stringify({ cart, paid: amount })); // ✨ gửi thêm paid
}

</script>
</body>
</html>


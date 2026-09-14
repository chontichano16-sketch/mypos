<?php
require_once "db.php";

if (!isset($_GET['tables_id']) || empty($_GET['tables_id'])) {
    die("กรุณาแสกนคิวอาร์โค้ดที่โต๊ะเพื่อสั่งอาหาร");
}
$tables_id = $_GET['tables_id'];
$type_id = isset($_GET['type_id']) ? $_GET['type_id'] : '';
$sql_type = "SELECT * FROM type";
$result_type = mysqli_query($conn, $sql_type);

if ($type_id != '') {
    $sql = "SELECT * FROM products WHERE type_id = '$type_id' ORDER BY p_id ASC";
} else {
    $sql = "SELECT * FROM products ORDER BY p_id ASC";
}
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สั่งอาหาร - โต๊ะ <?php echo htmlspecialchars($tables_id); ?></title>
    <link rel="stylesheet" href="customer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css" integrity="sha512-QeR2VH+lsBE5LSAe1Q5EnTBbe7XTBubt8dG93Y7gidSgdMCr8nVqKcfKAMyN96SV8KDbZVTDXChatu5G2KQGzg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@100..900&display=swap" rel="stylesheet">
</head>

<body>
    <div class="header-c">
        <img src="img/3.jpg" alt="logo">
        <p>The Story เรื่องเล่าร้านกาแฟ</p>
    </div>
    <h3 class="menu-title">รายการเมนู <i class="fa-solid fa-mug-hot"></i></h3>

    <div class="category-menu">
        <form action="" method="get" class="search-box">
            <button><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>
        <div class="list-menu">
            <button><i class="fa-solid fa-list-ul"></i></button>
        </div>

        <a href="?tables_id=<?php echo htmlspecialchars($tables_id); ?>" class="<?php echo ($type_id == '') ? 'active' : ''; ?>">ทั้งหมด</a>
        <?php while ($type = mysqli_fetch_assoc($result_type)) : ?>
            <a href="?tables_id=<?php echo htmlspecialchars($tables_id); ?>&category_id=<?php echo $type['type_id']; ?>"
                class="<?php echo ($type_id == $type['type_id']) ? 'active' : ''; ?>">
                <?php echo htmlspecialchars($type['type_name']); ?>
            </a>
        <?php endwhile; ?>
    </div>

    <div class="product-grid">
        <?php while ($row = mysqli_fetch_assoc($result)) :
            $placehold = "https://placehold.co/200x200?text=No+Image";
            $img = !empty($row['p_img']) ? 'upload/' . $row['p_img'] : $placehold;
        ?>
            <div class="product-card"
                data-id="<?php echo (int)$row['p_id']; ?>"
                data-name="<?php echo htmlspecialchars($row['p_name'], ENT_QUOTES); ?>"
                data-price="<?php echo (float)$row['p_price']; ?>"
                data-img="<?php echo htmlspecialchars($img, ENT_QUOTES); ?>">

                <img src="<?php echo htmlspecialchars($img); ?>"
                    alt="<?php echo htmlspecialchars($row['p_name']); ?>">

                <div class="product-name"><?php echo htmlspecialchars($row['p_name']); ?></div>
                <div class="product-price"><?php echo number_format($row['p_price'], 0); ?> ฿</div>

                <button type="button" class="btn-add">
                    <i class="fa-solid fa-circle-plus"></i>
                </button>
            </div>
        <?php endwhile; ?>
    </div>

    <input type="hidden" id="tablesId"
        value="<?php echo htmlspecialchars($tables_id, ENT_QUOTES); ?>">

    <!-- แถบตะกร้าด้านล่าง -->
    <!-- Modal เลือกจำนวน / หมายเหตุ -->
    <div class="order-modal" id="orderModal">
        <div class="om-sheet">
            <button class="om-close" onclick="closeOrderModal()">&times;</button>
            <img id="omImg" class="om-img" src="" alt="">
            <h3 id="omName">-</h3>

            <label class="om-label">จำนวน</label>
            <div class="om-qty">
                <button type="button" onclick="stepQty(-1)">−</button>
                <input type="number" id="omQty" value="1" min="1" readonly>
                <button type="button" onclick="stepQty(1)">+</button>
            </div>

            <label class="om-label">หมายเหตุ (ถ้ามี)</label>
            <textarea id="omNote" placeholder="เช่น ไม่ใส่ผัก, เผ็ดน้อย"></textarea>

            <button class="btn-send-order" onclick="confirmOrderModal()">
                เพิ่มลงตะกร้า
            </button>
        </div>
    </div>

    <!-- แถบตะกร้าลอยด้านล่าง -->
    <div class="cart-bar" id="cartBar" onclick="openCartModal()">
        <div class="cb-left">
            <i class="fa-solid fa-cart-shopping"></i>
            <span class="cb-badge" id="cartCount">0</span>
        </div>
        <div class="cb-mid">ดูตะกร้า</div>
        <div class="cb-right"><span id="cartTotal">0</span> ฿</div>
    </div>

    <!-- หน้าต่างตะกร้า -->
    <div class="cart-modal" id="cartModal">
        <div class="cm-sheet">
            <div class="cm-head">
                <h3>รายการที่สั่ง</h3>
                <button class="cm-close" onclick="closeCartModal()">&times;</button>
            </div>

            <div class="cm-body" id="cartItems"></div>

            <div class="cm-foot">
                <div class="cm-total">
                    <span>รวมทั้งหมด</span>
                    <strong><span id="cartTotal2">0</span> ฿</strong>
                </div>
                <button class="btn-send-order" onclick="submitOrder()">
                    ยืนยันสั่งอาหาร
                </button>
            </div>
        </div>
    </div>

    
    <script src="customer.js"></script>

</body>

</html>
<?php
require_once "db.php";

if (!isset($_GET['tables_id']) || empty($_GET['tables_id'])) {
    die("กรุณาแสกนคิวอาร์โค้ดที่โต๊ะเพื่อสั่งอาหาร");
}
$tables_id = $_GET['tables_id'];

// API สำหรับ ค้นหาเมนูแบบ Real-time Dropdown
if (isset($_GET['action']) && $_GET['action'] == 'live_search') {
    header('Content-Type: application/json');
    $query = isset($_GET['q']) ? trim($_GET['q']) : '';

    if ($query !== '') {
        $q_escaped = mysqli_real_escape_string($conn, $query);
        $search_sql = "SELECT p_id, p_name, p_price, p_img FROM products WHERE p_name LIKE '%{$q_escaped}%' ORDER BY p_name ASC LIMIT 8";
        $res = mysqli_query($conn, $search_sql);

        $items = [];
        while ($r = mysqli_fetch_assoc($res)) {
            $img = !empty($r['p_img']) ? 'upload/' . $r['p_img'] : 'https://placehold.co/100x100?text=No+Img';
            $items[] = [
                'id'    => (int)$r['p_id'],
                'name'  => $r['p_name'],
                'price' => (float)$r['p_price'],
                'img'   => $img
            ];
        }
        echo json_encode($items);
    } else {
        echo json_encode([]);
    }
    exit();
}

$type_id = isset($_GET['type_id']) ? $_GET['type_id'] : (isset($_GET['category_id']) ? $_GET['category_id'] : '');
$search  = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql_type = "SELECT * FROM type";
$result_type = mysqli_query($conn, $sql_type);
$result_type_drop = mysqli_query($conn, $sql_type);

$where = [];
if ($type_id != '') {
    $where[] = "type_id = '" . mysqli_real_escape_string($conn, $type_id) . "'";
}
if ($search != '') {
    $where[] = "p_name LIKE '%" . mysqli_real_escape_string($conn, $search) . "%'";
}

$sql = "SELECT * FROM products";
if (count($where) > 0) {
    $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " ORDER BY p_id ASC";

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
        <img src="img/โลโก้สี_n.jpg" alt="logo">
        <p>The Story เรื่องเล่าร้านกาแฟ</p>
    </div>
    <h3 class="menu-title">
        <span>รายการเมนู <i class="fa-solid fa-mug-hot"></i></span>
        <!-- แสดงเลขโต๊ะ -->
        <div class="table-info-badge">
            <i class="fa-solid fa-chair"></i> โต๊ะ : <span id="displayTableNo"><?php echo isset($table_no) ? $table_no : '-'; ?></span>
        </div>
    </h3>


    <div class="category-menu-wrapper">
        <div class="category-menu">
            <!-- ปุ่มแว่นขยายค้นหา -->
            <div class="search-inline-wrapper" id="searchWrapper">
                <button type="button" onclick="toggleInlineSearch()" title="ค้นหาเมนู" style="cursor: pointer; padding: 6px 8px; border: none; background: transparent; font-size: 16px;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
                <form action="" method="get" id="inlineSearchForm" style="display: inline-flex; margin: 0;" onsubmit="return false;">
                    <input type="hidden" name="tables_id" value="<?php echo htmlspecialchars($tables_id); ?>">
                    <?php if ($type_id != '') : ?>
                        <input type="hidden" name="type_id" value="<?php echo htmlspecialchars($type_id); ?>">
                    <?php endif; ?>
                    <input type="text" name="search" id="searchInput" class="search-input-inline" placeholder="พิมพ์ชื่อเมนู..." value="<?php echo htmlspecialchars($search); ?>" autocomplete="off" oninput="handleLiveSearch(this.value)">
                </form>
                <div class="live-search-dropdown" id="liveSearchResults"></div>
            </div>

            <!-- ปุ่ม 3 ขีดประเภทสินค้า -->
            <div class="list-menu">
                <button type="button" onclick="toggleTypeDropdown()" title="เลือกประเภทอาหาร" style="cursor: pointer; padding: 6px 8px; border: none; background: transparent; font-size: 16px;">
                    <i class="fa-solid fa-list-ul"></i>
                </button>
            </div>

            <a href="?tables_id=<?php echo htmlspecialchars($tables_id); ?>" class="<?php echo ($type_id == '' && $search == '') ? 'active' : ''; ?>">ทั้งหมด</a>
            <?php while ($type = mysqli_fetch_assoc($result_type)) : ?>
                <a href="?tables_id=<?php echo htmlspecialchars($tables_id); ?>&type_id=<?php echo $type['type_id']; ?>"
                    class="<?php echo ($type_id == $type['type_id']) ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($type['type_name']); ?>
                </a>
            <?php endwhile; ?>
        </div>

        <div class="type-dropdown-menu" id="typeDropdown">
            <a href="?tables_id=<?php echo htmlspecialchars($tables_id); ?>" class="type-dropdown-item <?php echo ($type_id == '' && $search == '') ? 'active' : ''; ?>">
                <span>ทั้งหมด</span>
                <i class="fa-solid fa-chevron-right" style="font-size: 12px; color: #94a3b8;"></i>
            </a>
            <?php while ($type_drop = mysqli_fetch_assoc($result_type_drop)) : ?>
                <a href="?tables_id=<?php echo htmlspecialchars($tables_id); ?>&type_id=<?php echo $type_drop['type_id']; ?>"
                    class="type-dropdown-item <?php echo ($type_id == $type_drop['type_id']) ? 'active' : ''; ?>">
                    <span><?php echo htmlspecialchars($type_drop['type_name']); ?></span>
                    <i class="fa-solid fa-chevron-right" style="font-size: 12px; color: #94a3b8;"></i>
                </a>
            <?php endwhile; ?>
        </div>
    </div>

    <div class="product-grid" id="productGrid">
        <?php if (mysqli_num_rows($result) > 0) : ?>
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
        <?php endif; ?>
        <p id="noProductsMessage">ไม่พบรายการอาหารที่ค้นหา</p>
    </div>

    <input type="hidden" id="tablesId" value="<?php echo htmlspecialchars($tables_id, ENT_QUOTES); ?>">

    <!-- Modal เลือกจำนวน / หมายเหตุ -->
    <div class="order-modal" id="orderModal">
        <div class="om-sheet">
            <button class="om-close" onclick="closeOrderModal()">&times;</button>
            <img id="omImg" class="om-img" src="" alt="">
            <h3 id="omName">-</h3>

            <div id="omPrice" style="color: #232323; font-size: 18px; font-weight: 500; margin-bottom: 10px; text-align: center;"></div>
            <div id="omOptions" style="display: flex; gap: 8px; justify-content: center; margin-bottom: 15px; flex-wrap: wrap;"></div>

            <label class="om-label">จำนวน</label>
            <div class="om-qty">
                <button type="button" onclick="stepQty(-1)">-</button>
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="customer.js"></script>

</body>

</html>
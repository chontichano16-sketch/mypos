<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Story เรื่องเล่ากาแฟ</title>
    <link rel="stylesheet" href="style2.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>
    <?php include "navbar.php" ?>
    <!-- ปุ่มแฮมเบอร์เกอร์ -->
    <!-- <nav class="navbar">
        <div class="dropdown">
            <button onclick="toggleMenu(event)" class="dropbtn"> &#9776; </button>

            <div id="myDropdown" class="dropdown-content">
                <button class="menu-btn">จัดการข้อมูลโต๊ะ</button>
                <ul class="submenu">
                    <li><a href="create_QR">สร้าง QR Code โตีะ</a></li>
                </ul>
                <button class="menu-btn">จัดการข้อมูลเมนูอาหาร</button>
                <ul class="submenu">
                    <li><a href="add_pro.php">เพิ่มสินค้า</a></li>
                    <li><a href="add_type.php">เพิ่มประเภทสินค้า</a></li>
                    <li><a href="edit_pro.php">แก้ไขสินค้า</a></li>
                </ul>
                <a href="#">รายการสินค้าทั้งหมด</a>
                <a href="#">รายงานยอดขาย</a>
                <a href="#">ออกจากระบบ</a>
            </div>
        </div>

        <ul class="nav-links" id="nav-links">
            <li><a href="index.php" class="active">หน้าร้าน</a></li>
            <li><a href="neworder.php">ออเดอร์ใหม่</a></li>
            <li><a href="#services">เปิดบิล</a></li>
        </ul>
        <form action="search.php" method="get" class="search-box">
            <input type="text" name="keyword" placeholder="ค้นหาเมนู...">
            <button type="submit">ค้นหา</button>
        </form>
    </nav> -->
    <div class="main-container">

        <div class="left-content">

            <div class="menu-list">
                <?php
                require_once 'db.php';

                if (isset($_GET['cate'])) {
                    $cate = $_GET['cate'];

                    $sql = "SELECT * FROM products WHERE type_id = '$cate'";
                } else {
                    $sql = "SELECT * FROM products";
                }
                $result = mysqli_query($conn, $sql);
                while ($row = mysqli_fetch_assoc($result)) {
                ?>
                    <!-- เมนูการ์ด -->
                    <div class="menu-item"
                        data-id="<?php echo $row['p_id']; ?>"
                        data-name="<?php echo htmlspecialchars($row['p_name']) ?>"
                        data-price="<?php echo $row['p_price'] ?>"
                        onclick="openOrderModal(<?php echo $row['p_id']; ?>, '<?php echo htmlspecialchars($row['p_name']); ?>', <?php echo $row['p_price']; ?>)">

                        <?php
                        $placehold = "https://placehold.co/200x200?text=No+Image";
                        $img_src = !empty($row['p_img']) ? "upload/" . $row['p_img'] : $placehold;
                        ?>
                        <img src="<?php echo $img_src; ?>" onerror="this.onerror=null; this.src='<?php echo $placehold; ?>'"
                            alt="<?php echo $row['p_name'] ?>">

                        <div class="overlay">
                            <h3><?php echo $row['p_name'] ?></h3>
                            <h5><?php echo $row['p_price'] ?> บาท</h5>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <!-- =============================================== หมวดหมู่ด้านล่าง ==================================================== -->
            <div class="side-bar-menu">
                <a href="index.php" class="<?php echo !isset($_GET['cate']) ? 'active' : ''; ?>">ทั้งหมด</a>

                <?php
                //ดึงรายชื่อประเภทสินค้าในตาราง type 
                $type_sql = "SELECT * FROM type";
                $type_result = mysqli_query($conn, $type_sql);

                while ($type_row = mysqli_fetch_assoc($type_result)) {
                    $active_class = (isset($_GET['cate']) && $_GET['cate'] == $type_row['type_id']) ? 'active' : ''; ?>

                    <a href="index.php?cate=<?php echo $type_row['type_id']; ?>" class="<?php echo $active_class; ?>">
                        <?php echo $type_row['type_name']; ?>
                    </a>
                <?php } ?>
            </div>

        </div>
        <!--========================================== รายการออเดอร์ฝั่งขวา =========================================-->
        <aside class="order-section">
            <div class="headorder">
                <h3>รายการออเดอร์</h3>
                <div>
                    <label>โต๊ะ : </label>
                    <select name="tables" id="tables" style="font-size: 14px;">
                        <option value="" selected>ไม่ได้เลือก</option>
                        <?php
                        $sql_tables = "SELECT * FROM tables";
                        $query_tabels = mysqli_query($conn, $sql_tables);

                        if (mysqli_num_rows($query_tabels) > 0) {
                            while ($row_table = mysqli_fetch_assoc($query_tabels)) {
                        ?>
                                <option value="<?php echo $row_table['tables_number']; ?>">
                                    T.<?php echo $row_table['tables_number']; ?>
                                </option>
                        <?php
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="order-items-container">รายการที่สั่งจะแสดงที่นี่</div>

            <p style="text-align: right; font-size: 18px; padding-bottom: 10px;"><strong>รวมทั้งหมด 0 บาท</strong></p>
            <div class="action-buttons">
                <button type="submit" class="btn-save" onclick="saveOrder()">บันทึก</button>
                <button type="button" class="btn-pay">ชำระเงิน</button>
            </div>
        </aside>

    </div>
    <!--========================================== popup เพิ่มสินค้า ===============================================-->
    <div id="addProductModal" class="modal-overlay" style="display: none;">

        <div class="modal-content">
            <div class="modal-header">
                <h3 style="color: #63554c;">เพิ่มเมนูใหม่</h3>
                <button class="close-btn-clean" onclick="closeModal()">&times;</button>
            </div>
            <form id="formAddProduct" onsubmit="saveProductAjax(event)" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="p_name">ชื่อเมนู</label>
                    <input type="text" name="p_name" id="p_name">
                </div>

                <div class="form-group">
                    <label for="p_price">ราคา</label>
                    <input type="text" name="p_price" id="p_price">
                </div>

                <div class="form-group">
                    <label for="file">รูปภาพ</label>
                    <input type="file" name="p_img" id="file" accept="image/*">
                </div>

                <div class="form-group">
                    <label for="type_id" id="type_id" class="form-label">ประเภทสินค้า</label>
                    <?php include "db.php";
                    $strSQL = "SELECT * FROM type";
                    $objQuery = mysqli_query($conn, $strSQL);
                    ?>
                    <select name="type_id" id="type_id">
                        <?php while ($objResult = mysqli_fetch_array($objQuery)) { ?>
                            <option value="<?php echo $objResult["type_id"]; ?>">
                                <?php echo $objResult["type_name"]; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-buntons">
                    <button type="button" class="btn-reset" onclick="closeModal()">ยกเลิก</button>
                    <button type="submit" class="btn-submit">บันทึกข้อมูล</button>
                </div>
            </form>
        </div>


    </div>
    <!-- ======================================= popup เพิ่มประเภทสินค้า ========================================== -->
    <div id="addTypeModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 style=" color: #63554c;">เพิ่มหมวดหมู่สินค้าใหม่</h3>
                <button class="close-btn-clean" onclick="closeModal()">&times;</button>
            </div>
            <form action="save_type.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="type_name">ชื่อประเภทสินค้า</label>
                    <input type="text" name="type_name" id="type_name">
                </div>

                <div class="form-buntons">
                    <button type="button" class="btn-reset" onclick="closeModal()">ยกเลิก</button>
                    <button type="submit" class="btn-submit">บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
    <!-- ================================= popup เปิดบิล =================================== -->
    <div id="openOrder" class="modal2-overlay" style="display: none;">
        <div class="modal2-content">
            <div class="modal2-header">
                <h3 style=" color: #63554c;">บิลทั้งหมด</h3>
                <button class="close-btn-clean" onclick="closeModal()">&times;</button>
            </div>

            <div class="form-openOrder">
                <div class="table-responsive" style="padding: 15px;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr style="border-bottom: 2px solid #ccc;">
                                <th style="padding: 10px; color: #63554c;">รหัสบิล</th>
                                <th style="padding: 10px; color: #63554c;">เบอร์โต๊ะ</th>
                                <th style="padding: 10px; color: #63554c;">หมายเหตุ</th>
                                <th style="padding: 10px; color: #63554c;">เวลาที่เปิดบิล</th>
                                <th style="padding: 10px; color: #63554c;">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody id="billListBody">
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 20px;">กำลังโหลดข้อมูล...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- ================================= popup สำหรับเพิ่มหมายเหตุ ================================ -->
    <div id="orderModal" class="modal-over-lay">
        <div class="order-modal-box">
            <h3 id="modalProductName">ชื่อเมนู</h3>
            <p>ราคา: <span id="modalProductPrice">0</span>บาท</p>

            <div class="modal-qty-control">
                <label>จำนวน: </label><br>
                <button type="button" class="btn-qty" onclick="changeModalQty(-1)">-</button>
                <input type="number" id="modalQty" class="input-qty" value="1" min="1" readonly>
                <button type="button" class="btn-qty" onclick="changeModalQty(1)">+</button>
            </div>

            <div class="modal-remark-section">
                <label>หมายเหตุ (ถ้ามี): </label><br>
                <textarea id="modalRemark" class="input-remark" rows="3" placeholder="เช่น เผ็ดน้อย, ไม่ใช่ผัก"></textarea>
            </div>

            <div class="modal-action">
                <button type="button" class="btn-cancel" onclick="closeOrderModal()">ยกเลิก</button>
                <button type="button" class="btn-confirm" onclick="confirmAddToOrder">เพิ่มลงบิล</button>
            </div>
        </div>
    </div>


    <script src="script.js"></script>
</body>

</html>
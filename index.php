<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Story เรื่องเล่ากาแฟ</title>
    <link rel="stylesheet" href="style2.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- เพิ่ม FontAwesome เพื่อรองรับไอคอน fa-solid fa-trash -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@100..900&display=swap" rel="stylesheet">
</head>

<body>
    <!-- แถบแจ้งเตือนออเดอร์ใหม่ -->
    <audio id="orderSound" src="notify.mp3" preload="auto"></audio>

    <div id="newOrderAlert" style="display:none; background:#ffb703; padding:10px; text-align:center; font-weight:bold; cursor:pointer;">
        🔔 มีออเดอร์ใหม่เข้ามา!
        <span id="orderBadge" style="background: #ff0000; color: #ffffff; border-radius: 50%; padding: 2px 8px; font-size: 14px; margin-left: 5px; margin-right: 5px; display: inline-block;">1</span>
        คลิกที่ออเดอร์ใหม่เพื่อดูรายละเอียด
    </div>

    <?php include "navbar.php" ?>
    <!-- ปุ่มแฮมเบอร์เกอร์ -->

    <div class="main-container">

        <div class="left-content">

            <div class="menu-list">
                <?php
                require_once 'db.php';

                if (isset($_GET['cate'])) {
                    $cate = $_GET['cate'];

                    $sql = "SELECT * FROM products WHERE type_id = '$cate' ORDER BY p_name ASC";
                } else {
                    #$sql = "SELECT * FROM products ORDER BY sort_order ASC";
                    $sql = "SELECT * FROM products ORDER BY sales_count DESC, p_id ASC";
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
                            <h4><?php echo $row['p_name'] ?></h4>
                            <h5><?php echo $row['p_price'] ?> บาท</h5>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <!-- ================================= ประเภทสินค้าด้านล่าง ================================= -->
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
        <!--======================================= รายการออเดอร์ฝั่งขวา ====================================-->
        <aside class="order-section">
            <div class="headorder">
                <h3>รายการออเดอร์</h3>
                <div>
                    <label>โต๊ะ : </label>
                    <select name="tables" id="tables" style="font-size: 14px;">
                        <option value="" selected>ไม่ได้เลือก</option>
                        <option value="Takeaway">กลับบ้าน</option>
                        <?php
                        $sql_tables = "SELECT * FROM tables";
                        $query_tabels = mysqli_query($conn, $sql_tables);

                        if (mysqli_num_rows($query_tabels) > 0) {
                            while ($row_table = mysqli_fetch_assoc($query_tabels)) {
                        ?>
                                <option value="<?php echo $row_table['tables_id']; ?>">
                                    <?php echo $row_table['tables_number']; ?>
                                </option>
                        <?php
                            }
                        }
                        ?>
                    </select>
                </div>
                <button type="button" id="btnCloseBillView" class="btn-clear-panel" onclick="closeBillView()" title="ปิดการดูบิล" style="display:none;"><i class="bi bi-x-circle-fill" style="font-size: x-large; color: #251b6f;"></i></button>
            </div>

            <div class="order-items-container">รายการที่สั่งจะแสดงที่นี่</div>

            <p class="total-price" style="text-align: right; font-size: 18px; padding-bottom: 10px;"><strong>รวมทั้งหมด 0 บาท</strong></p>
            <div class="action-buttons">
                <button type="submit" class="btn-save" onclick="saveOrder()">บันทึก</button>
                <button type="button" class="btn-pay" onclick="openPaymentModal()">ชำระเงิน</button>
            </div>
        </aside>

    </div>
    <!--====================================== popup เพิ่มสินค้า ===========================================-->
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
    <!-- ==================================== popup เพิ่มประเภทสินค้า ================================== -->
    <div id="addTypeModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 style=" color: #63554c;">เพิ่มประเภทสินค้าใหม่</h3>
                <button class="close-btn-clean" onclick="closeModal()">&times;</button>
            </div>
            <form id="formAddType" onsubmit="saveTypeAjax(event)" enctype="multipart/form-data">
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
                <h3 style="color: #63554c; margin: 0;">บิลทั้งหมด</h3>
                <button class="close-btn-clean" onclick="closeModal()">&times;</button>
            </div>

            <div class="form-openOrder">
                <div class="table-responsive" style="padding: 15px;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr style="border-bottom: 2px solid #ddd; background-color: #f1f3f5;">
                                <th style="padding: 10px; text-align: center; width: 40px; color: #63554c;"></th>
                                <th style="padding: 10px; text-align: center; color: #63554c;">รหัสบิล</th>
                                <th style="padding: 10px; text-align: center; color: #63554c;">เบอร์โต๊ะ</th>
                                <th style="padding: 10px; text-align: center; color: #63554c;">เวลาที่เปิดบิล</th>
                                <th style="padding: 10px; text-align: center; color: #63554c;">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody id="billListBody">
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 20px; color: #63554c;">กำลังโหลดข้อมูล...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================== popup ออเดอรืใหม่ =========================-->
    <div id="newOrderModal" class="modal-overlay" style="display: none; ">
        <div class="modal-content" style="height: 50vh;">
            <div>
                <h3>ออเดอร์ใหม่จากลูกค้า</h3>
                <button class="close-btn-clean" onclick="closeModal()">&times;</button>
            </div>
            <hr>
            <div id="newOrderList">กำลังโหลดข้อมูล...</div>
        </div>
    </div>

    <!-- ================================= popup สำหรับเพิ่มหมายเหตุ ================================ -->
    <div id="orderModal" class="modal-over-lay" style="display: none;">
        <div class="order-modal-box">
            <h3 id="modalProductName">ชื่อเมนู</h3>
            <p>ราคา: <span id="modalProductPrice">0</span>บาท</p>
            <div id="modalOptionsContainer" style="display: flex; gap: 10px; margin-bottom: 15px; justify-content: center; flex-wrap: wrap; padding: 10px;">
                <!-- ปุ่มตัวเลือกสร้างจาก script.js -->
            </div>

            <div class="modal-qty-control">
                <label>จำนวน: </label><br>
                <button type="button" class="btn-qty" onclick="changeModalQty(-1)"><i class="fa-solid fa-minus"></i></button>
                <input type="number" id="modalQty" class="input-qty" value="1" min="1" readonly>
                <button type="button" class="btn-qty" onclick="changeModalQty(1)"><i class="fa-solid fa-plus"></i></button>
            </div>

            <div class="modal-remark-section">
                <label>หมายเหตุ (ถ้ามี): </label><br>
                <textarea id="modalRemark" class="input-remark" rows="3" placeholder="เช่น เผ็ดน้อย, ไม่ใช่ผัก"></textarea>
            </div>

            <div class="modal-action">
                <button type="button" class="btn-cancel" onclick="closeOrderModal()">ยกเลิก</button>
                <button type="button" class="btn-confirm" onclick="confirmAddToOrder()">เพิ่มลงบิล</button>
            </div>
        </div>
    </div>
    <!-- ================================= popup ชำระเงิน ================================ -->
    <div id="paymentModal" class="modal-overlay1" style="display: none;">
        <div class="payment-modal-content">
            <div class="payment-modal-header">
                <h3>วิธีการชำระเงิน</h3>
                <button class="close-btn-pay" onclick="closeModal()">&times;</button>
            </div>

            <div class="payment-total-box">
                <h2>ยอดรวม: <span id="payTotalAmount" class="text-amount">0</span> บาท</h2>
            </div>

            <div class="payment-method-group">
                <label class="payment-label">
                    <span class="custom-check"></span>
                    <input type="radio" name="payment_method" id="paymentCash" value="Cash" checked onchange="togglePaymentMode()">เงินสด (Cash)
                </label>

                <label class="payment-label">
                    <span class="custom-check"></span>
                    <input type="radio" name="payment_method" id="paymentTransfer" value="Transfer" onchange="togglePaymentMode()">โอนเงิน (QR / PromptPay)
                </label>
            </div>

            <!-- ส่วนของเงินสด -->
            <div id="cashInputSection">
                <div class="form-group-pay">
                    <label>รับเงินมา (บาท): </label>
                    <input type="number" id="receiveMoney" onkeyup="calculateChange()" placeholder="กรอกจำนวนเงิน...">
                </div>
                <h3 class="change-money-box">เงินทอน: <span id="changeMoney"> 0</span> บาท</h3>
            </div>

            <!-- แจ้งเตือนโอนเงิน -->
            <div id="transferInputSection" class="transfer-section">
                <div class="qr-container">
                    <p class="qr-title">กรุณาตรวจสอบสลิปโอนเงินให้ตรงกับยอดรวมสุทธิ</p>
                    <img id="qrImage" src="" alt="PromptPay QR Code" class="qr-image">
                    <p class="qr-promptpay">
                        พร้อมเพย์: <span class="qr-number">0981833902</span>
                    </p>
                </div>
                <p class="qr-warning">
                    ⚠️ กรุณาตรวจสอบสลิปโอนเงินให้ตรงกับยอดรวมสุทธิ
                </p>
            </div>

            <div class="form-buntons-payment-buttons">
                <button type="button" class="btn-reset-pay" onclick="closeModal()">ยกเลิก</button>
                <button type="button" class="btn-submit-pay" onclick="confirmPayment()">ยืนยันชำระเงิน</button>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>
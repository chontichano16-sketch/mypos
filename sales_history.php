<?php
require 'db.php';

// ดึงรายการบิลล่าสุด 50 รายการ (สามารถค้นหาตามเลขบิลได้)
$search = $_GET['search'] ?? '';
$where = "";

if (!empty($search)) {
    $searchInt = (int)$search;
    $searchEscaped = mysqli_real_escape_string($conn, $search);
    // $where = "WHERE order_id = $searchInt OR table_id = '$search'";
    $where = "WHERE order_id = '$searchEscaped' OR table_id = '$searchEscaped'";
}

$sql = "SELECT * FROM `order` $where ORDER BY order_id DESC LIMIT 50";
$result = mysqli_query($conn, $sql);
?>
<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ประวัติการขาย - POS</title>
    <link rel="stylesheet" href="style2.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css" crossorigin="anonymous"
        referrerpolicy="no-referrer">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@100..900&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: sans-serif;

            overflow-y: auto;
        }

        .card {
            padding: 20px;
            border-radius: 8px;
            width: min(1200px, calc(100% - 32px));
            margin: 32px auto;
        }

        .detail-history {
            background-color: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .08);
        }

        .head-history {
            background-color: #fff;
            padding: 10px;
            border-radius: 12px;
            margin-bottom: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .08);
        }

        .search-box-history {
            display: flex;
            gap: 10px;
            margin-left: 10px;
        }

        .search-box-history input {
            padding: 8px 12px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 250px;
        }

        .search-box-history button {
            padding: 8px 16px;
            background: #2196F3;
            color: white;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-size: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            color: #3f342d;
        }

        th {
            background: #f8f9fa;
        }

        .btn-print-h {
            color: #414141;
            border: 1px solid oklch(0.84 0 0);
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-print-h:hover {
            background: oklch(93% 0.034 272.788);
        }

        .user-menu {
            position: relative;
            display: inline-block;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 8px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            color: #333;
            padding: 6px 10px;
            border-radius: 8px;
            transition: background .2s;
        }

        .user-info:hover {
            background: #f0f0f0;
        }

        .user-info i {
            font-size: 22px;
        }

        .user-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            min-width: 180px;
            background: #fff;
            border: 1px solid #e5e5e5;
            border-radius: 10px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, .12);
            padding: 6px;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-6px);
            transition: all .18s ease;
        }

        .user-dropdown.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .user-dropdown a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            color: #333;
            text-decoration: none;
            font-size: 15px;
        }

        .user-dropdown a:hover {
            background: #f5f5f5;
        }

        .user-dropdown a.logout {
            color: #d9534f;
        }

        .user-dropdown a.logout:hover {
            background: #fdecea;
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <div class="dropdown">
            <button type="button" onclick="toggleMenu(event)" class="dropbtn" aria-label="เปิดเมนู">&#9776;</button>
            <div id="myDropdown" class="dropdown-content">
                <button type="button" class="menu-btn"><i class="bi bi-chevron-down" style="float: right;"></i>จัดการข้อมูลโต๊ะ</button>
                <ul class="submenu">
                    <li><a href="print_qr.php"><i class="bi bi-qr-code"></i> พิมพ์ QR Code โต๊ะ</a></li>
                </ul>
                <button type="button" class="menu-btn"><i class="bi bi-chevron-down" style="float: right;"></i>จัดการข้อมูลเมนูอาหาร</button>
                <ul class="submenu">
                    <li><button type="button" onclick="openModal('product')">เพิ่มสินค้า</button></li>
                    <li><button type="button" onclick="openModal('type')">เพิ่มประเภทสินค้า</button></li>
                </ul>
                <a href="show_pro.php">รายการสินค้าทั้งหมด</a>
                <a href="sale_report.php">รายงานยอดขาย</a>
            </div>
        </div>

        <div class="nav-report">

            <div class="nav-report-header">
                <a href="index.php">หน้าร้าน</a>
                <span>/</span>
                <span>ประวัติการขาย</span>
            </div>

            <div class="btn-navreport">
                <button type="button" onclick="window.location.reload();">
                    <span><i class="fa-solid fa-rotate"></i></span> รีเฟรชข้อมูล
                </button>
                <button type="button" id="history-report">
                    <a href="sale_report.php"><i class="fa-solid fa-file"></i> รายงานยอดขาย</a>
                </button>
            </div>
        </div>

        <div class="user-menu">
            <button type="button" class="user-info" id="userInfoBtn" onclick="toggleUserMenu(event)">
                <?php echo $_SESSION["fullname"]; ?>
                <i class="fa-solid fa-circle-user"></i>
            </button>

            <div class="user-dropdown" id="userDropdown">
                <!-- <a href="profile.php"><i class="fa-solid fa-user"></i> โปรไฟล์</a> -->
                <a href="logout.php" class="logout"><i class="bi bi-box-arrow-right"></i> ออกจากระบบ</a>
            </div>
        </div>

    </nav>

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

    <div class="card">
        <section class="head-history">
            <h2 style="color: #3f342d;"><i class="fa-solid fa-scroll"></i> ประวัติการขาย (สำหรับสั่งพิมพ์ย้อนหลัง)</h2>
            <!-- ฟอร์มค้นหาเลขบิล หรือ เลขโต๊ะ -->
            <form class="search-box-history" method="GET">
                <input type="text" name="search" placeholder="ค้นหาเลขบิล หรือ โต๊ะ..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit">ค้นหา</button>
                <a href="sales_history.php" style="align-self:center; text-decoration:none; color:#666;">รีเซ็ต</a>
            </form>
        </section>

        <section class="detail-history">
            <table>
                <thead>
                    <tr>
                        <th style="text-align: center;">วัน/เวลา</th>
                        <th style="text-align: center;">เลขที่ออเดอร์</th>
                        <th style="text-align: center;">โต๊ะ</th>
                        <th style="text-align: center;">ราคารวม</th>
                        <th style="text-align: center;">สถานะ</th>
                        <th style="text-align: center;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <?php
                            $rawDate = $row['created_at'] ?? $row['order_date'] ?? $row['date_time'] ?? null;
                            $formattedDate = $rawDate ? date('d/m/Y H:i', strtotime($rawDate)) . ' น.' : '-';
                            ?>
                            <tr>
                                <td style="color: #555; text-align: center;"><?= $formattedDate ?></td>
                                <td style="text-align: center;">#ORD-<?= str_pad((string) $row['order_id'], 4, '0', STR_PAD_LEFT) ?></td>
                                <td style="text-align: center;"><?= $row['table_id'] ?? '-' ?></td>
                                <td style="text-align: center;"><?= number_format($row['total_amount'] ?? 0, 2) ?> ฿</td>
                                <td style="text-align: center;"><span style="color: green; font-weight: bold; "><?= $row['status'] ?></span></td>
                                <td style="text-align: center;">
                                    <button class="btn-print-h" onclick="printReceipt(<?= $row['order_id'] ?>)"><i class="fa-solid fa-print"></i> พิมพ์ใบเสร็จ
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center; color:#888;">ไม่พบประวัติการขาย</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </div>

    <script src="script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
    <script>
        function toggleUserMenu(e) {
            e.stopPropagation();
            document.getElementById('userDropdown').classList.toggle('show');
        }

        // คลิกที่อื่นแล้วปิดเมนู
        document.addEventListener('click', function() {
            document.getElementById('userDropdown').classList.remove('show');
        });

        // กด ESC ปิดเมนู
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.getElementById('userDropdown').classList.remove('show');
            }
        });

        function printReceipt(orderId) {
            // โยน order_id ไปยังไฟล์พิมพ์ใบเสร็จสำหรับลูกค้า
            window.open('print_receipt.php?id=' + orderId, '_blank', 'width=350,height=600');
        }
    </script>

</body>

</html>
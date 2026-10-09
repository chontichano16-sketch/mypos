<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require 'db.php';
include "all_popup.php";

$showHidden = isset($_GET['status']) && $_GET['status'] == 'hidden';
if ($showHidden) {
    // ดึงเฉพาะโต๊ะที่ถูกซ่อน
    $sql = "SELECT * FROM `table` WHERE `is_active` = 0 ORDER BY `table_id` ASC";
    $pageTitle = "โต๊ะที่ถูกซ่อน";
} else {
    // ดึงเฉพาะโต๊ะปกติ
    $sql = "SELECT * FROM `table` WHERE `is_active` = 1 ORDER BY `table_id` ASC";
    $pageTitle = "รายการโต๊ะทั้งหมด";
}
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จัดการโต๊ะอาหาร</title>
    <link rel="stylesheet" href="style2.css">
    <link rel="stylesheet" href="showtable.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
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
            <button onclick="toggleMenu(event)" class="dropbtn"><i class="fa-solid fa-bars"></i></button>
            <div id="myDropdown" class="dropdown-content" style="border: none;">
                <button class="menu-btn"> <i class="bi bi-chevron-down" style="float: right;"></i></i><i class="fa-solid fa-chair"></i> จัดการข้อมูลโต๊ะ</button>
                <ul class="submenu">
                    <li><a href="show_table.php"> รายการโต๊ะทั้งหมด</a></li>
                    <li><button onclick="openAddTableModal()">เพิ่มโต๊ะ</button></li>
                    <li><a href="print_qr.php"><!--<i class="bi bi-qr-code">--></i> พิมพ์ QR Code โต๊ะ</a></li>
                </ul>
                <button class="menu-btn"><i class="bi bi-chevron-down" style="float: right;"></i><i class="fa-solid fa-utensils"></i> จัดการข้อมูลเมนูอาหาร</button>
                <ul class="submenu">
                    <li><button onclick="openModal('product')">เพิ่มสินค้า</button></li>
                    <li><button onclick="openModal('type')">เพิ่มประเภทสินค้า</button></li>
                    <li><a href="show_pro.php" style="border-bottom: 1px solid #63554c1f;">รายการสินค้าทั้งหมด</a></li>
                    <li><a href="show_type.php" style="border-bottom: 1px solid #63554c1f;">ประเภทสินค้าทั้งหมด</a></li>
                </ul>
                <a href="sale_report.php"><i class="fa-solid fa-chart-line"></i> รายงานยอดขาย</a>
            </div>
        </div>

        <div class="nav-report">
            <div class="nav-report-header">
                <a href="index.php">หน้าร้าน</a>
                <span>/</span>
                <span>รายการโต๊ะทั้งหมด</span>
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

    <div class="table-container-1">
        <div class="header-action-1">
            <h2><?php echo $pageTitle; ?></h2>
            <?php if ($showHidden): ?>
                <a href="show_table.php" style="margin-right: 15px; color: #666;">กลับไปหน้าปกติ</a>
            <?php else: ?>
                <a href="show_table.php?status=hidden" style="margin-right: 15px; color: #ff9800;">ดูโต๊ะที่ถูกซ่อน</a>
            <?php endif; ?>
            <button class="btn-add" onclick="openAddTableModal()"><i class="fa-solid fa-plus"></i> เพิ่มโต๊ะ</button>
        </div>

        <!-- ค้นหาและแสดงจำนวนรายการ -->
        <?php
        $totalCount = mysqli_num_rows($result);
        ?>
        <div class="controls-1" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <div>แสดง <span id="table-count"><?= $totalCount ?></span> รายการ</div>
        </div>

        <!-- ตารางข้อมูล -->
        <table>
            <thead>
                <tr>
                    <th>ลำดับ</th>
                    <th>ชื่อโต๊ะ / เบอร์โต๊ะ</th>
                    <th>สถานะ</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($result) > 0) {
                    $i = 1;
                    while ($row = mysqli_fetch_assoc($result)) {
                        // กำหนดสถานะ
                        $statusClass = ($row['table_status'] === 'occupied') ? 'status-occupied' : 'status-available';
                        $statusText = ($row['table_status'] === 'occupied') ? 'มีออเดอร์' : 'ว่าง';

                        // แปลงข้อความป้องกัน XSS และ Single/Double quote พังใน JavaScript
                        $tableId = $row['table_id'];
                        $tableName = htmlspecialchars($row['table_number'], ENT_QUOTES);
                        $qrLink = htmlspecialchars($row['qr_link'], ENT_QUOTES);

                        echo "<tr>";
                        echo "<td>" . $i . "</td>";
                        echo "<td>" . $tableName . "</td>";
                        echo "<td><span class='status-dot {$statusClass}'></span>{$statusText}</td>";
                        echo "<td class='action-links'>";
                        if ($showHidden) {
                            // ถ้าอยู่หน้าซ่อนโต๊ะ ให้แสดงปุ่มกู้คืน
                            echo "<a href='#' style='color: #28a745;' onclick=\"restoreTable({$tableId})\">กู้คืน</a>";
                        } else {
                            // ถ้าอยู่หน้าปกติ แสดงปุ่ม แก้ไข | QR | ลบ
                            echo "<a href='#' onclick=\"editTable({$tableId}, '{$tableName}')\">แก้ไข</a> | 
                              <a href='#' onclick=\"showQR('{$qrLink}', '{$tableName}')\">QR</a> | 
                              <a href='#' class='delete' style='color: #dc3545;' onclick=\"deleteTable({$tableId})\">ลบ</a>";
                        }
                        echo "</td>";
                        $i++;
                    }
                } else {
                    echo "<tr><td colspan='4' style='text-align:center;'>ยังไม่มีข้อมูลโต๊ะ</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script src="script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
    <script>
        window.addEventListener('DOMContentLoaded', (event) => {
            let navSearch = document.querySelector('input[placeholder="ค้นหาเมนู..."]');

            if (navSearch) {
                navSearch.placeholder = "ค้นหาเบอร์โต๊ะ...";

                navSearch.addEventListener('keyup', function() {
                    let input = this.value.toLowerCase();
                    let table = document.querySelectorAll("table");

                    table.forEach(table => {
                        let rows = table.querySelectorAll("tbody tr");

                        rows.forEach(row => {
                            // ถ้ามี td colspan=3 (ไม่มีข้อมูล) ให้ข้ามไป
                            if (row.cells.length === 1) return;

                            let rowText = row.textContent.toLowerCase();
                            if (rowText.includes(input)) {
                                row.style.display = "";
                            } else {
                                row.style.display = "none";
                            }
                        });
                    });
                });
            }
        });

        // แก้ไข้โต๊ะ
        function editTable(tableId, currentName) {
            Swal.fire({
                title: 'แก้ไขชื่อโต๊ะ',
                input: 'text',
                inputValue: currentName,
                showCancelButton: true,
                confirmButtonText: 'บันทึก',
                cancelButtonText: 'ยกเลิก',
                inputValidator: (value) => {
                    if (!value) {
                        return 'กรุณากรอกชื่อโต๊ะ!'
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const newName = result.value;

                    // ส่งข้อมูลไปบันทึก
                    fetch('edit_table.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `id=${tableId}&new_name=${encodeURIComponent(newName)}`
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire({
                                        icon: 'success',
                                        title: 'อัปเดตสำเร็จ',
                                        showConfirmButton: false,
                                        timer: 1500
                                    })
                                    .then(() => location.reload());
                            } else {
                                Swal.fire('ผิดพลาด', data.message, 'error');
                            }
                        });
                }
            });
        }

        // เปิด qr
        function showQR(qrLink, tableName) {
            Swal.fire({
                title: 'QR Code ' + tableName,
                // ใช้ API ฟรีในการสร้างรูป QR Code จาก URL ที่ตั้งไว้
                html: `
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(qrLink)}" alt="QR Code" style="margin-top: 10px; border-radius: 8px;">
            <p style="margin-top: 15px; font-size: 14px; color: #666;">สแกนเพื่อสั่งอาหารสำหรับโต๊ะนี้</p>
        `,
                confirmButtonText: 'ปิดหน้าต่าง',
                confirmButtonColor: '#3085d6'
            });
        }

        // ปุ่มกู้คืน
        function restoreTable(tableId) {
            Swal.fire({
                title: 'ยืนยันการกู้คืน?',
                text: "โต๊ะนี้จะกลับไปแสดงในหน้าร้านอีกครั้ง",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#999',
                confirmButtonText: 'กู้คืนโต๊ะ',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('restore_table.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: 'id=' + tableId
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire({
                                        icon: 'success',
                                        title: 'กู้คืนสำเร็จ',
                                        showConfirmButton: false,
                                        timer: 1500
                                    })
                                    .then(() => location.reload());
                            } else {
                                Swal.fire('ผิดพลาด', data.message, 'error');
                            }
                        });
                }
            });
        }

        // ปุ่มลบ และซ่อนโต๊ะ
        function deleteTable(tableId) {
            Swal.fire({
                title: 'คุณต้องการจัดการโต๊ะนี้อย่างไร?',
                text: "การลบถาวรอาจส่งผลต่อข้อมูลบิลเก่าที่เคยใช้โต๊ะนี้",
                icon: 'warning',
                showCancelButton: true,
                showDenyButton: true,
                confirmButtonColor: '#ffc107', // สีเหลืองซ่อน
                denyButtonColor: '#d33', // สีแดงลบถาวร
                cancelButtonColor: '#999',
                confirmButtonText: 'ซ่อนโต๊ะ (แนะนำ)',
                denyButtonText: 'ลบทิ้งถาวร',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                let actionType = '';

                if (result.isConfirmed) {
                    actionType = 'hide'; // กดปุ่มซ่อน
                } else if (result.isDenied) {
                    actionType = 'force_delete'; // กดปุ่มลบถาวร
                }

                if (actionType !== '') {
                    fetch('delete_table.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: 'id=' + tableId + '&action=' + actionType
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                let msg = actionType === 'hide' ? 'ซ่อนข้อมูลสำเร็จ' : 'ลบข้อมูลถาวรสำเร็จ';
                                Swal.fire({
                                    icon: 'success',
                                    title: msg,
                                    showConfirmButton: false,
                                    timer: 1500
                                }).then(() => location.reload());
                            } else {
                                Swal.fire('ผิดพลาด', data.message, 'error');
                            }
                        })
                        .catch(err => {
                            Swal.fire('ผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
                        });
                }
            });
        }

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

        // ช่องค้นหาโต๊ะ
    </script>
</body>

</html>
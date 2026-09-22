<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประเภทสินค้าทั้งหมด</title>
    <link rel="stylesheet" href="style2.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css" integrity="sha512-QeR2VH+lsBE5LSAe1Q5EnTBbe7XTBubt8dG93Y7gidSgdMCr8nVqKcfKAMyN96SV8KDbZVTDXChatu5G2KQGzg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@100..900&display=swap" rel="stylesheet">
</head>

<body>
    <?php
    require_once "db.php";
    include "navbar.php";

    // ดึงข้อมูลประเภทสินค้าทั้งหมดจากตาราง type
    $sql = "SELECT * FROM type ORDER BY type_id ASC";
    $result = mysqli_query($conn, $sql);
    ?>

    <div class="container">
        <h2 style="color: #3f342d; margin-bottom: 20px;">ประเภทสินค้าทั้งหมด</h2>

        <table border="1" width="90%" style="border-collapse: collapse; text-align: center; margin-bottom: 12px; background-color: #fff;">
            <thead>
                <tr style="background-color: #f8f9fa;">
                    <th style="padding: 10px;">รหัสประเภทสินค้า</th>
                    <th style="padding: 10px;">ชื่อประเภทสินค้า</th>
                    <th style="padding: 10px;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0) { ?>
                    <?php while ($row = mysqli_fetch_array($result)) { ?>
                        <tr>
                            <td style="padding: 10px;"><?= $row['type_id']; ?></td>
                            <td style="padding: 10px;"><?= htmlspecialchars($row['type_name']); ?></td>
                            <td style="padding: 10px;">
                                <!-- ปุ่มแก้ไข ส่งข้อมูลไปที่ Modal -->
                                <a href="#" class="btn-edit" style="margin: 10px;"
                                    data-id="<?= $row['type_id']; ?>"
                                    data-name="<?= htmlspecialchars($row['type_name']); ?>"
                                    onclick="openEditTypeModal(this); return false;">
                                    <i class="fa-regular fa-pen-to-square"></i>
                                </a>
                                <!-- ปุ่มลบ -->
                                <a href="delete_type.php?id=<?= $row['type_id']; ?>" class="btn-delete" onclick="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบประเภทสินค้านี้?\n(คำเตือน: หากลบ สินค้าที่อยู่ในประเภทนี้อาจไม่มีการแสดงผล)')">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="3" style="padding: 20px; text-align: center;">ไม่มีข้อมูลประเภทสินค้า</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- ============================= popup เพิ่มประเภทสินค้า (ของเดิมจาก Navbar) ================================= -->
    <div id="addTypeModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 style=" color: #63554c;">เพิ่มประเภทสินค้าใหม่</h3>
                <button class="close-btn-clean" onclick="closeModal()">&times;</button>
            </div>
            <form action="save_type.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="type_name">ชื่อประเภทสินค้า</label>
                    <input type="text" name="type_name" id="type_name" required>
                </div>
                <div class="form-buntons">
                    <button type="button" class="btn-reset" onclick="closeModal()">ยกเลิก</button>
                    <button type="submit" class="btn-submit">บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================= popup แก้ไขประเภทสินค้า (สร้างใหม่สำหรับหน้านี้) ================================= -->
    <div id="editTypeModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 style=" color: #63554c;">แก้ไขประเภทสินค้า</h3>
                <button class="close-btn-clean" onclick="closeEditTypeModal()">&times;</button>
            </div>
            <form action="update_type.php" method="post">
                <!-- ซ่อน ID ไว้สำหรับส่งไปแก้ไข -->
                <input type="hidden" name="type_id" id="edit_type_id">

                <div class="form-group">
                    <label for="edit_type_name">ชื่อประเภทสินค้า</label>
                    <input type="text" name="type_name" id="edit_type_name" required>
                </div>
                <div class="form-buntons">
                    <button type="button" class="btn-reset" onclick="closeEditTypeModal()">ยกเลิก</button>
                    <button type="submit" class="btn-submit">อัปเดตข้อมูล</button>
                </div>
            </form>
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

    <script src="script.js"></script>

    <script>
        // ฟังก์ชันเปิด Modal แก้ไข
        function openEditTypeModal(button) {
            // ดึงค่ามาจาก data- attribute ของปุ่มที่ถูกคลิก
            var typeId = button.getAttribute('data-id');
            var typeName = button.getAttribute('data-name');

            // นำค่าไปใส่ใน input ภายในแบบฟอร์ม
            document.getElementById('edit_type_id').value = typeId;
            document.getElementById('edit_type_name').value = typeName;

            // แสดง Modal
            document.getElementById('editTypeModal').style.display = 'flex';
        }

        // ฟังก์ชันปิด Modal แก้ไข
        function closeEditTypeModal() {
            document.getElementById('editTypeModal').style.display = 'none';
        }

        // โค้ดค้นหาข้อมูลในตาราง
        window.addEventListener('DOMContentLoaded', (event) => {
            let navSearch = document.querySelector('input[placeholder="ค้นหาเมนู..."]');

            if (navSearch) {
                navSearch.placeholder = "ค้นหารหัส หรือ ชื่อประเภท...";

                navSearch.addEventListener('keyup', function() {
                    let input = this.value.toLowerCase();
                    let tables = document.querySelectorAll("table");

                    tables.forEach(table => {
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
    </script>
</body>

</html>
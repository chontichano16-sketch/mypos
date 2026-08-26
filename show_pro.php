<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการสินค้าทั้งหมด</title>
    <link rel="stylesheet" href="style2.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>
    <?php
    include "navbar.php";
    include "db.php";

    // คำสั่ง SQL ดึงข้อมูลสินค้าทั้งหมด
    $sql = "SELECT * FROM products";
    $result = mysqli_query($conn, $sql);
    ?>

    <div class="container">
        <h2 style="color: #63554c;">รายการสินค้าทั้งหมด</h2>
        <table border="1" width="95%" style="border-collapse: collapse; text-align: center;">
            <thead>
                <tr>
                    <th>รหัส</th>
                    <th>รูปภาพ</th>
                    <th>ชื่อสินค้า</th>
                    <th>ราคา</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // วนลูปดึงข้อมูลสินค้ามาแสดงทีละแถว
                while ($row = mysqli_fetch_array($result)) {
                ?>
                    <tr>
                        <td><?= $row['p_id']; ?></td>
                        <td>
                            <img src="upload/<?= $row['p_img']; ?>" width="50" alt="รูปสินค้า">
                        </td>
                        <td><?= $row['p_name']; ?></td>
                        <td><?= number_format($row['p_price'], 2); ?> ฿</td>
                        <td>
                            <!-- ปุ่มแก้ไขแบบเรียบ ไม่มีขอบ ไม่มีพื้นหลัง -->
                            <a href="javascript:void(0);" onclick="openEditModal('<?= $row['p_id']; ?>', '<?= htmlspecialchars($row['p_name'], ENT_QUOTES); ?>', '<?= $row['p_price']; ?>', '<?= $row['type_id']; ?>', '<?= $row['p_img']; ?>')" class="btn-edit">
                                <i class="bi bi-pencil-fill"></i>แก้ไข
                            </a>
                            <a href="delete_pro.php?p_id=<?= $row['p_id']; ?>" class="btn-delete" onclick="return confirm('คุณแน่ใจแล้วหรือไม่ว่าต้องการลบรายการสินค้านี้?')">
                                <i class="bi bi-trash3"></i>ลบ
                            </a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
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
                    <?php 
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

    <!-- =========================================== popup เพิ่มประเภทสินค้า =============================================== -->
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

    <!-- =========================================== popup แก้ไขสินค้า =============================================== -->
    <div id="editProductModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 style="color: #63554c;">แก้ไขสินค้า</h3>
                <button class="close-btn-clean" onclick="closeEditModal()">&times;</button>
            </div>
            <form action="update_pro.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="p_id" id="edit_p_id">
                <input type="hidden" name="old_img" id="edit_old_img">

                <div class="form-group">
                    <label for="edit_p_name">ชื่อเมนู</label>
                    <input type="text" name="p_name" id="edit_p_name" required>
                </div>

                <div class="form-group">
                    <label for="edit_p_price">ราคา</label>
                    <input type="text" name="p_price" id="edit_p_price" required>
                </div>

                <!-- แสดงรูปภาพปัจจุบัน -->
                <div class="form-group" style="text-align: center;">
                    <label>รูปภาพ</label>
                    <div>
                        <img id="current_img_preview" src="" width="80" alt="รูปปัจจุบัน" style="border-radius: 5px; border: 1px solid #ddd; padding: 2px;">
                    </div>
                </div>

                <div class="form-group">
                    <input type="file" name="p_img" id="edit_file" accept="image/*">
                </div>

                <div class="form-group">
                    <label for="edit_type_id" class="form-label">ประเภทสินค้า</label>
                    <select name="type_id" id="edit_type_id" required>
                        <option value="">-- เลือกประเภทสินค้า --</option>
                        <?php 
                        $strSQL2 = "SELECT * FROM type";
                        $objQuery2 = mysqli_query($conn, $strSQL2);
                        while ($objResult2 = mysqli_fetch_array($objQuery2)) { 
                        ?>
                            <option value="<?php echo $objResult2["type_id"]; ?>">
                                <?php echo $objResult2["type_name"]; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-buntons">
                    <button type="button" class="btn-reset" onclick="closeEditModal()">ยกเลิก</button>
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
                            <tr style="border-bottom: 2px solid #ddd;">
                                <th style="padding: 10px; text-align: center; color: #63554c;">รหัสบิล</th>
                                <th style="padding: 10px; text-align: center; color: #63554c;">เบอร์โต๊ะ</th>
                                <th style="padding: 10px; text-align: center; color: #63554c;">เวลาที่เปิดบิล</th>
                                <th style="padding: 10px; text-align: center; color: #63554c;">จัดการ</th>
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

    <script>
        function openEditModal(id, name, price, typeId, img) {
            document.getElementById('edit_p_id').value = id;
            document.getElementById('edit_p_name').value = name;
            document.getElementById('edit_p_price').value = price;
            document.getElementById('edit_type_id').value = typeId;
            document.getElementById('edit_old_img').value = img;
            
            // แสดงรูปภาพปัจจุบันในป๊อปอัพ
            document.getElementById('current_img_preview').src = 'upload/' + img;
            document.getElementById('edit_file').value = '';

            document.getElementById('editProductModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editProductModal').style.display = 'none';
        }
    </script>
    <script src="script.js"></script>
</body>

</html>
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
                while($row = mysqli_fetch_array($result)) { 
                ?>
                <tr>
                    <td><?= $row['p_id']; ?></td>
                    <td>
                        <img src="upload/<?= $row['p_img']; ?>" width="50" alt="รูปสินค้า">
                    </td>
                    <td><?= $row['p_name']; ?></td>
                    <td><?= number_format($row['p_price'], 2); ?> ฿</td>
                    <td>
                        <a href="edit_pro.php?p_id=<?= $row['p_id']; ?>" class="btn-edit"><i class="bi bi-pencil-fill"></i>แก้ไข</a>
                        <a href="delete_pro.php?p_id=<?= $row['p_id']; ?>" class="btn-delete" onclick="return confirm('คุณแน่ใจแล้วหรือไม่ว่าต้องการลบรายการสินค้านี้?')"><i class="bi bi-trash3"></i>ลบ</a>
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
                    <button type="button" class="btn-reset"  onclick="closeModal()">ยกเลิก</button>
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
                    <button type="button" class="btn-reset"  onclick="closeModal()">ยกเลิก</button>
                    <button type="submit" class="btn-submit">บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="script.js"></script>
</body>
</html>
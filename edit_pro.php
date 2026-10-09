<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขสินค้า</title>
    <link rel="stylesheet" href="style2.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>
    <?php
    include "navbar.php";
    include "db.php";

    // รับรหัสสินค้าจาก URL
    $product_id = isset($_GET['product_id']) ? $_GET['product_id'] : '';

    if (!empty($product_id)) {
        $sql = "SELECT * FROM product WHERE product_id = '$product_id'";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_array($result);
    }
    ?>

    <div class="edit-page-wrapper">
        <div class="edit-card">
            <div class="edit-header">
                <h3>แก้ไขสินค้า</h3>
            </div>

            <form action="update_pro.php" method="POST" enctype="multipart/form-data">
                <!-- เก็บ id ซ่อนไว้ส่งไปอัปเดต -->
                <input type="hidden" name="product_id" value="<?php echo $row['product_id'] ?? ''; ?>">
                <input type="hidden" name="old_img" value="<?php echo $row['product_img'] ?? ''; ?>">

                <div class="form-group-custom">
                    <label>ชื่อเมนู</label>
                    <input type="text" name="product_name" value="<?php echo htmlspecialchars($row['product_name'] ?? ''); ?>" required>
                </div>

                <div class="form-group-custom">
                    <label>ราคา</label>
                    <input type="number" step="0.01" name="product_price" value="<?php echo htmlspecialchars($row['product_price'] ?? ''); ?>" required>
                </div>

                <div class="form-group-custom">
                    <label>รูปภาพ</label>
                    <div class="file-upload-row">
                        <input type="file" name="product_img" accept="image/*">
                        <?php if (!empty($row['product_img'])): ?>
                            <img src="upload/<?php echo $row['product_img']; ?>" class="preview-img" alt="รูปเดิม" onerror="this.style.display='none'">
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group-custom">
                    <label>ประเภทสินค้า</label>
                    <select name="type_id" required>
                        <option value="">-- เลือกประเภทสินค้า --</option>
                        <?php
                        $strSQL = "SELECT * FROM type";
                        $objQuery = mysqli_query($conn, $strSQL);
                        if ($objQuery) {
                            while ($objResult = mysqli_fetch_array($objQuery)) {
                                // เช็คว่า type_id ในสินค้า ตรงกับ type_id ในตาราง type หรือไม่ ถ้าใช่ให้เลือก (selected)
                                $selected = (isset($row['type_id']) && $row['type_id'] == $objResult['type_id']) ? "selected" : "";
                        ?>
                                <option value="<?php echo $objResult['type_id']; ?>" <?php echo $selected; ?>>
                                    <?php echo $objResult['type_name']; ?>
                                </option>
                        <?php 
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="form-actions">
                    <a href="show_pro.php" class="btn-cancel">ยกเลิก</a>
                    <button type="submit" class="btn-save">บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
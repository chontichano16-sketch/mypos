<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style2.css">
</head>

<body>
    <!-- navbar -->
    <?php include "navbar.php" ?>

    <div class="add-product-container">
        <div class="add-product-card">
            <h2>เพิ่มประเภทสินค้า</h2>

            <form action="save_type.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="type_name">ชื่อประเภทสินค้า</label>
                    <input type="text" name="type_name" id="type_name">
                </div>

                <div class="form-buntons">
                    <button type="reset" class="btn-reset">ยกเลิก</button>
                    <button type="submit" class="btn-submit">บันทึกข้อมูล</button>
                </div>
        </div>
        </form>
    </div>
    <script src="script.js"></script>
</body>

</html>
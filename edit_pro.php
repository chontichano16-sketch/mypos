<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style2.css">
</head>

<body>
    <?php include "db.php";
    include "navbar.php";

    $p_id = $_REQUEST ['p_id'];
    $sql_edit = "SELECT * FROM products where p_id='$p_id'";
    $result_show = mysqli_query($conn,$sql_edit) or die(mysqli_error($conn));
    $row_show = mysqli_fetch_array($result_show); //ดึงข้อมูลมาเก็บใน $row_show

    $sql_type = "SELECT * FROM type";
    $objQuery = mysqli_query($conn,$sql_type) or die(mysqli_error($conn));
    ?>

    <div class="container-edit-product">
        <form action="update_pro.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="p_id" value="<?= $row_show['p_id']; ?>">
            <input type="hidden" name="old_img" value="<?= $row_show['p_img']; ?>">

            <div class="mb-3">
                <label for="type_id" class="form-label">หมวดหมู่สินค้า</label>
                <select name="type_id" id="type_id" class="form-control">
                    <?php while ($objResult = mysqli_fetch_array($objQuery)) { ?>
                        <option value="<?php echo $objResult["type_id"]; ?>"
                            <?php if ($row_show["type_id"] == $objResult["type_id"]) {
                                echo "selected";
                            } ?>>
                            <?php echo $objResult["type_name"]; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label for="p_name">ชื่อสินค้า</label>
                <input type="text" class="form-control" id="p_name" name="p_name" value="<?= $row_show['p_name']; ?>">
            </div>
            <div class="form-group">
                <label for="p_price">ราคา</label>
                <input type="text" class="form-control" id="p_price" name="p_price" value="<?= $row_show['p_price']; ?>">
            </div>
            <div class="mb-3">
                <label for="file" class="form-label">ภาพสินค้า</label>
                <input type="file" id="file" name="p_img" accept="upload/*" value="<?= $row_show['p_img']; ?>">
            </div>

            <button type="submit" class="btn-submit">บันทึก</button>
            <a href="show_pro.php" class="btn-cencel">ยกเลิก</a>
        </form>

    </div>
</body>

</html>
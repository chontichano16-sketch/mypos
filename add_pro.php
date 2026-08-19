<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>เพิ่มสินค้า</title>
  <link rel="stylesheet" href="style2.css">
</head>

<body>
  <!-- navber  -->
  <?php include "navbar.php"; ?>
  <div class="add-product-container">
    <div class="add-product-card">
      <h2>เพิ่มเมนูอาหารใหม่</h2>

      <form action="save_pro.php" method="post" enctype="multipart/form-data">
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
          <button type="reset" class="btn-reset">ยกเลิก</button>
          <button type="submit" class="btn-submit">บันทึกข้อมูล</button>
        </div>
      </form>
    </div>
    <script src="script.js"></script>
  </div>

</body>

</html>
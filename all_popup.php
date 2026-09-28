<!--====================================== popup เพิ่มสินค้า ===========================================-->
<div id="addProductModal" class="modal-overlay" style="display: none;">

    <div class="modal-content">
        <div class="modal-header">
            <h4 style="color: #63554c;">เพิ่มเมนูใหม่</h4>
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
            <h4 style=" color: #63554c;">เพิ่มประเภทสินค้าใหม่</h4>
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
                            <th style="padding: 10px; text-align: center; color: #63554c;">เลขที่บิล</th>
                            <th style="padding: 10px; text-align: center; color: #63554c;">เบอร์โต๊ะ</th>
                            <th style="padding: 10px; text-align: center; color: #63554c;">เวลาเปิดบิล</th>
                            <th style="padding: 10px; text-align: center; color: #63554c;">ยอดรวม</th>
                            <th style="padding: 10px; text-align: center; color: #63554c;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody id="billListBody">
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 20px; color: #63554c;">กำลังโหลดข้อมูล...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ========================== popup ออเดอร์ใหม่ =========================-->
<div id="newOrderModal" class="modal-overlay" style="display: none; ">
    <div class="modal-content" style="height: 40vh;">
        <div>
            <h4>ออเดอร์ใหม่จากลูกค้า</h4>
            <button class="close-btn-clean" onclick="closeModal()">&times;</button>
        </div>
        <hr>
        <div id="newOrderList">กำลังโหลดข้อมูล...</div>
    </div>
</div>

<!-- popup เพิ่มโต๊ะ -->
<div id="addTableModal" class="modal-overlay" style="display: none;" enctype="multipart/form-data">
    <div class="modal-content">
        <div class="modal-header">
            <h4 style=" color: #63554c;">เพิ่มโต๊ะใหม่</h4>
            <button class="close-btn-clean" onclick="closeModal()">&times;</button>
        </div>
        <form id="addTableForm" onsubmit="submitAddTable(event)">
            <div class="form-group">
                <label>หมายเลยโต๊ะ</label>
                <input type="text" name="table_name" id="table_name">
            </div>

            <div class="form-buntons">
                <button type="button" class="btn-reset" onclick="closeModal()">ยกเลิก</button>
                <button type="submit" class="btn-submit">บันทึกข้อมูล</button>
            </div>
        </form>
    </div>
</div>
<!-- end -->
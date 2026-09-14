<?php
require 'db.php';
$order_id = (int)$_GET['id'];
$sql = "SELECT od.quantity, od.remark, p.p_name FROM order_detail od JOIN products p ON od.product_id = p.p_id WHERE od.order_id = $order_id";
$res = mysqli_query($conn, $sql);
?> 

<div id="printArea" style="width: 200px; font-family: sans-serif;">
    <h3>รายการอาหาร (บิล <?php echo $order_id; ?>)</h3>
    <hr>
    <?php if (mysqli_num_rows($res) > 0) { ?>
        <?php while($row = mysqli_fetch_assoc($res)) { ?>
            <p>
                <b><?php echo $row['quantity']; ?> x <?php echo $row['p_name']; ?></b><br>
                <?php if(!empty($row['remark'])) echo "<small>หมายเหตุ: " . $row['remark'] . "</small>"; ?>
            </p>
        <?php } ?>
    <?php } else { ?>
        <p>ไม่พบรายการอาหารในบิลนี้</p>
    <?php } ?>
</div>

<script>
    window.print();
    // ย้ายข้อมูลหลังจากพิมพ์เสร็จ
    fetch('api/move_order_process.php?id=<?php echo $order_id; ?>'); 
    
    setTimeout(() => { window.close(); }, 2000);
</script>

<?php 
?>
<?php
require_once 'db.php';

// รับค่ารหัสบิลจาก URL
if (!isset($_GET['id'])) {
    die("ไม่พบรหัสบิลสำหรับการพิมพ์");
}

$order_id = intval($_GET['id']);

//  ดึงข้อมูลหลักของบิล (ตาราง order)
$sql_order = "SELECT * FROM `order` WHERE order_id = $order_id";
$result_order = mysqli_query($conn, $sql_order);
if (mysqli_num_rows($result_order) == 0) {
    die("ไม่พบข้อมูลบิลนี้ในระบบ");
}
$order = mysqli_fetch_assoc($result_order);

//  ดึงรายการอาหารในบิล (เชื่อมตาราง order_detail กับตารางสินค้า)
$sql_details = "
    SELECT od.*, p.p_name AS product_name 
    FROM order_detail od 
    LEFT JOIN products p ON od.product_id = p.p_id 
    WHERE od.order_id = $order_id
";
$result_details = mysqli_query($conn, $sql_details);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ใบเสร็จรับเงิน #<?php echo $order_id; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 20px;
            color: #000;
        }

        /* จำลองกระดาษใบเสร็จ */
        .receipt-container {
            background-color: #fff;
            width: 80mm;
            /* ขนาดมาตรฐานเครื่องพิมพ์ใบเสร็จ */
            max-width: 100%;
            margin: 0 auto;
            padding: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .store-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .receipt-header,
        .receipt-footer {
            font-size: 12px;
            margin-bottom: 10px;
        }

        .divider {
            border-bottom: 1px dashed #000;
            margin: 10px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        th,
        td {
            padding: 4px 0;
            vertical-align: top;
        }

        th {
            border-bottom: 1px dashed #000;
            text-align: left;
        }

        .total-section {
            font-size: 16px;
            font-weight: bold;
            margin-top: 10px;
        }

        /* ตั้งค่าตอนพิมพ์จริง (ซ่อนพื้นหลังและเงา) */
        @media print {
            body {
                background-color: #fff;
                padding: 0;
                margin: 0;
            }

            .receipt-container {
                width: 100%;
                box-shadow: none;
                padding: 0;
            }

            @page {
                margin: 0;
                /* ตัดขอบกระดาษ */
            }
        }

        @media print {
            @page {
                size: 80mm auto;
                /* บังคับขนาดกระดาษเป็นความกว้าง 80mm ตามความยาวเนื้อหา */
                margin: 0;
            }

            body {
                background-color: #fff;
                padding: 0;
                margin: 0;
            }

            .receipt-container {
                width: 100%;
                box-shadow: none;
                padding: 5px;
            }
        }
    </style>
</head>

<body>

    <div class="receipt-container">
        <!-- ส่วนหัวใบเสร็จ -->
        <div class="text-center">
            <div class="store-name">The Story เรื่องเล่าร้านกาแฟ</div>
            <div class="receipt-header">
                414 ถนนเลย เชียงคาน<br>
                ตำบลเมือง อำเภอเมือง จังหวัดเลย<br>
                โทร: 083-606-6697<br>
                -------------------------<br>
                ใบเสร็จรับเงิน
            </div>
        </div>

        <div class="receipt-header">
            <strong>เลขที่บิล:</strong> #<?php echo str_pad($order_id, 5, '0', STR_PAD_LEFT); ?><br>
            <strong>วันที่:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?><br>
            <strong>โต๊ะ:</strong> <?php echo ($order['table_id'] == '0' || $order['table_id'] == 'Takeaway') ? 'กลับบ้าน' : $order['table_id']; ?>
        </div>

        <div class="divider"></div>
        <!-- รายการอาหาร -->
        <table>
            <thead>
                <tr>
                    <th style="width: 15%;">จำนวน</th>
                    <th style="width: 55%;">รายการ</th>
                    <th style="width: 30%;" class="text-right">ราคา</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($result_details) > 0) {
                    while ($item = mysqli_fetch_assoc($result_details)) {
                        $item_total = $item['quantity'] * $item['price'];
                ?>
                        <tr>
                            <td class="text-center"><?php echo $item['quantity']; ?></td>
                            <td>
                                <?php echo htmlspecialchars($item['product_name']); ?>
                                <?php if (!empty($item['option_label'])) {
                                    echo "<br><small>* " . htmlspecialchars($item['option_label']) . "</small>";
                                } ?>
                                <?php if (!empty($item['remark'])) {
                                    echo "<br><small>- " . htmlspecialchars($item['remark']) . "</small>";
                                } ?>
                                </td>
                            <td class="text-right"><?php echo number_format($item_total, 2); ?></td>
                        </tr>
                <?php
                    }
                } else {
                    echo '<tr><td colspan="3" class="text-center">ไม่พบรายการอาหาร</td></tr>';
                }
                ?>
            </tbody>
        </table>

        <div class="divider"></div>
        <!-- สรุปยอด -->
        <div class="total-section">
            <table style="font-size: 16px;">
                <tr>
                    <td>ยอดรวมสุทธิ:</td>
                    <td class="text-right"><?php echo number_format($order['total_amount'], 2); ?> บาท</td>
                </tr>
            </table>
        </div>

        <div class="receipt-footer text-right" style="margin-top: 5px;">
            ชำระโดย: <?php echo ($order['payment_method'] == 'Cash') ? 'เงินสด' : 'พร้อมเพย์'; ?>
        </div>

        <div class="divider"></div>
        <div class="text-center receipt-footer">
            ขอบคุณที่ใช้บริการครับ/ค่ะ<br>
            <!-- Developed by Chonticha -->
        </div>
    </div>

    <!-- สั่งพิมพ์อัตโนมัติเมื่อเปิดหน้านี้ -->
    <script>
        window.onload = function() {
            window.print();

            window.onafterprint = function() {
                window.close();
            }
        };
    </script>

</body>

</html>
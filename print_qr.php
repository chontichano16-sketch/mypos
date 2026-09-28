<?php
require 'db.php'; 

// ดึงรายการโต๊ะทั้งหมดที่เปิดใช้งานอยู่
$sql = "SELECT * FROM `tables` WHERE `is_active` = 1 ORDER BY `tables_id` ASC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>พิมพ์ QR Code โต๊ะ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@100..900&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            font-family: "Noto Sans Thai", sans-serif;
        }

        body {
            background-color: #f0f2f5;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .no-print-bar {
            background: #ffffff;
            width: 100%;
            max-width: 850px;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .btn {
            padding: 10px 24px;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .btn-success {
            background-color: #28a745;
            color: white;
        }

        .btn-success:hover {
            background-color: #218838;
        }

        .a4-container {
            background: #ffffff;
            width: 210mm;
            min-height: 297mm;
            padding: 12mm 10mm;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border-radius: 4px;
        }

        .qr-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }

        .qr-card {
            border: 2px dashed #888888;
            border-radius: 16px;
            padding: 15px 10px;
            text-align: center;
            background-color: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
        }

        .shop-title {
            font-size: 13px;
            font-weight: 700;
            color: #222222;
            margin-bottom: 4px;
        }

        .table-title {
            font-size: 22px;
            font-weight: 700;
            color: #111111;
            margin-bottom: 8px;
        }

        .qr-code-img {
            width: 160px;
            height: 160px;
            object-fit: contain;
            margin-bottom: 12px;
        }

        .scan-btn-badge {
            background-color: #3d3532;
            color: #ffffff;
            padding: 6px 0;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            width: 85%;
        }

        /* ตั้งค่าสไตล์เมื่อสั่งพิมพ์จริง (Print CSS) */
        @media print {
            @page {
                size: A4 portrait;
                margin: 8mm;
            }

            body {
                background: none;
                padding: 0;
            }

            .no-print-bar {
                display: none !important;
                /* ซ่อนแถบปุ่มตอนปริ้น */
            }

            .a4-container {
                box-shadow: none;
                width: 100%;
                min-height: auto;
                padding: 0;
            }

            .qr-grid {
                gap: 10mm 6mm;
            }

            .qr-card {
                page-break-inside: avoid;
                border-color: #555555;
            }
        }
    </style>
</head>

<body>
    <!-- ปุ่มด้านบน -->
    <div class="no-print-bar">
        <a href="index.php" class="btn btn-secondary">กลับหน้าแรก</a>
        <button class="btn btn-success" onclick="window.print()"><i class="fa-solid fa-print"></i> กดเพื่อสั่งพิมพ์ (A4)</button>
    </div>

    <div class="a4-container">
        <div class="qr-grid">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <?php
                    $tableName = htmlspecialchars($row['tables_number']);
                    // ดึงลิงก์จาก DB มาเข้ารหัส URL สำหรับส่งให้ API สร้างรูป QR
                    $qrLink = urlencode($row['qr_link']);
                    $qrImageUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . $qrLink;
                    ?>
                    <div class="qr-card">
                        <div class="shop-title">The Story เรื่องเล่าร้านกาแฟ</div>
                        <div class="table-title">โต๊ะที่ <?php echo $tableName; ?></div>
                        <img src="<?php echo $qrImageUrl; ?>" alt="QR Code โต๊ะ <?php echo $tableName; ?>" class="qr-code-img">
                        <div class="scan-btn-badge">สแกนเพื่อสั่งอาหาร</div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="grid-column: 1/-1; text-align: center; font-size: 18px; color: #666;">ไม่พบข้อมูลโต๊ะในระบบ</p>
            <?php endif; ?>
        </div>
    </div>

</body>

</html>
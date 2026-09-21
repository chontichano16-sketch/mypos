<?php
$server_ip = "172.21.68.126";
$total_tables = 9;
$shop_name = "The Story เรื่องเล่าร้านกาแฟ";
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>พิมพ์ QR Code โต๊ะอาหาร</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css" integrity="sha512-QeR2VH+lsBE5LSAe1Q5EnTBbe7XTBubt8dG93Y7gidSgdMCr8nVqKcfKAMyN96SV8KDbZVTDXChatu5G2KQGzg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            font-family: 'Kanit', sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 20px;
        }

        .top-bar {
            display: flex;
            justify-content: center;
            gap: 15px;
            /* ระยะห่างระหว่างปุ่ม */
            margin-bottom: 20px;
            padding: 15px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* สไตล์ปุ่มกดทั่วไป */
        .btn {
            border: none;
            padding: 10px 25px;
            font-size: 18px;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Kanit', sans-serif;
            text-decoration: none;
            color: white;
            display: inline-flex;
            align-items: center;
        }

        /* ปุ่มพิมพ์ */
        .btn-print {
            background-color: #4CAF50;
        }

        .btn-print:hover {
            background-color: #45a049;
        }

        /* ปุ่มกลับหน้าแรก */
        .btn-back {
            background-color: #6c757d;
        }

        .btn-back:hover {
            background-color: #5a6268;
        }

        /* จัด Layout เป็น Grid สำหรับกระดาษ A4 */
        .qr-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            max-width: 210mm;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
        }

        /* ดีไซน์การ์ดแต่ละใบ */
        .qr-card {
            border: 2px dashed oklch(54.7% 0.021 43.1);
            border-radius: 15px;
            padding: 15px;
            text-align: center;
            background-color: oklch(98.5% 0.001 106.423);
            break-inside: avoid;
        }

        .qr-card h2 {
            margin: 0 0 5px 0;
            font-size: 18px;
            color: #333;
        }

        .qr-card .table-num {
            font-size: 26px;
            color: oklch(36.7% 0.016 35.7);
            font-weight: bold;
            margin-bottom: 10px;
        }

        .qr-card img {
            border-radius: 10px;
            border: 1px solid #ddd;
            padding: 5px;
            background: white;
            width: 150px;
            height: 150px;
        }

        .qr-card .instruction {
            margin-top: 15px;
            font-size: 14px;
            background: oklch(36.7% 0.016 35.7);
            color: white;
            padding: 5px;
            border-radius: 20px;
        }

        /* โหมดสำหรับเครื่องพิมพ์ */
        @media print {
            body {
                background: white;
                padding: 0;
            }

            .top-bar {
                display: none;
            }

            /* ซ่อนทั้งแถบ ตอนพิมพ์ */
            .qr-grid {
                box-shadow: none;
                padding: 0;
                gap: 10px;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>

<body>

    <div class="top-bar">
        <a href="index.php" class="btn btn-back">กลับหน้าแรก</a>
        <button class="btn btn-print" onclick="window.print()"><i class="fa-solid fa-print"></i> กดเพื่อสั่งพิมพ์ (A4)</button>
    </div>

    <!-- วนลูปสร้างการ์ดตามจำนวนโต๊ะ -->
    <div class="qr-grid">
        <?php for ($i = 1; $i <= $total_tables; $i++): ?>
            <!-- สร้าง URL สำหรับโต๊ะ -->
            <?php
            $table_url = "http://{$server_ip}/mypos/customer_menu.php?tables_id={$i}";
            // API สร้างรูป
            $qr_api_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($table_url);
            ?>
            <div class="qr-card">
                <h2><?php echo htmlspecialchars($shop_name); ?></h2>
                <div class="table-num">โต๊ะที่ <?php echo $i; ?></div>
                <img src="<?php echo $qr_api_url; ?>" alt="QR โต๊ะ <?php echo $i; ?>">
                <div class="instruction"> สแกนเพื่อสั่งอาหาร</div>
            </div>
        <?php endfor; ?>
    </div>

</body>

</html>
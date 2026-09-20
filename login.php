<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - The Story</title>
    <!-- Google Fonts: Kanit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- CSS File -->
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="card">
        <!-- ฝั่งซ้าย: Welcome Banner -->
        <div class="left">
           <img src="img/Logogogo.png" alt="The Story Logo" class="welcome-logo" style="width: 140px; max-width: 140px; height: auto; display: block; margin: 0 auto 15px auto; filter: brightness(0) invert(1);">

            <div class="welcome-block">
                <h1>Welcome</h1>
                <h1>To The Story</h1>
                <p class="brand-sub">เรื่องเล่าร้านกาแฟ</p>
            </div>
        </div>

        <!-- ฝั่งขวา: ฟอร์มใส่ PIN -->
        <form action="chk.php" method="post" id="loginForm">
            <input type="hidden" name="username" value="admin">
            <input type="hidden" name="pin" id="pin">

            <div class="right">
                <div class="clerk-icon">👤</div>
                <h2>เจ้าของร้าน</h2>
                <p class="sub">กรุณากรอกรหัส PIN เพื่อเข้าสู่ระบบ</p>

                <!-- จุดแสดงรหัส PIN -->
                <div class="pin-dots" id="pinDots">
                    <div class="pin-box"></div>
                    <div class="pin-box"></div>
                    <div class="pin-box"></div>
                    <div class="pin-box"></div>
                </div>

                <!-- แป้นพิมพ์ตัวเลข -->
                <div class="keypad" id="keypad">
                    <div class="key" data-k="1">1</div>
                    <div class="key" data-k="2">2</div>
                    <div class="key" data-k="3">3</div>
                    <div class="key" data-k="4">4</div>
                    <div class="key" data-k="5">5</div>
                    <div class="key" data-k="6">6</div>
                    <div class="key" data-k="7">7</div>
                    <div class="key" data-k="8">8</div>
                    <div class="key" data-k="9">9</div>
                    <div class="key empty"></div>
                    <div class="key" data-k="0">0</div>
                    <div class="key" data-k="back">⌫</div>
                </div>

                <!-- ข้อความแสดงสถานะ error -->
                <div class="status" id="status"></div>

                <!-- ลิงก์ลืมรหัสผ่าน -->
                <!-- <div class="forgot-password">
                    <a href="forgot-password.php">ลืมรหัสผ่าน?</a>
                </div> -->
            </div>
        </form>
    </div>

    <script src="script.js"></script>
</body>

</html>
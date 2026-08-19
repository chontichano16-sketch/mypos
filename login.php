<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="card">
        <div class="left">
            <div>
                <!-- <div class="shop-icon"></div> -->
                <div class="welcome-block">
                    <h1>Welcome</h1>
                    <h1>To The Story</h1>
                </div>
            </div>
        </div>
        <form action="chk.php" method="post" id="loginForm">
            <input type="hidden" name="username" value="admin">
            <input type="hidden" name="pin" id="pin">

            <div class="right">
                <div class="back-pill">เข้าสู่ระบบเครื่อง</div>
                <div class="clerk-icon">👤</div>
                <h2>เจ้าของร้าน</h2>
                <p class="sub">กรุณากรอกรหัส PIN เพื่อเข้าสู่ระบบ</p>

                <div class="pin-dots" id="pinDots">
                    <div class="pin-box"></div>
                    <div class="pin-box"></div>
                    <div class="pin-box"></div>
                    <div class="pin-box"></div>
                </div>

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
                </div><a href="#" class="forgot-pin">ลืมรหัส PIN ใช่หรือไม่?</a>

                <div class="status" id="status"></div>
            </div>
        </form>
    </div>
    <script src="script.js"></script>
</body>

</html>
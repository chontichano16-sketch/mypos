<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
?>

<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css" integrity="sha512-QeR2VH+lsBE5LSAe1Q5EnTBbe7XTBubt8dG93Y7gidSgdMCr8nVqKcfKAMyN96SV8KDbZVTDXChatu5G2KQGzg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        .user-menu {
            position: relative;
            display: inline-block;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 8px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            color: #333;
            padding: 6px 10px;
            border-radius: 8px;
            transition: background .2s;
        }

        .user-info:hover {
            background: #f0f0f0;
        }

        .user-info i {
            font-size: 22px;
        }

        .user-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            min-width: 180px;
            background: #fff;
            border: 1px solid #e5e5e5;
            border-radius: 10px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, .12);
            padding: 6px;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-6px);
            transition: all .18s ease;
        }

        .user-dropdown.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .user-dropdown a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            color: #333;
            text-decoration: none;
            font-size: 15px;
        }

        .user-dropdown a:hover {
            background: #f5f5f5;
        }

        .user-dropdown a.logout {
            color: #d9534f;
        }

        .user-dropdown a.logout:hover {
            background: #fdecea;
        }
    </style>
</head>

<!-- ปุ่มแฮมเบอร์เกอร์ -->
<nav class="navbar">
    <div class="dropdown">
        <button onclick="toggleMenu(event)" class="dropbtn"><i class="fa-solid fa-bars"></i></button>

        <div id="myDropdown" class="dropdown-content" style="border: none;">
            <button class="menu-btn"> <i class="bi bi-chevron-down" style="float: right;"></i></i>จัดการข้อมูลโต๊ะ</button>
            <ul class="submenu">
                <li><a href="print_qr.php"><i class="bi bi-qr-code"></i> พิมพ์ QR Code โต๊ะ</a></li>
            </ul>
            <button class="menu-btn"><i class="bi bi-chevron-down" style="float: right;"></i>จัดการข้อมูลเมนูอาหาร</button>

            <ul class="submenu">
                <li><button onclick="openModal('product')">เพิ่มสินค้า</button></li>
                <li><button onclick="openModal('type')">เพิ่มประเภทสินค้า</button></li>
                <li><a href="show_pro.php" style="border-bottom: 1px solid #63554c1f;">รายการสินค้าทั้งหมด</a></li>
                <li><a href="show_type.php" style="border-bottom: 1px solid #63554c1f;">ประเภทสินค้าทั้งหมด</a></li>
            </ul>

            <a href="sale_report.php">รายงานยอดขาย</a>
        </div>
    </div>

    <ul class="nav-links" id="nav-links">
        <li><a href="index.php" class="active">หน้าร้าน</a></li>
        <li><button onclick="openNewOrderModal()">ออเดอร์ใหม่</button></li>
        <li><button onclick="openModal('order')">เปิดบิล</button></li>
    </ul>

    <form action="" method="get" class="search-box">
        <input type="text" id="search-menu" onkeyup="filterMenu()" placeholder="ค้นหาเมนู...">
        <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
    </form>

    <div class="user-menu">
        <button type="button" class="user-info" id="userInfoBtn" onclick="toggleUserMenu(event)">
            <?php echo $_SESSION["fullname"]; ?>
            <i class="fa-solid fa-circle-user"></i>
        </button>

        <div class="user-dropdown" id="userDropdown">
            <!-- <a href="profile.php"><i class="fa-solid fa-user"></i> โปรไฟล์</a> -->
            <a href="logout.php" class="logout"><i class="bi bi-box-arrow-right"></i> ออกจากระบบ</a>
        </div>
    </div>

    <script>
        function toggleUserMenu(e) {
            e.stopPropagation();
            document.getElementById('userDropdown').classList.toggle('show');
        }

        // คลิกที่อื่นแล้วปิดเมนู
        document.addEventListener('click', function() {
            document.getElementById('userDropdown').classList.remove('show');
        });

        // กด ESC ปิดเมนู
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.getElementById('userDropdown').classList.remove('show');
            }
        });
    </script>
</nav>
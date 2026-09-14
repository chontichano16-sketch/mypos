<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
?>

<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css" integrity="sha512-QeR2VH+lsBE5LSAe1Q5EnTBbe7XTBubt8dG93Y7gidSgdMCr8nVqKcfKAMyN96SV8KDbZVTDXChatu5G2KQGzg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<!-- ปุ่มแฮมเบอร์เกอร์ -->
<nav class="navbar">
    <div class="dropdown">
        <button onclick="toggleMenu(event)" class="dropbtn"> &#9776; </button>

        <div id="myDropdown" class="dropdown-content">
            <button class="menu-btn"><i class="bi bi-chevron-down" style="float: right;"></i>จัดการข้อมูลโต๊ะ</button>
            <ul class="submenu">
                <li><a href="create_QR"><i class="bi bi-qr-code"></i> สร้าง QR Code โต๊ะ</a></li>
            </ul>
            <button class="menu-btn"><i class="bi bi-chevron-down" style="float: right;"></i>จัดการข้อมูลเมนูอาหาร</button>

            <ul class="submenu">
                <li><button onclick="openModal('product')">เพิ่มสินค้า</button></li>
                <li><button onclick="openModal('type')">เพิ่มประเภทสินค้า</button></li>
            </ul>
            <a href="show_pro.php">รายการสินค้าทั้งหมด</a>
            <a href="sale_report.php">รายงานยอดขาย</a>
            <a href="logout.php"><i class="bi bi-box-arrow-right" style="float: right;"></i> ออกจากระบบ</a>
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
    <div class="user-info"><?php echo $_SESSION["fullname"]; ?><i class="fa-solid fa-circle-user"></i></div>

</nav>
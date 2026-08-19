<?php
    session_start();

    if (!isset($_SESSION["user_id"])) {
        header("Location: login.php");
        exit();
    }
    ?>
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
            <a href="#">รายงานยอดขาย</a>
            <a href="logout.php"><i class="bi bi-box-arrow-right" style="float: right;"></i> ออกจากระบบ</a>
        </div>
    </div>

    <ul class="nav-links" id="nav-links">
        <li><a href="index.php" class="active">หน้าร้าน</a></li>
        <li><a href="neworder.php">ออเดอร์ใหม่</a></li>
        <li><button onclick="openModal('order')">เปิดบิล</button></li>
    </ul>
    
    <form action="search.php" method="get" class="search-box">
        <input type="text" name="keyword" placeholder="ค้นหาเมนู...">
        <button type="submit"><i class="bi bi-search"></i></button>
    </form>
    <div class="user-info"><?php echo $_SESSION["fullname"]; ?><i class="bi bi-person-circle"></i></div>
</nav>

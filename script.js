// Hambergers
function toggleMenu(event) {
    event.stopPropagation();
    document.getElementById("myDropdown").classList.toggle("show");
}

window.addEventListener('click', function (event) {

    const dropbtn = document.querySelector('.dropbtn');
    const dropdownContent = document.getElementById('myDropdown');

    if (dropbtn && dropdownContent) {
        if (
            !dropbtn.contains(event.target) &&
            !dropdownContent.contains(event.target)
        ) {
            dropdownContent.classList.remove('show');
        }
    }
});

// ฟังก์ชันคลิกเมนู เพิ่มรายการออเดอร์
let orderItems = [];
function addToOrder(card) {
    const id = card.dataset.id;
    const name = card.dataset.name;
    // แปลงตัวเลขให้คำนวณได้ 
    const price = parseFloat(card.dataset.price);

    const existing = orderItems.find(item => item.id === id);
    // ถ้าเจอเมนูซ่ำ +1 ถ้าไม่มี เพิ่มเมนูใหม่
    if (existing) {
        existing.quantity += 1;
    } else {
        orderItems.push({ id, name, price, quantity: 1 });
    }

    renderOrder();
}
// ฟังก์ชันกด + / − บนรายการออเดอร์
// id = รายการไหน, delta = จะเปลี่ยนเท่าไหร่ (+1 หรือ -1)
function changeQty(id, delta) {
    const item = orderItems.find(i => i.id === id);
    // ถ้าหาไม่เจอให้หยุดทำงานทันที (ป้องกัน error)
    if (!item) return;

    item.quantity += delta;
    if (item.quantity <= 0) {
        orderItems = orderItems.filter(i => i.id !== id);
    }
    renderOrder();
}
// วาด HTML ของรายการออเดอร์ใหม่ทุกครั้ง
function renderOrder() {
    sessionStorage.setItem('orderItems', JSON.stringify(orderItems));
    const container = document.querySelector('.order-items-container');

    if (orderItems.length == 0) {
        container.innerHTML = '<p style="text-align:center; color:#aaa;">รายการที่สั่งจะแสดงที่นี่</p>';
        updateTotal();
        return;
    }

    // .map() วนแปลงทุก item ใน array ให้เป็น HTML string
    // .join('') รวม array ของ string ทั้งหมดให้เป็น string เดียว แล้วยัดเข้า HTML
    container.innerHTML = orderItems.map(item => ` 
        <div class="order-row" onclick="changeQty('${item.id}', +1)" style="cursor:pointer;">
                <span class="order-name">${item.name} x${item.quantity}</span>
                <span class="order-price">${(item.price * item.quantity).toFixed(2)}</span>
        </div>
    `).join('');

    updateTotal();
}
function updateTotal() {
    const total = orderItems.reduce((sum, i) => sum + i.price * i.quantity, 0);
    // อัปเดตข้อความยอดรวมใน HTML
    document.querySelector('.order-section strong').textContent =
        // .toFixed(2) = แสดงทศนิยม 2 ตำแหน่ง
        `รวมทั้งหมด ${total.toFixed(2)} บาท`;
}

 
// ปุ่มบันทึกออเดอร์
function saveOrder() {
    orderItems = JSON.parse(sessionStorage.getItem('orderItems')) || [];

    if (orderItems.length === 0) {
        alert('กรุณาเลือกรายการอาหารก่อนบันทึก');
        return;
    }

    const table = document.getElementById('tables').value;
    console.log('table:', table); // เช็คค่า

    if (!table || table === 'T.0' || table === '' || table === 'ไม่ได้เลือก') {
        alert('กรุณาเลือกโต๊ะก่อนบันทึก');
        return;
    }

    const data = {
        table_id: table,
        items: orderItems
    };

    fetch('save_order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
        .then(res => res.json())
        .then(result => {
            if (result.success) {
                alert('บันทึกออเดอร์สำเร็จ!');
                orderItems = [];
                sessionStorage.removeItem('orderItems');
                renderOrder();
            } else {
                alert('เกิดข้อผิดพลาด: ' + result.message);
            }
        })
        .catch(err => {
            alert('ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้');
            console.error(err);
        });
}
// เมนูย่อย
const menuBtn = document.querySelectorAll('.menu-btn');

menuBtn.forEach(btn => {
    btn.addEventListener('click', () => {

        const submenu = btn.nextElementSibling;

        document.querySelectorAll('.submenu').forEach(menu => {
            if (menu !== submenu) {
                menu.style.display = 'none';
            }
        });

        submenu.style.display =
            submenu.style.display === 'block'
                ? 'none'
                : 'block';
    });
});

//-==================================== popup  =========================================
function openModal(type) {
    let modalProduct = document.getElementById('addProductModal');
    let modalType = document.getElementById('addTypeModal');
    let modalOrder = document.getElementById('openOrder');

    if (modalProduct) modalProduct.style.display = 'none';
    if (modalType) modalType.style.display = 'none';
    if (modalOrder) modalOrder.style.display = 'none';

    if (type === 'product') {
        if (modalProduct) modalProduct.style.display = 'flex';
    } else if (type === 'type') {
        if (modalType) modalType.style.display = 'flex';
    } else if (type === 'order') {
        if (modalOrder) {
            modalOrder.style.display = 'flex';

            fetchBillsData();
        } else {
            console.error('หา Popup id="openOrder" ไม่เจอ กรุณาตรวจสอบว่ามี HTML นี้ในหน้าปัจจุบันหรือไม่');
        }
    }
}
function closeModal() {
    const addProductModal = document.getElementById('addProductModal');
    const addTypeModal = document.getElementById('addTypeModal');
    const openOrder = document.getElementById('openOrder');

    if (addProductModal) addProductModal.style.display = 'none';
    if (addTypeModal) addTypeModal.style.display = 'none';
    if (openOrder) openOrder.style.display = 'none';

    const allInputs = document.querySelectorAll('#addProductModal input, #addTypeModal input');
    allInputs.forEach(input => {
        input.value = "";
    });
}

// ========================================= login ===============================================

let pin = "";

const boxes = document.querySelectorAll(".pin-box");
const statusEl = document.getElementById("status");
const keypad = document.getElementById("keypad");

const inputPin = document.getElementById("pin");
const loginForm = document.getElementById("loginForm");

function render() {
    boxes.forEach((box, index) => {
        if (index < pin.length) {
            box.innerHTML = "●";
            box.classList.add("filled");
        } else {
            box.innerHTML = "";
            box.classList.remove("filled");
        }
    });
    inputPin.value = pin;
}

if (keypad) {
    keypad.addEventListener("click", function (e) {
        const key = e.target.closest(".key");
        if (!key || key.classList.contains("empty")) return;
        const value = key.dataset.k;
        if (value == "back") {
            pin = pin.slice(0, -1);
            render();
            return;
        }

        if (pin.length < 4) {
            pin += value;
            render();
        }

        if (pin.length == 4) {
            loginForm.submit();
        }
    });
}

// ============================================== save menu ไม่เปลี่ยนหน้า ==================================================
function saveProductAjax(event) {
    event.preventDefault();

    let form = document.getElementById('formAddProduct');
    let formData = new FormData(form);

    fetch('save_pro.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.text())
        .then(data => {
            if (data.trim() === 'success') {
                alert('บันทึกข้อมูลเรียบร้อยแล้ว ');
                window.location.reload();
            } else {
                alert('เกิดข้อผิดพลาด: ' + data);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
        });
}

function fetchBillsData() {
    let tbody = document.getElementById('billListBody');
    if (!tbody) return;
    tbody.innerHTML = '<tr><td colspan="5" style="text-align: center;">กำลังโหลดข้อมูล...</td></tr>';

    fetch('get_bills.php')
        .then(response => response.json())
        .then(data => {
            tbody.innerHTML = '';

            if (data.length > 0) {
                data.forEach(bill => {
                    let tableId = bill.table_id ? bill.table_id : '-';
                    let note = bill.note ? bill.note : '-';

                    // สร้างแถว HTML ของตาราง
                    let row = `
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 10px; text-align: center;">${bill.order_id}</td>
                        <td style="padding: 10px; text-align: center;">${tableId}</td>
                        <td style="padding: 10px; text-align: center;">${note}</td>
                        <td style="padding: 10px; text-align: center;">${bill.formatted_date}</td>
                        <td style="padding: 10px; text-align: center;">
                            <button class="btn-bill" onclick="viewBill(${bill.order_id})">ดู</button>
                        </td>
                    </tr>
                `;
                    // เติมแถวลงในตาราง
                    tbody.innerHTML += row;
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px;">ยังไม่มีข้อมูลบิลในระบบ</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error fetching bills:', error);
            tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; color: red;">เกิดข้อผิดพลาดในการดึงข้อมูล (เช็คไฟล์ get_bills.php)</td></tr>';
        });
}

function viewBill(orderId) {
    let modalOrder = document.getElementById('openOrder');
    if (modalOrder) modalOrder.style.display = 'none';
    console.log("พนักงานต้องการเปิดบิลรหัส: " + orderId);

    fetch(`get_order_detail.php?id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // A. เปลี่ยนค่าตัวเลือกเบอร์โต๊ะ (ถ้ามี id="tables")
                let tableSelect = document.getElementById('tables');
                if (tableSelect) tableSelect.value = data.table_id;

                // B. นำรายการอาหารมาแสดงผลฝั่งขวา
                // (หมายเหตุ: เปลี่ยนคำว่า '.order-items-container' และ '.total-price' ให้ตรงกับ Class ฝั่งขวาของคุณ)
                let orderContainer = document.querySelector('.order-items-container');
                if (orderContainer) {
                    let html = '';
                    data.items.forEach(item => {
                        let sum = item.price * item.quantity;
                        html += `
                            <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #eee;">
                                <div>${item.name} x ${item.quantity}</div>
                                <div>${sum} บาท</div>
                            </div>`;
                    });
                    orderContainer.innerHTML = html;
                }

                // C. แสดงราคารวม
                let totalPriceElement = document.querySelector('.total-price');
                if (totalPriceElement) {
                    totalPriceElement.innerText = `รวมทั้งหมด ${data.total} บาท`;
                }

            } else {
                alert('ไม่พบข้อมูลออเดอร์นี้');
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
        });
}

function decreaseItem(productId) {
    // สมมติว่าตัวแปรตะกร้าสินค้าของคุณชื่อ cart (ถ้าของคุณชื่ออื่น เช่น orderItems ให้เปลี่ยนตามด้วยนะครับ)
    let index = cart.findIndex(item => item.id == productId);
    
    if (index !== -1) {
        if (cart[index].quantity > 1) {
            // ถ้ามีมากกว่า 1 ชิ้น ให้ลดจำนวนลงทีละ 1
            cart[index].quantity--;
        } else {
            // ถ้าเหลือแค่ 1 ชิ้น แล้วกดลบอีก ให้เอาออกจากตะกร้าไปเลย
            cart.splice(index, 1);
        }
        
        // **สำคัญมาก**: ตรงนี้ต้องเรียกใช้ฟังก์ชันที่ทำหน้าที่ "อัปเดตหน้าจอ" ของคุณ
        // (เช่น updateCart(), renderOrder(), หรืออัปเดต HTML ฝั่งขวามือ)
        renderCart(); 
    }
}

// =========================================================== ลูกศรเมนูย่อย ================================================================
// รอให้หน้าเว็บโหลด HTML เสร็จก่อน
document.addEventListener("DOMContentLoaded", function () {
    let menuButtons = document.querySelectorAll('.menu-btn');

    menuButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            // หาไอคอน <i> ที่อยู่ข้างในปุ่มนี้
            let icon = this.querySelector('i');
            // ถ้าเจอไอคอน ให้สลับคลาสเพื่อหมุน
            if (icon) {
                icon.classList.toggle('rotate-icon');
            }
        });
    });

});
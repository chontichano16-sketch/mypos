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

// ตัวแปรเก็บตะกร้าสินค้า (ดึงจาก sessionStorage ถ้าเคยมีของอยู่)
let orderItems = JSON.parse(sessionStorage.getItem('orderItems')) || [];
let editingIndex = null; // เก็บ index ของรายการที่กำลังแก้ไข

// เรียกให้แสดงผลตะกร้าทันทีตอนโหลดหน้าเว็บ
renderOrder();

// วาด HTML ของรายการออเดอร์ใหม่ทุกครั้ง
function renderOrder() {
    sessionStorage.setItem('orderItems', JSON.stringify(orderItems));
    const container = document.querySelector('.order-items-container');

    if (!container) return;

    if (orderItems.length == 0) {
        container.innerHTML = '<p style="text-align:center; color:#aaa;">รายการที่สั่งจะแสดงที่นี่</p>';
        updateTotal();
        return;
    }
    
    // อัปเดต HTML: กดที่ตัวรายการ/ข้อความตรงไหนก็ได้เพื่อเปิดหน้าแก้ไข
    container.innerHTML = orderItems.map((item, index) => ` 
        <div class="order-row" style="display:flex; justify-content:space-between; align-items:flex-start; padding: 8px; border-bottom: 1px solid #eee; cursor: pointer; border-radius: 4px; transition: background 0.2s;" 
             onclick="editOrderItem(${index})" 
             onmouseover="this.style.background='#f5f5f5'" 
             onmouseout="this.style.background='transparent'">
             
            <div style="display:flex; flex-direction:column; gap: 2px; flex: 1;">
                <div style="display:flex; align-items:center;">
                    <!-- ปุ่มลบ (-) กั้นไม่ให้เกิด event การคลิกแก้ไข -->
                    <button type="button" onclick="event.stopPropagation(); decreaseItem('${item.id}', '${item.remark || ''}')" 
                            style="background-color: #ab1625; color: white; border: none; border-radius: 4px; padding: 2px 8px; margin-right: 8px; cursor: pointer; font-weight:bold;">-</button>
                    
                    <span class="order-name" style="font-weight: 500; color: #333;">${item.name} x${item.quantity}</span>
                </div>
                ${item.remark ? `<small style="color: #666; margin-left: 32px; font-size: 12px;">* ${item.remark}</small>` : ''}
            </div>
            <span class="order-price" style="font-weight: bold; white-space: nowrap; color: #333;">${(item.price * item.quantity).toFixed(2)}</span>
        </div>
    `).join('');

    updateTotal();
}

function updateTotal() {
    const total = orderItems.reduce((sum, i) => sum + i.price * i.quantity, 0);
    const totalElement = document.querySelector('.order-section strong');
    if (totalElement) {
        totalElement.textContent = `รวมทั้งหมด ${total.toFixed(2)} บาท`;
    }
}

// ปุ่มบันทึกออเดอร์
function saveOrder() {
    orderItems = JSON.parse(sessionStorage.getItem('orderItems')) || [];

    if (orderItems.length === 0) {
        alert('กรุณาเลือกรายการอาหารก่อนบันทึก');
        return;
    }

    const table = document.getElementById('tables').value;

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

//-==================================== popup =========================================
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
    const paymentModal = document.getElementById('paymentModal');

    if (addProductModal) addProductModal.style.display = 'none';
    if (addTypeModal) addTypeModal.style.display = 'none';
    if (openOrder) openOrder.style.display = 'none';
    if (paymentModal) paymentModal.style.display = 'none';

    const allInputs = document.querySelectorAll('#addProductModal input, #addTypeModal input');
    allInputs.forEach(input => {
        input.value = "";
    });

    const receiveMoney = document.getElementById('receiveMoney');
    const changeMoney = document.getElementById('changeMoney');
    if (receiveMoney) receiveMoney.value = '';
    if (changeMoney) changeMoney.innerText = '0';
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
    if (inputPin) inputPin.value = pin;
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

// ============================================== ดูบิล ==================================================
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

                    let row = `
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 10px; text-align: center;">${bill.order_id}</td>
                        <td style="padding: 10px; text-align: center;">${tableId}</td>
                        <td style="padding: 10px; text-align: center;">${bill.formatted_date}</td>
                        <td style="padding: 10px; text-align: center;">
                            <button class="btn-bill" onclick="viewBill(${bill.order_id})">ดู</button>
                        </td>
                    </tr>
                `;
                    tbody.innerHTML += row;
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 20px;">ยังไม่มีข้อมูลบิลในระบบ</td></tr>';
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

    fetch(`get_order_detail.php?id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let tableSelect = document.getElementById('tables');
                if (tableSelect) tableSelect.value = data.table_id;

                let orderContainer = document.querySelector('.order-items-container');
                if (orderContainer) {
                    let html = '';
                    let grandTotal = 0;

                    data.items.forEach(item => {
                        let sum = item.price * item.quantity;
                        grandTotal += sum;

                        let remarkHtml = item.remark ? `<small style="color: gray; margin-left: 10px;">* ${item.remark}</small>` : '';

                        html += `
                            <div style="display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid #eee;">
                                <div style="display:flex; flex-direction:column;">
                                    <div>${item.name} x ${item.quantity}</div>
                                    ${remarkHtml}
                                </div>
                                <div>${sum} บาท</div>
                            </div>`;
                    });
                    orderContainer.innerHTML = html;
                }

                let totalPriceElement = document.querySelector('.total-price');
                if (totalPriceElement) {
                    totalPriceElement.innerHTML = `<strong>รวมทั้งหมด ${data.total} บาท</strong>`;
                }

            } else {
                alert('ไม่พบข้อมูลออเดอร์นี้');
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
        });

    document.getElementById('btnCloseBillView').style.display = 'inline-block';
}

function closeBillView() {
    document.getElementById('tables').value = "";

    let orderContainer = document.querySelector('.order-items-container');
    if (orderContainer) {
        orderContainer.innerHTML = 'รายการที่สั่งจะแสดงที่นี่';
    }

    let totalElement = document.querySelector('.total-price');
    if (totalElement) {
        totalElement.innerHTML = '<strong>รวมทั้งหมด 0 บาท</strong>';
    }
    document.getElementById('btnCloseBillView').style.display = 'none';
}

function decreaseItem(productId, remark) {
    let index = orderItems.findIndex(item => item.id == productId && (item.remark || '') == remark);

    if (index !== -1) {
        if (orderItems[index].quantity > 1) {
            orderItems[index].quantity--;
        } else {
            orderItems.splice(index, 1);
        }
        renderOrder();
    }
}

// =========================================================== ลูกศรเมนูย่อย ================================================================
document.addEventListener("DOMContentLoaded", function () {
    let menuButtons = document.querySelectorAll('.menu-btn');

    menuButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            let icon = this.querySelector('i');
            if (icon) {
                icon.classList.toggle('rotate-icon');
            }
        });
    });
});

// ================================================== popup เพิ่ม / แก้ไข หมายเหตุ ==========================================================
let currentSelectedItem = null;

function openOrderModal(id, name, price) {
    editingIndex = null;
    currentSelectedItem = { id: id, name: name, price: price };

    document.getElementById('modalProductName').innerText = name;
    document.getElementById('modalProductPrice').innerText = price;

    document.getElementById('modalQty').value = 1;
    document.getElementById('modalRemark').value = '';

    const confirmBtn = document.querySelector('#orderModal .btn-confirm');
    if (confirmBtn) confirmBtn.innerText = 'เพิ่มลงบิล';

    document.getElementById('orderModal').style.display = 'flex';
}

function editOrderItem(index) {
    let item = orderItems[index];
    if (!item) return;

    editingIndex = index;
    currentSelectedItem = { id: item.id, name: item.name, price: item.price };

    document.getElementById('modalProductName').innerText = item.name;
    document.getElementById('modalProductPrice').innerText = item.price;

    document.getElementById('modalQty').value = item.quantity;
    document.getElementById('modalRemark').value = item.remark || '';

    const confirmBtn = document.querySelector('#orderModal .btn-confirm');
    if (confirmBtn) confirmBtn.innerText = 'บันทึกการแก้ไข';

    document.getElementById('orderModal').style.display = 'flex';
}

function closeOrderModal() {
    document.getElementById('orderModal').style.display = 'none';
    currentSelectedItem = null;
    editingIndex = null;
}

function changeModalQty(amount) {
    let qtyInput = document.getElementById('modalQty');
    let currentQty = parseInt(qtyInput.value);
    let newQty = currentQty + amount;
    if (newQty >= 1) {
        qtyInput.value = newQty;
    }
}

function confirmAddToOrder() {
    if (!currentSelectedItem) return;

    let qty = parseInt(document.getElementById('modalQty').value);
    let remark = document.getElementById('modalRemark').value.trim();

    if (editingIndex !== null) {
        // อัปเดตรายการเดิม
        orderItems[editingIndex].quantity = qty;
        orderItems[editingIndex].remark = remark;
    } else {
        // เพิ่มรายการใหม่
        let index = orderItems.findIndex(item => item.id == currentSelectedItem.id && (item.remark || '') == remark);

        if (index !== -1) {
            orderItems[index].quantity += qty;
        } else {
            orderItems.push({
                id: currentSelectedItem.id,
                name: currentSelectedItem.name,
                price: currentSelectedItem.price,
                quantity: qty,
                remark: remark
            });
        }
    }

    closeOrderModal();
    renderOrder();
}

// ================================================== popup ชำระเงิน =================================
function togglePaymentMode() {
    let isCash = document.getElementById('paymentCash').checked;
    let cashSection = document.getElementById('cashInputSection');
    let transferSection = document.getElementById('transferInputSection');

    if (isCash) {
        cashSection.style.display = 'block';
        transferSection.style.display = 'none';
    } else {
        cashSection.style.display = 'none';
        transferSection.style.display = 'block';
    }
}

// ========================================== Popup ชำระเงิน ==========================================
function openPaymentModal() {
    let totalElement = document.querySelector('.total-price strong') || document.querySelector('.order-section strong');
    let totalText = totalElement ? totalElement.innerText : '0';
    let totalAmount = parseFloat(totalText.replace(/[^0-9.]/g, '')) || 0;

    if (totalAmount <= 0) {
        alert('กรุณาเลือกรายการอาหารก่อนชำระเงินครับ');
        return;
    }

    document.getElementById('payTotalAmount').innerText = totalAmount.toFixed(2);

    let promptpayNo = "0981833902";
    let payload = generatePromptPayPayload(promptpayNo, totalAmount);
    let qrImageUrl = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(payload)}`;

    document.getElementById('qrImage').src = qrImageUrl;

    document.getElementById('receiveMoney').value = '';
    document.getElementById('changeMoney').innerText = '0';
    document.getElementById('paymentCash').checked = true;
    togglePaymentMode();

    document.getElementById('paymentModal').style.display = 'flex';
}

function generatePromptPayPayload(promptpayID, amount) {
    let target = promptpayID.replace(/[^0-9]/g, '');
    let targetTag = '';

    if (target.length >= 15) {
        targetTag = "0315" + target;
    } else if (target.length >= 13) {
        targetTag = "0213" + target;
    } else {
        let formattedPhone = "0066" + target.substring(1);
        targetTag = "0113" + formattedPhone;
    }

    let tag29Value = "0016A000000677010111" + targetTag;
    let tag29Length = tag29Value.length.toString().padStart(2, '0');
    let tag29 = "29" + tag29Length + tag29Value;

    let payload = "000201";
    payload += amount ? "010211" : "010212";
    payload += tag29;
    payload += "5303764";

    if (amount) {
        let amountStr = parseFloat(amount).toFixed(2);
        let amountLength = amountStr.length.toString().padStart(2, '0');
        payload += "54" + amountLength + amountStr;
    }

    payload += "5802TH";
    payload += "6304";

    let crc = 0xFFFF;
    for (let i = 0; i < payload.length; i++) {
        crc ^= (payload.charCodeAt(i) << 8);
        for (let j = 0; j < 8; j++) {
            if ((crc & 0x8000) !== 0) {
                crc = ((crc << 1) ^ 0x1021) & 0xFFFF;
            } else {
                crc = (crc << 1) & 0xFFFF;
            }
        }
    }

    let crcHex = (crc & 0xFFFF).toString(16).toUpperCase().padStart(4, '0');
    return payload + crcHex;
}

function confirmPayment() {
    let tableId = document.getElementById('tables').value;
    if (tableId === "") {
        alert("กรุณาเลือกโต๊ะก่อนชำระเงิน");
        return;
    }

    let isCash = document.getElementById('paymentCash').checked;
    let paymentMethod = isCash ? "Cash" : "Transfer";
    let totalAmount = document.getElementById('payTotalAmount').innerText;

    if (isCash) {
        let receiveMoney = document.getElementById('receiveMoney').value;
        if (receiveMoney === "" || parseFloat(receiveMoney) < parseFloat(totalAmount)) {
            alert("กรุณากรอกเงินที่รับมาให้ถูกต้อง (ต้องไม่น้อยกว่ายอดรวม)");
            return;
        }
    }

    let formData = new FormData();
    formData.append("table_id", tableId);
    formData.append("payment_method", paymentMethod);
    formData.append("total_amount", totalAmount);

    fetch('save_payment.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.text())
        .then(data => {
            if (data.trim() === "Success"){
                alert("บันทึกการชำระเงินสำเร็จ!");
                location.reload();
            } else {
                alert("ไม่สามารถบันทึกได้: " + data);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์");
        })
}

function calculateChange() {
    let totalText = document.getElementById('payTotalAmount').innerText;
    let totalAmount = parseFloat(totalText) || 0;

    let receiveText = document.getElementById('receiveMoney').value;
    let receiveAmount = parseFloat(receiveText) || 0;

    let changeDisplay = document.getElementById('changeMoney');

    if (receiveAmount >= totalAmount) {
        let change = receiveAmount - totalAmount;
        changeDisplay.innerText = change.toFixed(2); 
    } else {
        changeDisplay.innerText = "0.00"; 
    }
}
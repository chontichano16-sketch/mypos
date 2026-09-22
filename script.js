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
                
                <!-- เพิ่ม Checkbox สำหรับพิมพ์ใบครัวที่ปรับแต่งแล้ว -->
                <label style="margin-right: 12px; cursor: pointer; display: flex; align-items: center;" onclick="event.stopPropagation();">
                    <input type="checkbox" class="print-kitchen-cb" value="${item.id}" checked 
                           style="cursor: pointer; width: 20px; height: 20px; accent-color: #28a745; margin: 0; border-radius: 20px;">
                </label>

                <!-- ปุ่มลบ (-) กันไม่ให้เกิด event การคลิกแก้ไข -->
                <button type="button" onclick="event.stopPropagation(); decreaseItem(${index})"
                    style="background-color: #ab1625; color: white; border: none; border-radius: 4px; padding: 2px 8px; margin-right: 8px; cursor: pointer; font-weight:bold;">-</button>
                
                <span class="order-name" style="font-weight: 500; color: #333;">${item.name} x${item.quantity}</span>
            </div>
            
                

                ${item.optionLabel ? `<small style="color: #666; margin-left: 32px; font-size: 12px;">* ${item.optionLabel}</small>` : ''}
                ${item.remark && item.remark !== item.optionLabel ? `<small style="color: #666; margin-left: 32px; font-size: 12px;"><br>* ${item.remark}</small>` : ''}
                
            </div>
            <span class="order-price" style=" white-space: nowrap; color: #333;">${(item.price * item.quantity).toFixed(2)}</span>
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
            if (result.success || result.status === 'success') {
                alert('บันทึกออเดอร์สำเร็จ!');

                // --- โค้ดที่เพิ่มเข้ามาสำหรับพิมพ์ครัว ---
                // หา checkbox ทั้งหมดที่ถูกติ๊กเลือก
                const printCheckboxes = document.querySelectorAll('.print-kitchen-cb:checked');
                const itemsToPrint = Array.from(printCheckboxes).map(cb => cb.value);

                // ถ้ามีรายการที่ต้องพิมพ์ ให้เปิดหน้าต่างใหม่ไปที่ไฟล์ print_kitchen.php
                if (itemsToPrint.length > 0) {
                    const printIds = itemsToPrint.join(','); // รวม ID เป็น 1,2,3
                    const printUrl = `print_kitchen.php?order_id=${result.order_id}&pids=${printIds}`;

                    // เปิดหน้าต่างใหม่เพื่อสั่งพิมพ์
                    window.open(printUrl, '_blank', 'width=400,height=600');
                }
                // ------------------------------------

                orderItems = [];
                sessionStorage.removeItem('orderItems');
                renderOrder();
            } else {
                alert('เกิดข้อผิดพลาด: ' + result.message);
            }
        })
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
    const openNewOrderModal = document.getElementById('newOrderModal')

    if (addProductModal) addProductModal.style.display = 'none';
    if (addTypeModal) addTypeModal.style.display = 'none';
    if (openOrder) openOrder.style.display = 'none';
    if (paymentModal) paymentModal.style.display = 'none';
    if (newOrderModal) openNewOrderModal.style.display = 'none';

    const allInputs = document.querySelectorAll('#addProductModal input, #addTypeModal input');
    allInputs.forEach(input => {
        input.value = "";
    });

    const receiveMoney = document.getElementById('receiveMoney');
    const changeMoney = document.getElementById('changeMoney');
    if (receiveMoney) receiveMoney.value = '';
    if (changeMoney) changeMoney.innerText = '0';
}

// ================================= popup แก้ไขสินค้า =================================
function openEditModal(button) {
    const modal = document.getElementById('editProductModal');
    if (!modal || !button.dataset.product) return;

    let product;
    try {
        product = JSON.parse(button.dataset.product);
    } catch (error) {
        console.error('ไม่สามารถอ่านข้อมูลสินค้าที่ต้องการแก้ไขได้', error);
        return;
    }

    document.getElementById('edit_p_id').value = product.id ?? '';
    document.getElementById('edit_old_img').value = product.image ?? '';
    document.getElementById('edit_p_name').value = product.name ?? '';
    document.getElementById('edit_p_price').value = product.price ?? '';
    document.getElementById('edit_type_id').value = product.typeId ?? '';

    const preview = document.getElementById('current_img_preview');
    if (preview) {
        preview.src = product.image ? `upload/${product.image}` : 'https://placehold.co/80x80?text=No+Image';
    }

    const fileInput = document.getElementById('edit_file');
    if (fileInput) fileInput.value = '';
    loadEditProductOptions(product.id);
    modal.style.display = 'flex';
}

function closeEditModal() {
    const modal = document.getElementById('editProductModal');
    if (modal) modal.style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('edit_file');
    const preview = document.getElementById('current_img_preview');

    if (fileInput && preview) {
        fileInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) preview.src = URL.createObjectURL(file);
        });
    }
});

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

// =============================== save menu/type ไม่เปลี่ยนหน้า ===============================
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
            let result = data.trim(); 

            if (result === 'success' || result.includes('บันทึกข้อมูลเรียบร้อย')) { 
                Swal.fire({
                    icon: 'success',
                    title: 'สำเร็จ!',
                    text: 'บันทึกข้อมูลเรียบร้อยแล้ว',
                    showConfirmButton: false,
                    timer: 1500,
                    // ดัน Alert ให้อยู่หน้าสุด
                    didOpen: () => {
                        document.querySelector('.swal2-container').style.zIndex = '10000';
                    }
                }).then(() => {
                    window.location.reload(); 
                });

            } else if (result.includes('Duplicate entry')) {
                Swal.fire({
                    icon: 'warning',
                    title: 'ซ้ำ!',
                    text: 'มีชื่อสินค้านี้ในระบบแล้ว กรุณาใช้ชื่ออื่น',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'ตกลง',
                    didOpen: () => {
                        document.querySelector('.swal2-container').style.zIndex = '10000';
                    }
                });

            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด!',
                    text: data, 
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'ปิด',
                    didOpen: () => {
                        document.querySelector('.swal2-container').style.zIndex = '10000';
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error:', error); 
            Swal.fire({
                icon: 'error',
                title: 'เชื่อมต่อล้มเหลว',
                text: 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์',
                confirmButtonColor: '#d33',
                confirmButtonText: 'ปิด',
                didOpen: () => {
                    document.querySelector('.swal2-container').style.zIndex = '10000';
                }
            });
        });
}

function saveTypeAjax(event) {
    event.preventDefault();

    let form = document.getElementById('formAddType');
    let formData = new FormData(form);

    fetch('save_type.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.text())
        .then(data => {
            let result = data.trim();

            if (result === 'success' || result.includes('บันทึกข้อมูลเรียบร้อย')) {
                Swal.fire({
                    icon: 'success',
                    title: 'สำเร็จ!',
                    text: 'บันทึกข้อมูลเรียบร้อยแล้ว',
                    showConfirmButton: false,
                    timer: 1500,
                    // ดัน Alert ให้อยู่หน้าสุด
                    didOpen: () => {
                        document.querySelector('.swal2-container').style.zIndex = '10000';
                    }
                }).then(() => {
                    window.location.reload();
                });

            } else if (result.includes('Duplicate entry')) {
                Swal.fire({
                    icon: 'warning',
                    title: 'ซ้ำ!',
                    text: 'ใส่ประเภทซ้ำไม่ได้ กรุณาใช้ชื่ออื่น',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'ตกลง',
                    // ดัน Alert ให้อยู่หน้าสุด
                    didOpen: () => {
                        document.querySelector('.swal2-container').style.zIndex = '10000';
                    }
                });

            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด!',
                    text: data,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'ปิด',
                    // ดัน Alert ให้อยู่หน้าสุด
                    didOpen: () => {
                        document.querySelector('.swal2-container').style.zIndex = '10000';
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'เชื่อมต่อล้มเหลว',
                text: 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์',
                confirmButtonColor: '#d33',
                confirmButtonText: 'ปิด',
                // ดัน Alert ให้อยู่หน้าสุด
                didOpen: () => {
                    document.querySelector('.swal2-container').style.zIndex = '10000';
                }
            });
        });
}

// ============================================== ดูบิล ==================================================
// ฟังก์ชันดึงรายการบิลมาแสดง (ถังขยะหน้าสุด + ปุ่มดูสีเทา)
function fetchBillsData() {
    let tbody = document.getElementById('billListBody');
    if (!tbody) return;

    tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 15px;">กำลังโหลดข้อมูล...</td></tr>';

    fetch('get_bills.php')
        .then(response => response.json())
        .then(data => {
            tbody.innerHTML = '';

            if (Array.isArray(data) && data.length > 0) {
                data.forEach(bill => {
                    let tableId = bill.table_id ? bill.table_id : '-';

                    let row = `
                    <tr style="border-bottom: 1px solid #eee;">
                        <!-- 1. ปุ่มถังขยะอยู่หน้าสุด -->
                        <td style="padding: 10px; text-align: center; width: 40px;">
                            <button type="button" class="btn-delete-bill" onclick="deleteBill(${bill.order_id})" title="ลบบิล">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                        <!-- 2. รหัสบิล -->
                        <td style="padding: 10px; text-align: center;">${bill.order_id}</td>
                        <!-- 3. เบอร์โต๊ะ -->
                        <td style="padding: 10px; text-align: center;">${tableId}</td>
                        <!-- 4. เวลาที่เปิดบิล -->
                        <td style="padding: 10px; text-align: center;">${bill.formatted_date}</td>
                        <!-- 5. ปุ่มดู (สีเทาเดิม) -->
                        <td style="padding: 10px; text-align: center;">
                            <button type="button" class="btn-bill" onclick="viewBill(${bill.order_id})">ดู</button>
                        </td>
                    </tr>
                    `;
                    tbody.innerHTML += row;
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px;">ยังไม่มีข้อมูลบิลในระบบ</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error fetching bills:', error);
            tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; color: red; padding: 15px;">เกิดข้อผิดพลาดในการดึงข้อมูล</td></tr>';
        });
}


// ฟังก์ชันส่งคำสั่งลบบิลไปยัง backend
function deleteBill(orderId) {
    if (confirm(`คุณต้องการลบบิลรหัส ${orderId} หรือไม่?`)) {
        let formData = new FormData();
        formData.append('order_id', orderId);

        fetch('delete_bill.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('ลบบิลเรียบร้อยแล้ว');
                    fetchBillsData(); // รีโหลดรายการบิลใหม่ทันที
                } else {
                    alert('เกิดข้อผิดพลาด: ' + (data.message || 'ไม่สามารถลบบิลได้'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
            });
    }
}

// ==================== ปุ่มดูบิล ====================
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
                        // ดึง option_label แยกต่างหาก พร้อมจัด margin-left ให้ตรงกับชื่อเมนู
                        let optionText = item.option_label || item.optionLabel || '';
                        let optionHtml = optionText ? `<small style="color: gray; margin-left: 32px; font-size: 12px;">* ${optionText}</small>` : '';

                        // ดึง remark
                        let remarkHtml = item.remark && item.remark !== optionText
                            ? `<small style="color: gray; margin-left: 32px; font-size: 12px;">* ${item.remark}</small>`
                            : '';

                        html += `
                                <div class="order-row" style="display:flex; justify-content:space-between; align-items:flex-start; padding: 8px; border-bottom: 1px solid #eee; border-radius: 4px;">
            
                                 <div style="display:flex; flex-direction:column; gap: 2px; flex: 1;">
                                <div style="display:flex; align-items:center;">
                                <!-- ใส่ Checkbox หลอกไว้เพื่อให้ Layout ตรงกับหน้าตะกร้า (ตั้ง disabled ไว้ไม่ให้กด) -->
                                <label style="margin-right: 12px; display: flex; align-items: center; cursor: default;">

                             <span class="order-name" style="font-weight: 500; color: #333;">${item.name} x${item.quantity}</span>
                            </div>

                ${optionHtml}
                ${remarkHtml}
            </div>

            <span class="order-price" style="white-space: nowrap; color: #333; margin-top: 2px;">${sum.toFixed(2)}</span>
        </div>
        `;
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

function decreaseItem(index) {
    if (orderItems[index]) {
        if (orderItems[index].quantity > 1) {
            orderItems[index].quantity--;
        } else {
            orderItems.splice(index, 1);
        }
        renderOrder();
    }
}

// ================================= ลูกศรเมนูย่อย =====================================
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

// ============================= popup เพิ่ม / แก้ไข หมายเหตุ ================================
let currentSelectedItem = null;
let selectedModalOption = null;

function renderModalOptions(selectedOptionId = 0) {
    const container = document.getElementById('modalOptionsContainer');
    if (!container) return;

    const options = currentSelectedItem.options || [];
    if (options.length === 0) {
        selectedModalOption = null;
        container.innerHTML = '';
        container.style.display = 'none';
        return;
    }

    container.style.display = 'flex';
    selectedModalOption = options.find(option => option.id === Number(selectedOptionId)) || options[0];

    container.innerHTML = options.map((option, index) => {
        const isSelected = option.id === selectedModalOption.id;
        const price = Number(currentSelectedItem.basePrice) + option.adjustment;
        return `<button type="button" class="btn-option${isSelected ? ' active' : ''}" onclick="selectModalOption(${index})">${option.label} ${price.toFixed(2)} บาท</button>`;
    }).join('');
}

function loadEditProductOptions(productId) {
    const container = document.getElementById('editProductOptions');
    if (!container) return;

    container.innerHTML = '';
    fetch(`get_product_options.php?product_id=${encodeURIComponent(productId)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                data.options.forEach(option => addProductOptionRow(option.label, option.adjustment));
            }
        })
        .catch(error => console.error('Unable to load product options', error));
}

function addProductOptionRow(name = '', adjustment = '') {
    const container = document.getElementById('editProductOptions');
    if (!container) return;

    const row = document.createElement('div');
    row.style.cssText = 'display:flex; gap:6px; margin-bottom:6px; align-items:center;';

    const nameInput = document.createElement('input');
    nameInput.type = 'text';
    nameInput.name = 'option_name[]';
    nameInput.placeholder = 'ชื่อตัวเลือก เช่น เย็น';
    nameInput.value = name;
    nameInput.required = true;
    nameInput.style.flex = '1';

    const priceInput = document.createElement('input');
    priceInput.type = 'number';
    priceInput.name = 'option_adjustment[]';
    priceInput.placeholder = 'ราคาเพิ่ม';
    priceInput.step = '0.01';
    priceInput.value = adjustment;
    priceInput.required = true;
    priceInput.style.width = '100px';

    const removeButton = document.createElement('button');
    removeButton.type = 'button';
    removeButton.className = 'btn-delete-bill';
    removeButton.innerHTML = '<i class="fa-regular fa-trash-can"></i>';
    removeButton.title = 'ลบตัวเลือก';
    removeButton.setAttribute('aria-label', 'ลบตัวเลือก');
    removeButton.onclick = () => row.remove();

    row.append(nameInput, priceInput, removeButton);
    container.appendChild(row);
}

function selectModalOption(index) {
    selectedModalOption = currentSelectedItem.options[index] || null;
    if (!selectedModalOption) return;
    document.getElementById('modalProductPrice').innerText = (Number(currentSelectedItem.basePrice) + selectedModalOption.adjustment).toFixed(2);
    renderModalOptions(selectedModalOption.id);
}

function openOrderModal(id, name, price) {
    editingIndex = null;
    currentSelectedItem = { id: id, name: name, basePrice: Number(price), options: [] };

    document.getElementById('modalProductName').innerText = name;
    document.getElementById('modalProductPrice').innerText = Number(price).toFixed(2);

    document.getElementById('modalQty').value = 1;
    document.getElementById('modalRemark').value = '';
    loadProductOptions(id);

    const confirmBtn = document.querySelector('#orderModal .btn-confirm');
    if (confirmBtn) confirmBtn.innerText = 'เพิ่มลงบิล';

    document.getElementById('orderModal').style.display = 'flex';
}

function editOrderItem(index) {
    let item = orderItems[index];
    if (!item) return;

    editingIndex = index;
    const optionAdjustment = Number(item.optionAdjustment || 0);
    currentSelectedItem = { id: item.id, name: item.name, basePrice: Number(item.price) - optionAdjustment, options: [] };

    document.getElementById('modalProductName').innerText = item.name;
    document.getElementById('modalProductPrice').innerText = item.price;

    document.getElementById('modalQty').value = item.quantity;
    document.getElementById('modalRemark').value = item.remark || '';
    loadProductOptions(item.id, item.optionId || 0);

    const confirmBtn = document.querySelector('#orderModal .btn-confirm');
    if (confirmBtn) confirmBtn.innerText = 'บันทึกการแก้ไข';

    document.getElementById('orderModal').style.display = 'flex';
}

function closeOrderModal() {
    document.getElementById('orderModal').style.display = 'none';
    currentSelectedItem = null;
    editingIndex = null;
}

function loadProductOptions(productId, selectedOptionId = 0) {
    fetch(`get_product_options.php?product_id=${encodeURIComponent(productId)}`)
        .then(response => response.json())
        .then(data => {
            if (!currentSelectedItem || currentSelectedItem.id != productId) return;
            currentSelectedItem.options = data.success ? data.options : [];
            renderModalOptions(selectedOptionId);
            if (selectedModalOption) {
                document.getElementById('modalProductPrice').innerText =
                    (Number(currentSelectedItem.basePrice) + selectedModalOption.adjustment).toFixed(2);
            }
        })
        .catch(() => {
            if (currentSelectedItem && currentSelectedItem.id == productId) {
                currentSelectedItem.options = [];
                renderModalOptions();
            }
        });
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

    //  ดึงค่าจำนวนและหมายเหตุ (เอาแค่ที่ลูกค้าพิมพ์ในช่องจริงๆ)
    const parsedQty = parseInt(document.getElementById('modalQty').value, 10);
    const qty = (!isNaN(parsedQty) && parsedQty > 0) ? parsedQty : 1;
    const userRemark = document.getElementById('modalRemark').value.trim();

    // ดึงข้อมูล Option แยกต่างหาก
    const option = selectedModalOption;
    const optionId = option ? option.id : 0;
    const optionLabel = option ? option.label : '';
    const optionAdjustment = option ? Number(option.adjustment) : 0;

    const unitPrice = Number(currentSelectedItem.basePrice) + optionAdjustment;

    //  จัดการเพิ่มหรืออัปเดตออเดอร์
    if (editingIndex !== null) {
        // อัปเดตรายการเดิม
        orderItems[editingIndex].quantity = qty;
        orderItems[editingIndex].remark = userRemark; // เก็บแค่หมายเหตุเพียวๆ
        orderItems[editingIndex].optionId = optionId;
        orderItems[editingIndex].optionLabel = optionLabel;
        orderItems[editingIndex].optionAdjustment = optionAdjustment;
        orderItems[editingIndex].price = unitPrice;
    } else {
        // ค้นหาว่ามีรายการที่เหมือนกันเป๊ะๆ อยู่แล้วหรือไม่
        const index = orderItems.findIndex(item =>
            item.id === currentSelectedItem.id &&
            (item.remark || '') === userRemark && // เทียบเฉพาะหมายเหตุ
            Number(item.optionId || 0) === Number(optionId)
        );

        if (index !== -1) {
            // ถัามีให้บวกจำนวนเพิ่ม
            orderItems[index].quantity += qty;
        } else {
            // ถ้าไม่มีให้เพิ่มเป็นรายการใหม่
            orderItems.push({
                id: currentSelectedItem.id,
                name: currentSelectedItem.name,
                price: unitPrice,
                quantity: qty,
                remark: userRemark, // เก็บแค่หมายเหตุเพียวๆ
                optionId: optionId,
                optionLabel: optionLabel,
                optionAdjustment: optionAdjustment
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

// =================== popup ออเดอร์ใหม่ =====================
// เปิด Modal และโหลดข้อมูล
// function openNewOrderModal() {
//     document.getElementById('newOrderModal').style.display = 'flex';

//     // ดึงข้อมูลรายการจากไฟล์ PHP ที่จะทำใหม่
//     fetch('get_new_order.php')
//         .then(res => res.text())
//         .then(html => {
//             document.getElementById('newOrderList').innerHTML = html;
//         });
// }

let previousOrderCount = 0;

// เช็คออเดอร์ใหม่แบบอัตโนมัติ
function checkNewOrders() {
    fetch('check_new_order_count.php')
        .then(res => res.json())
        .then(data => {
            const count = data.count || 0;
            const alertBox = document.getElementById('newOrderAlert');
            const badge = document.getElementById('orderBadge');
            const sound = document.getElementById('orderSound');

            if (count > 0) {
                if (badge) badge.innerText = count;
                if (alertBox) alertBox.style.display = 'block';

                // เล่นเสียงเฉพาะเมื่อมีออเดอร์เพิ่มขึ้น
                if (count > previousOrderCount) {
                    if (sound) {
                        sound.currentTime = 0;
                        sound.play().catch(err => {
                            console.log('แจ้งเตือน: คลิกบนหน้าเว็บก่อน 1 ครั้งเพื่อให้เสียงเล่นอัตโนมัติ');
                        });
                    }
                }
            } else {
                if (alertBox) alertBox.style.display = 'none';
            }

            previousOrderCount = count;
        })
        .catch(err => console.error('Error checking order count:', err));
}

// เปิด Popup และโหลดตารางรายการออเดอร์ใหม่
function openNewOrderModal() {
    const modal = document.getElementById('newOrderModal');
    if (modal) modal.style.display = 'flex';

    fetch('get_new_order.php')
        .then(res => res.text())
        .then(html => {
            const listContainer = document.getElementById('newOrderList');
            if (listContainer) listContainer.innerHTML = html;
        })
        .catch(err => console.error('Error fetching order list:', err));
}

// ตั้งเวลาให้เช็คออเดอร์ใหม่ทุกๆ 5 วินาที
setInterval(checkNewOrders, 5000);
document.addEventListener('DOMContentLoaded', checkNewOrders);

function confirmPayment() {
    let tableId = document.getElementById('tables').value;
    if (tableId === "") {
        alert("กรุณาเลือกโต๊ะก่อนชำระเงิน");
        return;
    }

    const isTakeaway = tableId === 'Takeaway';
    if (isTakeaway && orderItems.length === 0) {
        alert("กรุณาเลือกรายการอาหารก่อนชำระเงิน");
        return;
    }

    let isCash = document.getElementById('paymentCash').checked;
    let paymentMethod = isCash ? "Cash" : "Transfer";
    let totalAmount = document.getElementById('payTotalAmount').innerText;

    if (isCash) {
        let receiveMoney = document.getElementById('receiveMoney').value;
        if (receiveMoney !== "" && parseFloat(receiveMoney) < parseFloat(totalAmount)) {
            alert("กรุณากรอกเงินที่รับมาให้ถูกต้อง (ต้องไม่น้อยกว่ายอดรวม)");
            return;
        }
    }

    // เปิดหน้าต่างไว้จากการคลิกของผู้ใช้ เพื่อไม่ให้เบราว์เซอร์บล็อกหน้าพิมพ์
    const receiptWindow = window.open('about:blank', '_blank');


    let formData = new FormData();
    formData.append("table_id", tableId);
    formData.append("payment_method", paymentMethod);
    formData.append("total_amount", totalAmount);

    // ออเดอร์กลับบ้านยังไม่มีบิลค้างชำระ จึงส่งรายการให้ระบบสร้างบิลและ
    // บันทึกการชำระเงินในครั้งเดียว
    if (isTakeaway) {
        formData.append("is_takeaway", "yes");
        formData.append("items", JSON.stringify(orderItems));
    }

    fetch('save_payment.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                alert("บันทึกการชำระเงินสำเร็จ!");
                if (isTakeaway) {
                    orderItems = [];
                    sessionStorage.removeItem('orderItems');
                }

                const receiptUrl = `print_receipt.php?id=${encodeURIComponent(data.order_id)}`;
                if (receiptWindow) {
                    receiptWindow.location.replace(receiptUrl);
                    location.reload();
                } else {
                    // กรณีเบราว์เซอร์บล็อกหน้าต่างใหม่ ให้แสดงใบเสร็จในหน้าเดิม
                    window.location.href = receiptUrl;
                }
            } else {
                if (receiptWindow) receiptWindow.close();
                alert("ไม่สามารถบันทึกได้: " + (data.message || JSON.stringify(data)));
            }
        })
        .catch(error => {
            if (receiptWindow) receiptWindow.close();
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

// =========================== ปุ่มเลือกรายวัน เดือน ปี ==============================
let currentType = 'daily';

function loadReport(type, btnElement) {
    currentType = type;
    const selectedDate = document.getElementById('date-report').value;
    const selectedMonth = document.getElementById('filterMonth').value;
    const selectedYear = document.getElementById('filterYear').value;
    const dateInput = document.getElementById('date-report');
    const dateFilterWrap = document.getElementById('date-filter-wrap');
    const monthFilter = document.getElementById('filterMonth');
    const yearFilter = document.getElementById('filterYear');

    // Show only the control that applies to the selected report type.
    dateInput.parentElement.style.display = type === 'daily' ? 'flex' : 'none';
    dateFilterWrap.style.display = type === 'daily' ? 'none' : 'flex';
    monthFilter.style.display = type === 'monthly' ? 'block' : 'none';
    yearFilter.style.display = type === 'yearly' ? 'block' : 'none';

    // สลับคลาส active ไปที่ปุ่มที่ถูกกด
    if (btnElement && btnElement.tagName === 'BUTTON') {
        document.querySelectorAll('.report-buttons button').forEach(btn => {
            btn.classList.remove('active');
        });
        btnElement.classList.add('active');
    }

    // ดึงข้อมูล AJAX ตามปกติ
    const params = new URLSearchParams({ type, date: selectedDate });
    if (type === 'monthly') params.set('month', selectedMonth);
    if (type === 'yearly') params.set('year', selectedYear);

    fetch(`get_report.php?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('val-daily').innerText = '฿' + data.daily_total;
            document.getElementById('val-monthly').innerText = '฿' + data.monthly_total;
            document.getElementById('val-yearly').innerText = '฿' + data.yearly_total;
            document.getElementById('val-count').innerText = data.daily_orders + ' บิล';

            document.getElementById('table-title').innerText = data.table_title;
            document.getElementById('report-tbody').innerHTML = data.table_html;

            if (document.getElementById('header-date-text')) {
                const [y, m, d] = selectedDate.split('-');
                document.getElementById('header-date-text').innerText = `${d}/${m}/${y}`;
            }
        })
        .catch(error => console.error('Error fetching report:', error));
}

// ส่งออก excel
const exportBtn = document.getElementById('export-excel');

// เช็คว่ามีปุ่มนี้อยู่ในหน้าปัจจุบันหรือไม่ ถ้ามีค่อยทำงาน
if (exportBtn) {
    exportBtn.addEventListener('click', function () {
        const table = document.getElementById("report-table");
        const wb = XLSX.utils.table_to_book(table, { sheet: "สรุปยอดขาย" });
        const selectedDate = document.getElementById('date-report').value;
        const fileName = "Sale_Report_" + selectedDate + ".xlsx";
        XLSX.writeFile(wb, fileName);
    });
}

// =================== ช่องต้นหาเมนู =====================
function filterMenu() {
    //รับค่าคำที่พิมพ์เข้ามาและแปลงเป็นตัวพิมพ์เล็ก (เผื่อพิมพ์ภาษาอังกฤษจะได้ค้นหาเจอง่าย)
    const searchInput = document.getElementById('search-menu').value.toLowerCase();
    const menuItems = document.querySelectorAll('.menu-item');

    // วนลูปเช็คทีละเมนู
    menuItems.forEach(function (item) {
        const rawName = item.getAttribute('data-name') || '';
        const menuName = rawName.toLowerCase();

        if (menuName.includes(searchInput)) {
            item.style.display = "";
        } else {
            item.style.display = "none";
        }
    });
}

const observer = new MutationObserver((mutations, obs) => {
    const trashIcons = document.querySelectorAll('#billListBody .bi-trash, #billListBody .fa-trash');
    if (trashIcons.length > 0) {
        trashIcons.forEach(icon => {
            // หากยังเป็นไอคอนเก่า ให้แปลงร่างเป็น FontAwesome
            if (icon.classList.contains('bi-trash')) {
                icon.className = 'fa-solid fa-trash';
            }

            icon.style.setProperty('color', '#dc3545', 'important');
            icon.style.setProperty('font-size', '16px', 'important');

            let btn = icon.closest('button') || icon.parentElement;
            if (btn && btn.tagName === 'BUTTON') {
                btn.style.setProperty('background', 'transparent', 'important');
                btn.style.setProperty('background-color', 'transparent', 'important');
                btn.style.setProperty('border', 'none', 'important');
                btn.style.setProperty('box-shadow', 'none', 'important');
                btn.style.setProperty('padding', '4px 8px', 'important');
                btn.style.setProperty('cursor', 'pointer', 'important');

                btn.onmouseover = function () {
                    icon.style.color = '#a71d2a';
                };
                btn.onmouseout = function () {
                    icon.style.color = '#dc3545';
                };
            }
        });
    }
});

const billBody = document.querySelector('#billListBody');
if (billBody) {
    observer.observe(billBody, { childList: true, subtree: true });
}

setTimeout(() => {
    document.querySelectorAll('#billListBody .bi-trash, #billListBody .fa-trash').forEach(icon => {
        if (icon.classList.contains('bi-trash')) {
            icon.className = 'fa-solid fa-trash';
        }

        icon.style.setProperty('color', '#dc3545', 'important');
        icon.style.setProperty('font-size', '16px', 'important');

        let btn = icon.closest('button') || icon.parentElement;
        if (btn && btn.tagName === 'BUTTON') {
            btn.style.setProperty('background', 'transparent', 'important');
            btn.style.setProperty('background-color', 'transparent', 'important');
            btn.style.setProperty('border', 'none', 'important');
            btn.style.setProperty('box-shadow', 'none', 'important');
            btn.style.setProperty('padding', '4px 8px', 'important');
            btn.style.setProperty('cursor', 'pointer', 'important');

            btn.onmouseover = function () {
                icon.style.color = '#a71d2a';
            };
            btn.onmouseout = function () {
                icon.style.color = '#dc3545';
            };
        }
    });
}, 300);

// ปุ่มรับออเดอร์และสั่งปริ้น
// function processOrder(orderId) {
//     // เรียก API เพื่ออัปเดตสถานะในฐานข้อมูล

//     fetch('api/accept_order_v2.php', {
//         method: 'POST',
//         headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
//         body: 'order_id=' + orderId
//     })
//         .then(response => response.json())
//         .then(data => {
//             if (data.status === 'success') {
//                 // window.open('print_kitchen.php?id=' + data.print_id, '_blank', 'width=300,height=500');
//                 window.open('print_kitchen.php?order_id=' + data.print_id, '_blank', 'width=300,height=500');
//                 location.reload();
//             }
//         });
// }

// ปุ่มรับออเดอร์และสั่งปริ้น
function processOrder(orderId) {
    // เรียก API เพื่ออัปเดตสถานะในฐานข้อมูล
    fetch('api/accept_order_v2.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'order_id=' + orderId
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // เปลี่ยนจาก data.print_id เป็น data.order_id ให้ตรงกับที่ PHP ส่งมา
                window.open('print_kitchen.php?order_id=' + data.order_id, '_blank', 'width=300,height=500');
                location.reload();
            } else {
                // แนะนำให้เพิ่ม alert ไว้ด้วยครับ จะได้รู้ว่าถ้าไม่ success เกิดจากอะไร
                alert('เกิดข้อผิดพลาด: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

/* ===== customer.js ===== */
const $ = id => document.getElementById(id);

const TABLE_ID = $('tablesId')?.value || '';
const CART_KEY = 'cart_' + TABLE_ID;
let cart = JSON.parse(localStorage.getItem(CART_KEY) || '[]');
let current = null;
let selectedOption = null;

/*  ส่วนจัดการ Option (แก้ไขให้ตรงกับร้านได้เลย)  */
const menuOptions = {
    // หมวดเครื่องดื่ม
    "coffee": [
        { id: "opt1", label: "ร้อน", adjustment: 0 },
        { id: "opt2", label: "เย็น", adjustment: 5 }, 
        { id: "opt3", label: "ปั่น", adjustment: 15 }  
    ],
    "drink": [
        { id: "opt4", label: "เย็น", adjustment: 0 }, 
        { id: "opt5", label: "ปั่น", adjustment: 10 } 
    ]
};

// ฟังก์ชันเช็คว่าเมนูนี้ต้องโชว์ Option ไหน
function getOptionsForProduct(productName) {
    const name = productName.toLowerCase();
    
    // ถ้าชื่อเมนูมีคำพวกนี้ ให้แสดงตัวเลือก ร้อน/เย็น/ปั่น
    if (name.includes('กาแฟดำ (') || name.includes('ลาเต้') || name.includes('คาปูชิโน่') || name.includes('มอคค่า') || name.includes('เอสเพรสโซ่') || name.includes('คาราเมล')) {
        return menuOptions["coffee"];

    } else if (name.includes('ชา') || name.includes('นม')) {
        return menuOptions["drink"];
    }
    
    
    return []; // ถ้าไม่เข้าเงื่อนไขเลย ก็ไม่ต้องโชว์ Option
}
/*  สิ้นสุดส่วนจัดการ Option  */


const money = n => Number(n).toLocaleString('th-TH');
const save = () => localStorage.setItem(CART_KEY, JSON.stringify(cart));

function saveCart() {
    localStorage.setItem('cart_' + TABLE_ID, JSON.stringify(cart));
}

/* ---------- ตะกร้า ---------- */
function addOrder(p, qty = 1, remark = '') {
    const hit = cart.find(i => i.id === p.id && i.remark === remark && i.name === p.name);
    if (hit) hit.qty += qty;
    else cart.push({ ...p, qty, remark });
    save(); renderCart();
    toast(`เพิ่ม ${p.name} แล้ว`);
}

function changeQty(idx, diff) {
    cart[idx].qty += diff;
    if (cart[idx].qty <= 0) cart.splice(idx, 1);
    save(); renderCart();
}

function renderCart() {
    const count = cart.reduce((s, i) => s + i.qty, 0);
    const total = cart.reduce((s, i) => s + i.price * i.qty, 0);

    if ($('cartCount')) $('cartCount').textContent = count;
    if ($('cartTotal')) $('cartTotal').textContent = money(total);
    if ($('cartTotal2')) $('cartTotal2').textContent = money(total);
    if ($('cartBar')) $('cartBar').style.display = count ? 'flex' : 'none';

    const box = $('cartItems');
    if (!box) return;

    box.innerHTML = cart.length
        ? cart.map((i, idx) => `
      <div class="cart-item">
        <img src="${i.img}" alt="">
        <div class="ci-info">
          <div class="ci-name">${i.name}</div>
          ${i.remark ? `<div class="ci-note">* ${i.remark}</div>` : ''}
          <div class="ci-price">${money(i.price * i.qty)} ฿</div>
        </div>
        <div class="ci-qty">
          <button type="button" onclick="changeQty(${idx},-1)">−</button>
          <span>${i.qty}</span>
          <button type="button" onclick="changeQty(${idx},1)">+</button>
        </div>
      </div>`).join('')
        : '<p class="cart-empty">ยังไม่มีรายการ</p>';
}

/* ---------- ล็อก/ปลดล็อกการเลื่อนหน้าหลัง ---------- */
let scrollY = 0;

function lockScroll() {
    scrollY = window.scrollY;
    document.body.style.top = `-${scrollY}px`;
    document.body.classList.add('modal-open');
}

function unlockScroll() {
    if (document.querySelector('.cart-modal.show, .order-modal.show')) return;
    document.body.classList.remove('modal-open');
    document.body.style.top = '';
    window.scrollTo(0, scrollY);
}

/* ---------- Modal ---------- */
function openOrderModal(p) {
    current = p;
    $('omName').textContent = p.name;
    $('omImg').src = p.img;
    $('omQty').value = 1;
    $('omNote').value = '';
    
    // แสดงราคาเริ่มต้น
    if ($('omPrice')) {
        $('omPrice').textContent = p.price + ' ฿';
    }

    selectedOption = null; 
    
    const optionsDiv = $('omOptions');
    if (optionsDiv) {
        optionsDiv.innerHTML = ''; 

        //  ดึง Option จาก ฟังก์ชัน JavaScript ด้านบน 
        const options = getOptionsForProduct(p.name);

        if (options.length > 0) {
            options.forEach(opt => {
                let finalPrice = p.price + parseFloat(opt.adjustment);
                
                let btn = document.createElement('button');
                btn.className = 'cust-opt-btn'; 
                
                // ถ้าราคาบวกเพิ่มเป็น 0 ไม่ต้องโชว์ราคาในปุ่ม
                if(opt.adjustment > 0) {
                    btn.textContent = `${opt.label} (+${opt.adjustment}฿)`;
                } else {
                    btn.textContent = opt.label;
                }
                
                btn.onclick = () => {
                    // เปลี่ยนสีปุ่มที่เลือก
                    document.querySelectorAll('#omOptions .cust-opt-btn').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    
                    selectedOption = opt; // จำว่าลูกค้าเลือกอะไร
                    
                    // อัปเดตราคา
                    if ($('omPrice')) {
                        $('omPrice').textContent = finalPrice + ' ฿';
                    }
                };
                optionsDiv.appendChild(btn);
            });
        }
    }

    $('orderModal').classList.add('show');
    lockScroll();
}

function closeOrderModal() {
    $('orderModal').classList.remove('show');
    unlockScroll();
}

function openCartModal() {
    $('cartModal').classList.add('show');
    lockScroll();
}

function closeCartModal() {
    $('cartModal').classList.remove('show');
    unlockScroll();
}

['orderModal', 'cartModal'].forEach(id => {
    const el = $(id);
    if (!el) return;
    el.addEventListener('click', e => {
        if (e.target === el) { 
            el.classList.remove('show');
            unlockScroll();
        }
    });
});

/* ---------- ส่งออเดอร์ ---------- */
async function submitOrder() {
    if (!cart.length) return alert('ยังไม่มีรายการอาหาร');
    const btn = document.querySelector('#cartModal .btn-send-order');
    btn.disabled = true; btn.textContent = 'กำลังส่ง...';

    try {
        const res = await fetch('api/submit_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ table_id: TABLE_ID, items: cart })
        });

        const raw = await res.text();
        console.log('RAW RESPONSE >>>', raw);

        let data;
        try {
            data = JSON.parse(raw);
        } catch (err) {
            alert('เซิร์ฟเวอร์ตอบกลับผิดรูปแบบ:\n' + (raw || '(ว่างเปล่า)'));
            return;
        }

        if (data.status === 'success') {
            cart = []; saveCart(); renderCart(); closeCartModal();
            alert(data.is_new
                ? `ส่งออเดอร์เรียบร้อย! หมายเลขบิล #${data.order_id}`
                : `เพิ่มรายการลงบิล #${data.order_id} เรียบร้อย!`);

        } else {
            alert('เกิดข้อผิดพลาด: ' + data.message);
        }

    } catch (e) {
        console.error(e);
        alert('เชื่อมต่อไม่ได้ กรุณาลองใหม่');
    } finally {
        btn.disabled = false;
        btn.textContent = 'ยืนยันสั่งอาหาร';
    }
}

/* ---------- Event ---------- */
document.addEventListener('click', e => {
    const card = e.target.closest('.product-card');
    if (!card) return;
    const p = {
        id: +card.dataset.id,
        name: card.dataset.name,
        price: +card.dataset.price,
        img: card.dataset.img
    };
    if (e.target.closest('.btn-add')) { e.stopPropagation(); addOrder(p); }
    else openOrderModal(p);
});

function toast(msg) {
    const t = document.createElement('div');
    t.className = 'toast';
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 1800);
}

renderCart();

/* ---------- เพิ่ม/ลดจำนวนใน modal ---------- */
function stepQty(d) {
    const el = $('omQty');
    el.value = Math.max(1, (+el.value || 1) + d);
}

/* ---------- ยืนยันเพิ่มลงตะกร้า ---------- */
function confirmOrderModal() {
    if (!current) return;
    const qty = Math.max(1, +$('omQty').value || 1);
    let remark = $('omNote').value.trim();
    
    let productToCart = { ...current }; 
    
    if (selectedOption) {
        // อัปเดตราคารวม
        productToCart.price = current.price + parseFloat(selectedOption.adjustment);
        
        // ส่งค่าชื่อ Option ไปตรงๆ ด้วย Key ชื่อ option_label
        productToCart.option_label = selectedOption.label;
        
        // เก็บตัวเลือกแยกใน option_label; remark เก็บเฉพาะข้อความที่ลูกค้าพิมพ์
        // เพื่อไม่ให้หน้ารับออเดอร์แสดง option ซ้ำสองบรรทัด
    }

    addOrder(productToCart, qty, remark);
    closeOrderModal();
}

function toggleTypeDropdown() {
    var dropdown = document.getElementById('typeDropdown');
    dropdown.classList.toggle('show');
}

function toggleInlineSearch() {
    var wrapper = document.getElementById('searchWrapper');
    var input = document.getElementById('searchInput');
    wrapper.classList.toggle('active');
    if (wrapper.classList.contains('active')) {
        input.focus();
    }
}

function handleLiveSearch(query) {
    const filter = query.trim().toLowerCase();
    const cards = document.querySelectorAll('#productGrid .product-card');
    const noMsg = document.getElementById('noProductsMessage');
    let visibleCount = 0;

    cards.forEach(card => {
        const name = card.getAttribute('data-name') ? card.getAttribute('data-name').toLowerCase() : '';
        if (name.includes(filter)) {
            card.style.display = '';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    if (visibleCount === 0) {
        noMsg.style.display = 'block';
    } else {
        noMsg.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const initialVal = document.getElementById('searchInput').value;
    if (initialVal) {
        handleLiveSearch(initialVal);
    }
});

window.onclick = function (event) {
    if (!event.target.closest('.list-menu') && !event.target.closest('#typeDropdown')) {
        var dropdown = document.getElementById('typeDropdown');
        if (dropdown && dropdown.classList.contains('show')) {
            dropdown.classList.remove('show');
        }
    }
}

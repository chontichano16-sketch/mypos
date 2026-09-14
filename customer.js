/* ===== customer.js ===== */
const $ = id => document.getElementById(id);

const TABLE_ID = $('tablesId')?.value || '';
const CART_KEY = 'cart_' + TABLE_ID;
let cart = JSON.parse(localStorage.getItem(CART_KEY) || '[]');
let current = null;

const money = n => Number(n).toLocaleString('th-TH');
const save = () => localStorage.setItem(CART_KEY, JSON.stringify(cart));

function saveCart() {
    localStorage.setItem('cart_' + TABLE_ID, JSON.stringify(cart));
}

/* ---------- ตะกร้า ---------- */
function addOrder(p, qty = 1, remark = '') {
    const hit = cart.find(i => i.id === p.id && i.remark === remark);
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
    // ปลดล็อกเฉพาะตอนไม่มี modal เปิดค้างอยู่
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
        if (e.target === el) {                      // กดโดนฉากหลังเท่านั้น
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
    const remark = $('omNote').value.trim();
    addOrder(current, qty, remark);
    closeOrderModal();
}

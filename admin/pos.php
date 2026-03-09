<?php
include '../db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../home.php");
    exit;
}

$currentPage = basename($_SERVER['PHP_SELF'], '.php');

$sql = "SELECT id, brand, name, category, srp, price, quantity, image FROM products";
$result = $conn->query($sql);

$products = [];
while ($row = $result->fetch_assoc()) {
  $products[] = [
      "id"       => (int)$row["id"],
      "brand"    => $row["brand"],
      "name"     => $row["name"],
      "category" => strtolower($row["category"]),
      "price"    => (float)$row["srp"],   // SELLING
      "cost"     => (float)$row["price"], // COST
      "qty"      => (int)$row["quantity"],
      "img"      => !empty($row["image"])
          ? "../uploads/products/" . basename($row["image"])
          : "../assets/no-image.png"
  ];
}
?>

<!DOCTYPE html>
<html lang="en">

<?php
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$pageTitle = ucfirst(str_replace('_', ' ', $currentPage));
?>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?> - OC Brand</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
  :root{
    --bg: #121214;
    --panel: #1c1c1f;
    --muted: #9aa0a6;
    --accent: #ffd54d;
    --accent-dark: #d4af37;
    --text: #ffffff;
    --success: #2dd36f;
    --danger: #ff6b6b;
    --card-radius: 12px;
    --touch-size: 64px;
  }

  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Poppins", sans-serif;
  }

  body{display:flex; min-height:100vh;}
  .main-wrap{flex:1; margin-left:250px; padding:18px; background: #f4f4f4; overflow:hidden;}

  .topbar{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    margin-bottom:12px;
  }
  .top-left{ display:flex; align-items:center; gap:14px; }
  .brand {
    background: linear-gradient(90deg,#111,#1a1a1a);
    color:var(--text);
    padding:10px 14px;
    border-radius:10px;
    font-weight:700;
    display:flex;
    align-items:center;
    gap:10px;
    box-shadow:0 6px 14px rgba(0,0,0,0.1);
  }
  .brand .logo {
    width:40px; height:40px; border-radius:8px; background:var(--accent); display:flex; align-items:center; justify-content:center; color:#000; font-weight:800;
  }
  .top-right{ display:flex; align-items:center; gap:14px; }
  .top-info{ text-align:right; line-height:1; }
  .top-info .time{ font-size:18px; font-weight:700; color:#1a1a1a; }
  .top-info .date{ font-size:13px; color:#555; }
  .chip{ background:#fff; padding:8px 12px; border-radius:10px; box-shadow:0 6px 14px rgba(0,0,0,0.06); font-weight:600; color:#111; }

  .pos-grid{
    display:grid;
    grid-template-columns: 240px 1fr 420px;
    gap:16px;
    height:calc(100vh - 86px);
    align-items:start;
  }

  .categories{
    background:#fff; border-radius:12px; padding:12px;
    box-shadow:0 6px 20px rgba(0,0,0,0.06); overflow:auto; height:100%;
  }
  .category-btn{
    display:block; width:100%; text-align:center; padding:18px 12px; margin-bottom:12px; border-radius:10px;
    background:linear-gradient(180deg, #111, #222); color:var(--text); font-weight:700; font-size:16px; cursor:pointer;
    border:none; box-shadow: inset 0 -3px rgba(0,0,0,0.25);
  }
  .category-btn.active{ background:linear-gradient(180deg,var(--accent),#e6bb3a); color:#000; }

  .products-panel{
    background:#fff; border-radius:12px; padding:14px; box-shadow:0 6px 20px rgba(0,0,0,0.06); overflow:auto; height:100%;
  }
  .products-head{
    display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;
  }
  .search{ display:flex; gap:8px; align-items:center; }
  .search input{
    padding:10px 12px; border-radius:10px; border:1px solid #ddd; width:320px;
  }
  .grid-products{
    display:grid; grid-template-columns: repeat(auto-fill,minmax(160px,1fr)); gap:12px;
    align-items:stretch;
  }
  .product-tile{
    background:var(--panel); border-radius:12px; padding:10px; color:var(--text); display:flex; flex-direction:column; justify-content:space-between;
    min-height:140px; cursor:pointer; user-select:none; transition:transform .12s ease, box-shadow .12s ease;
  }
  .product-tile:active{ transform:scale(.995) }
  .product-top{ display:flex; gap:10px; align-items:center; }
  .product-thumb{ width:64px; height:64px; border-radius:8px; object-fit:cover; background:#222; flex-shrink:0; }
  .product-title{ font-weight:700; font-size:14px; color:#fff; }
  .product-desc{ font-size:12px; color:var(--muted); margin-top:4px; }
  .product-bottom{ display:flex; justify-content:space-between; align-items:center; margin-top:12px; gap:8px; }
  .product-price{ font-weight:800; color:var(--accent); font-size:16px; }
  .add-btn{
    background:var(--accent); border:none; padding:10px 12px; border-radius:8px; font-weight:800; cursor:pointer;
    transition:transform .08s ease;
  }
  .add-btn:active{ transform:translateY(1px) }

  .cart-panel{
    background:#fff; border-radius:12px; padding:12px; height:100%; box-shadow:0 6px 20px rgba(0,0,0,0.06); overflow:auto;
    display:flex; flex-direction:column;
  }
  .cart-head{ display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; }
  .cart-items{ flex:1; overflow:auto; }
  .cart-row{ display:flex; gap:8px; align-items:center; padding:10px; border-radius:8px; margin-bottom:8px; background:#f7f7f7; }
  .cart-name{ flex:1; font-weight:700; color:#111; font-size:14px; }
  .cart-price{ width:78px; text-align:right; font-weight:700; color:#111; }
  .qty-control{ display:flex; align-items:center; gap:6px; }
  .qty-btn{ width:30px; height:30px; border-radius:6px; border:none; background:#e9e9e9; font-weight:700; cursor:pointer; }
  .qty-val{ min-width:30px; text-align:center; font-weight:700; }

  .totals{
    margin-top:12px; padding-top:12px; border-top:1px dashed #eee; display:flex; flex-direction:column; gap:8px;
  }
  .tot-row{ display:flex; justify-content:space-between; align-items:center; font-weight:700; font-size:15px; color:#111; }
  .total-big{ font-size:22px; font-weight:900; color:var(--accent-dark); }

  .checkout-actions{ margin-top:12px; display:flex; gap:10px; }
  .btn-secondary{ flex:1; padding:12px; border-radius:10px; border:1px solid #ddd; background:#fff; cursor:pointer; font-weight:800; }
  .btn-primary{ flex:2; padding:12px; border-radius:10px; border:none; background:var(--accent); color:#000; font-weight:900; cursor:pointer; }

  @media (max-width:1100px){
    .pos-grid{ grid-template-columns: 1fr; height:auto; }
    .categories{ order:1 }
    .products-panel{ order:2; margin-top:12px }
    .cart-panel{ order:3; margin-top:12px }
    .top-info .time{ font-size:16px }
  }
</style>
</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="main-wrap">

  <div class="topbar">
    <div class="top-left">
      <div></div>
    </div>

    <div class="top-right">
      <div class="top-info">
        <div class="time" id="posTime">--:--:--</div>
        <div class="date" id="posDate">Loading date...</div>
      </div>
    </div>
  </div>

  <div class="pos-grid">

    <div class="categories" id="categories"></div>

    <div class="products-panel">
      <div class="products-head">
        <div class="search">
          <input id="searchInput" placeholder="Search product (tap to type)" aria-label="Search"/>
          <button style="padding:10px 12px;border-radius:8px;border:1px solid #ddd; background:#fff; cursor:pointer;" id="clearSearch">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>

      <div class="grid-products" id="productsContainer"></div>
    </div>

    <!-- CART -->
    <div class="cart-panel">
      <div class="cart-head">
        <div style="font-weight:900; font-size:18px;">Cart</div>
        <div style="color:var(--muted); font-size:14px;" id="cartCount">0 items</div>
      </div>

      <div class="cart-items" id="cartItems"></div>

      <div class="totals">
        <div class="tot-row"><div>Subtotal</div><div id="subtotal">₱0</div></div>
        <div class="tot-row"><div>VAT (12%)</div><div id="vatAmount">₱0</div></div>
        <div class="tot-row total-big"><div>Total</div><div id="grandTotal">₱0</div></div>

        <!-- PAYMENT METHOD -->
        <div class="tot-row" style="margin-top:8px;">
          <div>Payment</div>
          <select id="paymentMethod"
            style="width:180px; padding:8px; border-radius:8px; border:1px solid #ddd; font-weight:700;">
            <option value="Cash" selected>Cash</option>
            <option value="GCash">GCash</option>
            <option value="Maya">Maya</option>
            <option value="Online Banking">Online Banking</option>
          </select>
        </div>

        <!-- CASH ONLY -->
        <div id="cashWrap">
          <div class="tot-row" style="margin-top:8px;">
            <div>Cash</div>
            <input id="cashInput" type="number" min="0"
              style="width:140px; padding:6px; border-radius:8px; border:1px solid #ddd; text-align:right; font-weight:700;">
          </div>

          <div class="tot-row">
            <div>Change</div>
            <div id="changeAmount">₱0</div>
          </div>
        </div>

        <!-- NON-CASH ONLY -->
        <div id="nonCashWrap" style="display:none;">
          <div class="tot-row" style="margin-top:8px;">
            <div>Amount</div>
            <input id="nonCashAmount" type="number" min="0"
              style="width:140px; padding:6px; border-radius:8px; border:1px solid #ddd; text-align:right; font-weight:700;">
          </div>
        </div>
      </div>

      <div class="checkout-actions">
        <button class="btn-secondary" id="clearCartBtn"><i class="fas fa-trash"></i> Clear</button>
        <button class="btn-primary" id="checkoutBtn" disabled style="opacity:.5;cursor:not-allowed;">
          <i class="fas fa-money-bill-wave" style="margin-right:8px;"></i>
          Checkout
        </button>
      </div>
    </div>

  </div>
</div>

<!-- HIDDEN RECEIPT FORM -->
<form id="receiptForm" action="receipt.php" method="POST" target="_blank" style="display:none;">
  <input type="hidden" name="cart_data" id="cart_data">
  <input type="hidden" name="customer_name" id="customer_name_input">
  <input type="hidden" name="customer_tin" id="customer_tin_input">
  <input type="hidden" name="customer_address" id="customer_address_input">

  <input type="hidden" name="cash_amount" id="cash_amount_input">
  <input type="hidden" name="change_amount" id="change_amount_input">
  <input type="hidden" name="vatable_amount" id="vatable_amount_input">
  <input type="hidden" name="vat_amount" id="vat_amount_input">

  <!-- NEW -->
  <input type="hidden" name="payment_method" id="payment_method_input">
  <input type="hidden" name="paid_amount" id="paid_amount_input">
</form>

<!-- CUSTOMER MODAL -->
<div id="customerModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); align-items:center; justify-content:center; z-index:9999;">
  <div style="width:360px; background:#fff; padding:20px; border-radius:12px;">
    <h3 style="margin:0 0 8px 0;">Customer Details</h3>

    <label style="font-size:13px; color:#555">Customer Name</label>
    <input
      id="custName"
      style="width:100%; padding:10px; margin:8px 0 12px; border-radius:8px; border:1px solid #ddd"
      oninput="formatName(this)"
    />

    <label style="font-size:13px; color:#555">TIN Number</label>
    <input
      id="custTin"
      maxlength="11"
      style="width:100%; padding:10px; margin:8px 0 12px; border-radius:8px; border:1px solid #ddd"
      oninput="formatTIN(this)"
    />

    <label style="font-size:13px; color:#555">Address</label>
    <input id="custAddress"
      style="width:100%; padding:10px; margin:8px 0 12px;border-radius:8px; border:1px solid #ddd"
      oninput="formatName(this)"
    />

    <div style="display:flex; gap:10px; margin-top:12px;">
      <button id="cancelCust" style="flex:1; padding:10px; border-radius:8px; border:1px solid #ddd; background:#fff; cursor:pointer;">Cancel</button>
      <button id="confirmCust" style="flex:1; padding:10px; border-radius:8px; border:none; background:var(--accent); cursor:pointer; font-weight:800;">Confirm</button>
    </div>
  </div>
</div>

<script>
function formatName(input) {
  input.value = input.value
    .toLowerCase()
    .replace(/\b\w/g, char => char.toUpperCase());
}
function formatTIN(input) {
  let value = input.value.replace(/\D/g, '');
  value = value.substring(0, 9);

  if (value.length > 6) {
    value = value.replace(/(\d{3})(\d{3})(\d+)/, '$1-$2-$3');
  } else if (value.length > 3) {
    value = value.replace(/(\d{3})(\d+)/, '$1-$2');
  }
  input.value = value;
}

let grandTotalValue = 0;
let paymentMethodValue = "Cash";

/* ---------- Real-time clock ---------- */
function updatePosClock(){
  const now = new Date();
  const opts = { weekday: "long", year:"numeric", month:"long", day:"numeric" };
  const timeOpts = { hour: "2-digit", minute:"2-digit", second:"2-digit", hour12:true };
  document.getElementById('posDate').innerText = now.toLocaleDateString('en-US', opts);
  document.getElementById('posTime').innerText = now.toLocaleTimeString('en-US', timeOpts);
}
setInterval(updatePosClock, 1000);
updatePosClock();

/* ---------- OC BRAND PRODUCTS ---------- */
const PRODUCTS = <?= json_encode($products, JSON_UNESCAPED_SLASHES); ?>;

/* ---------- Build categories ---------- */
const categories = Array.from(new Set(PRODUCTS.map(p => p.category)));
const categoriesEl = document.getElementById('categories');

function createCategoryBtn(cat, active=false){
  const btn = document.createElement('button');
  btn.className = 'category-btn' + (active ? ' active' : '');
  btn.innerText = cat.toUpperCase();
  btn.dataset.cat = cat;
  btn.addEventListener('click', () => {
    document.querySelectorAll('.category-btn').forEach(x=>x.classList.remove('active'));
    btn.classList.add('active');
    renderProducts(cat);
  });
  return btn;
}
categoriesEl.appendChild(createCategoryBtn('all', true));
categories.forEach(c=>categoriesEl.appendChild(createCategoryBtn(c)));

/* ---------- Product rendering ---------- */
const productsContainer = document.getElementById('productsContainer');

function renderProducts(filter='all', query=''){
  productsContainer.innerHTML = '';
  const items = PRODUCTS
    .filter(p => (filter==='all' ? true : p.category===filter))
    .filter(p => p.name.toLowerCase().includes(query.toLowerCase()) || p.brand.toLowerCase().includes(query.toLowerCase()));

  if(items.length===0){
    productsContainer.innerHTML='<div style="padding:24px;color:#666">No products</div>';
    return;
  }

  items.forEach(p => {
    const tile = document.createElement('div');
    tile.className='product-tile';
    tile.innerHTML=`
      <div class="product-top">
        <img class="product-thumb" src="${p.img||'https://via.placeholder.com/120x90?text=IMG'}" alt="${p.name}">
        <div style="flex:1;">
          <div class="product-title">${p.name}</div>
          <div class="product-desc">
            ${p.brand}<br>
            <span style="color:${p.qty > 0 ? '#2dd36f' : '#ff6b6b'}">
              Stock: ${p.qty}
            </span>
          </div>
        </div>
      </div>
      <div class="product-bottom">
        <div class="product-price">₱${p.price.toLocaleString()}</div>
        <button class="add-btn" data-id="${p.id}">ADD</button>
      </div>
    `;
    productsContainer.appendChild(tile);
  });
}
renderProducts('all');

/* SEARCH */
document.getElementById('searchInput').addEventListener('input', (e)=>{
  const q=e.target.value.trim();
  const cat=(document.querySelector('.category-btn.active')||{}).dataset.cat||'all';
  renderProducts(cat,q);
});
document.getElementById('clearSearch').addEventListener('click', ()=>{
  document.getElementById('searchInput').value='';
  renderProducts((document.querySelector('.category-btn.active')||{}).dataset.cat||'all','');
});

/* ---------- CART LOGIC ---------- */
let cart=[]; // {id,name,price,cost,qty}

function findProduct(id){ return PRODUCTS.find(p=>p.id===parseInt(id)); }

function addToCart(productId, qty = 1){
  const p = findProduct(productId);
  if(!p) return;

  if(p.qty < qty){
    alert('Out of stock');
    return;
  }

  const existing = cart.find(x => x.id === p.id);
  if(existing){
    existing.qty += qty;
  }else{
    cart.push({
      id: p.id,
      name: p.name,
      price: p.price,
      cost: p.cost,
      qty: qty
    });
  }

  p.qty -= qty;

  renderCart();
  const cat = (document.querySelector('.category-btn.active')||{}).dataset.cat || 'all';
  renderProducts(cat, document.getElementById('searchInput').value);
}

document.addEventListener('click',(e)=>{
  if(e.target.matches('.add-btn')) addToCart(e.target.dataset.id);
});

/* Render cart items */
function renderCart(){
  const cartItems=document.getElementById('cartItems');
  cartItems.innerHTML='';

  if(cart.length===0){
    cartItems.innerHTML='<div style="padding:18px;color:#666">Cart is empty</div>';
  } else {
    cart.forEach(item=>{
      const row=document.createElement('div');
      row.className='cart-row';
      row.dataset.id=item.id;
      row.innerHTML=`
        <div class="cart-name">${item.name}</div>
        <div style="display:flex;align-items:center;gap:8px;">
          <div class="cart-price">₱${item.price.toLocaleString()}</div>
          <div class="qty-control">
            <button class="qty-btn dec" data-id="${item.id}">-</button>
            <div class="qty-val" data-id="${item.id}">${item.qty}</div>
            <button class="qty-btn inc" data-id="${item.id}">+</button>
          </div>
        </div>
      `;
      cartItems.appendChild(row);
    });
  }

  updateTotals();
}

/* Qty buttons */
document.getElementById('cartItems').addEventListener('click',(e)=>{
  if(e.target.matches('.qty-btn')||e.target.closest('.qty-btn')){
    const btn=e.target.closest('.qty-btn');
    const id=parseInt(btn.dataset.id);
    const item=cart.find(x=>x.id===id);
    if(!item) return;
    if(btn.classList.contains('inc')) item.qty++;
    if(btn.classList.contains('dec')) item.qty--;
    if(item.qty<=0) cart=cart.filter(x=>x.id!==id);
    renderCart();
  }
});

/* Totals WITH VAT (VAT-inclusive prices) */
function updateTotals(){
  const subtotal = cart.reduce((s,i)=>s+i.price*i.qty,0);

  const vatable = +(subtotal / 1.12).toFixed(2);
  const vat = +(subtotal - vatable).toFixed(2);
  const total = subtotal;

  grandTotalValue = total;

  document.getElementById('subtotal').innerText    = `₱${vatable.toLocaleString()}`;
  document.getElementById('vatAmount').innerText   = `₱${vat.toLocaleString()}`;
  document.getElementById('grandTotal').innerText  = `₱${total.toLocaleString()}`;
  document.getElementById('cartCount').innerText   = `${cart.reduce((s,i)=>s+i.qty,0)} items`;

  document.getElementById('vatable_amount_input').value = vatable;
  document.getElementById('vat_amount_input').value    = vat;

  validatePayment();
}

/* Clear cart */
document.getElementById('clearCartBtn').addEventListener('click',()=>{
  if(!confirm('Clear the cart?')) return;
  cart=[];
  renderCart();
});

/* ---------- PAYMENT METHOD UI + VALIDATION ---------- */
function setPaymentMethodUI(){
  const method = document.getElementById("paymentMethod").value;
  paymentMethodValue = method;

  const cashWrap = document.getElementById("cashWrap");
  const nonCashWrap = document.getElementById("nonCashWrap");

  if(method === "Cash"){
    cashWrap.style.display = "block";
    nonCashWrap.style.display = "none";
    document.getElementById("nonCashAmount").value = "";
  }else{
    cashWrap.style.display = "none";
    nonCashWrap.style.display = "block";
    document.getElementById("cashInput").value = "";
    document.getElementById("changeAmount").innerText = "₱0.00";
  }

  validatePayment();
}

/* CASH, CHANGE (Cash only) */
function computeChangeCashOnly() {
  const cashInput  = document.getElementById("cashInput");
  const changeEl   = document.getElementById("changeAmount");

  const cash = parseFloat(cashInput.value) || 0;

  if (cash <= 0 || cash < grandTotalValue) {
    changeEl.innerText = "₱0.00";
    return { paid: cash, change: 0, valid: false };
  }

  const change = cash - grandTotalValue;
  changeEl.innerText = "₱" + change.toFixed(2);

  return { paid: cash, change: change, valid: true };
}

function setCheckoutState(valid){
  const checkoutBtn = document.getElementById("checkoutBtn");
  checkoutBtn.disabled = !valid;
  checkoutBtn.style.opacity = valid ? "1" : ".5";
  checkoutBtn.style.cursor = valid ? "pointer" : "not-allowed";
}

/* Main validator for both cash and non-cash */
function validatePayment(){
  // disable if empty cart
  if(cart.length === 0){
    setCheckoutState(false);
    return { valid:false, paid:0, change:0 };
  }

  if(paymentMethodValue === "Cash"){
    const res = computeChangeCashOnly();
    setCheckoutState(res.valid);
    return res;
  }

  // NON-CASH: require amount == total (common for e-wallet/bank)
  const amt = parseFloat(document.getElementById("nonCashAmount").value) || 0;
  const valid = (amt > 0 && Math.abs(amt - grandTotalValue) < 0.01);

  // If you want allow >= total for non-cash, use:
  // const valid = (amt > 0 && amt >= grandTotalValue);

  setCheckoutState(valid);
  return { valid, paid: amt, change: 0 };
}

document.getElementById("paymentMethod").addEventListener("change", setPaymentMethodUI);
document.getElementById("cashInput").addEventListener("input", validatePayment);
document.getElementById("nonCashAmount").addEventListener("input", validatePayment);

/* ---------- CHECKOUT ---------- */
document.getElementById('checkoutBtn').addEventListener('click', () => {
  if (cart.length === 0) {
    alert('Cart is empty');
    return;
  }

  const result = validatePayment();
  if (!result.valid) {
    if(paymentMethodValue === "Cash"){
      alert('Cash must be equal to or greater than total amount');
      document.getElementById('cashInput').focus();
    }else{
      alert('Amount must match the total for ' + paymentMethodValue);
      document.getElementById('nonCashAmount').focus();
    }
    return;
  }

  document.getElementById('customerModal').style.display = 'flex';
});

/* ---------- CUSTOMER MODAL ---------- */
document.getElementById('cancelCust').addEventListener('click', () => {
  document.getElementById('customerModal').style.display = 'none';
});

document.getElementById("confirmCust").addEventListener("click", () => {
  const name = document.getElementById("custName").value.trim();
  const tin  = document.getElementById("custTin").value.trim();
  const address = document.getElementById("custAddress").value.trim();

  const computed = validatePayment();
  if (!computed.valid) {
    if(paymentMethodValue === "Cash"){
      alert('Cash must be equal to or greater than total amount');
      document.getElementById('cashInput').focus();
    }else{
      alert('Amount must match the total for ' + paymentMethodValue);
      document.getElementById('nonCashAmount').focus();
    }
    return;
  }

  // Customer info
  document.getElementById("customer_name_input").value = name;
  document.getElementById("customer_tin_input").value  = tin;
  document.getElementById("customer_address_input").value = address;

  // Payment info
  document.getElementById("payment_method_input").value = paymentMethodValue;
  document.getElementById("paid_amount_input").value    = computed.paid;

  // For compatibility with your current receipt.php fields:
  document.getElementById("cash_amount_input").value   = (paymentMethodValue === "Cash") ? computed.paid : computed.paid;
  document.getElementById("change_amount_input").value = (paymentMethodValue === "Cash") ? computed.change : 0;

  // Cart
  document.getElementById("cart_data").value = JSON.stringify(cart);

  // Submit receipt
  document.getElementById("receiptForm").submit();

  // Reset UI
  cart = [];
  renderCart();

  document.getElementById('custName').value = '';
  document.getElementById('custTin').value  = '';
  document.getElementById('custAddress').value = '';
  document.getElementById('cashInput').value = '';
  document.getElementById('nonCashAmount').value = '';

  document.getElementById('paymentMethod').value = 'Cash';
  setPaymentMethodUI();

  document.getElementById('customerModal').style.display = 'none';
});

/* init */
renderCart();
setPaymentMethodUI();
</script>

</body>
</html>
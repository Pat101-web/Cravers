
<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}
?>


<?php
// public/checkout.php

require_once __DIR__ . '/../config/db.php';
$keys = require __DIR__ . '/../config/keys.php';

// Ensure user logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=checkout.php");
    exit;
}

// Compute total from session cart (fallback to fixed if not present)
$totalAmount = 0;
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $it) {
        $price = floatval($it['price'] ?? 0);
        $qty   = intval($it['quantity'] ?? 1);
        $totalAmount += $price * $qty;
    }
}
if ($totalAmount <= 0) $totalAmount = 6000; // fallback sample amount
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <title>Checkout - Cravers</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <style>
    /* KEEPING YOUR ORANGE/WHITE THEME */
    body { font-family: Arial, sans-serif; background: #fff3e0; margin:0; padding:20px; }
    .container { max-width:780px; margin:24px auto; background:#fff; border-radius:12px; padding:24px; box-shadow:0 6px 22px rgba(0,0,0,0.06); }
    h2 { text-align:center; color:#e65100; margin:0 0 18px 0; }
    .section { border:2px solid #ffa726; background:#fff; padding:18px; border-radius:10px; margin-bottom:22px; }
    label{display:block;color:#e65100;font-weight:700;margin-bottom:6px}
    input[type="file"], input[type="number"], button { width:100%; padding:10px; border-radius:8px; border:1px solid #ddd; box-sizing:border-box; }
    input[readonly] { background:#fffbe6; }
    button { background:#ff9800; color:#fff; border:none; font-weight:700; cursor:not-allowed; margin-top:10px; padding:12px; border-radius:8px; }
    button.enabled { background:#e65100; cursor:pointer; }
    button.enabled:hover { background:#bf360c; }
    /* card element styling space */
    #card-element { padding:12px; border:1px solid #ddd; border-radius:8px; background:#fff; margin-top:8px; }
    .message { margin-top:12px; padding:10px; border-radius:8px; display:none; font-weight:700; text-align:center; }
    .success { background:#e8f5e9; color:#2e7d32; border:1px solid #c8e6c9; }
    .error { background:#ffebee; color:#c62828; border:1px solid #ffcdd2; }
  </style>
</head>
<body>
  <div class="container">
    <h2>Checkout - Select Payment Method</h2>

    <!-- BANK TRANSFER -->
    <div class="section">
      <h3 style="color:#e65100;margin-top:0;">🏦 Bank Transfer</h3>
      <form action="../payments/confirm_transfer.php" method="post" enctype="multipart/form-data">
        <label>Upload Bank Transfer Receipt</label>
        <input type="file" name="receipt" accept="image/*,.pdf" onchange="fileToggle(this,'bankBtn')" required>
        <label style="margin-top:10px">Amount</label>
        <input type="number" name="amount" value="<?= htmlentities($totalAmount) ?>" readonly>
        <button type="submit" id="bankBtn" disabled>Proceed Payment</button>
      </form>
    </div>

    <!-- MOBILE / USSD -->
    <div class="section">
      <h3 style="color:#e65100;margin-top:0;">📱 Mobile App / USSD</h3>
      <form action="../payments/confirm_mobile.php" method="post" enctype="multipart/form-data">
        <label>Upload Screenshot of Mobile Payment</label>
        <input type="file" name="receipt" accept="image/*,.pdf" onchange="fileToggle(this,'mobileBtn')" required>
        <label style="margin-top:10px">Amount</label>
        <input type="number" name="amount" value="<?= htmlentities($totalAmount) ?>" readonly>
        <button type="submit" id="mobileBtn" disabled>Confirm Mobile Payment</button>
      </form>
    </div>

    <!-- CARD -->
    <div class="section">
      <h3 style="color:#e65100;margin-top:0;">💳 Pay with Card</h3>

      <form id="card-form">
        <label>Enter Card Details</label>
        <div id="card-element"></div>
        <input type="number" name="amount" value="<?= htmlentities($totalAmount) ?>" readonly>
        <button id="cardBtn" disabled>Save Card & Pay</button>
      </form>

      <div id="card-message" class="message"></div>
    </div>
  </div>

  <!-- Stripe.js -->
  <script src="https://js.stripe.com/v3/"></script>
  <script>
    // Enable button only when file selected
    function fileToggle(inputEl, btnId) {
      const btn = document.getElementById(btnId);
      if (inputEl.files && inputEl.files.length > 0) {
        btn.disabled = false;
        btn.classList.add('enabled');
      } else {
        btn.disabled = true;
        btn.classList.remove('enabled');
      }
    }

    // Stripe setup
    const stripe = Stripe("<?= addslashes($keys['stripe_publishable'] ?? $keys['stripe_public'] ?? '') ?>");
    const elements = stripe.elements();
    const card = elements.create('card', { style: { base: { fontSize: '16px' } } });
    card.mount('#card-element');

    const cardBtn = document.getElementById('cardBtn');
    const cardMessage = document.getElementById('card-message');
    const TOTAL = document.getElementById('card-amount').value;

    // Enable card button when card element reports it's complete
    card.on('change', (ev) => {
      if (ev.complete) {
        cardBtn.disabled = false;
        cardBtn.classList.add('enabled');
      } else {
        cardBtn.disabled = true;
        cardBtn.classList.remove('enabled');
      }
      if (ev.error) {
        showCardMessage(ev.error.message, 'error');
      } else {
        hideCardMessage();
      }
    });

    function showCardMessage(msg, type){
      cardMessage.textContent = msg;
      cardMessage.className = 'message ' + (type === 'error' ? 'error' : 'success');
      cardMessage.style.display = 'block';
    }
    function hideCardMessage(){
      cardMessage.style.display = 'none';
      cardMessage.textContent = '';
    }

    // On form submit — create setup intent, confirm card, save payment method and insert transaction,
    // then redirect to history.php
    document.getElementById('card-form').addEventListener('submit', async (e) => {
      e.preventDefault();
      cardBtn.disabled = true;
      cardBtn.classList.remove('enabled');
      hideCardMessage();

      // 1) create setup intent (server endpoint)
      let res = await fetch('../payments/create_setup_intent.php', { method: 'POST' });
      let json = await res.json();

      if (!res.ok || json.error) {
        showCardMessage(json.error?.message || json.error || 'Could not create payment intent', 'error');
        cardBtn.disabled = false;
        return;
      }

      // 2) Confirm card setup with stripe
      const { setupIntent, error } = await stripe.confirmCardSetup(json.client_secret, {
        payment_method: { card: card }
      });

      if (error) {
        showCardMessage(error.message, 'error');
        cardBtn.disabled = false;
        return;
      }

      // 3) Save payment method and create transaction (backend)
      // send payment_method + amount to backend (x-www-form-urlencoded)
      const params = new URLSearchParams();
      params.append('payment_method', setupIntent.payment_method);
      params.append('amount', TOTAL);

      try {
        let saveRes = await fetch('../payments/save_payment_method.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: params.toString()
        });
        let saveJson = await saveRes.json();
        if (saveJson.success) {
          // redirect to history so user can see transaction status
          window.location.href = 'history.php';
        } else {
          showCardMessage(saveJson.message || 'Failed to save card', 'error');
          cardBtn.disabled = false;
        }
      } catch (err) {
        showCardMessage('Network error. Try again.', 'error');
        cardBtn.disabled = false;
      }
    });
  </script>
</body>
</html>

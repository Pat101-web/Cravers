<?php
session_start();
$keys = require __DIR__ . '/../config/keys.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Card - Cravers</title>
  <script src="https://js.stripe.com/v3/"></script>
  <style>
      body { font-family: Arial, sans-serif; background: #f9f9f9; display: flex; justify-content: center; align-items: center; height: 100vh; }
      .card-box { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); width: 400px; }
      h2 { color: #ff6600; margin-bottom: 20px; }
      #card-element { border: 1px solid #ccc; padding: 10px; border-radius: 8px; margin-bottom: 15px; }
      button { background: #ff6600; color: white; border: none; padding: 12px; border-radius: 8px; cursor: pointer; width: 100%; }
      button:hover { background: #e65c00; }
  </style>
</head>
<body>
  <div class="card-box">
    <h2>Add a Payment Method</h2>
    <form id="card-form">
        <div id="card-element"></div>
        <button type="submit">Save Card</button>
    </form>
  </div>

  <script>
    const stripe = Stripe("<?= $keys['stripe_publishable'] ?>");
    const elements = stripe.elements();
    const cardElement = elements.create("card");
    cardElement.mount("#card-element");

    document.getElementById("card-form").addEventListener("submit", async (e) => {
        e.preventDefault();

        const res = await fetch("create_setup_intent.php");
        const data = await res.json();

        if (data.error) {
            alert("Error: " + data.error.message);
            return;
        }

        const result = await stripe.confirmCardSetup(data.client_secret, {
            payment_method: { card: cardElement }
        });

        if (result.error) {
            alert(result.error.message);
        } else {
            fetch("save_payment_method.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ payment_method: result.setupIntent.payment_method })
            }).then(res => res.json()).then(data => {
                if (data.success) {
                    alert("✅ Card saved successfully!");
                    window.location.href = "../public/history.php";
                } else {
                    alert("❌ Error saving card.");
                }
            });
        }
    });
  </script>
</body>
</html>

<?php
declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

$cart = new Cart();
if ($cart->isEmpty()) {
    header('Location: kosik.php');
    exit;
}

// Zabezpečení přeskakování kroků
if (!isset($_SESSION['order_step_1']) || !isset($_SESSION['order_step_2'])) {
    header('Location: objednavka-1.php');
    exit;
}

$formData = $_SESSION['order_step_1'];
$step2Data = $_SESSION['order_step_2'];

$shippingRepo = new ShippingMethodRepository();
$paymentRepo = new PaymentMethodRepository();

$shipping = $shippingRepo->getById($step2Data['shipping_id']);
$payment = $paymentRepo->getById($step2Data['payment_id']);

$itemsPrice = $cart->getTotalPrice();
$shippingPrice = $shipping ? $shipping->price : 0.0;
$paymentPrice = $payment ? $payment->price : 0.0;
$totalPrice = $itemsPrice + $shippingPrice + $paymentPrice;

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ověříme souhlas s obchodními podmínkami
    $terms = isset($_POST['terms']);
    
    if (!$terms) {
        $errors['terms'] = 'Pro odeslání objednávky musíte souhlasit s obchodními podmínkami.';
    }

    if (empty($errors)) {
        // Vše v pořádku - přesměrujeme na odesílací/potvrzovací skript
        $_SESSION['order_step_3_confirmed'] = true;
        header('Location: objednavka-potvrzeni.php');
        exit;
    }
}

$pageTitle = 'Objednávka (3/3) - Shrnutí';
require_once __DIR__ . '/partials/header.php';
?>

<main class="container">
    <h1 style="margin-top:20px;">Košík (3/3): Shrnutí objednávky</h1>
    
    <div class="cart-layout">
        <div>
            <!-- Rekapitulace položek a cen -->
            <div style="background:#1e1e1e; padding:20px; margin-bottom:20px; border-radius: var(--radius); border: 1px solid #333;">
                <h3 style="border-bottom: 1px solid #333; padding-bottom: 10px;">Položky objednávky</h3>
                <table style="width:100%; text-align:left; border-collapse: collapse; margin-top:10px;">
                    <thead>
                        <tr style="border-bottom:1px solid #333; color: var(--text-muted);">
                            <th style="padding:10px 5px;">Produkt</th>
                            <th style="padding:10px 5px;">Varianta</th>
                            <th style="padding:10px 5px; text-align: center;">Množství</th>
                            <th style="padding:10px 5px; text-align: right;">Cena</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart->getItems() as $item): ?>
                            <tr style="border-bottom: 1px solid #222;">
                                <td style="padding:12px 5px; font-weight: bold;"><?= htmlspecialchars($item->productName) ?></td>
                                <td style="padding:12px 5px; font-size: 0.9rem; color: var(--text-muted);"><?= $item->variant !== '' ? htmlspecialchars($item->variant) : 'Standardní' ?></td>
                                <td style="padding:12px 5px; text-align: center;"><?= $item->quantity ?> ks</td>
                                <td style="padding:12px 5px; text-align: right; color: var(--primary); font-weight: bold;"><?= number_format($item->getTotalPrice(), 0, ',', ' ') ?> Kč</td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <!-- Doprava -->
                        <tr style="border-bottom: 1px solid #222;">
                            <td colspan="2" style="padding:12px 5px; font-weight: bold; color: var(--text-muted);">Doprava: <?= htmlspecialchars($shipping->name) ?></td>
                            <td style="padding:12px 5px; text-align: center;">x 1</td>
                            <td style="padding:12px 5px; text-align: right; font-weight: bold;"><?= $shippingPrice === 0.0 ? 'ZDARMA' : number_format($shippingPrice, 0, ',', ' ') . ' Kč' ?></td>
                        </tr>

                        <!-- Platba -->
                        <tr style="border-bottom: 1px solid #222;">
                            <td colspan="2" style="padding:12px 5px; font-weight: bold; color: var(--text-muted);">Platba: <?= htmlspecialchars($payment->name) ?></td>
                            <td style="padding:12px 5px; text-align: center;">x 1</td>
                            <td style="padding:12px 5px; text-align: right; font-weight: bold;"><?= $paymentPrice === 0.0 ? 'ZDARMA' : number_format($paymentPrice, 0, ',', ' ') . ' Kč' ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Rekapitulace adresy -->
            <div style="background:#1e1e1e; padding:20px; border-radius: var(--radius); border: 1px solid #333; margin-bottom: 30px;">
                <h3 style="border-bottom: 1px solid #333; padding-bottom: 10px;">Fakturační a dodací údaje</h3>
                <p style="line-height: 1.8; margin-top: 10px;">
                    <strong>Jméno:</strong> <?= htmlspecialchars($formData['first_name'] . ' ' . $formData['last_name']) ?><br>
                    <strong>Adresa:</strong> <?= htmlspecialchars($formData['street'] . ', ' . $formData['zip'] . ' ' . $formData['city']) ?><br>
                    <strong>E-mail:</strong> <?= htmlspecialchars($formData['email']) ?><br>
                    <strong>Telefon:</strong> <?= htmlspecialchars($formData['phone'] !== '' ? $formData['phone'] : 'Neuveden') ?><br>
                    <?php if ($formData['note'] !== ''): ?>
                        <strong>Poznámka:</strong> <?= htmlspecialchars($formData['note']) ?>
                    <?php endif; ?>
                </p>
            </div>

            <!-- Formulář s odesláním objednávky -->
            <form action="objednavka-3.php" method="POST">
                <div style="margin-bottom: 25px;">
                    <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; font-size: 0.95rem;">
                        <input type="checkbox" name="terms" style="width: auto; margin-top: 4px;" required>
                        Souhlasím s obchodními podmínkami MTB Gravity Shopu a beru na vědomí zpracování osobních údajů.
                    </label>
                    <?php if (isset($errors['terms'])): ?>
                        <span style="color: var(--primary); font-size: 0.85rem; display: block; margin-top: 8px; font-weight: bold;"><?= htmlspecialchars($errors['terms']) ?></span>
                    <?php endif; ?>
                </div>

                <div style="display:flex; gap:10px;">
                     <a href="objednavka-2.php" class="btn btn-outline">Zpět</a>
                     <button type="submit" class="btn" style="padding: 15px 35px; font-size: 1.1rem;">ZÁVAZNĚ OBJEDNAT</button>
                </div>
            </form>
        </div>

        <!-- Shrnutí výsledné ceny vpravo -->
        <div class="order-summary" style="background:#1e1e1e; padding:20px; border:1px solid #333; height:fit-content; border-radius: var(--radius);">
            <h3 style="border-bottom: 1px solid #333; padding-bottom: 10px; margin-bottom: 15px;">Konečné vyúčtování</h3>
            <p style="margin-bottom: 8px;">Zboží celkem: <span style="float: right;"><?= number_format($itemsPrice, 0, ',', ' ') ?> Kč</span></p>
            <p style="margin-bottom: 8px;">Doprava: <span style="float: right;"><?= $shippingPrice === 0.0 ? 'ZDARMA' : number_format($shippingPrice, 0, ',', ' ') . ' Kč' ?></span></p>
            <p style="margin-bottom: 15px; border-bottom: 1px solid #333; padding-bottom: 10px;">Platba: <span style="float: right;"><?= $paymentPrice === 0.0 ? 'ZDARMA' : number_format($paymentPrice, 0, ',', ' ') . ' Kč' ?></span></p>
            
            <p style="font-size:1.4rem; font-weight:bold; color: var(--primary);">Celková cena: <br><span style="float: right; font-size: 1.8rem; margin-top: 10px;"><?= number_format($totalPrice, 0, ',', ' ') ?> Kč</span></p>
        </div>
    </div>
</main>

<?php
require_once __DIR__ . '/partials/footer.php';
?>
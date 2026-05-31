<?php
declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

$cart = new Cart();
if ($cart->isEmpty()) {
    header('Location: kosik.php');
    exit;
}

// Zabezpečíme, že uživatel nemůže přeskočit krok 1
if (!isset($_SESSION['order_step_1'])) {
    header('Location: objednavka-1.php');
    exit;
}

$shippingRepo = new ShippingMethodRepository();
$paymentRepo = new PaymentMethodRepository();

// Načteme číselníky dopravy a platby z databáze
$shippingMethods = $shippingRepo->getAll();
$paymentMethods = $paymentRepo->getAll();

// Načteme dříve vybrané hodnoty ze session, abychom je označili jako "checked"
$selectedShipping = (int)($_SESSION['order_step_2']['shipping_id'] ?? 0);
$selectedPayment = (int)($_SESSION['order_step_2']['payment_id'] ?? 0);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shippingId = (int)($_POST['doprava'] ?? 0);
    $paymentId = (int)($_POST['platba'] ?? 0);

    // Načteme zvolené metody pro kontrolu existence
    $shipping = $shippingRepo->getById($shippingId);
    $payment = $paymentRepo->getById($paymentId);

    if ($shipping === null) {
        $errors['doprava'] = 'Vyberte prosím způsob dopravy.';
    }
    if ($payment === null) {
        $errors['platba'] = 'Vyberte prosím způsob platby.';
    }

    if (empty($errors)) {
        // Uložíme do session a přesměrujeme na krok 3
        $_SESSION['order_step_2'] = [
            'shipping_id' => $shippingId,
            'payment_id' => $paymentId,
        ];
        header('Location: objednavka-3.php');
        exit;
    }
}

// Nastavíme výchozí vybrané metody, pokud nejsou v session, vezmeme první z databáze
if ($selectedShipping === 0 && !empty($shippingMethods)) {
    $selectedShipping = $shippingMethods[0]->id;
}
if ($selectedPayment === 0 && !empty($paymentMethods)) {
    $selectedPayment = $paymentMethods[0]->id;
}

$pageTitle = 'Objednávka (2/3) - Doprava a platba';
require_once __DIR__ . '/partials/header.php';
?>

<main class="container">
    <h1 style="margin-top:20px;">Košík (2/3): Doprava a platba</h1>
    
    <div class="cart-layout">
        <form action="objednavka-2.php" method="POST">
            
            <h3>Způsob dopravy</h3>
            <?php if (isset($errors['doprava'])): ?>
                <span style="color: var(--primary); font-size: 0.85rem; display: block; margin-bottom: 10px;"><?= htmlspecialchars($errors['doprava']) ?></span>
            <?php endif; ?>
            
            <?php foreach ($shippingMethods as $method): ?>
                <label style="display:block; padding:15px; background:#1e1e1e; margin-bottom:10px; border:1px solid #333; cursor:pointer; border-radius: var(--radius);">
                    <input type="radio" name="doprava" value="<?= $method->id ?>" <?= $method->id === $selectedShipping ? 'checked' : '' ?>>
                    <strong style="margin-left: 10px;"><?= htmlspecialchars($method->name) ?></strong> 
                    (<?= htmlspecialchars($method->deliveryDays) ?>)
                    <span style="float: right; color: var(--primary); font-weight: bold;">
                        <?= $method->price === 0.0 ? 'ZDARMA' : number_format($method->price, 0, ',', ' ') . ' Kč' ?>
                    </span>
                </label>
            <?php endforeach; ?>

            <h3 style="margin-top:30px;">Způsob platby</h3>
            <?php if (isset($errors['platba'])): ?>
                <span style="color: var(--primary); font-size: 0.85rem; display: block; margin-bottom: 10px;"><?= htmlspecialchars($errors['platba']) ?></span>
            <?php endif; ?>

            <?php foreach ($paymentMethods as $method): ?>
                <label style="display:block; padding:15px; background:#1e1e1e; margin-bottom:10px; border:1px solid #333; cursor:pointer; border-radius: var(--radius);">
                    <input type="radio" name="platba" value="<?= $method->id ?>" <?= $method->id === $selectedPayment ? 'checked' : '' ?>>
                    <strong style="margin-left: 10px;"><?= htmlspecialchars($method->name) ?></strong>
                    <span style="float: right; color: var(--primary); font-weight: bold;">
                        <?= $method->price === 0.0 ? 'ZDARMA' : '+' . number_format($method->price, 0, ',', ' ') . ' Kč' ?>
                    </span>
                </label>
            <?php endforeach; ?>

            <div style="margin-top:30px; display:flex; gap:10px;">
                <a href="objednavka-1.php" class="btn btn-outline">Zpět</a>
                <button type="submit" class="btn">Pokračovat na shrnutí ></button>
            </div>
        </form>
        
        <!-- Shrnutí cen vpravo -->
        <div class="order-summary" style="background:#1e1e1e; padding:20px; border:1px solid #333; height:fit-content; border-radius: var(--radius);">
            <h3>V košíku:</h3>
            <div style="border-bottom:1px solid #333; padding-bottom:10px; margin-bottom:10px;">
                <p>Zboží celkem <span style="float:right; font-weight: bold;"><?= number_format($cart->getTotalPrice(), 0, ',', ' ') ?> Kč</span></p>
            </div>
            <p style="font-size:1.2rem; font-weight:bold;">Celkem za zboží: <span style="float: right; color: var(--primary);"><?= number_format($cart->getTotalPrice(), 0, ',', ' ') ?> Kč</span></p>
        </div>
    </div>
</main>

<?php
require_once __DIR__ . '/partials/footer.php';
?>
<?php
declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

$cart = new Cart();

// Ujistíme se, že objednávka byla úspěšně shrnuta a potvrzena v kroku 3
if (!isset($_SESSION['order_step_3_confirmed'])) {
    header('Location: kosik.php');
    exit;
}

$formData = $_SESSION['order_step_1'];
$step2Data = $_SESSION['order_step_2'];

$customerRepo = new CustomerRepository();
$orderRepo = new OrderRepository();

// Spustíme transakci v DB a uložíme zákazníka a objednávku (podle požadavků učitele)
try {
    // 1. Vytvoříme zákazníka v databázi
    $customer = $customerRepo->create(
        $formData['first_name'],
        $formData['last_name'],
        $formData['email'],
        $formData['phone'],
        $formData['street'],
        $formData['city'],
        $formData['zip']
    );

    // 2. Vytvoříme objednávku s položkami košíku
    $order = $orderRepo->create(
        $customer->id,
        $step2Data['shipping_id'],
        $step2Data['payment_id'],
        $formData['note'],
        $cart->getItems()
    );

    // Vyčistíme nákupní košík a uložené kroky v session
    $cart->clear();
    unset($_SESSION['order_step_1']);
    unset($_SESSION['order_step_2']);
    unset($_SESSION['order_step_3_confirmed']);

    $orderNumber = $order ? str_pad((string)$order->id, 6, '0', STR_PAD_LEFT) : '000001';

} catch (\Throwable $e) {
    // Pokud se stala chyba při ukládání do DB, vypíšeme ji
    die('Chyba při ukládání objednávky: ' . htmlspecialchars($e->getMessage()));
}

$pageTitle = 'Objednávka úspěšně odeslána';
require_once __DIR__ . '/partials/header.php';
?>

<main class="container" style="text-align:center; padding-top:80px; padding-bottom: 80px;">
    <h1 style="color:#4BB543; font-size:3.5rem; margin-bottom: 20px;">Objednávka úspěšně odeslána!</h1>
    <p style="font-size:1.3rem; margin:20px 0; color: #fff;">Děkujeme za váš nákup v MTB Gravity Shopu. Potvrzení a informace k platbě vám zašleme na e-mail.</p>
    
    <div style="background: #1e1e1e; border: 1px solid #333; padding: 30px; display: inline-block; margin: 30px 0; border-radius: var(--radius); text-align: left; min-width: 350px;">
        <h3 style="border-bottom: 1px solid #333; padding-bottom: 10px; margin-bottom: 15px;">Detaily objednávky:</h3>
        <p style="font-size: 1.1rem; margin-bottom: 10px;">Číslo objednávky: <strong>#<?= htmlspecialchars($orderNumber) ?></strong></p>
        <p style="font-size: 1.1rem; margin-bottom: 10px;">Doručovací e-mail: <strong><?= htmlspecialchars($customer->email) ?></strong></p>
        <p style="font-size: 1.1rem;">Způsob platby: <strong><?= htmlspecialchars($order->paymentMethod ? $order->paymentMethod->name : 'Zvoleno') ?></strong></p>
    </div>
    
    <div style="margin-top:20px;">
        <a href="index.php" class="btn" style="padding: 15px 40px; font-size: 1.1rem;">Zpět na úvodní stránku</a>
    </div>
</main>

<?php
require_once __DIR__ . '/partials/footer.php';
?>
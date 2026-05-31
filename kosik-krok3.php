<?php
declare(strict_types=1);
// 1. Načteme jádro webu, databázi a košík
require_once __DIR__ . '/src/bootstrap.php';

$productRepo = new ProductRepository();
$cart = new Cart();

// Bezpečnostní pojistka: Pokud je košík prázdný, přesměrujeme na hlavní stránku
if ($cart->isEmpty()) {
    header('Location: index.php');
    exit;
}

// Bezpečnostní pojistka: Pokud chybí adresa z 1. kroku, vrátíme uživatele na začátek
if (!isset($_SESSION['checkout_customer'])) {
    header('Location: kosik-krok1.php');
    exit;
}

// 2. Uložení dopravy a platby z 2. kroku do Session (pokud přicházíme metodou POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['checkout_shipping'] = $_POST['shipping'] ?? 'ppl';
    $_SESSION['checkout_payment'] = $_POST['payment'] ?? 'card';
}

// Pojistka: Pokud v session chybí doprava nebo platba, vrátíme uživatele na krok 2
if (!isset($_SESSION['checkout_shipping']) || !isset($_SESSION['checkout_payment'])) {
    header('Location: kosik-krok2.php');
    exit;
}

// Vytáhneme si uložená data ze session do přehledných proměnných
$customer = $_SESSION['checkout_customer'];
$shippingMethod = $_SESSION['checkout_shipping'];
$paymentMethod = $_SESSION['checkout_payment'];

// 3. Výpočet ceny dopravy
$shippingPrice = 0;
$shippingName = 'Osobní odběr';
if ($shippingMethod === 'ppl') {
    $shippingPrice = 90;
    $shippingName = 'PPL Kurýr';
}

// 4. Výpočet ceny platby
$paymentPrice = 0;
$paymentName = 'Kartou online';
if ($paymentMethod === 'cod') {
    $paymentPrice = 49;
    $paymentName = 'Dobírka';
}

// 5. Načtení produktů z košíku a výpočet mezisoučtu za zboží
$cartItems = $cart->getItems();
$productsInCart = [];
$productsTotal = 0;

foreach ($cartItems as $productId => $quantity) {
    $product = $productRepo->getById((int)$productId);
    if ($product) {
        $subtotal = $product->price * $quantity;
        $productsTotal += $subtotal;
        $productsInCart[] = [
            'product' => $product,
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ];
    }
}

// Konečná cena včetně dopravy a platby
$grandTotal = $productsTotal + $shippingPrice + $paymentPrice;

// 6. Nastavení proměnných pro hlavičku
$pageTitle = 'Košík (3/3) - Shrnutí objednávky | GRAVITY SHOP';
$cartItemCount = $cart->getTotalQuantity();

require __DIR__ . '/partials/header.php';
?>

<main class="container">
    <h1 style="margin-top:20px;">Košík (3/3): Shrnutí objednávky</h1>
    <div class="cart-layout">
        <div>
            
            <div style="background:#1e1e1e; padding:20px; margin-bottom:20px; border: 1px solid #333;">
                <h3>Položky objednávky</h3>
                <table style="width:100%; text-align:left; border-collapse: collapse; margin-top:10px;">
                    <thead>
                        <tr style="border-bottom:1px solid #333;">
                            <th style="padding:10px;">Produkt</th>
                            <th>Množství</th>
                            <th style="text-align: right; padding-right: 10px;">Cena</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productsInCart as $item): ?>
                            <tr style="border-bottom:1px solid #222;">
                                <td style="padding:10px;"><?= htmlspecialchars($item['product']->name) ?></td>
                                <td><?= (int)$item['quantity'] ?> ks</td>
                                <td style="text-align: right; padding-right: 10px;"><?= number_format($item['subtotal'], 0, ',', ' ') ?> Kč</td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <tr style="border-bottom:1px solid #222; color: #aaa;">
                            <td style="padding:10px;">Doprava: <?= htmlspecialchars($shippingName) ?></td>
                            <td>1x</td>
                            <td style="text-align: right; padding-right: 10px;"><?= $shippingPrice > 0 ? number_format($shippingPrice, 0, ',', ' ') . ' Kč' : 'ZDARMA' ?></td>
                        </tr>
                        
                        <?php if ($paymentPrice > 0): ?>
                            <tr style="border-bottom:1px solid #222; color: #aaa;">
                                <td style="padding:10px;">Platba: <?= htmlspecialchars($paymentName) ?></td>
                                <td>1x</td>
                                <td style="text-align: right; padding-right: 10px;"><?= number_format($paymentPrice, 0, ',', ' ') ?> Kč</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <div style="margin-top: 20px; text-align: right; padding-right: 10px;">
                    <span style="font-size:1.4rem; font-weight:bold; color: #e62117;">Celkem k úhradě: <?= number_format($grandTotal, 0, ',', ' ') ?> Kč</span>
                </div>
            </div>

            <div style="background:#1e1e1e; padding:20px; border: 1px solid #333;">
                <h3>Fakturační a dodací údaje</h3>
                <p style="line-height: 1.6; margin-top: 10px;">
                    <strong>Jméno a příjmení:</strong> <?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?><br>
                    <strong>Adresa:</strong> <?= htmlspecialchars($customer['address']) ?>, <?= htmlspecialchars($customer['zip']) ?> <?= htmlspecialchars($customer['city']) ?><br>
                    <strong>E-mail:</strong> <?= htmlspecialchars($customer['email']) ?><br>
                    <strong>Telefon:</strong> <?= htmlspecialchars($customer['phone'] ?: 'Nevyplněno') ?>
                </p>
                <?php if (!empty($customer['note']
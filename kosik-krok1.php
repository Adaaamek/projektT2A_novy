<?php
declare(strict_types=1);
// 1. Načteme jádro webu, databázi a košík
require_once __DIR__ . '/src/bootstrap.php';

$productRepo = new ProductRepository();
$cart = new Cart();

// 2. Zpracování akce: Pokud sem uživatel přišel přes tlačítko "Vložit do košíku"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $productId = (int)($_POST['product_id'] ?? 0);
    if ($productId > 0) {
        $cart->add($productId); // Přidá 1 kus produktu do košíku
    }
    // Přesměrujeme sami na sebe (čisté GET načtení), aby se při refreshování stránky zboží nepřidávalo znova
    header('Location: kosik-krok1.php');
    exit;
}

// 3. Příprava dat pro výpis košíku v pravém sloupci
$cartItems = $cart->getItems(); // Načte obsah košíku [id_produktu => množství]
$productsInCart = [];
$totalPrice = 0;

foreach ($cartItems as $productId => $quantity) {
    $product = $productRepo->getById((int)$productId);
    if ($product) {
        $subtotal = $product->price * $quantity;
        $totalPrice += $subtotal;
        $productsInCart[] = [
            'product' => $product,
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ];
    }
}

// 4. Nastavení proměnných pro hlavičku
$pageTitle = 'Košík (1/3) - Dodací údaje | GRAVITY SHOP';
$cartItemCount = $cart->getTotalQuantity();

// 5. Načteme hlavičku stránky
require __DIR__ . '/partials/header.php';
?>

<main class="container">
    <h1 style="margin-top:20px;">Košík (1/3): Dodací údaje</h1>
    <div class="cart-layout">
        
        <form action="kosik-krok2.php" method="POST">
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                <input type="text" name="first_name" placeholder="Jméno" required>
                <input type="text" name="last_name" placeholder="Příjmení" required>
            </div>
            <input type="email" name="email" placeholder="E-mail" required>
            <input type="tel" name="phone" placeholder="Telefon (+420)">
            <input type="text" name="address" placeholder="Ulice a číslo popisné" required>
            
            <div style="display:grid; grid-template-columns: 1fr 2fr; gap:15px;">
                <input type="text" name="zip" placeholder="PSČ" required>
                <input type="text" name="city" placeholder="Město" required>
            </div>
            <textarea name="note" placeholder="Poznámka pro kurýra..."></textarea>
            
            <button type="submit" class="btn" <?= empty($productsInCart) ? 'disabled style="opacity:0.5; cursor:not-allowed;"' : '' ?>>
                Pokračovat na dopravu >
            </button>
        </form>

        <div class="order-summary" style="background:#1e1e1e; padding:20px; border:1px solid #333; height:fit-content;">
            <h3>V košíku:</h3>
            
            <?php if (!empty($productsInCart)): ?>
                <?php foreach ($productsInCart as $item): ?>
                    <div style="border-bottom:1px solid #333; padding-bottom:10px; margin-bottom:10px;">
                        <p>
                            <?= (int)$item['quantity'] ?>x <?= htmlspecialchars($item['product']->name) ?> 
                            <span style="float:right;"><?= number_format($item['subtotal'], 0, ',', ' ') ?> Kč</span>
                        </p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="border-bottom:1px solid #333; padding-bottom:10px; margin-bottom:10px; color: #aaa;">
                    <p>Tvůj košík zeje prázdnotou...</p>
                </div>
            <?php endif; ?>
            
            <p style="font-size:1.2rem; font-weight:bold;">Celkem: <?= number_format($totalPrice, 0, ',', ' ') ?> Kč</p>
        </div>
        
    </div>
</main>

<?php 
// 6. Načteme patičku
require __DIR__ . '/partials/footer.php'; 
?>
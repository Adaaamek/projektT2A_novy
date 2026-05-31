<?php
declare(strict_types=1);
// 1. Načteme jádro webu, databázi a košík
require_once __DIR__ . '/src/bootstrap.php';

$productRepo = new ProductRepository();
$cart = new Cart();

// 2. Zjistíme, jaký produkt uživatel otevřel (podle parametru ?slug= v URL)
$slug = $_GET['slug'] ?? '';
$product = $productRepo->getBySlug($slug);

// Pokud by někdo zadal špatnou adresu a produkt se nenašel, vrátíme ho na e-shop
if (!$product) {
    header('Location: produkty.php');
    exit;
}

// 3. Nastavení proměnných pro hlavičku
$pageTitle = htmlspecialchars($product->name) . ' | GRAVITY SHOP';
$cartItemCount = $cart->getTotalQuantity();

// 4. Načteme hlavičku
require __DIR__ . '/partials/header.php';
?>

<main class="container">
    <div class="breadcrumbs">Domů / Všechny produkty / <?= htmlspecialchars($product->name) ?></div>
    
    <div class="cart-layout" style="margin-top:20px;">
        <div class="gallery">
            <img src="<?= htmlspecialchars($product->image) ?>" alt="<?= htmlspecialchars($product->name) ?>" style="width:100%; border:1px solid #333; margin-bottom:10px;">
            <div style="display:flex; gap:10px;">
                <img src="https://placehold.co/150x100/222/666?text=Detail+1" style="cursor:pointer;" alt="Detail 1">
                <img src="https://placehold.co/150x100/222/666?text=Detail+2" style="cursor:pointer;" alt="Detail 2">
                <img src="https://placehold.co/150x100/222/666?text=Detail+3" style="cursor:pointer;" alt="Detail 3">
            </div>
        </div>

        <div class="product-summary">
            <h1><?= htmlspecialchars($product->name) ?></h1>
            <p class="price" style="font-size:2rem; margin-bottom:20px;"><?= number_format($product->price, 0, ',', ' ') ?> Kč</p>
            <p><?= htmlspecialchars($product->description ?? 'K tomuto produktu momentálně nemáme podrobnější popis.') ?></p>
            
            <div style="margin: 20px 0;">
                <label>Velikost rámu:</label>
                <select><option>M</option><option>L</option><option>XL</option></select>
                
                <label style="margin-left: 15px;">Barva:</label>
                <select><option>Výchozí</option></select>
            </div>

            <form action="kosik-krok1.php" method="POST" style="margin: 0;">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?= (int)$product->id ?>">
                <button type="submit" class="btn" style="width:100%; text-align:center; padding:15px; border: none; cursor: pointer; font-size: 1.1rem; font-weight: bold;">
                    🛒 Vložit do košíku
                </button>
            </form>
            
            <ul style="margin-top:20px; font-size:0.9rem; color:#aaa;">
                <li>✅ Skladem (ihned k odeslání)</li>
                <li>✅ Doprava zdarma</li>
                <li>✅ Záruka stability a kvality</li>
            </ul>
        </div>
    </div>
</main>

<?php 
// 5. Načteme patičku
require __DIR__ . '/partials/footer.php'; 
?>
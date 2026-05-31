<?php
declare(strict_types=1);
// 1. Načteme jádro webu, databázi a košík
require_once __DIR__ . '/src/bootstrap.php';

$productRepo = new ProductRepository();
$cart = new Cart();

// 2. Připravíme proměnné, které potřebuje partials/header.php
$pageTitle = 'GRAVITY SHOP | Domů';
$cartItemCount = $cart->getTotalQuantity();

// 3. Vytáhneme z databáze přesně 4 doporučené produkty pro "Žhavé novinky"
$featuredProducts = $productRepo->getFeatured(limit: 4);

// 4. Vložíme začátek stránky a hlavičku
require __DIR__ . '/partials/header.php';
?>

<main>
    <section style="background: url('https://placehold.co/1920x600/111/333?text=DOWNHILL+ADDICTION') center/cover; padding-top: 400px; padding-bottom: 50px; text-align: center;">
        <div class="container">
            <h1 style="font-size: 3.5rem; text-shadow: 2px 2px 4px #000;">Překonej své limity</h1>
            <p style="font-size: 1.2rem; margin-bottom: 20px;"><br>Nejlepší vybavení pro sjezd a enduro na trhu.</p>
            <a href="produkty.php" class="btn" style="padding: 15px 40px; font-size: 1.2rem;">Nakupovat</a>
        </div>
    </section>

    <section class="container" style="padding-top: 50px;">
        <h2>Žhavé novinky</h2>
        <div class="product-grid">
            <?php foreach ($featuredProducts as $product): ?>
                <?php require __DIR__ . '/partials/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<?php 
// 5. Vložíme patičku a uzavřeme HTML strukturu
require __DIR__ . '/partials/footer.php'; 
?>
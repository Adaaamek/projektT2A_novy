<?php
declare(strict_types=1);
// 1. Načteme jádro webu, databázi a košík
require_once __DIR__ . '/src/bootstrap.php';

$productRepo = new ProductRepository();
$cart = new Cart();

$cartItemCount = $cart->getTotalQuantity();

// 2. Zkontrolujeme, zda v adrese posíláme vybranou kategorii (např. ?category=brzdy)
$categorySlug = $_GET['category'] ?? null;

if ($categorySlug) {
    // Pokud je kategorie vybraná, načteme pouze produkty z této kategorie
    $products = $productRepo->getByCategorySlug($categorySlug);
    $pageTitle = 'Kategorie | GRAVITY SHOP';
    $headline = 'Filtrovaná nabídka';
} else {
    // Pokud klikneme v menu na "Všechny produkty", načteme jich z DB rovnou 30 (což je maximum v DB)
    $products = $productRepo->getFeatured(limit: 30);
    $pageTitle = 'Všechny produkty | GRAVITY SHOP';
    $headline = 'Naše nabídka pro downhill';
}

// 3. Načteme hlavičku
require __DIR__ . '/partials/header.php';
?>

<main class="container">
    <div class="breadcrumbs">Domů / Kategorie / Všechny produkty</div>
    <h1><?= htmlspecialchars($headline) ?></h1>
    
    <div class="product-grid">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $product): ?>
                <?php require __DIR__ . '/partials/product-card.php'; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <p>V této kategorii se momentálně nenachází žádné zboží.</p>
        <?php endif; ?>
    </div>
</main>

<?php 
// 4. Načteme patičku
require __DIR__ . '/partials/footer.php'; 
?>
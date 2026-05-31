<?php
declare(strict_types=1);
// 1. Načteme jádro webu a databázi
require_once __DIR__ . '/src/bootstrap.php';

$categoryRepo = new CategoryRepository();
$cart = new Cart();

// 2. Připravíme proměnné pro hlavičku
$pageTitle = 'Kategorie | GRAVITY SHOP';
$cartItemCount = $cart->getTotalQuantity();

// 3. Vytáhneme všechny kategorie z databáze
$categories = $categoryRepo->getAll();

// 4. Načteme hlavičku
require __DIR__ . '/partials/header.php';
?>

<main class="container">
    <div class="breadcrumbs">Domů / Kategorie</div>
    <h1>Kategorie produktů</h1>
    
    <div class="category-grid">
        <?php foreach ($categories as $category): ?>
            <a href="produkty.php?category=<?= htmlspecialchars($category->slug) ?>" 
               class="product-card" 
               style="display:flex; align-items:center; justify-content:center; height:200px; font-size:1.5rem; font-weight:bold; color: black; text-transform: uppercase; background: url('<?= htmlspecialchars($category->image) ?>') center/cover;">
                <?= htmlspecialchars($category->name) ?>
            </a>
        <?php endforeach; ?>
    </div>
</main>

<?php 
// 5. Načteme patičku
require __DIR__ . '/partials/footer.php'; 
?>
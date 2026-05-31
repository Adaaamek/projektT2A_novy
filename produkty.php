<?php
declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

$categoryRepo = new CategoryRepository();
$productRepo = new ProductRepository();

// Zjistíme, jestli filtrujeme podle kategorie (přes URL parametr ?category=...)
$categorySlug = $_GET['category'] ?? null;
$category = null;

if ($categorySlug !== null) {
    $category = $categoryRepo->getBySlug($categorySlug);
    // Pokud kategorie v parametru neexistuje, přesměrujeme na 404.php
    if ($category === null) {
        header('Location: 404.php');
        exit;
    }
    // Načteme produkty pouze pro danou kategorii
    $products = $productRepo->getByCategory($category->id);
    $pageTitle = $category->name;
} else {
    // Jinak načteme úplně všechny produkty
    $products = $productRepo->getAll();
    $pageTitle = 'Všechny produkty';
}

require_once __DIR__ . '/partials/header.php';
?>

<main class="container">
    <div class="breadcrumbs">
        Domů / 
        <?php if ($category): ?>
            <a href="kategorie.php">Kategorie</a> / <?= htmlspecialchars($category->name) ?>
        <?php else: ?>
            Všechny produkty
        <?php endif; ?>
    </div>
    
    <h1><?= htmlspecialchars($pageTitle) ?></h1>
    
    <?php if ($category): ?>
        <p style="color: var(--text-muted); margin-bottom: 20px;"><?= htmlspecialchars($category->description) ?></p>
    <?php endif; ?>

    <?php if (empty($products)): ?>
        <p style="padding: 40px 0; text-align: center; color: var(--text-muted);">V této kategorii zatím nejsou žádné produkty.</p>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <?php require __DIR__ . '/partials/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php
require_once __DIR__ . '/partials/footer.php';
?>
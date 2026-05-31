<?php
declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

$productRepo = new ProductRepository();

// Získáme hledaný dotaz z URL parametru ?q=...
$query = trim($_GET['q'] ?? '');

$products = [];
if ($query !== '') {
    // Použijeme repozitář pro bezpečné vyhledání v databázi (včetně ochrany proti SQL injection)
    $products = $productRepo->search($query);
}

$pageTitle = 'Výsledky vyhledávání pro: "' . $query . '"';
require_once __DIR__ . '/partials/header.php';
?>

<main class="container">
    <div class="breadcrumbs">Domů / Vyhledávání</div>
    
    <h1>Výsledky vyhledávání</h1>
    <p style="margin-bottom: 30px; color: var(--text-muted);">
        Pro hledaný výraz: <strong><?= htmlspecialchars($query) ?></strong>
    </p>

    <?php if ($query === ''): ?>
        <p style="padding: 40px 0; text-align: center; color: var(--text-muted);">Zadejte prosím hledaný výraz do vyhledávacího pole.</p>
    <?php elseif (empty($products)): ?>
        <p style="padding: 40px 0; text-align: center; color: var(--text-muted);">Nebyly nalezeny žádné produkty odpovídající vašemu dotazu.</p>
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
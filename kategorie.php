<?php
declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

$categoryRepo = new CategoryRepository();
$categories = $categoryRepo->getAll();

$pageTitle = 'Kategorie produktů';
require_once __DIR__ . '/partials/header.php';
?>

<main class="container">
    <div class="breadcrumbs">Domů / Kategorie</div>
    <h1>Kategorie produktů</h1>
    
    <div class="category-grid">
        <?php foreach ($categories as $cat): ?>
            <a href="produkty.php?category=<?= htmlspecialchars($cat->slug) ?>" class="product-card" style="display:flex; flex-direction: column; align-items:center; justify-content:center; height:220px; font-size:1.5rem; font-weight:bold; color: white; text-shadow: 2px 2px 4px #000; background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('<?= htmlspecialchars($cat->image) ?>') center/cover; padding: 20px; text-align: center;">
                <span><?= htmlspecialchars(mb_strtoupper($cat->name)) ?></span>
                <span style="font-size: 0.9rem; font-weight: normal; margin-top: 10px; text-shadow: 1px 1px 2px #000;"><?= htmlspecialchars($cat->description) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</main>

<?php
require_once __DIR__ . '/partials/footer.php';
?>
<?php
declare(strict_types=1);

// 1. Načteme bootstrap, který automaticky načte všechny DTO, repozitáře a databázi
require_once __DIR__ . '/src/bootstrap.php';

// 2. Vytvoříme si repozitáře pro vytahování dat
$categoryRepo = new CategoryRepository();
$productRepo = new ProductRepository();

// 3. Vytáhneme data z databáze
$categories = $categoryRepo->getAll();
$featuredProducts = $productRepo->getFeatured(4); // Načteme 4 doporučené produkty na homepage

// Nastavíme titulek stránky pro hlavičku
$pageTitle = 'Překonej své limity';

// 4. Vložíme společnou hlavičku webu
require_once __DIR__ . '/partials/header.php';
?>

<main>
    <!-- Hero banner -->
    <section style="background: url('https://placehold.co/1920x600/111/333?text=DOWNHILL+ADDICTION') center/cover; padding-top: 400px; padding-bottom: 50px; text-align: center;">
        <div class="container">
            <h1 style="font-size: 3.5rem; text-shadow: 2px 2px 4px #000;">Překonej své limity</h1>
            <p style="font-size: 1.2rem; margin-bottom: 20px;"><br>Nejlepší vybavení pro sjezd a enduro na trhu.</p>
            <a href="produkty.php" class="btn" style="padding: 15px 40px; font-size: 1.2rem;">Nakupovat</a>
        </div>
    </section>

    <!-- Přehled kategorií -->
    <section class="container" style="padding-top: 50px;">
        <h2>Kategorie produktů</h2>
        <div class="category-grid">
            <?php foreach (array_slice($categories, 0, 3) as $cat): ?>
                <a href="produkty.php?category=<?= htmlspecialchars($cat->slug) ?>" class="product-card" style="display:flex; align-items:center; justify-content:center; height:200px; font-size:1.5rem; font-weight:bold; color: white; text-shadow: 2px 2px 4px #000; background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('<?= htmlspecialchars($cat->image) ?>') center/cover;">
                    <?= htmlspecialchars(mb_strtoupper($cat->name)) ?>
                </a>
            <?php endforeach; ?>
        </div>
        <div style="text-align: center; margin-top: 20px;">
            <a href="kategorie.php" class="btn btn-outline">Zobrazit všechny kategorie</a>
        </div>
    </section>

    <!-- Žhavé novinky (doporučené produkty) -->
    <section class="container" style="padding-top: 50px;">
        <h2>Žhavé novinky</h2>
        <div class="product-grid">
            <?php foreach ($featuredProducts as $product): ?>
                <?php 
                // Vložíme šablonu produktové karty a předáme jí aktuální produkt
                require __DIR__ . '/partials/product-card.php'; 
                ?>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<?php
// 5. Vložíme společnou patičku webu
require_once __DIR__ . '/partials/footer.php';
?>
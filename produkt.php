<?php
declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

$productRepo = new ProductRepository();
$categoryRepo = new CategoryRepository();

// Načteme slug produktu z URL (např. produkt.php?slug=santa-cruz-v10)
$slug = $_GET['slug'] ?? '';
$product = $productRepo->getBySlug($slug);

// Pokud produkt neexistuje, přesměrujeme na 404.php
if ($product === null) {
    header('Location: 404.php');
    exit;
}

// Načteme další obrázky do galerie a parametry
$galleryImages = $productRepo->getImages($product->id);
$parameters = $productRepo->getParameters($product->id);

$pageTitle = $product->name;
require_once __DIR__ . '/partials/header.php';
?>

<main class="container">
    <div class="breadcrumbs">
        <a href="index.php">Domů</a> / 
        <a href="produkty.php">Produkty</a> / 
        <?php if ($product->categorySlug): ?>
            <a href="produkty.php?category=<?= htmlspecialchars($product->categorySlug) ?>"><?= htmlspecialchars($product->categoryName) ?></a> / 
        <?php endif; ?>
        <?= htmlspecialchars($product->name) ?>
    </div>
    
    <div class="cart-layout" style="margin-top:20px;">
        <!-- Galerie obrázků -->
        <div class="gallery">
            <img id="main-gallery-image" src="<?= htmlspecialchars($product->image) ?>" alt="<?= htmlspecialchars($product->name) ?>" style="width:100%; max-height: 500px; object-fit: contain; border:1px solid #333; margin-bottom:10px; background: #fff; padding: 10px;">
            
            <?php if (!empty($galleryImages)): ?>
                <div style="display:flex; gap:10px;">
                    <!-- První je vždy hlavní obrázek -->
                    <img src="<?= htmlspecialchars($product->image) ?>" style="width:80px; height:60px; object-fit: contain; cursor:pointer; border: 1px solid #555; background: #fff; padding: 2px;" onclick="document.getElementById('main-gallery-image').src=this.src">
                    <?php foreach ($galleryImages as $img): ?>
                        <img src="<?= htmlspecialchars($img->image) ?>" style="width:80px; height:60px; object-fit: contain; cursor:pointer; border: 1px solid #333; background: #fff; padding: 2px;" onclick="document.getElementById('main-gallery-image').src=this.src">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Informace a formulář pro vložení do košíku -->
        <div class="product-summary">
            <h1><?= htmlspecialchars($product->name) ?></h1>
            
            <?php if ($product->hasDiscount()): ?>
                <p style="text-decoration: line-through; color: var(--text-muted); margin-bottom: 0;">
                    <?= number_format($product->originalPrice, 0, ',', ' ') ?> Kč
                </p>
                <p class="price" style="font-size:2rem; margin-top:0; margin-bottom:20px;">
                    <?= number_format($product->price, 0, ',', ' ') ?> Kč
                    <span style="font-size: 1rem; background: var(--primary); color: #fff; padding: 4px 8px; border-radius: var(--radius); vertical-align: middle; margin-left: 10px;">
                        Sleva -<?= $product->getDiscountPercent() ?>%
                    </span>
                </p>
            <?php else: ?>
                <p class="price" style="font-size:2rem; margin-bottom:20px;"><?= number_format($product->price, 0, ',', ' ') ?> Kč</p>
            <?php endif; ?>
            
            <p style="margin-bottom: 25px; line-height: 1.7;"><?= htmlspecialchars($product->description) ?></p>
            
            <!-- Formulář pro přidání do košíku (odesílá se na akci v kosik.php) -->
            <form action="kosik.php" method="POST">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?= $product->id ?>">
                
                <?php 
                // Rozdělíme parametry na volitelné (select) a informační (info)
                $selectParams = array_filter($parameters, fn($p) => $p->isSelectable());
                $infoParams = array_filter($parameters, fn($p) => !$p->isSelectable());
                ?>

                <!-- Volby variant (velikosti, barvy apod.) -->
                <?php if (!empty($selectParams)): ?>
                    <div style="margin: 20px 0; background: #1e1e1e; padding: 15px; border-radius: var(--radius);">
                        <?php foreach ($selectParams as $param): ?>
                            <div style="margin-bottom: 15px;">
                                <label style="display: block; margin-bottom: 5px; font-weight: bold;"><?= htmlspecialchars($param->name) ?>:</label>
                                <select name="variant[<?= htmlspecialchars($param->name) ?>]" required style="margin-bottom: 0;">
                                    <?php foreach ($param->getOptions() as $option): ?>
                                        <option value="<?= htmlspecialchars($option) ?>"><?= htmlspecialchars($option) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div style="display: flex; gap: 15px; align-items: center; margin-bottom: 25px;">
                    <div style="width: 100px;">
                        <label style="display: block; margin-bottom: 5px; font-size: 0.8rem;">Množství:</label>
                        <input type="number" name="quantity" value="1" min="1" max="100" style="margin-bottom: 0; text-align: center;">
                    </div>
                    <button type="submit" class="btn" style="flex: 1; padding: 15px; font-size: 1.1rem; height: auto; margin-top: 20px;">Vložit do košíku</button>
                </div>
            </form>
            
            <ul style="margin-top:20px; font-size:0.9rem; color:#aaa; list-style: none; padding-left: 0; display: grid; gap: 8px;">
                <li>✅ Skladem v e-shopu (odesíláme ihned)</li>
                <li>✅ Možnost vrácení zboží do 14 dnů</li>
                <?php if ($product->price >= 2000): ?>
                    <li>✅ Doprava ZDARMA pro tento produkt</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <!-- Tabulka informačních parametrů -->
    <?php if (!empty($infoParams)): ?>
        <div style="margin-top: 50px; background: #1e1e1e; padding: 30px; border: 1px solid #333;">
            <h3 style="margin-bottom: 20px;">Technické parametry</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <?php foreach ($infoParams as $param): ?>
                    <tr style="border-bottom: 1px solid #333;">
                        <td style="padding: 12px; font-weight: bold; width: 30%; color: var(--text-muted);"><?= htmlspecialchars($param->name) ?></td>
                        <td style="padding: 12px; color: #fff;"><?= htmlspecialchars($param->value) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    <?php endif; ?>
</main>

<?php
require_once __DIR__ . '/partials/footer.php';
?>
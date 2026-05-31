<?php
declare(strict_types=1);

/** @var ProductDTO $product */
if (!isset($product)) {
    return;
}
?>
<article class="product-card">
    <img src="<?= htmlspecialchars($product->image) ?>" alt="<?= htmlspecialchars($product->name) ?>" class="card-img" style="object-fit: contain; padding: 10px; background: #ffffff;">
    <div class="card-info">
        <h3><?= htmlspecialchars($product->name) ?></h3>
        
        <?php if ($product->hasDiscount()): ?>
            <span style="text-decoration: line-through; color: var(--text-muted); font-size: 0.9rem;">
                <?= number_format($product->originalPrice, 0, ',', ' ') ?> Kč
            </span>
            <span class="price" style="margin-top: 0;">
                <?= number_format($product->price, 0, ',', ' ') ?> Kč 
                <span style="font-size: 0.8rem; background: var(--primary); color: #fff; padding: 2px 6px; border-radius: var(--radius); margin-left: 5px;">
                    -<?= $product->getDiscountPercent() ?>%
                </span>
            </span>
        <?php else: ?>
            <span class="price"><?= number_format($product->price, 0, ',', ' ') ?> Kč</span>
        <?php endif; ?>
        
        <a href="produkt.php?slug=<?= htmlspecialchars($product->slug) ?>" class="btn">Detail</a>
    </div>
</article>
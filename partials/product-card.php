<article class="product-card">
    <img src="<?= htmlspecialchars($product->image) ?>" alt="<?= htmlspecialchars($product->name) ?>" class="card-img">
    <div class="card-info">
        <h3><?= htmlspecialchars($product->name) ?></h3>
        <span class="price"><?= number_format($product->price, 0, ',', ' ') ?> Kč</span>
        <a href="produkt-detail.php?slug=<?= htmlspecialchars($product->slug) ?>" class="btn">Detail</a>
    </div>
</article>
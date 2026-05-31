<?php
declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

$cart = new Cart();
$productRepo = new ProductRepository();

// Zpracování akcí košíku (přidat, odebrat, upravit množství)
$action = $_POST['action'] ?? $_GET['action'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $action !== null) {
    if ($action === 'add') {
        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);
        
        // Sesbíráme vybrané varianty do jednoho řetězce (např. "Velikost rámu: M, Barva: White")
        $selectedVariants = [];
        if (isset($_POST['variant']) && is_array($_POST['variant'])) {
            foreach ($_POST['variant'] as $paramName => $paramValue) {
                $selectedVariants[] = "$paramName: $paramValue";
            }
        }
        $variantString = implode(', ', $selectedVariants);

        $product = $productRepo->getById($productId);
        if ($product !== null) {
            $cart->add(
                $product->id,
                $product->name,
                $product->price,
                $product->image,
                $quantity,
                $variantString
            );
        }
        // Přesměrujeme na košík (Post/Redirect/Get), aby se při refreshu nepřidalo zboží znovu
        header('Location: kosik.php');
        exit;
    }

    if ($action === 'update') {
        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);
        $variant = $_POST['variant'] ?? '';
        
        $cart->updateQuantity($productId, $quantity, $variant);
        header('Location: kosik.php');
        exit;
    }

    if ($action === 'remove') {
        $productId = (int)($_GET['product_id'] ?? 0);
        $variant = $_GET['variant'] ?? '';
        
        $cart->remove($productId, $variant);
        header('Location: kosik.php');
        exit;
    }
}

$cartItems = $cart->getItems();
$totalPrice = $cart->getTotalPrice();

$pageTitle = 'Nákupní košík';
require_once __DIR__ . '/partials/header.php';
?>

<main class="container">
    <div class="breadcrumbs">Domů / Košík</div>
    <h1>Nákupní košík</h1>

    <?php if ($cart->isEmpty()): ?>
        <div style="text-align:center; padding: 50px 0;">
            <p style="font-size:1.2rem; color: var(--text-muted); margin-bottom: 20px;">Váš košík je momentálně prázdný.</p>
            <a href="produkty.php" class="btn">Pokračovat v nákupu</a>
        </div>
    <?php else: ?>
        <div class="cart-layout">
            <div>
                <table style="width:100%; text-align:left; border-collapse: collapse; margin-bottom: 20px;">
                    <thead>
                        <tr style="border-bottom: 2px solid #333; color: var(--text-muted);">
                            <th style="padding:15px 10px;">Produkt</th>
                            <th style="padding:15px 10px;">Varianta</th>
                            <th style="padding:15px 10px; text-align: center; width: 120px;">Množství</th>
                            <th style="padding:15px 10px; text-align: right;">Cena</th>
                            <th style="padding:15px 10px; text-align: center; width: 80px;">Akce</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                            <tr style="border-bottom: 1px solid #222;">
                                <td style="padding:15px 10px; display: flex; align-items: center; gap: 15px;">
                                    <img src="<?= htmlspecialchars($item->image) ?>" alt="" style="width: 50px; height: 50px; object-fit: contain; background: #fff; padding: 2px;">
                                    <span style="font-weight: bold;"><?= htmlspecialchars($item->productName) ?></span>
                                </td>
                                <td style="padding:15px 10px; font-size: 0.9rem; color: var(--text-muted);">
                                    <?= $item->variant !== '' ? htmlspecialchars($item->variant) : 'Standardní' ?>
                                </td>
                                <td style="padding:15px 10px; text-align: center;">
                                    <!-- Formulář pro změnu množství -->
                                    <form action="kosik.php" method="POST" style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="product_id" value="<?= $item->productId ?>">
                                        <input type="hidden" name="variant" value="<?= htmlspecialchars($item->variant) ?>">
                                        <input type="number" name="quantity" value="<?= $item->quantity ?>" min="1" max="99" style="width: 60px; text-align: center; margin-bottom: 0; padding: 5px;" onchange="this.form.submit()">
                                    </form>
                                </td>
                                <td style="padding:15px 10px; text-align: right; font-weight: bold; color: var(--primary);">
                                    <?= number_format($item->getTotalPrice(), 0, ',', ' ') ?> Kč
                                </td>
                                <td style="padding:15px 10px; text-align: center;">
                                    <a href="kosik.php?action=remove&product_id=<?= $item->productId ?>&variant=<?= urlencode($item->variant) ?>" style="color: var(--primary); font-size: 1.2rem; font-weight: bold;" title="Odebrat">❌</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div style="display: flex; gap: 15px;">
                    <a href="produkty.php" class="btn btn-outline">Zpět k nákupu</a>
                </div>
            </div>

            <!-- Shrnutí košíku v pravém sloupci -->
            <div style="background:#1e1e1e; padding:20px; border:1px solid #333; height:fit-content; border-radius: var(--radius);">
                <h3 style="margin-bottom: 20px; border-bottom: 1px solid #333; padding-bottom: 10px;">Shrnutí košíku</h3>
                <div style="display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 1.1rem;">
                    <span>Mezisoučet za zboží:</span>
                    <strong><?= number_format($totalPrice, 0, ',', ' ') ?> Kč</strong>
                </div>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 25px;">Doprava a platba budou spočítány v dalším kroku.</p>
                <a href="objednavka-1.php" class="btn" style="width: 100%; text-align: center; display: block; padding: 15px 0;">Pokračovat k objednávce</a>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php
require_once __DIR__ . '/partials/footer.php';
?>
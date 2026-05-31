<?php
declare(strict_types=1);
// 1. Načteme jádro webu a košík
require_once __DIR__ . '/src/bootstrap.php';

$productRepo = new ProductRepository();
$cart = new Cart();

// Bezpečnostní pojistka: Pokud sem někdo vleze přímo a neodeslal formulář, hodíme ho na e-shop
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['place_order'])) {
    header('Location: produkty.php');
    exit;
}

// 2. Vytáhneme si data, dokud je máme v session, abychom je mohli naposledy ukázat
$customer = $_SESSION['checkout_customer'] ?? null;
$shippingMethod = $_SESSION['checkout_shipping'] ?? 'ppl';

// Pokud chybí zákaznická data, něco je špatně – směr začátek košíku
if (!$customer) {
    header('Location: kosik-krok1.php');
    exit;
}

// 3. Vygenerujeme náhodné číslo objednávky pro efekt (např. 20260001)
$orderNumber = '2026' . str_pad((string)rand(1, 9999), 4, '0', STR_PAD_LEFT);

// 4. VYČIŠTĚNÍ KOŠÍKU A CHECKOUT SESSION
// Zboží je koupeno, takže košík musí zůstat prázdný.
if (method_exists($cart, 'clear')) {
    $cart->clear();
} else {
    // Pokud tvá třída Cart nemá metodu clear(), vymažeme session košíku ručně
    $_SESSION['cart'] = []; 
}

// Smažeme uložené adresní údaje z checkoutu, ať je stůl čistý
unset($_SESSION['checkout_customer'], $_SESSION['checkout_shipping'], $_SESSION['checkout_payment']);

// 5. Nastavení proměnných pro hlavičku (košík už bude mít 0 kusů)
$pageTitle = 'Objednávka dokončena | GRAVITY SHOP';
$cartItemCount = 0;

require __DIR__ . '/partials/header.php';
?>

<main class="container" style="text-align: center; padding: 60px 20px;">
    <div style="background: #1e1e1e; max-width: 600px; margin: 0 auto; padding: 40px; border: 1px solid #333; border-radius: 4px;">
        <span style="font-size: 4rem;">🎉</span>
        <h1 style="color: #e62117; margin-top: 20px;">Děkujeme za objednávku!</h1>
        
        <p style="font-size: 1.2rem; margin: 20px 0;">
            Vaše objednávka číslo <strong>#<?= htmlspecialchars($orderNumber) ?></strong> byla úspěšně přijata ke zpracování.
        </p>
        
        <p style="color: #aaa; line-height: 1.6;">
            Potvrzení objednávky a podklady k platbě jsme právě odeslali na e-mailovou adresu <strong style="color: #fff;"><?= htmlspecialchars($customer['email']) ?></strong>.
        </p>

        <hr style="border: 0; border-top: 1px solid #333; margin: 30px 0;">

        <p style="font-size: 0.9rem; color: #888; margin-bottom: 30px;">
            <?php if ($shippingMethod === 'ppl'): ?>
                Kurýr PPL vás bude kontaktovat formou SMS, jakmile zásilku převezme do přepravy.
            <?php else: ?>
                Jakmile pro vás zboží na prodejně připravíme k odběru, pošleme vám výzvu.
            <?php endif; ?>
        </p>

        <a href="index.php" class="btn" style="display: inline-block; padding: 12px 30px; text-decoration: none;">
            Zpět na hlavní stránku
        </a>
    </div>
</main>

<?php 
// 6. Načteme patičku
require __DIR__ . '/partials/footer.php'; 
?>
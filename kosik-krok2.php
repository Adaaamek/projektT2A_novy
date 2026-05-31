<?php
declare(strict_types=1);
// 1. Načteme jádro webu, databázi a košík (spouští i session)
require_once __DIR__ . '/src/bootstrap.php';

$productRepo = new ProductRepository();
$cart = new Cart();

// Pokud je košík úplně prázdný, nemá smysl tu být – hodíme uživatele na hlavní stránku
if ($cart->isEmpty()) {
    header('Location: index.php');
    exit;
}

// 2. Uložení adresních údajů z 1. kroku do Session
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['checkout_customer'] = [
        'first_name' => $_POST['first_name'] ?? '',
        'last_name'  => $_POST['last_name'] ?? '',
        'email'      => $_POST['email'] ?? '',
        'phone'      => $_POST['phone'] ?? '',
        'address'    => $_POST['address'] ?? '',
        'zip'        => $_POST['zip'] ?? '',
        'city'       => $_POST['city'] ?? '',
        'note'       => $_POST['note'] ?? '',
    ];
}

// Pojistka: Pokud někdo skočí přímo na krok 2 a nemá vyplněnou adresu, vrátíme ho na krok 1
if (!isset($_SESSION['checkout_customer'])) {
    header('Location: kosik-krok1.php');
    exit;
}

// 3. Příprava dat pro dynamický pravý sloupec se shrnutím
$cartItems = $cart->getItems();
$productsInCart = [];
$totalPrice = 0;

foreach ($cartItems as $productId => $quantity) {
    $product = $productRepo->getById((int)$productId);
    if ($product) {
        $subtotal = $product->price * $quantity;
        $totalPrice += $subtotal;
        $productsInCart[] = [
            'product' => $product,
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ];
    }
}

// 4. Nastavení proměnných pro hlavičku
$pageTitle = 'Košík (2/3) - Doprava a platba | GRAVITY SHOP';
$cartItemCount = $cart->getTotalQuantity();

require __DIR__ . '/partials/header.php';
?>

<main class="container">
    <h1 style="margin-top:20px;">Košík (2/3): Doprava a platba</h1>
    <div class="cart-layout">
        
        <form action="kosik-krok3.php" method="POST">
            
            <h3>Doprava</h3>
            <label style="display:block; padding:15px; background:#1e1e1e; margin-bottom:10px; border:1px solid #333; cursor:pointer;">
                <input type="radio" name="shipping" value="ppl" checked> PPL Kurýr (+90 Kč)
            </label>
            <label style="display:block; padding:15px; background:#1e1e1e; margin-bottom:10px; border:1px solid #333; cursor:pointer;">
                <input type="radio" name="shipping" value="personal"> Osobní odběr (ZDARMA)
            </label>

            <h3 style="margin-top:30px;">Platba</h3>
            <label style="display:block; padding:15px; background:#1e1e1e; margin-bottom:10px; border:1px solid #333; cursor:pointer;">
                <input type="radio" name="payment" value="card" checked> Kartou online
            </label>
            <label style="display:block; padding:15px; background:#1e1e1e; margin-bottom:10px; border:1px solid #333; cursor:pointer;">
                <input type="radio" name="payment" value="cod"> Dobírka (+49 Kč)
            </label>

            <div style="margin-top:20px; display:flex; gap:10px;">
                <a href="kosik-krok1.php" class="btn btn-outline" style="text-decoration: none; text-align: center; line-height: 1.2;">Zpět</a>
                <button type="submit" class="btn">Pokračovat na shrnutí ></button>
            </div>
        </form>
        
        <div class="order-summary" style="background:#1e1e1e; padding:20px; border:1px solid #333; height:fit-content;">
            <h3>V košíku:</h3>
            <?php foreach ($productsInCart as $item): ?>
                <div style="border-bottom:1px solid #333; padding-bottom:5px; margin-bottom:5px;">
                    <p>
                        <?= (int)$item['quantity'] ?>x <?= htmlspecialchars($item['product']->name) ?> 
                        <span style="float:right;"><?= number_format($item['subtotal'], 0, ',', ' ') ?> Kč</span>
                    </p>
                </div>
            <?php endforeach; ?>
            
            <p style="font-size:1.2rem; font-weight:bold; margin-top:10px;">
                Celkem za zboží: <?= number_format($totalPrice, 0, ',', ' ') ?> Kč
            </p>
            <small style="color: #888;">* Cena dopravy a platby se přičte v dalším kroku.</small>
        </div>
        
    </div>
</main>

<?php 
// 5. Načteme patičku
require __DIR__ . '/partials/footer.php'; 
?>
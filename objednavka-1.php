<?php
declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

$cart = new Cart();
if ($cart->isEmpty()) {
    header('Location: kosik.php');
    exit;
}

// Inicializace polí ze session, abychom předvyplnili to, co už uživatel jednou zadal
$formData = $_SESSION['order_step_1'] ?? [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone' => '',
    'street' => '',
    'city' => '',
    'zip' => '',
    'note' => '',
];

$errors = [];
$v = new Validator();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Načteme a ořežeme hodnoty z formuláře
    $formData = [
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name' => trim($_POST['last_name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'street' => trim($_POST['street'] ?? ''),
        'city' => trim($_POST['city'] ?? ''),
        'zip' => trim($_POST['zip'] ?? ''),
        'note' => trim($_POST['note'] ?? ''),
    ];

    // Použijeme náš Validator na povinná pole
    $v->required('first_name', $formData['first_name'], 'Jméno je povinné.')
      ->required('last_name', $formData['last_name'], 'Příjmení je povinné.')
      ->required('email', $formData['email'], 'E-mail je povinný.')
      ->email('email', $formData['email'], 'Neplatný formát e-mailu.')
      ->required('street', $formData['street'], 'Ulice a číslo popisné jsou povinné.')
      ->required('city', $formData['city'], 'Město je povinné.')
      ->required('zip', $formData['zip'], 'PSČ je povinné.')
      // Regulární výraz kontroluje 5 číslic (může obsahovat i nepovinnou mezeru)
      ->pattern('zip', $formData['zip'], '/^\d{3}\s?\d{2}$/', 'PSČ musí obsahovat přesně 5 číslic.');

    if ($v->isValid()) {
        // Uložíme validní data do session a jdeme na krok 2
        $_SESSION['order_step_1'] = $formData;
        header('Location: objednavka-2.php');
        exit;
    } else {
        $errors = $v->getErrors();
    }
}

$pageTitle = 'Objednávka (1/3) - Dodací údaje';
require_once __DIR__ . '/partials/header.php';
?>

<main class="container">
    <h1 style="margin-top:20px;">Košík (1/3): Dodací údaje</h1>
    
    <div class="cart-layout">
        <!-- Hlavní formulář dodacích údajů -->
        <form action="objednavka-1.php" method="POST">
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-bottom: 15px;">
                <div>
                    <input type="text" name="first_name" placeholder="Jméno" value="<?= htmlspecialchars($formData['first_name']) ?>" style="margin-bottom: 5px;">
                    <?php if (isset($errors['first_name'])): ?>
                        <span style="color: var(--primary); font-size: 0.85rem; display: block;"><?= htmlspecialchars($errors['first_name']) ?></span>
                    <?php endif; ?>
                </div>
                <div>
                    <input type="text" name="last_name" placeholder="Příjmení" value="<?= htmlspecialchars($formData['last_name']) ?>" style="margin-bottom: 5px;">
                    <?php if (isset($errors['last_name'])): ?>
                        <span style="color: var(--primary); font-size: 0.85rem; display: block;"><?= htmlspecialchars($errors['last_name']) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div style="margin-bottom: 15px;">
                <input type="email" name="email" placeholder="E-mail" value="<?= htmlspecialchars($formData['email']) ?>" style="margin-bottom: 5px;">
                <?php if (isset($errors['email'])): ?>
                    <span style="color: var(--primary); font-size: 0.85rem; display: block;"><?= htmlspecialchars($errors['email']) ?></span>
                <?php endif; ?>
            </div>

            <div style="margin-bottom: 15px;">
                <input type="tel" name="phone" placeholder="Telefon (+420)" value="<?= htmlspecialchars($formData['phone']) ?>" style="margin-bottom: 5px;">
            </div>

            <div style="margin-bottom: 15px;">
                <input type="text" name="street" placeholder="Ulice a číslo popisné" value="<?= htmlspecialchars($formData['street']) ?>" style="margin-bottom: 5px;">
                <?php if (isset($errors['street'])): ?>
                    <span style="color: var(--primary); font-size: 0.85rem; display: block;"><?= htmlspecialchars($errors['street']) ?></span>
                <?php endif; ?>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 2fr; gap:15px; margin-bottom: 15px;">
                <div>
                    <input type="text" name="zip" placeholder="PSČ" value="<?= htmlspecialchars($formData['zip']) ?>" style="margin-bottom: 5px;">
                    <?php if (isset($errors['zip'])): ?>
                        <span style="color: var(--primary); font-size: 0.85rem; display: block;"><?= htmlspecialchars($errors['zip']) ?></span>
                    <?php endif; ?>
                </div>
                <div>
                    <input type="text" name="city" placeholder="Město" value="<?= htmlspecialchars($formData['city']) ?>" style="margin-bottom: 5px;">
                    <?php if (isset($errors['city'])): ?>
                        <span style="color: var(--primary); font-size: 0.85rem; display: block;"><?= htmlspecialchars($errors['city']) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <textarea name="note" placeholder="Poznámka pro kurýra..." style="margin-bottom: 20px;"><?= htmlspecialchars($formData['note']) ?></textarea>
            
            <button type="submit" class="btn">Pokračovat na dopravu ></button>
        </form>

        <!-- Pravý sloupec: Malé shrnutí -->
        <div class="order-summary" style="background:#1e1e1e; padding:20px; border:1px solid #333; height:fit-content; border-radius: var(--radius);">
            <h3>V košíku:</h3>
            <div style="border-bottom:1px solid #333; padding-bottom:10px; margin-bottom:10px; max-height: 250px; overflow-y: auto;">
                <?php foreach ($cart->getItems() as $item): ?>
                    <p style="margin-bottom: 8px; font-size: 0.95rem;">
                        <?= $item->quantity ?>x <?= htmlspecialchars($item->productName) ?>
                        <span style="float:right; font-weight: bold;"><?= number_format($item->getTotalPrice(), 0, ',', ' ') ?> Kč</span>
                    </p>
                <?php endforeach; ?>
            </div>
            <p style="font-size:1.2rem; font-weight:bold; margin-top: 15px;">Celkem za zboží: <span style="float: right; color: var(--primary);"><?= number_format($cart->getTotalPrice(), 0, ',', ' ') ?> Kč</span></p>
        </div>
    </div>
</main>

<?php
require_once __DIR__ . '/partials/footer.php';
?>
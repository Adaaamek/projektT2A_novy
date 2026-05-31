<?php
declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

// Inicializace proměnných pro zachování hodnot po neúspěšném odeslání
$name = '';
$email = '';
$messageText = '';
$successMessage = null;
$errors = [];

$v = new Validator();

// Zkontrolujeme, zda byl odeslán formulář (metodou POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $messageText = trim($_POST['message'] ?? '');

    // Validace dat pomocí našeho Validatoru
    $v->required('name', $name, 'Jméno je povinné.')
      ->minLength('name', $name, 2, 'Jméno musí mít alespoň 2 znaky.')
      ->required('email', $email, 'E-mail je povinný.')
      ->email('email', $email, 'Zadejte platný formát e-mailu.')
      ->required('message', $messageText, 'Zpráva nesmí být prázdná.')
      ->minLength('message', $messageText, 10, 'Zpráva musí mít alespoň 10 znaků.');

    if ($v->isValid()) {
        // Formulář je v pořádku - simulujeme odeslání
        $successMessage = 'Zpráva byla úspěšně odeslána! Ozveme se vám co nejdříve.';
        // Vyčistíme pole formuláře
        $name = '';
        $email = '';
        $messageText = '';
    } else {
        $errors = $v->getErrors();
    }
}

$pageTitle = 'Kontakt';
require_once __DIR__ . '/partials/header.php';
?>

<main class="container">
    <div class="breadcrumbs">Domů / Kontakt</div>
    <h1>Kontaktujte nás</h1>
    
    <?php if ($successMessage !== null): ?>
        <div style="background: #4BB543; color: white; padding: 15px; margin-bottom: 20px; border-radius: var(--radius); font-weight: bold;">
            <?= htmlspecialchars($successMessage) ?>
        </div>
    <?php endif; ?>

    <div class="cart-layout">
        <div>
            <h3>Kontaktní formulář</h3>
            <!-- Formulář se odesílá na stejnou stránku metodou POST -->
            <form action="kontakt.php" method="POST">
                
                <div style="margin-bottom: 15px;">
                    <input type="text" name="name" placeholder="Vaše jméno" value="<?= htmlspecialchars($name) ?>" style="margin-bottom: 5px;">
                    <?php if (isset($errors['name'])): ?>
                        <span style="color: var(--primary); font-size: 0.85rem; display: block;"><?= htmlspecialchars($errors['name']) ?></span>
                    <?php endif; ?>
                </div>

                <div style="margin-bottom: 15px;">
                    <input type="email" name="email" placeholder="Váš email" value="<?= htmlspecialchars($email) ?>" style="margin-bottom: 5px;">
                    <?php if (isset($errors['email'])): ?>
                        <span style="color: var(--primary); font-size: 0.85rem; display: block;"><?= htmlspecialchars($errors['email']) ?></span>
                    <?php endif; ?>
                </div>

                <div style="margin-bottom: 15px;">
                    <textarea name="message" placeholder="Vaše zpráva..." style="height:150px; margin-bottom: 5px;"><?= htmlspecialchars($messageText) ?></textarea>
                    <?php if (isset($errors['message'])): ?>
                        <span style="color: var(--primary); font-size: 0.85rem; display: block;"><?= htmlspecialchars($errors['message']) ?></span>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn">Odeslat zprávu</button>
            </form>
        </div>
        <div style="background:#1e1e1e; padding:20px; border-radius: var(--radius); height: fit-content;">
            <h3>Kde nás najdete</h3>
            <p><strong>MTB Gravity Shop</strong><br>Pod Sjezdovkou 666<br>123 00 Hory</p>
            <br>
            <p>Tel: +420 670 670 670</p>
            <p>Email: info@gravityshop67.cz</p>
            <br>
            <h3>Otevírací doba</h3>
            <p>Po-Pá: 6:00 - 19:00</p>
        </div>
    </div>
</main>

<?php
require_once __DIR__ . '/partials/footer.php';
?>
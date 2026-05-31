<?php
declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

// Nastavíme správnou HTTP hlavičku pro vyhledávače, že stránka neexistuje
http_response_code(404);

$pageTitle = 'Stránka nenalezena';
require_once __DIR__ . '/partials/header.php';
?>

<main class="container" style="text-align: center; padding: 100px 20px;">
    <h1 style="font-size: 6rem; color: var(--primary); margin-bottom: 10px; line-height: 1;">404</h1>
    <h2 style="margin-bottom: 30px;">Omlouváme se, ale tato stránka neexistuje</h2>
    <p style="color: var(--text-muted); margin-bottom: 40px; max-width: 600px; margin-left: auto; margin-right: auto;">
        Požadovaný produkt, kategorie nebo stránka nebyla nalezena. Pravděpodobně byla přesunuta nebo zadal/a špatnou adresu.
    </p>
    <a href="index.php" class="btn" style="padding: 15px 30px;">Zpět na hlavní stránku</a>
</main>

<?php
require_once __DIR__ . '/partials/footer.php';
?>
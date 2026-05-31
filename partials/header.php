<?php
declare(strict_types=1);

// Načtení košíku, abychom mohli dynamicky zobrazovat počet kusů
$cart = new Cart();
$totalItemsInCart = $cart->getTotalQuantity();
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | GRAVITY SHOP' : 'GRAVITY SHOP' ?></title>
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
    <header class="main-header">
        <div class="container header-flex">
            <a href="index.php" class="logo">GRAVITY<span>SHOP</span></a>
            <div class="search-box">
                <!-- Vyhledávací formulář, který odesílá data na vyhledavani.php -->
                <form action="vyhledavani.php" method="GET" style="display: flex; gap: 10px;">
                    <input type="text" name="q" placeholder="Hledat komponenty..." 
                           value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" 
                           style="margin:0; width: 250px;">
                    <button type="submit" class="btn" style="padding: 12px 15px;">Hledat</button>
                </form>
            </div>
        </div>
    </header>
    <nav class="main-nav">
        <div class="container">
            <ul>
                <?php 
                // Pomocná proměnná pro aktivní záložku v menu
                $current_page = basename($_SERVER['SCRIPT_NAME']); 
                ?>
                <li><a href="index.php" class="<?= $current_page === 'index.php' ? 'active' : '' ?>">Domů</a></li>
                <li><a href="kategorie.php" class="<?= $current_page === 'kategorie.php' ? 'active' : '' ?>">Kategorie</a></li>
                <li><a href="produkty.php" class="<?= $current_page === 'produkty.php' ? 'active' : '' ?>">Všechny produkty</a></li>
                <li><a href="o-nas.php" class="<?= $current_page === 'o-nas.php' ? 'active' : '' ?>">O nás</a></li>
                <li><a href="kontakt.php" class="<?= $current_page === 'kontakt.php' ? 'active' : '' ?>">Kontakt</a></li>
                <li>
                    <a href="kosik.php" style="color: var(--primary);" class="<?= $current_page === 'kosik.php' ? 'active' : '' ?>">
                        🛒 Košík (<?= $totalItemsInCart ?>)
                    </a>
                </li>
            </ul>
        </div>
    </nav>
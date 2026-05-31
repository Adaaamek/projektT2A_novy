<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'GRAVITY SHOP' ?></title>
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
    <header class="main-header">
        <div class="container header-flex">
            <a href="index.php" class="logo">GRAVITY<span>SHOP</span></a>
            <div class="search-box">
                <form action="vyhledavani.php" method="GET" style="margin: 0;">
                    <input type="text" name="q" placeholder="Hledat komponenty..." style="margin:0; width: 250px;">
                </form>
            </div>
        </div>
    </header>
    <nav class="main-nav">
        <div class="container">
            <ul>
                <li><a href="index.php">Domů</a></li>
                <li><a href="kategorie.php">Kategorie</a></li>
                <li><a href="produkty.php">E-shop</a></li>
                <li><a href="o-nas.php">O nás</a></li>
                <li><a href="kontakt.php">Kontakt</a></li>
                <li><a href="kosik-krok1.php" style="color: var(--primary);">🛒 Košík (<?= $cartItemCount ?? 0 ?>)</a></li>
            </ul>
        </div>
    </nav>
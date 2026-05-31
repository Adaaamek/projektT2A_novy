<?php
declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

$pageTitle = 'O nás';
require_once __DIR__ . '/partials/header.php';
?>

<main class="container">
    <div class="breadcrumbs">Domů / O nás</div>
    
    <h1>JSME GRAVITY SHOP</h1>
    <img src="https://placehold.co/1200x400/111/e62117?text=N%C3%81%C5%A0+T%C3%9DM+NAD%C5%A0ENC%C5%AE" alt="Náš tým nadšenců" style="margin-bottom:30px; width: 100%; height: auto;">
    
    <p style="margin-bottom:20px; font-size: 1.1rem; line-height: 1.8;">
        Jsme parta nadšenců downhillu a endura. Nejsme jen e-shop, ale jsme jezdci. Vše, co prodáváme, sami testujeme na nejdrsnějších tratích v Česku i zahraničí. Máme dlouholetou zkušenost s horskou cyklistikou a servisem horských kol. Desítky poskládaných kol na zakázku, stovky hodin servisu a plno spokojených zákazníků.
    </p>
    
    <p style="font-size: 1.2rem; font-weight: bold; margin-top: 30px;">Specializujeme se na:</p>
    <ul style="list-style:disc; margin-left:20px; color:var(--text-muted); line-height: 1.8; margin-bottom: 40px;">
        <li>Stavby kol na zakázku</li>
        <li>Servis odpružení</li>
        <li>Prodej prémiových značek</li>
    </ul>
</main>

<?php
require_once __DIR__ . '/partials/footer.php';
?>
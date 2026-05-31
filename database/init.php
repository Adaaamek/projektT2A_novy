<?php

declare(strict_types=1);

/**
 * Inicializace databáze – vytvoří tabulky a naplní vzorovými daty.
 *
 * Spuštění: php projekt/database/init.php
 *
 * POZOR: Smaže existující databázi a vytvoří novou!
 */

$dbPath = __DIR__ . '/eshop.db';

// Smazat existující databázi
if (file_exists($dbPath)) {
	unlink($dbPath);
	echo "Stará databáze smazána.\n";
}

$db = new PDO('sqlite:' . $dbPath, options: [
	PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$db->exec('PRAGMA journal_mode = WAL');
$db->exec('PRAGMA foreign_keys = ON');

// ============================================================
// Vytvoření tabulek
// ============================================================

$db->exec('
    CREATE TABLE categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        slug TEXT NOT NULL UNIQUE,
        image TEXT NOT NULL DEFAULT "",
        description TEXT NOT NULL DEFAULT ""
    )
');

$db->exec('
    CREATE TABLE products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        category_id INTEGER NOT NULL,
        name TEXT NOT NULL,
        slug TEXT NOT NULL UNIQUE,
        price REAL NOT NULL,
        original_price REAL,
        description TEXT NOT NULL DEFAULT "",
        image TEXT NOT NULL DEFAULT "",
        featured INTEGER NOT NULL DEFAULT 0,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id)
    )
');

$db->exec('
    CREATE TABLE product_images (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        product_id INTEGER NOT NULL,
        image TEXT NOT NULL,
        sort_order INTEGER NOT NULL DEFAULT 0,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )
');

$db->exec('
    CREATE TABLE product_parameters (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        product_id INTEGER NOT NULL,
        name TEXT NOT NULL,
        value TEXT NOT NULL,
        type TEXT NOT NULL DEFAULT "info",
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )
');

$db->exec('
    CREATE TABLE customers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        first_name TEXT NOT NULL,
        last_name TEXT NOT NULL,
        email TEXT NOT NULL,
        phone TEXT NOT NULL DEFAULT "",
        street TEXT NOT NULL DEFAULT "",
        city TEXT NOT NULL DEFAULT "",
        zip TEXT NOT NULL DEFAULT "",
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )
');

$db->exec('
    CREATE TABLE shipping_methods (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        price REAL NOT NULL DEFAULT 0,
        delivery_days TEXT NOT NULL DEFAULT ""
    )
');

$db->exec('
    CREATE TABLE payment_methods (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        price REAL NOT NULL DEFAULT 0
    )
');

$db->exec('
    CREATE TABLE orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        customer_id INTEGER NOT NULL,
        shipping_method_id INTEGER NOT NULL,
        payment_method_id INTEGER NOT NULL,
        shipping_price REAL NOT NULL DEFAULT 0,
        payment_price REAL NOT NULL DEFAULT 0,
        note TEXT NOT NULL DEFAULT "",
        total_price REAL NOT NULL,
        status TEXT NOT NULL DEFAULT "new",
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (customer_id) REFERENCES customers(id),
        FOREIGN KEY (shipping_method_id) REFERENCES shipping_methods(id),
        FOREIGN KEY (payment_method_id) REFERENCES payment_methods(id)
    )
');

$db->exec('
    CREATE TABLE order_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        order_id INTEGER NOT NULL,
        product_id INTEGER NOT NULL,
        product_name TEXT NOT NULL,
        variant TEXT NOT NULL DEFAULT "",
        quantity INTEGER NOT NULL,
        unit_price REAL NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id)
    )
');

echo "Tabulky vytvořeny.\n";

// ============================================================
// Vzorová data – téma: Sportovní e-shop
// ============================================================

// Kategorie
$categories = [
	['Běžecké vybavení', 'bezecke-vybaveni', 'assets/images/kategorie/beh.svg', 'Vše pro běžce – boty, oblečení, doplňky a příslušenství pro trénink i závody.'],
	['Cyklistika', 'cyklistika', 'assets/images/kategorie/cyklistika.svg', 'Kola, helmy, dresy a veškeré příslušenství pro cyklisty všech úrovní.'],
	['Fitness', 'fitness', 'assets/images/kategorie/fitness.svg', 'Činky, expandéry, podložky a další vybavení pro cvičení doma i v posilovně.'],
	['Outdoor a turistika', 'outdoor-turistika', 'assets/images/kategorie/outdoor.svg', 'Batohy, stany, spacáky a vybavení pro turistiku a pobyt v přírodě.'],
	['Míčové sporty', 'micove-sporty', 'assets/images/kategorie/micove-sporty.svg', 'Fotbalové, basketbalové, volejbalové míče a vybavení pro týmové sporty.'],
	['Zimní sporty', 'zimni-sporty', 'assets/images/kategorie/zimni-sporty.svg', 'Lyže, snowboardy, brusle a kompletní výbava na zimní sezónu.'],
];

$catStmt = $db->prepare('INSERT INTO categories (name, slug, image, description) VALUES (?, ?, ?, ?)');
foreach ($categories as $cat) {
	$catStmt->execute($cat);
}

echo "Kategorie vloženy.\n";

// Produkty
$products = [
	// Běžecké vybavení (category_id = 1)
	[1, 'Nike Air Zoom Pegasus 41', 'nike-air-zoom-pegasus-41', 3299, 3899, 'Univerzální běžecká bota s technologií Air Zoom pro maximální odpružení. Vhodná pro každodenní tréninky na silnici.', 'assets/images/produkty/pegasus-41.svg', 1],
	[1, 'Adidas Ultraboost Light', 'adidas-ultraboost-light', 4499, NULL, 'Lehká a pohodlná běžecká bota s technologií Boost pro návrat energie při každém kroku.', 'assets/images/produkty/ultraboost-light.svg', 1],
	[1, 'Garmin Forerunner 265', 'garmin-forerunner-265', 9990, 11490, 'Sportovní GPS hodinky s AMOLED displejem, měřením tepové frekvence a pokročilými tréninkovými funkcemi.', 'assets/images/produkty/forerunner-265.svg', 1],
	[1, 'Běžecký pás na koleno', 'bezecky-pas-na-koleno', 349, NULL, 'Elastická kolenní bandáž pro podporu kloubu při běhu a dalších sportovních aktivitách.', 'assets/images/produkty/koleni-pas.svg', 0],
	[1, 'CamelBak běžecký batoh 1.5L', 'camelbak-bezecky-batoh', 1890, NULL, 'Lehký běžecký batoh s hydratačním vakem o objemu 1,5 litru. Ideální pro dlouhé tréninky.', 'assets/images/produkty/camelbak-batoh.svg', 0],

	// Cyklistika (category_id = 2)
	[2, 'Specialized Allez Sport', 'specialized-allez-sport', 28990, 32990, 'Silniční kolo s hliníkovým rámem a karbonovou vidlicí. Skvělá volba pro začínající i pokročilé silničáře.', 'assets/images/produkty/allez-sport.svg', 1],
	[2, 'Cyklistická helma Giro Syntax', 'cyklisticka-helma-giro-syntax', 1890, NULL, 'Lehká a větraná helma s technologií MIPS pro lepší ochranu hlavy při nárazu.', 'assets/images/produkty/giro-syntax.svg', 0],
	[2, 'Cyklistický dres Castelli', 'cyklisticky-dres-castelli', 2490, 2990, 'Prodyšný letní dres s plným zipem a třemi zadními kapsami. Materiál rychle odvádí pot.', 'assets/images/produkty/castelli-dres.svg', 0],
	[2, 'Lezyne Strip Drive zadní světlo', 'lezyne-strip-drive-zadni', 690, NULL, 'Kompaktní USB nabíjecí zadní světlo s 15 lumenů a 6 režimy svícení.', 'assets/images/produkty/lezyne-svetlo.svg', 0],
	[2, 'Wahoo ELEMNT Bolt V2', 'wahoo-elemnt-bolt-v2', 7490, NULL, 'GPS cyklopočítač s barevným displejem, navigací a propojením se senzory ANT+ i Bluetooth.', 'assets/images/produkty/wahoo-bolt.svg', 1],

	// Fitness (category_id = 3)
	[3, 'Kettlebell 16 kg', 'kettlebell-16kg', 1290, NULL, 'Litinový kettlebell s vinylovou povrchovou úpravou. Základní náčiní pro funkční trénink.', 'assets/images/produkty/kettlebell-16.svg', 1],
	[3, 'Expandér sada 5 ks', 'expander-sada-5ks', 599, 799, 'Sada pěti odporových gum s různou silou odporu. Vhodné pro cvičení doma i na cestách.', 'assets/images/produkty/expandery-sada.svg', 1],
	[3, 'Yoga podložka TPE 6mm', 'yoga-podlozka-tpe-6mm', 690, NULL, 'Ekologická podložka z materiálu TPE. Protiskluzový povrch, tloušťka 6 mm pro komfort kloubů.', 'assets/images/produkty/yoga-podlozka.svg', 0],
	[3, 'TRX Suspension Trainer', 'trx-suspension-trainer', 4490, 4990, 'Originální závěsný posilovací systém TRX pro trénink s vlastní vahou. Včetně kotvy do dveří.', 'assets/images/produkty/trx-trainer.svg', 1],
	[3, 'Foam Roller 45 cm', 'foam-roller-45cm', 490, NULL, 'Masážní válec pro uvolnění svalů a fascií po tréninku. Střední tvrdost.', 'assets/images/produkty/foam-roller.svg', 0],

	// Outdoor a turistika (category_id = 4)
	[4, 'Osprey Atmos AG 65', 'osprey-atmos-ag-65', 6490, 7290, 'Turistický batoh s anti-gravitačním systémem pro maximální pohodlí na vícedenních túrách.', 'assets/images/produkty/osprey-atmos.svg', 1],
	[4, 'Salomon X Ultra 4 GTX', 'salomon-x-ultra-4-gtx', 3990, NULL, 'Lehká turistická obuv s membránou Gore-Tex a podrážkou Contagrip pro spolehlivou trakci.', 'assets/images/produkty/salomon-x-ultra.svg', 1],
	[4, 'Stan MSR Hubba Hubba NX 2', 'stan-msr-hubba-hubba-nx2', 12990, NULL, 'Ultralehký dvouvrstvý stan pro 2 osoby. Hmotnost pouze 1,72 kg, ideální pro bikepacking.', 'assets/images/produkty/msr-hubba.svg', 0],
	[4, 'Čelovka Petzl Actik Core', 'celovka-petzl-actik-core', 1490, 1790, 'Nabíjecí čelovka se svítivostí 600 lumenů. Červené světlo pro noční vidění.', 'assets/images/produkty/petzl-actik.svg', 0],
	[4, 'Therm-a-Rest NeoAir XTherm', 'thermarest-neoair-xtherm', 5990, NULL, 'Nafukovací karimatka s R-hodnotou 6,9. Ideální pro zimní kempování a náročné podmínky.', 'assets/images/produkty/thermarest-xtherm.svg', 0],

	// Míčové sporty (category_id = 5)
	[5, 'Adidas UCL Pro míč', 'adidas-ucl-pro-mic', 1290, 1590, 'Oficiální zápasový míč UEFA Champions League. Bezešvý povrch pro přesnou kontrolu.', 'assets/images/produkty/adidas-ucl.svg', 1],
	[5, 'Spalding TF-1000 Legacy', 'spalding-tf-1000-legacy', 1890, NULL, 'Prémiový basketbalový míč z kompozitní kůže. Výborný grip a trvanlivost pro halové hřiště.', 'assets/images/produkty/spalding-tf1000.svg', 0],
	[5, 'Mikasa V200W volejbalový míč', 'mikasa-v200w', 1690, NULL, 'Oficiální míč FIVB pro halový volejbal. Technologie dvouvrstvého panelu pro stabilní let.', 'assets/images/produkty/mikasa-v200w.svg', 0],
	[5, 'Nike Mercurial Superfly 9', 'nike-mercurial-superfly-9', 6490, 7490, 'Profesionální kopačky s technologií Zoom Air a Flyknit svrškem pro maximální rychlost.', 'assets/images/produkty/mercurial-superfly.svg', 1],
	[5, 'Wilson Clash 100 v2 tenisová raketa', 'wilson-clash-100-v2', 5490, NULL, 'Tenisová raketa s technologií FreeFlex pro unikátní kombinaci flexibility a stability.', 'assets/images/produkty/wilson-clash.svg', 0],

	// Zimní sporty (category_id = 6)
	[6, 'Atomic Redster S9i', 'atomic-redster-s9i', 24990, 29990, 'Závodní sjezdové lyže s karbonovým sendvičem. Pro pokročilé lyžaře hledající maximální výkon.', 'assets/images/produkty/atomic-redster.svg', 1],
	[6, 'Burton Custom Flying V', 'burton-custom-flying-v', 13990, 15990, 'Univerzální snowboard s profilem Flying V. Hravý a odpouštějící, skvělý pro freestyle i freeride.', 'assets/images/produkty/burton-custom.svg', 1],
	[6, 'Lyžařské brýle Oakley Flight Deck', 'oakley-flight-deck', 4990, NULL, 'Bezrámové brýle s prizmatickým sklem pro vynikající kontrast a široké zorné pole.', 'assets/images/produkty/oakley-flight-deck.svg', 0],
	[6, 'Bauer Supreme M5 Pro brusle', 'bauer-supreme-m5-pro', 8990, 10490, 'Hokejové brusle s kompozitní botou a nerezovým nožem. Vynikající přenos energie a pohodlí.', 'assets/images/produkty/bauer-m5-pro.svg', 0],
	[6, 'Swix Triac 3.0 běžecké hole', 'swix-triac-30-bezecke-hole', 3490, NULL, 'Karbonové běžecké hole pro závodní použití. Extrémně lehké s ergonomickou rukojetí.', 'assets/images/produkty/swix-triac.svg', 0],
];

$prodStmt = $db->prepare('
    INSERT INTO products (category_id, name, slug, price, original_price, description, image, featured)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
');

foreach ($products as $prod) {
	$prodStmt->execute($prod);
}

echo "Produkty vloženy (" . count($products) . ").\n";

// Obrázky produktů (galerie) – ukázkově pro pár produktů
$images = [
	// Nike Pegasus (product_id = 1)
	[1, 'assets/images/produkty/pegasus-41-2.svg', 1],
	[1, 'assets/images/produkty/pegasus-41-3.svg', 2],
	[1, 'assets/images/produkty/pegasus-41-4.svg', 3],

	// Specialized Allez (product_id = 6)
	[6, 'assets/images/produkty/allez-sport-2.svg', 1],
	[6, 'assets/images/produkty/allez-sport-3.svg', 2],

	// Osprey Atmos (product_id = 16)
	[16, 'assets/images/produkty/osprey-atmos-2.svg', 1],
	[16, 'assets/images/produkty/osprey-atmos-3.svg', 2],

	// Atomic Redster (product_id = 26)
	[26, 'assets/images/produkty/atomic-redster-2.svg', 1],
	[26, 'assets/images/produkty/atomic-redster-3.svg', 2],

	// Nike Mercurial (product_id = 24)
	[24, 'assets/images/produkty/mercurial-superfly-2.svg', 1],
	[24, 'assets/images/produkty/mercurial-superfly-3.svg', 2],
];

$imgStmt = $db->prepare('INSERT INTO product_images (product_id, image, sort_order) VALUES (?, ?, ?)');
foreach ($images as $img) {
	$imgStmt->execute($img);
}

echo "Obrázky vloženy.\n";

// Parametry produktů – type: 'select' = volitelný (dropdown), 'info' = pouze informační
$parameters = [
	// Nike Pegasus (1)
	[1, 'Velikost', '36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46', 'select'],
	[1, 'Barva', 'Černá, Bílá, Šedá', 'select'],
	[1, 'Hmotnost', '272 g (vel. 42)', 'info'],
	[1, 'Povrch', 'Silnice', 'info'],
	[1, 'Drop', '10 mm', 'info'],

	// Adidas Ultraboost (2)
	[2, 'Velikost', '36, 37, 38, 39, 40, 41, 42, 43, 44, 45', 'select'],
	[2, 'Barva', 'Modrá, Bílá', 'select'],
	[2, 'Hmotnost', '280 g (vel. 42)', 'info'],
	[2, 'Technologie', 'Boost, Continental', 'info'],

	// Garmin Forerunner (3)
	[3, 'Displej', 'AMOLED 1,3"', 'info'],
	[3, 'Výdrž baterie', 'Až 13 dní (hodinky) / 20 h (GPS)', 'info'],
	[3, 'Vodotěsnost', '5 ATM', 'info'],
	[3, 'Barva', 'Černá, Bílá, Modrá', 'select'],

	// Specialized Allez (6)
	[6, 'Velikost rámu', '49, 52, 54, 56, 58, 61', 'select'],
	[6, 'Materiál rámu', 'Hliník E5', 'info'],
	[6, 'Vidlice', 'Karbon FACT', 'info'],
	[6, 'Řazení', 'Shimano Claris R2000, 2x8', 'info'],
	[6, 'Hmotnost', '9,4 kg (vel. 56)', 'info'],

	// Kettlebell (11)
	[11, 'Hmotnost', '16 kg', 'info'],
	[11, 'Materiál', 'Litina, vinylový potah', 'info'],
	[11, 'Barva', 'Žlutá', 'info'],

	// TRX (14)
	[14, 'Max. nosnost', '160 kg', 'info'],
	[14, 'Materiál', 'Nylon, ocel', 'info'],
	[14, 'Obsah balení', 'TRX pás, kotva do dveří, taška', 'info'],

	// Osprey Atmos (16)
	[16, 'Objem', '65 litrů', 'info'],
	[16, 'Hmotnost', '2,01 kg', 'info'],
	[16, 'Materiál', '100D Nylon', 'info'],
	[16, 'Velikost', 'S/M, L/XL', 'select'],

	// Salomon X Ultra (17)
	[17, 'Velikost', '40, 41, 42, 43, 44, 45, 46', 'select'],
	[17, 'Membrána', 'Gore-Tex', 'info'],
	[17, 'Podrážka', 'Contagrip MA', 'info'],
	[17, 'Hmotnost', '370 g (vel. 42)', 'info'],

	// Adidas UCL míč (21)
	[21, 'Velikost', '5', 'info'],
	[21, 'Materiál', 'Polyuretan, bezešvý', 'info'],
	[21, 'Certifikace', 'FIFA Quality Pro', 'info'],

	// Nike Mercurial (24)
	[24, 'Velikost', '39, 40, 41, 42, 43, 44, 45', 'select'],
	[24, 'Barva', 'Červená, Černá', 'select'],
	[24, 'Svršek', 'Flyknit', 'info'],
	[24, 'Podešev', 'FG (přírodní tráva)', 'info'],

	// Atomic Redster (26)
	[26, 'Délka', '157 cm, 162 cm, 167 cm, 172 cm', 'select'],
	[26, 'Rádius', '12,7 m (167 cm)', 'info'],
	[26, 'Technologie', 'Servotec, Power Woodcore', 'info'],
	[26, 'Vázání', 'X 12 GW', 'info'],

	// Burton Custom (27)
	[27, 'Délka', '150 cm, 154 cm, 156 cm, 158 cm, 162 cm', 'select'],
	[27, 'Profil', 'Flying V (camber + rocker)', 'info'],
	[27, 'Flex', '6/10 (střední)', 'info'],
	[27, 'Jádro', 'Super Fly II 700G', 'info'],
];

$paramStmt = $db->prepare('INSERT INTO product_parameters (product_id, name, value, type) VALUES (?, ?, ?, ?)');
foreach ($parameters as $param) {
	$paramStmt->execute($param);
}

echo "Parametry vloženy.\n";

// Způsoby dopravy
$shippingMethods = [
	['Osobní odběr', 0, 'Ihned k vyzvednutí'],
	['Zásilkovna', 69, '2–3 pracovní dny'],
	['PPL', 99, '1–2 pracovní dny'],
	['Česká pošta', 129, '3–5 pracovních dnů'],
	['DPD', 89, '1–2 pracovní dny'],
];

$shipStmt = $db->prepare('INSERT INTO shipping_methods (name, price, delivery_days) VALUES (?, ?, ?)');
foreach ($shippingMethods as $method) {
	$shipStmt->execute($method);
}

echo "Způsoby dopravy vloženy.\n";

// Způsoby platby
$paymentMethods = [
	['Kartou online', 0],
	['Bankovním převodem', 0],
	['Dobírkou', 39],
	['Apple Pay / Google Pay', 0],
];

$payStmt = $db->prepare('INSERT INTO payment_methods (name, price) VALUES (?, ?)');
foreach ($paymentMethods as $method) {
	$payStmt->execute($method);
}

echo "Způsoby platby vloženy.\n";

// Vzorový zákazník
$db->exec('
    INSERT INTO customers (first_name, last_name, email, phone, street, city, zip)
    VALUES ("Jan", "Novák", "jan.novak@email.cz", "+420 777 123 456", "Sportovní 42", "Praha", "11000")
');

echo "Vzorový zákazník vytvořen.\n";

// Vzorová objednávka (Zásilkovna = id 2, cena 69 Kč; Kartou online = id 1, cena 0 Kč)
// Celková cena: 3299 + 599 + 490 + 69 (doprava) + 0 (platba) = 4457
$db->exec('
    INSERT INTO orders (customer_id, shipping_method_id, payment_method_id, shipping_price, payment_price, note, total_price, status)
    VALUES (1, 2, 1, 69, 0, "Prosím zabalit jako dárek.", 4457, "new")
');

$db->exec('
    INSERT INTO order_items (order_id, product_id, product_name, variant, quantity, unit_price)
    VALUES
        (1, 1, "Nike Air Zoom Pegasus 41", "Barva: Černá, Velikost: 42", 1, 3299),
        (1, 12, "Expandér sada 5 ks", "", 1, 599),
        (1, 15, "Foam Roller 45 cm", "", 1, 490)
');

// Indexy pro rychlejší vyhledávání
$db->exec('CREATE INDEX idx_products_category ON products(category_id)');
$db->exec('CREATE INDEX idx_products_slug ON products(slug)');
$db->exec('CREATE INDEX idx_products_featured ON products(featured)');
$db->exec('CREATE INDEX idx_categories_slug ON categories(slug)');
$db->exec('CREATE INDEX idx_order_items_order ON order_items(order_id)');
$db->exec('CREATE INDEX idx_product_images_product ON product_images(product_id)');
$db->exec('CREATE INDEX idx_product_params_product ON product_parameters(product_id)');

echo "\nDatabáze úspěšně inicializována!\n";
echo "Soubor: $dbPath\n";
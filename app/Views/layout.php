<!doctype html>
<html lang="nl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#f8f7f3">
  <meta name="description" content="Persoonlijke vertaal- en tolkdiensten in het Nederlands, Engels en Arabisch.">
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="public/css/style.css">
  <script defer src="public/js/app.js"></script>
</head>
<body>
<header class="topbar"><a class="brand" href="index.php" aria-label="Woorden verbinden, startpagina"><span class="brand-mark">W<span>↔</span></span><span>woorden<span class="brand-light">verbinden</span></span></a>
  <nav class="nav" aria-label="Hoofdnavigatie"><a href="index.php?page=diensten" data-i18n="nav_services">Diensten</a><a href="index.php?page=over-mij" data-i18n="nav_about">Over mij</a><a href="index.php?page=contact" data-i18n="nav_contact">Contact</a></nav>
  <div class="header-actions"><label class="sr-only" for="language" data-i18n="language_label">Kies je taal</label><select id="language" aria-label="Kies je taal"><option value="nl">NL</option><option value="en">EN</option><option value="ar">عربي</option></select><a class="button button-small" href="index.php?page=contact" data-i18n="nav_cta">Vraag een offerte</a><button class="menu-toggle" aria-label="Open menu" aria-expanded="false">☰</button></div>
</header>
<main><?php require $view; ?></main>
<footer><a class="brand footer-brand" href="index.php"><span class="brand-mark">W<span>↔</span></span><span>woorden<span class="brand-light">verbinden</span></span></a><p data-i18n="footer_line">Persoonlijke vertaal- en tolkdiensten · Nederlands · Engels · Arabisch</p><a href="index.php" class="back-top" data-i18n="back_top">Terug naar start ↑</a></footer>
</body>
</html>

<?php
require_once 'config.php';
if (!isset($pageTitle)) {
    $pageTitle = SITE_NAME;
} else {
    $pageTitle = SITE_NAME . ' - ' . $pageTitle;
}
if (!isset($activePage)) {
    $activePage = '';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?php echo $pageTitle; ?></title>
  <link rel="stylesheet" href="/WaveNet/views/assets/css/style11.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <?php if (isset($additionalCss)): ?>
    <?php echo $additionalCss; ?>
  <?php endif; ?>
</head>
<body>
  <!-- HEADER -->
  <header class="main-header">
    <div class="nav-container">
      <div class="logo">
        <h1><?php echo SITE_NAME; ?></h1>
      </div>
      <nav>
        <ul class="nav-links">
          <li><a href="<?php echo SITE_URL; ?>" class="<?php echo ($activePage == 'home') ? 'active' : ''; ?>">Accueil</a></li>
          <li><a href="<?php echo SITE_URL; ?>frontoffice/about.php" class="<?php echo ($activePage == 'about') ? 'active' : ''; ?>">À propos</a></li>
          <li><a href="<?php echo SITE_URL; ?>frontoffice/blog.php" class="<?php echo ($activePage == 'blog') ? 'active' : ''; ?>">Blog</a></li>
          <li><a href="<?php echo SITE_URL; ?>frontoffice/contact.php" class="<?php echo ($activePage == 'contact') ? 'active' : ''; ?>">Contact</a></li>
        </ul>
      </nav>
      <div class="user-actions">
        <a href="<?php echo SITE_URL; ?>./views/frontoffice/login.php" class="btn btn-outline <?php echo ($activePage == 'login') ? 'active' : ''; ?>">Connexion</a>
        <a href="<?php echo SITE_URL; ?>views/frontoffice/register.php" class="btn btn-primary <?php echo ($activePage == 'register') ? 'active' : ''; ?>">Inscription</a>
      </div>
    </div>
  </header>

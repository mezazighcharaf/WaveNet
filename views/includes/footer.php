<?php
if (!defined('SITE_NAME')) {
    require_once 'config.php';
}
$currentYear = date('Y');
?>
  <!-- FOOTER -->
  <footer>
    <div class="footer-content">
      <div class="footer-section">
        <h4>À propos</h4>
        <p><?php echo SITE_NAME; ?> est une plateforme collaborative pour améliorer votre cadre de vie urbain et participer à des initiatives écologiques locales.</p>
      </div>
      <div class="footer-section">
        <h4>Liens rapides</h4>
        <ul>
          <li><a href="<?php echo SITE_URL; ?>">Accueil</a></li>
          <li><a href="<?php echo SITE_URL; ?>client/about.html">À propos</a></li>
          <li><a href="<?php echo SITE_URL; ?>client/blog.html">Blog</a></li>
          <li><a href="<?php echo SITE_URL; ?>client/report.html">Contact</a></li>
        </ul>
      </div>
      <div class="footer-section">
        <h4>Contact</h4>
        <p>Email: contact@example.com</p>
        <div class="social-links">
          <a href="#"><i class="fab fa-facebook-f"></i></a>
          <a href="#"><i class="fab fa-twitter"></i></a>
          <a href="#"><i class="fab fa-instagram"></i></a>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; <?php echo $currentYear; ?> <?php echo SITE_NAME; ?>. Tous droits réservés.</p>
    </div>
  </footer>
  <?php if (isset($additionalScripts)): ?>
    <?php echo $additionalScripts; ?>
  <?php endif; ?>
</body>
</html>

<?php
require_once 'views/includes/config.php';
$activePage = 'home';
$pageTitle = 'Urbaverse - La ville du futur';
require_once 'views/includes/header.php';
?>
<!-- Élément de fond avec effet de vague -->
<div class="page-background"></div>
<!-- HERO SECTION AMÉLIORÉE -->
<section class="hero">
  <div class="hero-container">
    <!-- Contenu textuel (côté gauche) -->
    <div class="hero-content">
      <h1 class="hero-title">Ensemble pour une ville <span style="color: var(--accent-green);">durable</span></h1>
      <p class="hero-text">Participez à la transformation écologique de votre quartier et contribuez à créer un environnement urbain plus vert, plus inclusif et plus résilient face aux défis climatiques.</p>
      <div class="hero-actions">
        <a href="./frontoffice/register.php" class="btn btn-primary btn-large">Rejoindre la communauté</a>
        <a href="#fonctionnement" class="btn btn-secondary">En savoir plus</a>
      </div>
    </div>
    <!-- Image (côté droit) -->
    <div class="hero-image-container">
      <img src="views/assets/img/urbanise.jpeg" alt="Ville verte" class="hero-image">
    </div>
  </div>
</section>
<!-- SECTION FONCTIONNEMENT -->
<section id="fonctionnement" class="section section-light">
  <div class="container">
    <div class="section-title">
      <h2>Comment ça <span style="color: var(--accent-green);">fonctionne</span></h2>
      <p><?php echo SITE_NAME; ?> est une plateforme collaborative pour un urbanisme citoyen et durable</p>
    </div>
    <div class="stats-grid">
      <div class="stat-card" style="transition: transform 0.3s; cursor: pointer;" onmouseover="this.style.transform='translateY(-10px)'" onmouseout="this.style.transform='translateY(0)'">
        <div class="stat-icon"><i class="fas fa-leaf"></i></div>
        <h3>Actions Vertes</h3>
        <p class="stat-value">15,420</p>
        <p class="stat-label">Actions réalisées</p>
      </div>
      <div class="stat-card" style="transition: transform 0.3s; cursor: pointer;" onmouseover="this.style.transform='translateY(-10px)'" onmouseout="this.style.transform='translateY(0)'">
        <div class="stat-icon"><i class="fas fa-users"></i></div>
        <h3>Communauté</h3>
        <p class="stat-value">2,845</p>
        <p class="stat-label">Membres actifs</p>
      </div>
      <div class="stat-card" style="transition: transform 0.3s; cursor: pointer;" onmouseover="this.style.transform='translateY(-10px)'" onmouseout="this.style.transform='translateY(0)'">
        <div class="stat-icon"><i class="fas fa-seedling"></i></div>
        <h3>Projets</h3>
        <p class="stat-value">127</p>
        <p class="stat-label">Initiatives financées</p>
      </div>
    </div>
  </div>
</section>
<!-- SECTION IMPACT -->
<section class="section section-accent">
  <div class="container">
    <div class="section-title">
      <h2>Notre <span style="color: var(--accent-green);">impact</span></h2>
      <p>Découvrez comment notre communauté transforme les quartiers</p>
    </div>
    <div class="d-flex flex-wrap justify-between">
      <div style="flex: 1 1 30%; min-width: 300px; margin-bottom: 2rem;">
        <div class="card" style="height: 100%; transition: all 0.3s;" onmouseover="this.style.boxShadow='var(--shadow-lg)'" onmouseout="this.style.boxShadow='var(--shadow-md)'">
          <div class="card-body">
            <div style="font-size: 2.5rem; color: var(--accent-green); margin-bottom: 1rem;">
              <i class="fas fa-tree"></i>
            </div>
            <h3>Végétalisation urbaine</h3>
            <p>Création de jardins partagés, plantations d'arbres et végétalisation de façades pour lutter contre les îlots de chaleur.</p>
            <a href="#" class="btn btn-primary mt-3">Découvrir</a>
          </div>
        </div>
      </div>
      <div style="flex: 1 1 30%; min-width: 300px; margin-bottom: 2rem;">
        <div class="card" style="height: 100%; transition: all 0.3s;" onmouseover="this.style.boxShadow='var(--shadow-lg)'" onmouseout="this.style.boxShadow='var(--shadow-md)'">
          <div class="card-body">
            <div style="font-size: 2.5rem; color: var(--accent-green); margin-bottom: 1rem;">
              <i class="fas fa-bicycle"></i>
            </div>
            <h3>Mobilité durable</h3>
            <p>Développement des infrastructures cyclables, promotion du covoiturage et optimisation des transports en commun.</p>
            <a href="#" class="btn btn-primary mt-3">Découvrir</a>
          </div>
        </div>
      </div>
      <div style="flex: 1 1 30%; min-width: 300px; margin-bottom: 2rem;">
        <div class="card" style="height: 100%; transition: all 0.3s;" onmouseover="this.style.boxShadow='var(--shadow-lg)'" onmouseout="this.style.boxShadow='var(--shadow-md)'">
          <div class="card-body">
            <div style="font-size: 2.5rem; color: var(--accent-green); margin-bottom: 1rem;">
              <i class="fas fa-solar-panel"></i>
            </div>
            <h3>Énergie renouvelable</h3>
            <p>Installation de panneaux solaires communautaires et sensibilisation à la consommation responsable d'énergie.</p>
            <a href="#" class="btn btn-primary mt-3">Découvrir</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- SECTION REJOINDRE -->
<section class="section section-light">
  <div class="container text-center">
    <div class="section-title">
      <h2>Rejoignez le <span style="color: var(--accent-green);">mouvement</span></h2>
      <p>Ensemble, construisons des villes plus vertes et plus durables</p>
    </div>
    <div style="max-width: 600px; margin: 0 auto;">
      <p style="margin-bottom: 2rem;">Créez votre compte gratuitement et commencez à participer aux initiatives écologiques de votre quartier. Chaque action compte pour transformer notre environnement urbain.</p>
      <a href="./frontoffice/login.php" class="btn btn-primary">Connexion</a>
      <a href="./frontoffice/register.php" class="btn btn-secondary">Inscription</a>
    </div>
  </div>
</section>
<?php
$additionalScripts = <<<EOT
<script>
  document.querySelector('.mobile-menu-btn').addEventListener('click', function() {
    document.querySelector('.nav-links').classList.toggle('active');
  });
</script>
EOT;
require_once 'views/includes/footer.php';
?>

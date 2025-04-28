<?php
// Vérifie si la session est démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Réinitialiser le compteur de tentatives à chaque nouvelle demande de CAPTCHA si non défini
if (!isset($_SESSION['captcha_attempts'])) {
    $_SESSION['captcha_attempts'] = 0;
} else if (isset($_GET['reset'])) {
    $_SESSION['captcha_attempts'] = 0;
}

// Pour s'assurer que le nombre de tentatives restantes est bien calculé
$tentatives_max = 3;
$tentatives_restantes = $tentatives_max - $_SESSION['captcha_attempts'];
if ($tentatives_restantes < 0) {
    $tentatives_restantes = 0;
}

// Génère un token unique pour cette session de CAPTCHA
if (!isset($_SESSION['captcha_token'])) {
    $_SESSION['captcha_token'] = bin2hex(random_bytes(32));
}
?>

<div id="eco-captcha-container" class="minimal-captcha">
    <!-- Titre et instructions (gardés simples) -->
    <h3 style="font-size: 1.1rem; margin-bottom: 10px; text-align: center;">Vérification Rapide</h3>
    <p style="font-size: 0.85rem; margin-bottom: 15px; text-align: center; color: #555;">
        Sélectionnez des éléments pour atteindre un score de 50.<br>
        <span style="color:#4caf50">Éléments durables: +20 points</span> | 
        <span style="color:#f44336">Éléments non-durables: -10 points</span>
    </p>
    
    <div class="captcha-content-wrapper">
        <!-- Zone de dépôt -->
        <div id="city-grid" class="captcha-grid-minimal">
            <!-- Cellules générées par JS -->
        </div>
        
        <!-- Éléments disponibles -->
        <div id="elements-container">
            <div class="elements-list">
                <!-- Éléments durables -->
                <div class="element durable drag-item" data-type="durable" data-value="20" draggable="true" id="bike-path"><i class="fas fa-bicycle"></i></div>
                <div class="element durable drag-item" data-type="durable" data-value="20" draggable="true" id="solar-panels"><i class="fas fa-solar-panel"></i></div>
                <div class="element durable drag-item" data-type="durable" data-value="20" draggable="true" id="tramway"><i class="fas fa-train"></i></div>
                <div class="element durable drag-item" data-type="durable" data-value="20" draggable="true" id="community-garden"><i class="fas fa-seedling"></i></div>
                <div class="element durable drag-item" data-type="durable" data-value="20" draggable="true" id="green-roof"><i class="fas fa-leaf"></i></div>
                <!-- Éléments non durables -->
                <div class="element non-durable drag-item" data-type="non-durable" data-value="-10" draggable="true" id="highway"><i class="fas fa-road"></i></div>
                <div class="element non-durable drag-item" data-type="non-durable" data-value="-10" draggable="true" id="mall"><i class="fas fa-store"></i></div>
                <div class="element non-durable drag-item" data-type="non-durable" data-value="-10" draggable="true" id="big-road"><i class="fas fa-truck"></i></div>
                <div class="element non-durable drag-item" data-type="non-durable" data-value="-10" draggable="true" id="industrial-zone"><i class="fas fa-industry"></i></div>
            </div>
        </div>
    </div>

    <!-- Score et barre -->
    <div class="captcha-score-area">
        <span class="meter-label">Score: </span>
        <span class="meter-value" id="durability-score">0</span>
        <span class="meter-label"> (Élément durable +20, non-durable -10)</span>
    </div>
    
    <!-- Feedback -->
    <div class="captcha-feedback" id="captcha-feedback">Cliquez sur les éléments pour les sélectionner.</div>
    
    <!-- Actions et Tentatives -->
    <div class="captcha-bottom-bar">
        <div class="captcha-actions">
            <button type="button" id="reset-captcha" class="btn-cancel">Recommencer</button>
            <button type="button" id="validate-captcha" class="btn-verify">Valider</button>
        </div>
        <div class="captcha-attempts">
            Tentatives restantes : <span id="attempts-left"><?php echo $tentatives_restantes ?? 3; ?></span>
        </div>
    </div>

    <!-- Formulaire caché -->
    <form id="captcha-form" method="post" action="/WaveNet/controller/CaptchaController.php?action=validate" style="display: none;">
        <input type="hidden" name="captcha_score" id="captcha-score" value="0">
        <input type="hidden" name="captcha_token" value="<?php echo $_SESSION['captcha_token'] ?? ''; ?>">
        <input type="hidden" name="captcha_elements" id="captcha-elements" value="[]">
    </form>
</div>

<script src="/WaveNet/views/assets/js/eco-captcha-handler.js"></script> 

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

<div id="eco-captcha-container" class="minimal-captcha" style="overflow: visible;">
    <!-- Titre et instructions (gardés simples) -->
    <h3 style="font-size: 1.1rem; margin-bottom: 10px; text-align: center;">Vérification Rapide</h3>
    <p style="font-size: 0.85rem; margin-bottom: 15px; text-align: center; color: #555;">
        Sélectionnez des éléments pour atteindre un score de 50.<br>
        <span style="color:#4caf50">Éléments durables: +20 points</span> | 
        <span style="color:#f44336">Éléments non-durables: -10 points</span>
    </p>
    
    <div class="captcha-content-wrapper" style="display: flex !important; flex-direction: column !important; gap: 15px !important; margin-bottom: 15px !important; align-items: center !important;">
        <!-- Zone de dépôt -->
        <div id="city-grid" class="captcha-grid-minimal" style="display: flex !important; flex-wrap: wrap !important; min-height: 60px !important; background-color: #f8f8f8 !important; padding: 10px !important; border-radius: 8px !important; margin: 0 auto !important; width: 100% !important; max-width: 350px !important; z-index: 1 !important;">
            <!-- Cellules générées par JS -->
        </div>
        
        <!-- Éléments disponibles -->
        <div id="elements-container" style="display: block !important; width: 100% !important;">
            <div class="elements-list" style="display: grid !important; grid-template-columns: repeat(5, 1fr) !important; gap: 10px !important; justify-content: center !important; margin-top: 15px !important;">
                <!-- Éléments durables - modifiés avec ID correspondant à ceux du script -->
                <div class="element durable drag-item" data-type="durable" data-value="20" draggable="true" id="element-bike" style="display: flex !important; align-items: center !important; justify-content: center !important; width: 48px !important; height: 48px !important; cursor: pointer !important; background-color: #e8f5e9 !important; border: 1px solid #81c784 !important; position: relative !important;"><i class="fas fa-bicycle" style="font-size: 1.5rem !important; color: #4caf50 !important; font-weight: 900 !important;"></i></div>
                <div class="element durable drag-item" data-type="durable" data-value="20" draggable="true" id="element-solar" style="display: flex !important; align-items: center !important; justify-content: center !important; width: 48px !important; height: 48px !important; cursor: pointer !important; background-color: #e8f5e9 !important; border: 1px solid #81c784 !important; position: relative !important;"><i class="fas fa-solar-panel" style="font-size: 1.5rem !important; color: #4caf50 !important; font-weight: 900 !important;"></i></div>
                <div class="element durable drag-item" data-type="durable" data-value="20" draggable="true" id="element-bus" style="display: flex !important; align-items: center !important; justify-content: center !important; width: 48px !important; height: 48px !important; cursor: pointer !important; background-color: #e8f5e9 !important; border: 1px solid #81c784 !important; position: relative !important;"><i class="fas fa-bus" style="font-size: 1.5rem !important; color: #4caf50 !important; font-weight: 900 !important;"></i></div>
                <div class="element durable drag-item" data-type="durable" data-value="20" draggable="true" id="element-plant" style="display: flex !important; align-items: center !important; justify-content: center !important; width: 48px !important; height: 48px !important; cursor: pointer !important; background-color: #e8f5e9 !important; border: 1px solid #81c784 !important; position: relative !important;"><i class="fas fa-seedling" style="font-size: 1.5rem !important; color: #4caf50 !important; font-weight: 900 !important;"></i></div>
                <div class="element durable drag-item" data-type="durable" data-value="20" draggable="true" id="element-leaf" style="display: flex !important; align-items: center !important; justify-content: center !important; width: 48px !important; height: 48px !important; cursor: pointer !important; background-color: #e8f5e9 !important; border: 1px solid #81c784 !important; position: relative !important;"><i class="fas fa-leaf" style="font-size: 1.5rem !important; color: #4caf50 !important; font-weight: 900 !important;"></i></div>
                <!-- Éléments non durables - modifiés avec ID correspondant à ceux du script -->
                <div class="element non-durable drag-item" data-type="non-durable" data-value="-10" draggable="true" id="element-road" style="display: flex !important; align-items: center !important; justify-content: center !important; width: 48px !important; height: 48px !important; cursor: pointer !important; background-color: #ffebee !important; border: 1px solid #e57373 !important; position: relative !important;"><i class="fas fa-road" style="font-size: 1.5rem !important; color: #f44336 !important; font-weight: 900 !important;"></i></div>
                <div class="element non-durable drag-item" data-type="non-durable" data-value="-10" draggable="true" id="element-shop" style="display: flex !important; align-items: center !important; justify-content: center !important; width: 48px !important; height: 48px !important; cursor: pointer !important; background-color: #ffebee !important; border: 1px solid #e57373 !important; position: relative !important;"><i class="fas fa-store" style="font-size: 1.5rem !important; color: #f44336 !important; font-weight: 900 !important;"></i></div>
                <div class="element non-durable drag-item" data-type="non-durable" data-value="-10" draggable="true" id="element-truck" style="display: flex !important; align-items: center !important; justify-content: center !important; width: 48px !important; height: 48px !important; cursor: pointer !important; background-color: #ffebee !important; border: 1px solid #e57373 !important; position: relative !important;"><i class="fas fa-truck" style="font-size: 1.5rem !important; color: #f44336 !important; font-weight: 900 !important;"></i></div>
                <div class="element non-durable drag-item" data-type="non-durable" data-value="-10" draggable="true" id="element-factory" style="display: flex !important; align-items: center !important; justify-content: center !important; width: 48px !important; height: 48px !important; cursor: pointer !important; background-color: #ffebee !important; border: 1px solid #e57373 !important; position: relative !important;"><i class="fas fa-industry" style="font-size: 1.5rem !important; color: #f44336 !important; font-weight: 900 !important;"></i></div>
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

    <!-- Add debugging style to make the city grid more visible -->
    <style>
    #city-grid {
        border: 2px dashed #ccc !important;
        min-height: 80px !important;
    }
    </style>
</div>

<script src="/WaveNet/views/assets/js/eco-captcha-handler.js"></script> 

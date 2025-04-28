<?php

if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    CaptchaController::route();
}

class CaptchaController {
    /**
     * Valide le CAPTCHA.
     */
    public function validate() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['captcha_attempts'])) {
            $_SESSION['captcha_attempts'] = 0;
        }

        if ($_SESSION['captcha_attempts'] >= 3) {
            $_SESSION['error'] = "Vous avez dépassé le nombre maximum de tentatives. Veuillez réessayer plus tard.";
            header('Location: /WaveNet/views/frontoffice/login.php');
            exit;
        }

        $captchaScore = isset($_POST['captcha_score']) ? floatval($_POST['captcha_score']) : 0;
        $captchaElements = isset($_POST['captcha_elements']) ? json_decode($_POST['captcha_elements'], true) : [];
        $captchaToken = isset($_POST['captcha_token']) ? $_POST['captcha_token'] : '';

        if (!isset($_SESSION['captcha_token']) || $_SESSION['captcha_token'] !== $captchaToken) {
            $_SESSION['captcha_attempts']++;
            $_SESSION['error'] = "Token CAPTCHA invalide. Veuillez réessayer.";
            header('Location: /WaveNet/views/frontoffice/login.php');
            exit;
        }

        $totalCount = is_array($captchaElements) ? count($captchaElements) : 0;
        
        error_log("[CaptchaController] Validation - Reçu: totalCount={$totalCount}, captchaScore=".var_export($captchaScore, true));
        
        $isValid = ($totalCount >= 3 && $captchaScore >= (float)50);

        if (!$isValid) {
            $_SESSION['captcha_attempts']++;
            if ($totalCount < 3) {
                 $_SESSION['error'] = "Vérification échouée. Veuillez placer au moins 3 éléments. 🧩";
            } elseif ($captchaScore < 50.0) {
                 $_SESSION['error'] = "Vérification échouée. Le score écologique doit être d'au moins 50. 🌱";
            } else {
                 $_SESSION['error'] = "Vérification échouée. Veuillez réessayer. 🤔"; 
            }
            header('Location: /WaveNet/views/frontoffice/login.php?keep_attempts=1'); 
            exit;
        }

        $_SESSION['captcha_validated'] = true;
        unset($_SESSION['captcha_token']);
        
        // Le commentaire important sur la responsabilité de cette fonction est conservé
        // **Important** : Le reste du code de cette fonction (récupération user ID, connexion, etc.)
        // devrait idéalement être déplacé dans UserController::finalizeLoginAfterCaptcha.
        // Cette fonction 'validate' ne devrait QUE valider le captcha et mettre un flag en session.
        
        $_SESSION['success'] = 'Vérification réussie ! Vous pouvez maintenant vous connecter.'; 
        header('Location: /WaveNet/views/frontoffice/login.php?keep_attempts=1&captcha_success=1');
        exit;
    }

    /**
     * Vérifie si le token CAPTCHA est valide pour l'utilisateur en cours de connexion.
     */
    public function checkToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['tmp_user_id'])) {
            return false;
        }

        if (!isset($_SESSION['validated_captcha_token'])) {
            return false;
        }

        try {
            require_once __DIR__ . '/../views/includes/config.php';
            $db = connectDB();

            $tableExists = $this->checkCaptchaTokensTable($db);
            if (!$tableExists) {
                return false;
            }

            $stmt = $db->prepare("SELECT id_token FROM CAPTCHA_TOKENS WHERE token = ? AND id_utilisateur = ? AND utilisé = 0");
            $stmt->execute([$_SESSION['validated_captcha_token'], $_SESSION['tmp_user_id']]);
            
            return $stmt->fetch() !== false;

        } catch (Exception $e) {
            error_log("Erreur lors de la vérification du token CAPTCHA: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Marque un token comme utilisé après connexion réussie.
     */
    public function useToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['tmp_user_id']) || !isset($_SESSION['validated_captcha_token'])) {
            return false;
        }

        try {
            require_once __DIR__ . '/../views/includes/config.php';
            $db = connectDB();

            $stmt = $db->prepare("UPDATE CAPTCHA_TOKENS SET utilisé = 1 WHERE token = ? AND id_utilisateur = ?");
            $result = $stmt->execute([$_SESSION['validated_captcha_token'], $_SESSION['tmp_user_id']]);

            unset($_SESSION['validated_captcha_token']);
            unset($_SESSION['tmp_user_id']);

            return $result;

        } catch (Exception $e) {
            error_log("Erreur lors de l'utilisation du token CAPTCHA: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifie si la table CAPTCHA_TOKENS existe.
     */
    private function checkCaptchaTokensTable($db) {
        $stmt = $db->query("SHOW TABLES LIKE 'CAPTCHA_TOKENS'");
        return $stmt->rowCount() > 0;
    }

    /**
     * Nettoie les anciens tokens CAPTCHA non utilisés.
     */
    public function cleanupOldTokens() {
        try {
            require_once __DIR__ . '/../views/includes/config.php';
            $db = connectDB();

            if (!$this->checkCaptchaTokensTable($db)) {
                return;
            }

            $stmt = $db->prepare("DELETE FROM CAPTCHA_TOKENS WHERE utilisé = 0 AND date_creation < DATE_SUB(NOW(), INTERVAL 24 HOUR)");
            $stmt->execute();

        } catch (Exception $e) {
            error_log("Erreur lors du nettoyage des anciens tokens CAPTCHA: " . $e->getMessage());
        }
    }

    /**
     * Génère et renvoie le fragment HTML pour le CAPTCHA interactif.
     */
    private function getEcoCaptchaHTML() {
        header('Content-Type: text/html; charset=utf-8');

        // --- DÉBUT : NOUVELLE STRUCTURE HTML (SANS STYLE INTÉGRÉ) --- 
        ?>
        <form id="captcha-form">
            <h4 style="text-align: center; margin-bottom: 10px;">Vérification Rapide</h4> 
            <p style="text-align: center; margin-bottom: 20px; font-size: 0.95em; color: #666;">Faites glisser 3 éléments verts dans la zone.</p>

            <div class="captcha-wrapper"> 
                <div class="captcha-drop-zone" id="captcha-drop-zone">
                    <div id="city-grid"></div>
                </div>
                <div id="elements-container"> 
                    <div class="captcha-elements-zone elements-list" id="captcha-items">
                        <div id="element-bike" class="element drag-item durable" data-type="durable" data-value="12" draggable="true" title="Vélo"><i class="fas fa-bicycle"></i></div>
                        <div id="element-solar" class="element drag-item durable" data-type="durable" data-value="15" draggable="true" title="Panneau Solaire"><i class="fas fa-solar-panel"></i></div>
                        <div id="element-bus" class="element drag-item durable" data-type="durable" data-value="8" draggable="true" title="Bus"><i class="fas fa-bus-alt"></i></div>
                        <div id="element-plant" class="element drag-item durable" data-type="durable" data-value="10" draggable="true" title="Plante"><i class="fas fa-seedling"></i></div>
                        <div id="element-leaf" class="element drag-item durable" data-type="durable" data-value="5" draggable="true" title="Feuille (Énergie verte)"><i class="fas fa-leaf"></i></div>
                        <div id="element-road" class="element drag-item non-durable" data-type="non-durable" data-value="-5" draggable="true" title="Route (Trafic)"><i class="fas fa-road"></i></div>
                        <div id="element-shop" class="element drag-item non-durable" data-type="non-durable" data-value="-6" draggable="true" title="Magasin (Consommation)"><i class="fas fa-store"></i></div>
                        <div id="element-truck" class="element drag-item non-durable" data-type="non-durable" data-value="-10" draggable="true" title="Camion"><i class="fas fa-truck"></i></div>
                        <div id="element-factory" class="element drag-item non-durable" data-type="non-durable" data-value="-15" draggable="true" title="Usine"><i class="fas fa-industry"></i></div>
                    </div>
                </div>
            </div>

            <div class="captcha-bottom-info" id="captcha-score-container"> 
                <span id="captcha-score-label">Score:</span> 
                <span id="durability-score">0</span>
                <div id="captcha-feedback" style="margin-left: 15px; font-weight: bold; min-height: 20px;">Glissez les éléments verts.</div>
                <input type="hidden" id="captcha-score" name="captcha_score" value="0">
                <input type="hidden" id="captcha-elements" name="captcha_elements" value="{}">
            </div>

            <div class="captcha-footer" id="captcha-actions">
                 <button type="button" id="reset-captcha" class="btn btn-secondary">Recommencer</button>
                 <div class="captcha-attempts" id="attempts-left">Tentatives restantes : 3</div> 
                 <button type="button" id="validate-captcha" class="btn btn-success">Valider</button>
            </div>
        </form>
        <?php
        // --- FIN : NOUVELLE STRUCTURE HTML --- 

        exit; 
    }

    /**
     * Router pour les actions du CaptchaController.
     */
    public static function route() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $controller = new CaptchaController();
        $action = isset($_GET['action']) ? $_GET['action'] : 'default';
        
        switch ($action) {
            case 'getEcoCaptchaHTML':
                $controller->getEcoCaptchaHTML();
                break;
            case 'validate':
                $controller->validate();
                break;
            case 'checkToken':
                echo json_encode(['valid' => $controller->checkToken()]);
                break;
            case 'useToken':
                echo json_encode(['success' => $controller->useToken()]);
                break;
            case 'cleanupOldTokens':
                $controller->cleanupOldTokens();
                echo json_encode(['success' => true]);
                break;
            default:
                // Log l'action inconnue au lieu de rediriger silencieusement
                error_log("[CaptchaController] Action inconnue reçue: " . $action);
                header("HTTP/1.1 404 Not Found");
                echo json_encode(['success' => false, 'message' => 'Action Captcha inconnue.']);
                exit;
        }
    }
}
<?php
session_start();
// Initialiser les variables requises par header.php
$pageTitle = "Connexion";
$activePage = "login";

// Réinitialisation du compteur de tentatives de captcha lors de l'accès à la page de login
if (!isset($_GET['keep_attempts'])) {
    $_SESSION['captcha_attempts'] = 0;
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Ajouter le lien vers style11.css -->
<link rel="stylesheet" href="/WaveNet/views/assets/css/style11.css">
<!-- Inclure le fichier captcha.css -->
<link rel="stylesheet" href="/WaveNet/views/assets/css/captcha.css">
<!-- Ajouter Font Awesome 6.4.0 -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="report-form">
                <h2>Connexion</h2>
                <p>Veuillez vous connecter pour accéder à votre compte WaveNet</p>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div style="background-color: rgba(244, 67, 54, 0.1); border-left: 3px solid #f44336; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                        <p style="margin: 0; color: #d32f2f;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert-success">
                        <p><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
                    </div>
                <?php endif; ?>
                
                <!-- Formulaire de connexion normal (toujours affiché) -->
                <form id="login-form" action="/WaveNet/controller/UserController.php?action=checkCredentials" method="post">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email">
                        <div class="error-message" id="email-error"></div>
                    </div>
                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <div style="position: relative;">
                            <input type="password" class="form-control" id="password" name="password">
                            <button type="button" id="togglePassword" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer;">
                                <i class="fa fa-eye"></i>
                            </button>
                        </div>
                        <div class="error-message" id="password-error"></div>
                    </div>
                    <div class="form-group">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Se souvenir de moi</label>
                    </div>
                    
                    <!-- Remplacement de la checkbox par le bouton déclencheur -->
                    <div class="form-group captcha-trigger-container" style="margin-top: 15px;">
                        <button type="button" id="captcha-trigger-button" class="btn btn-secondary" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 10px; padding: 10px;">
                            <i class="fas fa-shield-alt"></i>
                            <span>Vérification de sécurité</span>
                        </button>
                        <div id="captcha-status" style="margin-top: 10px; text-align: center; height: 20px;">
                            <!-- Statut du captcha (ex: checkmark) ira ici -->
                        </div>
                    </div>
                    
                    <button type="submit" id="login-submit-button" class="btn btn-primary" style="margin-top: 20px;" disabled>Se connecter</button>
                    <div id="login-spinner" class="spinner" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i> Vérification en cours...
                    </div>
                    <!-- Div for displaying AJAX login feedback -->
                    <div id="login-feedback" style="margin-top: 15px; color: red; text-align: center; min-height: 20px;"></div>
                </form>
                
                <div class="mt-3 text-center">
                    <p>Pas encore de compte? <a href="register.php">Inscrivez-vous</a></p>
                    <p><a href="forgot_password_request.php">Mot de passe oublié?</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour l'eco-captcha -->
<div id="captcha-modal" class="wavenet-captcha-modal modal" style="display: none;">
    <div class="modal-content captcha-content">
        <span class="close-modal">&times;</span>
        <h3>Vérification de sécurité</h3>
        <p>Pour finaliser votre connexion, veuillez compléter le CAPTCHA ci-dessous</p>
        
        <div id="eco-captcha-container" class="wavenet-captcha-container">
            <!-- Le contenu du eco-captcha sera chargé ici dynamiquement -->
        </div>
    </div>
</div>

<!-- Bouton de secours pour ouvrir le CAPTCHA dans un iframe isolé en cas de conflit -->
<div class="fallback-container" style="display: none; margin-top: 15px; text-align: center;">
    <p style="color: #d32f2f; margin-bottom: 10px;">Problème d'affichage détecté. Utilisez cette méthode alternative :</p>
    <button id="captcha-fallback-button" class="btn btn-warning" style="padding: 8px 15px;">
        <i class="fas fa-shield-alt"></i> Ouvrir le CAPTCHA en mode isolé
    </button>
</div>

<style>
/* Namespace spécifique pour isoler les styles du CAPTCHA */
.wavenet-captcha-modal {
    display: none;
    position: fixed !important;
    z-index: 9999 !important; /* Z-index élevé pour être sûr qu'il passe au-dessus de tout */
    left: 0 !important;
    top: 0 !important;
    width: 100% !important;
    height: 100% !important;
    overflow: auto !important;
    background-color: rgba(0, 0, 0, 0.6) !important;
    text-align: center !important;
}

.wavenet-captcha-modal .modal-content {
    background-color: #fff !important;
    margin: 10% auto !important;
    padding: 15px !important;
    border-radius: 8px !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2) !important;
    width: auto !important;
    max-width: 420px !important;
    position: relative !important;
    display: inline-block !important;
    text-align: left !important;
}

.wavenet-captcha-modal .close-modal {
    position: absolute !important;
    top: 8px !important;
    right: 12px !important;
    font-size: 22px !important;
    font-weight: bold !important;
    cursor: pointer !important;
    color: #777 !important;
}

.wavenet-captcha-modal .close-modal:hover {
    color: #333 !important;
}

/* Assurez-vous que le conteneur du captcha ne soit pas affecté par d'autres styles */
.wavenet-captcha-container {
    max-width: 100% !important;
    position: relative !important;
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

/* Réinitialiser tous les styles potentiellement hérités pour les enfants du container */
.wavenet-captcha-container * {
    visibility: visible !important;
    display: block !important;
    opacity: 1 !important;
}

/* Styles spécifiques pour différents types d'éléments dans le conteneur */
.wavenet-captcha-container .element {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.wavenet-captcha-container .elements-list {
    display: grid !important;
}

.wavenet-captcha-container #city-grid {
    display: flex !important;
    flex-wrap: wrap !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    
    if (togglePassword) {
        togglePassword.addEventListener('click', function(e) {
            e.preventDefault();
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.querySelectorAll('.fa').forEach(icon => {
                icon.classList.toggle('d-none');
            });
        });
    }
    
    // Formulaire de validation
    const loginForm = document.getElementById('login-form');
    const emailInput = document.getElementById('email');
    const emailError = document.getElementById('email-error');
    const passwordInput = document.getElementById('password');
    const passwordError = document.getElementById('password-error');
    
    // Validation lors de la soumission du formulaire
    loginForm.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Validation de l'email
        if (!emailInput.value.trim()) {
            emailError.textContent = 'Veuillez saisir votre adresse email';
            emailError.style.color = 'red';
            isValid = false;
        } else if (!isValidEmail(emailInput.value.trim())) {
            emailError.textContent = 'Veuillez saisir une adresse email valide';
            emailError.style.color = 'red';
            isValid = false;
        } else {
            emailError.textContent = '';
        }
        
        // Validation du mot de passe
        if (!passwordInput.value.trim()) {
            passwordError.textContent = 'Veuillez saisir votre mot de passe';
            passwordError.style.color = 'red';
            isValid = false;
        } else {
            passwordError.textContent = '';
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
    
    // Validation en temps réel
    emailInput.addEventListener('input', function() {
        if (this.value.trim() && isValidEmail(this.value.trim())) {
            emailError.textContent = '';
        }
    });
    
    passwordInput.addEventListener('input', function() {
        if (this.value.trim()) {
            passwordError.textContent = '';
        }
    });
    
    // Fonction pour valider l'email
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // NE PAS INITIALISER LE CAPTCHA ICI - C'est maintenant géré par eco-captcha-handler.js
    // pour éviter les conflits d'initialisation
});

// Script pour gérer le fallback en cas de problème d'affichage
document.addEventListener('DOMContentLoaded', function() {
    // Cette fonction sera appelée si le diagnostic détecte un problème
    window.activateCaptchaFallback = function() {
        document.querySelector('.fallback-container').style.display = 'block';
        
        // Gestionnaire pour le bouton de secours
        document.getElementById('captcha-fallback-button').addEventListener('click', function() {
            // Ouvrir un nouvel onglet/fenêtre avec juste le CAPTCHA
            const captchaWindow = window.open('/WaveNet/controller/CaptchaController.php?action=getEcoCaptchaHTML&standalone=1', 
                                             'captcha_window', 
                                             'width=450,height=550,resizable=yes');
            
            // Communiquer avec la fenêtre ouverte
            if (captchaWindow) {
                // Fonction pour recevoir la validation du CAPTCHA depuis l'iframe
                window.handleExternalCaptchaSuccess = function(data) {
                    console.log('[Fallback] CAPTCHA validé avec succès depuis la fenêtre externe');
                    // Simuler la validation dans la page principale
                    if (window.handleEcoCaptchaSuccess) {
                        window.handleEcoCaptchaSuccess(data);
                    }
                    captchaWindow.close();
                };
            } else {
                alert("Le navigateur a bloqué l'ouverture de la fenêtre. Veuillez autoriser les popups pour ce site.");
            }
        });
    };
    
    // Si après 5 secondes les éléments ne sont toujours pas visibles, activer le fallback
    setTimeout(function() {
        const elements = document.querySelectorAll('.element.drag-item');
        let allVisible = true;
        
        elements.forEach(el => {
            const style = window.getComputedStyle(el);
            if (style.display === 'none' || style.visibility === 'hidden' || style.opacity === '0') {
                allVisible = false;
            }
        });
        
        if (!allVisible && elements.length > 0) {
            console.warn('[Fallback] Problème d\'affichage détecté, activation du mode de secours');
            window.activateCaptchaFallback();
        }
    }, 5000);
});
</script>

<!-- Inclusion du nouveau fichier JS (defer pour exécution après parsing HTML) -->
<script src="/WaveNet/views/assets/js/eco-captcha-handler.js" defer></script>

<!-- Ajout de règles CSS spécifiques pour neutraliser les conflits avec style11.css -->
<style id="captcha-override-styles">
/* Cette règle désactive tous les styles de style11.css pour nos éléments */
.wavenet-captcha-modal *,
.wavenet-captcha-container * {
    all: revert !important;
}

/* Puis nous réappliquons nos styles spécifiques avec une spécificité plus élevée */
.wavenet-captcha-modal.modal {
    display: none;
    position: fixed !important;
    z-index: 99999 !important; /* Augmenté davantage */
    left: 0 !important;
    top: 0 !important;
    width: 100% !important;
    height: 100% !important;
    background-color: rgba(0, 0, 0, 0.6) !important;
}

.wavenet-captcha-modal .modal-content {
    background-color: #fff !important;
    margin: 10% auto !important;
    padding: 20px !important;
    border-radius: 8px !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3) !important;
    width: 400px !important;
    max-width: 90% !important;
    position: relative !important;
}

.wavenet-captcha-container .element.drag-item {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 48px !important;
    height: 48px !important;
    cursor: pointer !important;
    border-radius: 6px !important;
    margin: 5px !important;
    position: relative !important;
    background-color: white !important;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2) !important;
}

.wavenet-captcha-container .element.drag-item.durable {
    border: 2px solid #4caf50 !important;
    background-color: #e8f5e9 !important;
}

.wavenet-captcha-container .element.drag-item.non-durable {
    border: 2px solid #f44336 !important;
    background-color: #ffebee !important;
}

.wavenet-captcha-container .element.drag-item i {
    font-size: 1.5rem !important;
    display: inline-block !important;
    pointer-events: none !important;
    color: inherit !important;
    font-family: "Font Awesome 6 Free" !important;
    font-weight: 900 !important;
}

.wavenet-captcha-container .element.drag-item.durable i {
    color: #4caf50 !important;
}

.wavenet-captcha-container .element.drag-item.non-durable i {
    color: #f44336 !important;
}

.wavenet-captcha-container .elements-list {
    display: grid !important;
    grid-template-columns: repeat(5, 1fr) !important;
    gap: 10px !important;
    margin-top: 15px !important;
}

.wavenet-captcha-container #city-grid {
    display: flex !important;
    flex-wrap: wrap !important;
    min-height: 80px !important;
    border: 2px dashed #ccc !important;
    padding: 10px !important;
    background-color: #f8f8f8 !important;
    margin-bottom: 15px !important;
    border-radius: 6px !important;
}

.wavenet-captcha-container .element-in-grid {
    width: 48px !important;
    height: 48px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    position: relative !important;
    border-radius: 6px !important;
    margin: 5px !important;
    background-color: white !important;
}

.wavenet-captcha-container .element-in-grid i {
    font-size: 1.6rem !important;
    display: inline-block !important;
    font-family: "Font Awesome 6 Free" !important;
    font-weight: 900 !important;
}

.wavenet-captcha-container .element-in-grid.durable {
    background-color: #e8f5e9 !important;
    border: 1px solid #81c784 !important;
}

.wavenet-captcha-container .element-in-grid.durable i {
    color: #4caf50 !important;
}

.wavenet-captcha-container .element-in-grid.non-durable {
    background-color: #ffebee !important;
    border: 1px solid #e57373 !important;
}

.wavenet-captcha-container .element-in-grid.non-durable i {
    color: #f44336 !important;
}
</style>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>

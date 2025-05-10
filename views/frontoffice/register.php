<?php
session_start();
$error = null;
if (isset($_SESSION['register_error'])) {
    echo '<div class="alert alert-danger" style="color:red;">' . htmlspecialchars($_SESSION['register_error']) . '</div>';
    unset($_SESSION['register_error']);
}
try {
    require_once __DIR__ . '/../../views/includes/config.php';
    $db = connectDB();
    include_once "../../Controller/quartierC.php"; 
    $quartierC = new quartierC();
    $quartiersData = $quartierC->afficherQuartier();
    $quartiers = [];
    if (is_array($quartiersData)) {
        foreach ($quartiersData as $data) {
            $quartier = new \stdClass();
            $quartier->id = $data['idq'];
            $quartier->nom = $data['nomq'];
            $quartiers[] = $quartier;
        }
    }
} catch (Exception $e) {
    $error = "Erreur de connexion à la base de données: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription | <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../../views/assets/css/style11.css">
    <link rel="stylesheet" href="../../views/assets/css/captcha.css">
    <style>
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            border: 1px solid #f5c6cb;
        }
        .error-message {
            color: #dc3545;
            font-size: 0.85rem;
            margin-top: 5px;
        }
        .password-strength-meter {
            margin-top: 5px;
            width: 100%;
        }
        .password-strength-bar {
            height: 8px;
            border-radius: 4px;
            background-color: #ccc; /* gris par défaut */
            width: 0%;
            transition: width 0.3s, background-color 0.3s;
        }
        .password-strength-text {
            font-size: 0.85rem;
            margin-top: 5px;
            color: #666;
        }
        .strength-weak .password-strength-bar {
            background-color: #ff4d4d;
            width: 33%;
        }
        .strength-medium .password-strength-bar {
            background-color: #ffa64d; 
            width: 66%;
        }
        .strength-strong .password-strength-bar {
            background-color: #4CAF50; 
            width: 100%;
        }
    </style>
</head>
<body>
    <section class="report">
        <form class="report-form" method="post" action="/WaveNet/controller/UserController.php?action=register" id="register-form">
            <h2>Créer un compte</h2>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <div class="form-group">
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom">
                <div class="error-message" id="nom-error"></div>
            </div>
            <div class="form-group">
                <label for="prenom">Prénom</label>
                <input type="text" id="prenom" name="prenom">
                <div class="error-message" id="prenom-error"></div>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="text" id="email" name="email">
                <div class="error-message" id="email-error"></div>
            </div>
            <div class="form-group">
                <label for="quartier_id">Quartier</label>
                <select id="quartier_id" name="quartier_id">
                    <option value="">Sélectionnez votre quartier</option>
                    <?php if (!empty($quartiers)): ?>
                        <?php foreach ($quartiers as $quartier): ?>
                            <option value="<?php echo $quartier->id; ?>"><?php echo htmlspecialchars($quartier->nom); ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <div class="error-message" id="quartier-error"></div>
            </div>
            <div class="form-group">
                <label for="mot_de_passe">Mot de passe</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe">
                <div class="password-strength-meter">
                    <div class="password-strength-bar"></div>
                    <div class="password-strength-text">Force du mot de passe</div>
                </div>
                <div class="error-message" id="mot_de_passe-error"></div>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirmer le mot de passe</label>
                <input type="password" id="confirm_password" name="confirm_password">
                <div class="error-message" id="confirm_password-error"></div>
            </div>
            <button type="submit" class="btn btn-primary">S'inscrire</button>
            <p>Déjà un compte ? <a href="login.php">Se connecter</a></p>
            <p><a href="/">Retour à l'accueil</a></p>
        </form>
    </section>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const registerForm = document.getElementById('register-form');
        const nomInput = document.getElementById('nom');
        const prenomInput = document.getElementById('prenom');
        const emailInput = document.getElementById('email');
        const quartierSelect = document.getElementById('quartier_id');
        const passwordInput = document.getElementById('mot_de_passe');
        const confirmInput = document.getElementById('confirm_password');
        
        const nomError = document.getElementById('nom-error');
        const prenomError = document.getElementById('prenom-error');
        const emailError = document.getElementById('email-error');
        const quartierError = document.getElementById('quartier-error');
        const passwordError = document.getElementById('mot_de_passe-error');
        const confirmError = document.getElementById('confirm_password-error');
        
        // Validation lors de la soumission du formulaire
        registerForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Validation du nom
            if (!nomInput.value.trim()) {
                nomError.textContent = 'Veuillez saisir votre nom';
                isValid = false;
            } else {
                nomError.textContent = '';
            }
            
            // Validation du prénom
            if (!prenomInput.value.trim()) {
                prenomError.textContent = 'Veuillez saisir votre prénom';
                isValid = false;
            } else {
                prenomError.textContent = '';
            }
            
            // Validation de l'email
            if (!emailInput.value.trim()) {
                emailError.textContent = 'Veuillez saisir votre adresse email';
                isValid = false;
            } else if (!isValidEmail(emailInput.value.trim())) {
                emailError.textContent = 'Veuillez saisir une adresse email valide';
                isValid = false;
            } else {
                emailError.textContent = '';
            }
            
            // Validation du quartier
            if (!quartierSelect.value) {
                quartierError.textContent = 'Veuillez sélectionner votre quartier';
                isValid = false;
            } else {
                quartierError.textContent = '';
            }
            
            // Validation du mot de passe
            if (!passwordInput.value) {
                passwordError.textContent = 'Veuillez saisir un mot de passe';
                isValid = false;
            } else if (passwordInput.value.length < 8) {
                passwordError.textContent = 'Le mot de passe doit contenir au moins 8 caractères';
                isValid = false;
            } else {
                passwordError.textContent = '';
            }
            
            // Validation de la confirmation du mot de passe
            if (!confirmInput.value) {
                confirmError.textContent = 'Veuillez confirmer votre mot de passe';
                isValid = false;
            } else if (confirmInput.value !== passwordInput.value) {
                confirmError.textContent = 'Les mots de passe ne correspondent pas';
                isValid = false;
            } else {
                confirmError.textContent = '';
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
        
        // Validation en temps réel
        nomInput.addEventListener('input', function() {
            if (this.value.trim()) {
                nomError.textContent = '';
            }
        });
        
        prenomInput.addEventListener('input', function() {
            if (this.value.trim()) {
                prenomError.textContent = '';
            }
        });
        
        emailInput.addEventListener('input', function() {
            if (this.value.trim() && isValidEmail(this.value.trim())) {
                emailError.textContent = '';
            }
        });
        
        quartierSelect.addEventListener('change', function() {
            if (this.value) {
                quartierError.textContent = '';
            }
        });
        
        // Fonction pour vérifier la force du mot de passe et mettre à jour la barre
        function checkPasswordStrength(password) {
            const strengthMeter = document.querySelector('.password-strength-meter');
            const strengthText = document.querySelector('.password-strength-text');
            
            // Réinitialiser les classes
            strengthMeter.classList.remove('strength-weak', 'strength-medium', 'strength-strong');
            
            // Si le champ est vide
            if (password.length === 0) {
                strengthText.textContent = 'Force du mot de passe';
                return;
            }
            
            // Critères de force
            const hasMinLength = password.length >= 8;
            const hasUpperCase = /[A-Z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSpecialChar = /[^A-Za-z0-9]/.test(password);
            
            // Calculer le score
            let score = 0;
            if (hasMinLength) score++;
            if (hasUpperCase) score++;
            if (hasNumber) score++;
            if (hasSpecialChar) score++;
            
            // Mettre à jour l'interface en fonction du score
            if (score === 0) {
                strengthText.textContent = 'Force du mot de passe';
            } else if (score <= 2) {
                strengthMeter.classList.add('strength-weak');
                strengthText.textContent = 'Faible';
            } else if (score === 3) {
                strengthMeter.classList.add('strength-medium');
                strengthText.textContent = 'Moyen';
            } else {
                strengthMeter.classList.add('strength-strong');
                strengthText.textContent = 'Fort';
            }
        }
        
        // Ajouter la vérification de force pour le mot de passe
        passwordInput.addEventListener('input', function() {
            checkPasswordStrength(this.value);
            
            // Vérification en temps réel
            const hasMinLength = this.value.length >= 8;
            const hasUpperCase = /[A-Z]/.test(this.value);
            const hasNumber = /[0-9]/.test(this.value);
            const hasSpecialChar = /[^A-Za-z0-9]/.test(this.value);
            
            if (hasMinLength && hasUpperCase && hasNumber && hasSpecialChar) {
                passwordError.textContent = '';
            } else {
                const missingCriteria = [];
                if (!hasMinLength) missingCriteria.push('8 caractères minimum');
                if (!hasUpperCase) missingCriteria.push('1 majuscule');
                if (!hasNumber) missingCriteria.push('1 chiffre');
                if (!hasSpecialChar) missingCriteria.push('1 caractère spécial');
                
                passwordError.textContent = 'Le mot de passe doit contenir: ' + missingCriteria.join(', ');
            }
            
            // Vérifier également la confirmation
            if (confirmInput.value && this.value !== confirmInput.value) {
                confirmError.textContent = 'Les mots de passe ne correspondent pas';
            } else if (confirmInput.value) {
                confirmError.textContent = '';
            }
        });
        
        confirmInput.addEventListener('input', function() {
            if (this.value === passwordInput.value) {
                confirmError.textContent = '';
            } else {
                confirmError.textContent = 'Les mots de passe ne correspondent pas';
            }
        });
        
        // Fonction pour valider l'email
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
    });
    </script>
</body>
</html>

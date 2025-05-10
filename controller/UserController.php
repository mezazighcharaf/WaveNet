<?php
error_log("[UserController.php] Fichier inclus."); // LOG AJOUTÉ TOUT EN HAUT

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    UserController::route();
}

class UserController {
    public function ajouterUtilisateur() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = $_POST['nom'] ?? '';
            $prenom = $_POST['prenom'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['mot_de_passe'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
    
            if (empty($nom) || empty($prenom) || empty($email) || empty($password) || empty($confirm_password)) {
                $_SESSION['register_error'] = "Veuillez remplir tous les champs.";
                header('Location: /WaveNet/views/frontoffice/register.php');
                exit;
            }
    
            if ($password !== $confirm_password) {
                $_SESSION['register_error'] = "Les mots de passe ne correspondent pas.";
                header('Location: /WaveNet/views/frontoffice/register.php');
                exit;
            }
    
            // Vérifier la robustesse du mot de passe
            list($isStrongPassword, $passwordError) = $this->checkPasswordStrength($password);
            if (!$isStrongPassword) {
                $_SESSION['register_error'] = $passwordError;
                header('Location: /WaveNet/views/frontoffice/register.php');
                exit;
            }
    
            require_once __DIR__ . '/../models/Utilisateur.php';
            require_once __DIR__ . '/../views/includes/config.php';
    
            $db = connectDB();
    
            $existingUser = Utilisateur::findByEmail($db, $email);
            if ($existingUser) {
                $_SESSION['register_error'] = "Cet email est déjà utilisé.";
                header('Location: /WaveNet/views/frontoffice/register.php');
                exit;
            }
    
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $data = [
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email,
                'mot_de_passe' => $hashedPassword,
                'niveau' => 'client',
                'point_vert' => 0,
                'quartier_id' => null
            ];
    
            try {
                $result = Utilisateur::create($db, $data);
    
                if ($result) {
                    $newUser = Utilisateur::findByEmail($db, $email);
    
                    if ($newUser) {
                        $_SESSION['user_id'] = $newUser->getId();
                        $_SESSION['success_message'] = "Inscription réussie ! Bienvenue sur WaveNet.";
                        header('Location: /WaveNet/views/frontoffice/userDashboard.php');
                        exit;
                    } else {
                        throw new Exception("Erreur lors de la récupération du nouvel utilisateur.");
                    }
                } else {
                    throw new Exception("Erreur lors de l'insertion dans la base de données.");
                }
            } catch (Exception $e) {
                error_log("Erreur d'inscription : " . $e->getMessage());
                $_SESSION['register_error'] = "Erreur lors de l'inscription : " . $e->getMessage();
                header('Location: /WaveNet/views/frontoffice/register.php');
                exit;
            }
        }
    }
    public function consulterUtilisateur() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: /WaveNet/views/frontoffice/login.php');
            exit;
        }
        
        require_once __DIR__ . '/../models/Utilisateur.php';
        require_once __DIR__ . '/../views/includes/config.php';
        
        $db = connectDB();
        
        $user = Utilisateur::findById($db, $_SESSION['user_id']);
        if ($user) {
            require __DIR__ . '/../views/frontoffice/consulterUtilisateur.php';
        } else {
            $_SESSION['error'] = "Utilisateur non trouvé.";
            header('Location: /WaveNet/views/frontoffice/userDashboard.php');
            exit;
        }
    }
    public function modifierUtilisateur() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: /WaveNet/views/frontoffice/login.php');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../models/Utilisateur.php';
            require_once __DIR__ . '/../views/includes/config.php';
            
            $db = connectDB();
            
            $user = Utilisateur::findById($db, $_SESSION['user_id']);
            if ($user) {
                $nom = $_POST['nom'] ?? $user->getNom();
                $prenom = $_POST['prenom'] ?? $user->getPrenom();
                $email = $_POST['email'] ?? $user->getEmail();
                $data = [
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'email' => $email
                ];
                $stmt = $db->prepare("UPDATE UTILISATEUR SET nom = :nom, prenom = :prenom, email = :email WHERE id_utilisateur = :id");
                $stmt->execute([
                    'nom' => $data['nom'],
                    'prenom' => $data['prenom'],
                    'email' => $data['email'],
                    'id' => $_SESSION['user_id']
                ]);
                $_SESSION['success'] = "Informations mises à jour.";
                header('Location: /WaveNet/views/frontoffice/userDashboard.php');
                exit;
            } else {
                $_SESSION['error'] = "Utilisateur non trouvé.";
                header('Location: /WaveNet/views/frontoffice/userDashboard.php');
                exit;
            }
        }
    }
    public function supprimerUtilisateur() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: /WaveNet/views/frontoffice/login.php');
            exit;
        }
        
        require_once __DIR__ . '/../models/Utilisateur.php';
        require_once __DIR__ . '/../views/includes/config.php';
        
        $db = connectDB();
        
        $stmt = $db->prepare("DELETE FROM UTILISATEUR WHERE id_utilisateur = :id");
        $stmt->execute(['id' => $_SESSION['user_id']]);
        session_destroy();
        header('Location: /WaveNet/views/frontoffice/register.php');
        exit;
    }
    public function listerUtilisateurs() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: /WaveNet/views/frontoffice/login.php');
            exit;
        }
        
        require_once __DIR__ . '/../models/Utilisateur.php';
        require_once __DIR__ . '/../views/includes/config.php';
        
        $db = connectDB();
        
        $user = Utilisateur::findById($db, $_SESSION['user_id']);
        if ($user && $user->getNiveau() === 'admin') {
            $stmt = $db->query("SELECT * FROM UTILISATEUR");
            $utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            require __DIR__ . '/../views/backoffice/listeUtilisateurs.php';
        } else {
            $_SESSION['error'] = "Accès refusé.";
            header('Location: /WaveNet/views/frontoffice/userDashboard.php');
            exit;
        }
    }
    public function supprimerUtilisateurAdmin() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: /WaveNet/views/frontoffice/login.php');
            exit;
        }
        
        require_once __DIR__ . '/../models/Utilisateur.php';
        require_once __DIR__ . '/../views/includes/config.php';
        
        $db = connectDB();
        
        $user = Utilisateur::findById($db, $_SESSION['user_id']);
        if ($user && $user->getNiveau() === 'admin') {
            $id = $_GET['id'] ?? null;
            if ($id) {
                $stmt = $db->prepare("DELETE FROM UTILISATEUR WHERE id_utilisateur = :id");
                $stmt->execute(['id' => $id]);
                $_SESSION['success'] = "Utilisateur supprimé.";
            }
            header('Location: /WaveNet/views/backoffice/listeUtilisateurs.php');
            exit;
        } else {
            $_SESSION['error'] = "Accès refusé.";
            header('Location: /WaveNet/views/frontoffice/userDashboard.php');
            exit;
        }
    }
    public function login() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if (empty($email) || empty($password)) {
                $_SESSION['login_error'] = "Veuillez remplir tous les champs.";
                header('Location: /WaveNet/views/frontoffice/login.php');
                exit;
            }
            
            require_once __DIR__ . '/../models/Utilisateur.php';
            require_once __DIR__ . '/../views/includes/config.php';
            
            try {
                $db = connectDB();
                if (!$db) {
                    throw new Exception("Erreur de connexion à la base de données.");
                }
                
                $columnExistsQuery = $db->query("SHOW COLUMNS FROM UTILISATEUR LIKE 'bloque'");
                $columnExists = $columnExistsQuery->rowCount() > 0;
                
                $user = Utilisateur::findByEmail($db, $email);
                
                if ($user && password_verify($password, $user->getMotDePasse())) {
                    // Journaliser la connexion réussie
                    require_once __DIR__ . '/../models/security_functions.php';
                    logConnection($user->getId(), true);
                    
                    // Vérifier si l'utilisateur est bloqué
                    if ($columnExists) {
                        $stmt = $db->prepare("SELECT bloque FROM UTILISATEUR WHERE email = :email");
                        $stmt->execute(['email' => $email]);
                        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if (isset($userData['bloque']) && $userData['bloque'] == 1) {
                            $_SESSION['login_error'] = "Votre compte a été bloqué. Veuillez contacter l'administrateur.";
                            header('Location: /WaveNet/views/frontoffice/login.php');
                            exit;
                        }
                    }
                    
                    // Vérifier si l'authentification à deux facteurs est activée
                    $twofa_enabled = false;
                    $stmt = $db->prepare("SHOW COLUMNS FROM UTILISATEUR LIKE 'twofa_enabled'");
                    $stmt->execute();
                    if ($stmt->rowCount() > 0) {
                        $stmt = $db->prepare("SELECT twofa_enabled FROM UTILISATEUR WHERE id_utilisateur = :id");
                        $stmt->execute(['id' => $user->getId()]);
                        $twofa_data = $stmt->fetch(PDO::FETCH_ASSOC);
                        $twofa_enabled = isset($twofa_data['twofa_enabled']) && $twofa_data['twofa_enabled'] == 1;
                    }
                    
                    if ($twofa_enabled) {
                        // Stocker l'ID utilisateur temporairement pour la vérification 2FA
                        $_SESSION['temp_user_id'] = $user->getId();
                        $_SESSION['auth_requires_2fa'] = true;
                        
                        // Effacer user_id pour bloquer l'accès
                        if (isset($_SESSION['user_id'])) {
                            unset($_SESSION['user_id']);
                        }
                        
                        // Rediriger vers la page de vérification 2FA
                        header('Location: /WaveNet/controller/UserController.php?action=verifier2FA');
                        exit;
                    }
                    
                    // Si pas de 2FA, connecter l'utilisateur normalement
                    $_SESSION['user_id'] = $user->getId();
                    $_SESSION['user_nom'] = $user->getNom();
                    $_SESSION['user_prenom'] = $user->getPrenom();
                    $_SESSION['user_niveau'] = $user->getNiveau();
                    
                    // Redirection basée sur le niveau de l'utilisateur
                    if ($user->getNiveau() === 'admin') {
                        // Rediriger l'administrateur vers la page d'accueil
                        header('Location: /WaveNet/views/backoffice/index.php');
                    } else {
                        // Rediriger les utilisateurs normaux vers le tableau de bord
                        header('Location: /WaveNet/views/frontoffice/userDashboard.php');
                    }
                    exit;
                } else {
                    // Journaliser la connexion échouée
                    if ($user) {
                        require_once __DIR__ . '/../models/security_functions.php';
                        logConnection($user->getId(), false, "Mot de passe incorrect");
                    }
                    
                    $_SESSION['login_error'] = "Email ou mot de passe incorrect.";
                    header('Location: /WaveNet/views/frontoffice/login.php');
                    exit;
                }
            } catch (Exception $e) {
                error_log("Erreur de connexion: " . $e->getMessage());
                $_SESSION['login_error'] = "Erreur de connexion: " . $e->getMessage();
                header('Location: /WaveNet/views/frontoffice/login.php');
                exit;
            }
        } else {
            // Si ce n'est pas une requête POST, rediriger vers le formulaire de connexion
            header('Location: /WaveNet/views/frontoffice/login.php');
            exit;
        }
    }
    // Méthode pour traiter l'inscription
    public function register() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = $_POST['nom'] ?? '';
            $prenom = $_POST['prenom'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['mot_de_passe'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            $id_quartier = !empty($_POST['quartier_id']) ? intval($_POST['quartier_id']) : null;
            
            // Log the input for debugging
            error_log("Register attempt - Email: " . $email . ", Quartier ID: " . ($id_quartier ?? 'null'));
            
            if (empty($nom) || empty($prenom) || empty($email) || empty($password) || empty($confirm_password) || empty($id_quartier)) {
                $_SESSION['register_error'] = "Veuillez remplir tous les champs obligatoires.";
                header('Location: /WaveNet/views/frontoffice/register.php');
                exit;
            }
            
            if ($password !== $confirm_password) {
                $_SESSION['register_error'] = "Les mots de passe ne correspondent pas.";
                header('Location: /WaveNet/views/frontoffice/register.php');
                exit;
            }
            
            // Vérifier la robustesse du mot de passe
            list($isStrongPassword, $passwordError) = $this->checkPasswordStrength($password);
            if (!$isStrongPassword) {
                $_SESSION['register_error'] = $passwordError;
                header('Location: /WaveNet/views/frontoffice/register.php');
                exit;
            }
            
            require_once __DIR__ . '/../models/Utilisateur.php';
            require_once __DIR__ . '/../views/includes/config.php';
            
            try {
                $db = connectDB();
                if (!$db) {
                    throw new Exception("Erreur de connexion à la base de données.");
                }
                
                // Vérifier si l'email existe déjà
                $existingUser = Utilisateur::findByEmail($db, $email);
                if ($existingUser) {
                    $_SESSION['register_error'] = "Cet email est déjà utilisé.";
                    header('Location: /WaveNet/views/frontoffice/register.php');
                    exit;
                }
                
                // Vérifier si le quartier existe
                require_once __DIR__ . '/../models/Quartier.php';
                $quartierData = Quartier::findById($db, $id_quartier);
                if (!$quartierData) {
                    $_SESSION['register_error'] = "Le quartier sélectionné n'existe pas.";
                    header('Location: /WaveNet/views/frontoffice/register.php');
                    exit;
                }
                
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                $userData = [
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'email' => $email,
                    'mot_de_passe' => $hashedPassword,
                    'niveau' => 'client',
                    'point_vert' => 0,
                    'idq' => $id_quartier
                ];
                
                $userId = Utilisateur::create($db, $userData);
                if (!$userId) {
                    throw new Exception("Erreur lors de la création de l'utilisateur.");
                }
                
                $newUser = Utilisateur::findById($db, $userId);
                if (!$newUser) {
                    throw new Exception("Erreur lors de la récupération du nouvel utilisateur.");
                }
                
                $_SESSION['user_id'] = $newUser->getId();
                $_SESSION['user_nom'] = $newUser->getNom();
                $_SESSION['user_prenom'] = $newUser->getPrenom();
                $_SESSION['user_niveau'] = $newUser->getNiveau();
                $_SESSION['success_message'] = "Inscription réussie ! Bienvenue sur " . SITE_NAME . ".";
                
                header('Location: /WaveNet/views/frontoffice/userDashboard.php');
                exit;
                
            } catch (Exception $e) {
                error_log("Erreur d'inscription: " . $e->getMessage());
                $_SESSION['register_error'] = "Erreur lors de l'inscription: " . $e->getMessage();
                header('Location: /WaveNet/views/frontoffice/register.php');
                exit;
            }
        } else {
            header('Location: /WaveNet/views/frontoffice/register.php');
            exit;
        }
    }
    public static function isLoggedIn() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    public function updateProfile() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            header("Location: /WaveNet/views/frontoffice/login.php");
            exit;
        }
        
        // Récupérer l'ID utilisateur de la session
        $userId = $_SESSION['user_id'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../views/includes/config.php';
            require_once __DIR__ . '/../models/Utilisateur.php';
            
            $db = connectDB();
            if (!$db) {
                $_SESSION['error_messages'] = ["Erreur de connexion à la base de données."];
                header("Location: /WaveNet/views/frontoffice/editProfile.php");
                exit;
            }
            
            // Récupérer l'utilisateur actuel
            $user = Utilisateur::findById($db, $userId);
            if (!$user) {
                $_SESSION['error_messages'] = ["Utilisateur non trouvé."];
                header("Location: /WaveNet/views/frontoffice/editProfile.php");
                exit;
            }
            
            // Récupérer les données du formulaire
            $nom = $_POST['nom'] ?? '';
            $prenom = $_POST['prenom'] ?? '';
            $email = $_POST['email'] ?? '';
            $idQuartier = !empty($_POST['id_quartier']) ? $_POST['id_quartier'] : null;
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $newsletter = isset($_POST['newsletter']) ? 1 : 0;
            $evenements = isset($_POST['evenements']) ? 1 : 0;
            
            $errors = [];
            
            if (empty($nom)) {
                $errors[] = "Le nom est requis.";
            }
            
            if (empty($prenom)) {
                $errors[] = "Le prénom est requis.";
            }
            
            if (empty($email)) {
                $errors[] = "L'email est requis.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "L'email n'est pas valide.";
            } else {
                $stmt = $db->prepare("SELECT id_utilisateur FROM UTILISATEUR WHERE email = :email AND id_utilisateur != :id");
                $stmt->execute(['email' => $email, 'id' => $userId]);
                
                if ($stmt->rowCount() > 0) {
                    $errors[] = "Cet email est déjà utilisé par un autre compte.";
                }
            }
            
            if (!empty($newPassword) || !empty($confirmPassword)) {
                if (empty($currentPassword)) {
                    $errors[] = "Le mot de passe actuel est requis pour changer votre mot de passe.";
                } elseif (!password_verify($currentPassword, $user->getMotDePasse())) {
                    $errors[] = "Le mot de passe actuel est incorrect.";
                }
                
                if (strlen($newPassword) < 8) {
                    $errors[] = "Le nouveau mot de passe doit contenir au moins 8 caractères.";
                }
                
                if ($newPassword !== $confirmPassword) {
                    $errors[] = "Les mots de passe ne correspondent pas.";
                }
            }
            
            if (!empty($errors)) {
                $_SESSION['error_messages'] = $errors;
                header("Location: /WaveNet/views/frontoffice/editProfile.php");
                exit;
            }
            
            $user->setNom($nom);
            $user->setPrenom($prenom);
            $user->setEmail($email);
            $user->setIdQuartier($idQuartier);
            $checkColumns = $db->query("SHOW COLUMNS FROM UTILISATEUR LIKE 'newsletter'");
            if ($checkColumns->rowCount() == 0) {
                // Ajouter les colonnes si elles n'existent pas
                $db->exec("ALTER TABLE UTILISATEUR ADD COLUMN newsletter TINYINT(1) DEFAULT 0, ADD COLUMN evenements TINYINT(1) DEFAULT 0");
            }
            
            if (method_exists($user, 'setNewsletter')) {
                $user->setNewsletter($newsletter);
            }
            
            if (method_exists($user, 'setEvenements')) {
                $user->setEvenements($evenements);
            }
            
            if (!empty($newPassword) && !empty($currentPassword) && password_verify($currentPassword, $user->getMotDePasse())) {
                // Enregistrer l'ancien mot de passe dans l'historique
                $stmt = $db->prepare("INSERT INTO password_history (id_utilisateur, mot_de_passe_hash) VALUES (?, ?)");
                $stmt->execute([$userId, $user->getMotDePasse()]);
                
                // Mise à jour du mot de passe (code existant)
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $user->setMotDePasse($hashedPassword);
            }
            
            try {
                $result = $user->update($db);
                
                if ($result) {
                    $_SESSION['user_nom'] = $user->getNom();
                    $_SESSION['user_prenom'] = $user->getPrenom();
                    
                    $_SESSION['success_message'] = "Votre profil a été mis à jour avec succès.";
                } else {
                    $_SESSION['error_messages'] = ["Une erreur est survenue lors de la mise à jour de votre profil."];
                }
            } catch (Exception $e) {
                $_SESSION['error_messages'] = ["Erreur: " . $e->getMessage()];
            }
            
            header("Location: /WaveNet/views/frontoffice/editProfile.php");
            exit;
        } else {
            header("Location: /WaveNet/views/frontoffice/editProfile.php");
            exit;
        }
    }
    // Router pour appeler la méthode en fonction de l'action demandée
    public static function route() {
        error_log("[UserController::route] Méthode route() démarrée."); // LOG AJOUTÉ
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $controller = new UserController();
        // Log GET and POST data for debugging routing
        error_log("[UserController::route] GET data: " . print_r($_GET, true));
        error_log("[UserController::route] POST data: " . print_r($_POST, true));

        $action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : 'default');
        error_log("[UserController::route] Action détectée: " . $action); // LOG AJOUTÉ

        switch ($action) {
            case 'login':
                error_log("[UserController::route] Exécution case 'login'."); // LOG AJOUTÉ
                $controller->login();
                break;
            case 'register':
                error_log("[UserController::route] Exécution case 'register'."); // LOG AJOUTÉ
                $controller->register();
                break;
            case 'logout':
                error_log("[UserController::route] Exécution case 'logout'."); // LOG AJOUTÉ
                $controller->logout();
                break;
            case 'supprimerUtilisateurAdmin':
                error_log("[UserController::route] Exécution case 'supprimerUtilisateurAdmin'."); // LOG AJOUTÉ
                $controller->supprimerUtilisateurAdmin();
                break;
            case 'bloquerUtilisateur':
                error_log("[UserController::route] Exécution case 'bloquerUtilisateur'."); // LOG AJOUTÉ
                $controller->bloquerUtilisateur();
                break;
            case 'debloquerUtilisateur':
                error_log("[UserController::route] Exécution case 'debloquerUtilisateur'."); // LOG AJOUTÉ
                $controller->debloquerUtilisateur();
                break;
            case 'changerNiveau':
                error_log("[UserController::route] Exécution case 'changerNiveau'."); // LOG AJOUTÉ
                $controller->changerNiveau();
                break;
            case 'ajouterTransport':
                error_log("[UserController::route] Exécution case 'ajouterTransport'."); // LOG AJOUTÉ
                $controller->ajouterTransport();
                break;
            case 'updateProfile':
                error_log("[UserController::route] Exécution case 'updateProfile'."); // LOG AJOUTÉ
                $controller->updateProfile();
                break;
            case 'gerer2FA':
                error_log("[UserController::route] Exécution case 'gerer2FA'."); // LOG AJOUTÉ
                $controller->gerer2FA();
                break;
            case 'generer2FASecret':
                error_log("[UserController::route] Exécution case 'generer2FASecret'."); // LOG AJOUTÉ
                $controller->generer2FASecret();
                break;
            case 'activer2FA':
                error_log("[UserController::route] Exécution case 'activer2FA'."); // LOG AJOUTÉ
                $controller->activer2FA();
                break;
            case 'desactiver2FA':
                error_log("[UserController::route] Exécution case 'desactiver2FA'."); // LOG AJOUTÉ
                $controller->desactiver2FA();
                break;
            case 'verifier2FA':
                error_log("[UserController::route] Exécution case 'verifier2FA'."); // LOG AJOUTÉ
                $controller->verifier2FA();
                break;
            case 'checkCredentials':
                error_log("[UserController::route] Exécution case 'checkCredentials'."); // LOG AJOUTÉ
                $controller->checkCredentials();
                break;
            case 'processLogin':
                error_log("[UserController::route] Exécution case 'processLogin'."); // LOG AJOUTÉ
                $controller->processLogin();
                break;
            case 'finalizeLoginAfterCaptcha':
                error_log("[UserController::route] Exécution case 'finalizeLoginAfterCaptcha'."); // LOG AJOUTÉ
                $controller->finalizeLoginAfterCaptcha();
                break;
            case 'showResetPasswordForm':
                 error_log("[UserController::route] Exécution case 'showResetPasswordForm'."); // LOG AJOUTÉ
                $controller->showResetPasswordForm();
                break;
            case 'handleForgotPasswordRequest': // <<< CIBLE
                 error_log("[UserController::route] Exécution case 'handleForgotPasswordRequest'."); // LOG AJOUTÉ
                $controller->handleForgotPasswordRequest();
                break;
            case 'handleResetPassword':
                error_log("[UserController::route] Exécution case 'handleResetPassword'."); // LOG AJOUTÉ
                $controller->handleResetPassword();
                break;
            case 'impersonate':
                error_log("[UserController::route] Exécution case 'impersonate'."); // LOG AJOUTÉ
                $controller->impersonate();
                break;
            case 'stopImpersonation':
                error_log("[UserController::route] Exécution case 'stopImpersonation'."); // LOG AJOUTÉ
                $controller->stopImpersonation();
                break;
            case 'sendEmailVerification':
                error_log("[UserController::route] Exécution case 'sendEmailVerification'."); // LOG
                $controller->sendEmailVerification();
                break;
            case 'verifyEmail':
                error_log("[UserController::route] Exécution case 'verifyEmail'."); // LOG
                $controller->verifyEmail();
                break;
            default:
                error_log("[UserController::route] Action par défaut ('" . $action . "'), redirection vers index.php."); // LOG AJOUTÉ
                header("Location: /WaveNet/views/index.php");
                exit;
        }
    }
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = array(); // Vide le tableau $_SESSION
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy(); // Détruit la session côté serveur

        header('Location: /WaveNet/index.php'); // Redirige vers la page d'accueil du site
        exit;
    }
    public function bloquerUtilisateur() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_niveau']) || $_SESSION['user_niveau'] !== 'admin') {
            header('Location: /WaveNet/views/frontoffice/login.php');
            exit;
        }
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = "ID utilisateur non spécifié.";
            header('Location: /WaveNet/views/backoffice/listeUtilisateurs.php');
            exit;
        }
        
        require_once __DIR__ . '/../views/includes/config.php';
        $db = connectDB();
        
        $stmt = $db->prepare("SELECT niveau FROM UTILISATEUR WHERE id_utilisateur = :id");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            $_SESSION['error'] = "Utilisateur non trouvé.";
            header('Location: /WaveNet/views/backoffice/listeUtilisateurs.php');
            exit;
        }
        
        if ($user['niveau'] === 'admin') {
            $_SESSION['error'] = "Impossible de bloquer un administrateur.";
            header('Location: /WaveNet/views/backoffice/listeUtilisateurs.php');
            exit;
        }
        
        try {
            $columnExistsQuery = $db->query("SHOW COLUMNS FROM UTILISATEUR LIKE 'bloque'");
            $columnExists = $columnExistsQuery->rowCount() > 0;
            
            if (!$columnExists) {
                $db->exec("ALTER TABLE UTILISATEUR ADD COLUMN bloque TINYINT(1) NOT NULL DEFAULT 0");
            }
            
            $stmt = $db->prepare("UPDATE UTILISATEUR SET bloque = 1 WHERE id_utilisateur = :id");
            $result = $stmt->execute(['id' => $id]);
            
            if ($result) {
                $_SESSION['success'] = "L'utilisateur a été bloqué avec succès.";
            } else {
                $_SESSION['error'] = "Erreur lors du blocage de l'utilisateur.";
            }
            
        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur de base de données: " . $e->getMessage();
        }
        
        header('Location: /WaveNet/views/backoffice/listeUtilisateurs.php');
        exit;
    }
    
    // Méthode pour débloquer un utilisateur (admin)
    public function debloquerUtilisateur() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Vérifier que l'utilisateur est admin
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_niveau']) || $_SESSION['user_niveau'] !== 'admin') {
            header('Location: /WaveNet/views/frontoffice/login.php');
            exit;
        }
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = "ID utilisateur non spécifié.";
            header('Location: /WaveNet/views/backoffice/listeUtilisateurs.php');
            exit;
        }
        
        require_once __DIR__ . '/../views/includes/config.php';
        $db = connectDB();
        
        // Vérifier si l'utilisateur existe
        $stmt = $db->prepare("SELECT id_utilisateur FROM UTILISATEUR WHERE id_utilisateur = :id");
        $stmt->execute(['id' => $id]);
        
        if (!$stmt->fetch()) {
            $_SESSION['error'] = "Utilisateur non trouvé.";
            header('Location: /WaveNet/views/backoffice/listeUtilisateurs.php');
            exit;
        }
        
        try {
            // Débloquer l'utilisateur
            $stmt = $db->prepare("UPDATE UTILISATEUR SET bloque = 0 WHERE id_utilisateur = :id");
            $result = $stmt->execute(['id' => $id]);
            
            if ($result) {
                $_SESSION['success'] = "L'utilisateur a été débloqué avec succès.";
            } else {
                $_SESSION['error'] = "Erreur lors du déblocage de l'utilisateur.";
            }
            
        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur de base de données: " . $e->getMessage();
        }
        
        header('Location: /WaveNet/views/backoffice/listeUtilisateurs.php');
        exit;
    }
    // Méthode pour ajouter un moyen de transport utilisé
    public function ajouterTransport() {
        // Vérifier si la session est active et si l'utilisateur est connecté
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            header("Location: /WaveNet/views/frontoffice/login.php");
            exit;
        }
        
        // Traiter le formulaire d'ajout de transport
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer les données du formulaire
            $idUtilisateur = isset($_POST['id_utilisateur']) ? intval($_POST['id_utilisateur']) : $_SESSION['user_id'];
            $typeTransport = isset($_POST['type_transport']) ? $_POST['type_transport'] : null;
            $distanceParcourue = isset($_POST['distance_parcourue']) ? floatval($_POST['distance_parcourue']) : 0;
            $frequence = isset($_POST['frequence']) ? intval($_POST['frequence']) : 0;
            $dateDerniereUtilisation = isset($_POST['date_derniere_utilisation']) && !empty($_POST['date_derniere_utilisation']) 
                ? $_POST['date_derniere_utilisation'] 
                : date('Y-m-d'); // Date actuelle par défaut
            
            // Validation des données
            $erreurs = [];
            if (empty($typeTransport)) {
                $erreurs[] = "Le type de transport est requis.";
            }
            if ($distanceParcourue <= 0) {
                $erreurs[] = "La distance parcourue doit être supérieure à zéro.";
            }
            if ($frequence < 0) {
                $erreurs[] = "La fréquence ne peut pas être négative.";
            }
            
            // Si pas d'erreurs, ajouter le transport
            if (empty($erreurs)) {
                require_once __DIR__ . '/../views/includes/config.php';
                $db = connectDB();
                require_once __DIR__ . '/../models/Transport.php';
                
                // Récupérer l'eco-index de la base de données pour ce type de transport
                try {
                    // D'abord, vérifions si la table TRANSPORT_TYPE existe
                    $tableExists = $db->query("SHOW TABLES LIKE 'TRANSPORT_TYPE'")->rowCount() > 0;
                    
                    if (!$tableExists) {
                        // Créer la table TRANSPORT_TYPE si elle n'existe pas
                        $db->exec("CREATE TABLE TRANSPORT_TYPE (
                            id_type INT AUTO_INCREMENT PRIMARY KEY,
                            nom VARCHAR(100) NOT NULL,
                            eco_index FLOAT NOT NULL
                        )");
                        
                        // Insérer les types de transport prédéfinis avec leurs éco-index
                        $typesDefault = [
                            ['Marche', 10.0],
                            ['Vélo', 9.5],
                            ['Trottinette électrique', 8.5],
                            ['Transport en commun (Bus)', 7.5],
                            ['Transport en commun (Tram/Métro)', 8.0],
                            ['Covoiturage', 6.0],
                            ['Voiture électrique', 5.0],
                            ['Voiture thermique', 3.0]
                        ];
                        
                        $insertType = $db->prepare("INSERT INTO TRANSPORT_TYPE (nom, eco_index) VALUES (?, ?)");
                        foreach ($typesDefault as $type) {
                            $insertType->execute([$type[0], $type[1]]);
                        }
                    }
                    
                    // Récupérer l'eco-index pour le type de transport sélectionné
                    $queryEcoIndex = $db->prepare("SELECT eco_index FROM TRANSPORT_TYPE WHERE nom = ?");
                    $queryEcoIndex->execute([$typeTransport]);
                    $ecoIndexData = $queryEcoIndex->fetch(PDO::FETCH_ASSOC);
                    
                    if ($ecoIndexData) {
                        $ecoIndex = floatval($ecoIndexData['eco_index']);
                    } else {
                        // Si le type n'existe pas dans la table, l'ajouter avec un index moyen
                        $ecoIndex = 5.0; // Valeur par défaut
                        $insertNewType = $db->prepare("INSERT INTO TRANSPORT_TYPE (nom, eco_index) VALUES (?, ?)");
                        $insertNewType->execute([$typeTransport, $ecoIndex]);
                    }
                    
                    // Préparation des données pour la création du transport
                    $transportData = [
                        'id_utilisateur' => $idUtilisateur,
                        'type_transport' => $typeTransport,
                        'distance_parcourue' => $distanceParcourue,
                        'frequence' => $frequence,
                        'eco_index' => $ecoIndex,
                        'date_derniere_utilisation' => $dateDerniereUtilisation
                    ];
                    
                    // Utiliser la méthode statique create() au lieu du constructeur
                    if (Transport::create($db, $transportData)) {
                        // Calculer des points verts en fonction de l'eco-index et de la distance
                        $pointsVerts = ceil($ecoIndex * $distanceParcourue * $frequence / 10);
                        
                        // Mettre à jour les points de l'utilisateur
                        require_once __DIR__ . '/../models/Utilisateur.php';
                        $utilisateur = Utilisateur::findById($db, $idUtilisateur);
                        if ($utilisateur) {
                            $pointsActuels = $utilisateur->getPointsVerts();
                            $utilisateur->setPointsVerts($pointsActuels + $pointsVerts);
                            $utilisateur->update($db);
                            
                            // Mise à jour de la session
                            $_SESSION['user_points'] = $pointsActuels + $pointsVerts;
                            
                            // Message de succès avec les points gagnés
                            $_SESSION['success_message'] = "Transport ajouté avec succès ! Vous avez gagné $pointsVerts points verts.";
                        } else {
                            $_SESSION['success_message'] = "Transport ajouté avec succès !";
                        }
                        
                        // Rediriger vers la page de gestion des transports
                        header("Location: /WaveNet/views/frontoffice/manageTransport.php");
                        exit;
                    } else {
                        $erreurs[] = "Erreur lors de l'ajout du transport.";
                    }
                } catch (Exception $e) {
                    $erreurs[] = "Erreur: " . $e->getMessage();
                }
            }
            
            // S'il y a des erreurs, les stocker dans la session et rediriger
            if (!empty($erreurs)) {
                $_SESSION['error_messages'] = $erreurs;
                header("Location: /WaveNet/views/frontoffice/manageTransport.php");
                exit;
            }
        } else {
            // Si accès direct sans formulaire POST, rediriger
            header("Location: /WaveNet/views/frontoffice/manageTransport.php");
            exit;
        }
    }
    // Méthode pour changer le niveau d'un utilisateur (admin à client ou client à admin)
    public function changerNiveau() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Vérifier que l'utilisateur est admin
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_niveau']) || $_SESSION['user_niveau'] !== 'admin') {
            header('Location: /WaveNet/views/frontoffice/login.php');
            exit;
        }
        
        $id = $_GET['id'] ?? null;
        $niveau = $_GET['niveau'] ?? null;
        
        if (!$id || !in_array($niveau, ['admin', 'client'])) {
            $_SESSION['error'] = "Paramètres invalides.";
            header('Location: /WaveNet/views/backoffice/listeUtilisateurs.php');
            exit;
        }
        
        // Empêcher l'admin de changer son propre niveau
        if ($_SESSION['user_id'] == $id) {
            $_SESSION['error'] = "Vous ne pouvez pas changer votre propre niveau.";
            header('Location: /WaveNet/views/backoffice/listeUtilisateurs.php');
            exit;
        }
        
        require_once __DIR__ . '/../views/includes/config.php';
        $db = connectDB();
        
        // Vérifier si l'utilisateur existe
        $stmt = $db->prepare("SELECT id_utilisateur, niveau FROM UTILISATEUR WHERE id_utilisateur = :id");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            $_SESSION['error'] = "Utilisateur non trouvé.";
            header('Location: /WaveNet/views/backoffice/listeUtilisateurs.php');
            exit;
        }
        
        // Si le niveau est déjà le même, pas besoin de le changer
        if ($user['niveau'] === $niveau) {
            $_SESSION['error'] = "L'utilisateur a déjà ce niveau.";
            header('Location: /WaveNet/views/backoffice/listeUtilisateurs.php');
            exit;
        }
        
        try {
            // Changer le niveau de l'utilisateur
            $stmt = $db->prepare("UPDATE UTILISATEUR SET niveau = :niveau WHERE id_utilisateur = :id");
            $result = $stmt->execute([
                'niveau' => $niveau,
                'id' => $id
            ]);
            
            if ($result) {
                $_SESSION['success'] = "Le niveau de l'utilisateur a été changé avec succès.";
            } else {
                $_SESSION['error'] = "Erreur lors du changement de niveau de l'utilisateur.";
            }
            
        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur de base de données: " . $e->getMessage();
        }
        
        header('Location: /WaveNet/views/backoffice/listeUtilisateurs.php');
        exit;
    }

    /**
     * Méthode pour configurer les tables pour le 2FA
     * Vérifie si les colonnes 2FA existent déjà et les ajoute si nécessaire
     */
    private function setup2FADatabase() {
        require_once __DIR__ . '/../views/includes/config.php';
        $db = connectDB();
        
        // Vérifier si les colonnes 2FA existent déjà
        error_log("[setup2FADatabase] Vérification des colonnes 2FA");
        
        // Récupérer la liste exacte des colonnes pour vérifier le nom exact avec la bonne casse
        try {
            $allColumns = $db->query("SHOW COLUMNS FROM UTILISATEUR")->fetchAll(PDO::FETCH_COLUMN);
            error_log("[setup2FADatabase] Colonnes existantes dans UTILISATEUR: " . implode(", ", $allColumns));
            
            // Vérifier si twofa_enabled existe (avec sensibilité à la casse)
            $twofaEnabledExists = false;
            $twofaSecretExists = false;
            $exactColumnNameEnabled = '';
            $exactColumnNameSecret = '';
            
            foreach ($allColumns as $column) {
                if (strtolower($column) === 'twofa_enabled') {
                    $twofaEnabledExists = true;
                    $exactColumnNameEnabled = $column;
                }
                if (strtolower($column) === 'twofa_secret') {
                    $twofaSecretExists = true;
                    $exactColumnNameSecret = $column;
                }
            }
            
            error_log("[setup2FADatabase] twofa_enabled existe: " . ($twofaEnabledExists ? 'Oui' : 'Non') . ", nom exact: " . $exactColumnNameEnabled);
            error_log("[setup2FADatabase] twofa_secret existe: " . ($twofaSecretExists ? 'Oui' : 'Non') . ", nom exact: " . $exactColumnNameSecret);
            
            // Si les colonnes n'existent pas, les ajouter
            if (!$twofaEnabledExists || !$twofaSecretExists) {
                error_log("[setup2FADatabase] Ajout des colonnes manquantes");
                
                // Construire la requête en fonction des colonnes manquantes
                $alterQuery = "ALTER TABLE UTILISATEUR";
                if (!$twofaEnabledExists) {
                    $alterQuery .= " ADD COLUMN twofa_enabled TINYINT(1) DEFAULT 0";
                }
                if (!$twofaSecretExists) {
                    if (!$twofaEnabledExists) $alterQuery .= ",";
                    $alterQuery .= " ADD COLUMN twofa_secret VARCHAR(255) DEFAULT NULL";
                }
                
                error_log("[setup2FADatabase] Requête ALTER: " . $alterQuery);
                
                try {
                    $db->exec($alterQuery);
                    error_log("[setup2FADatabase] Colonnes ajoutées avec succès");
                    
                    // Vérifier à nouveau après l'ajout pour confirmer le nom exact des colonnes
                    $updatedColumns = $db->query("SHOW COLUMNS FROM UTILISATEUR")->fetchAll(PDO::FETCH_COLUMN);
                    error_log("[setup2FADatabase] Colonnes après modification: " . implode(", ", $updatedColumns));
                    
                    // Vérifier les types de données des colonnes ajoutées
                    $columnDetails = $db->query("DESCRIBE UTILISATEUR")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($columnDetails as $column) {
                        if (strtolower($column['Field']) === 'twofa_enabled' || strtolower($column['Field']) === 'twofa_secret') {
                            error_log("[setup2FADatabase] Colonne " . $column['Field'] . " - Type: " . $column['Type'] . ", Null: " . $column['Null'] . ", Default: " . $column['Default']);
                        }
                    }
                } catch (PDOException $e) {
                    error_log("[setup2FADatabase] Erreur lors de l'ajout des colonnes: " . $e->getMessage());
                }
            } else {
                // Même si les colonnes existent, vérifions leurs types de données
                $columnDetails = $db->query("DESCRIBE UTILISATEUR")->fetchAll(PDO::FETCH_ASSOC);
                foreach ($columnDetails as $column) {
                    if (strtolower($column['Field']) === 'twofa_enabled' || strtolower($column['Field']) === 'twofa_secret') {
                        error_log("[setup2FADatabase] Colonne existante " . $column['Field'] . " - Type: " . $column['Type'] . ", Null: " . $column['Null'] . ", Default: " . $column['Default']);
                    }
                }
            }
        } catch (PDOException $e) {
            error_log("[setup2FADatabase] Erreur lors de la récupération des colonnes: " . $e->getMessage());
        }
        
        // Vérifions que les colonnes existent bien maintenant
        try {
            $checkAgain = $db->query("SHOW COLUMNS FROM UTILISATEUR LIKE 'twofa_enabled'")->rowCount();
            $checkAgainSecret = $db->query("SHOW COLUMNS FROM UTILISATEUR LIKE 'twofa_secret'")->rowCount();
            error_log("[setup2FADatabase] Vérification après modification: twofa_enabled=" . $checkAgain . ", twofa_secret=" . $checkAgainSecret);
        } catch (PDOException $e) {
            error_log("[setup2FADatabase] Erreur lors de la vérification finale: " . $e->getMessage());
        }
        
        return $db;
    }

    /**
     * Méthode pour générer un secret 2FA pour un utilisateur
     * et afficher le QR code à scanner
     */
    public function generer2FASecret() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        error_log("[generer2FASecret] Début de la méthode generer2FASecret");
        
        if (!isset($_SESSION['user_id'])) {
            error_log("[generer2FASecret] Utilisateur non connecté");
            header('Location: /WaveNet/views/frontoffice/login.php');
            exit;
        }
        
        error_log("[generer2FASecret] ID utilisateur: " . $_SESSION['user_id']);
        
        // Vérifier si un secret temporaire existe déjà dans la session
        if (isset($_SESSION['temp_2fa_secret'])) {
            error_log("[generer2FASecret] Un secret temporaire existe déjà en session, réutilisation");
            // Réutiliser le secret existant au lieu d'en générer un nouveau
            $secret = $_SESSION['temp_2fa_secret'];
        } else {
            // Initialiser la base de données pour 2FA si nécessaire
            $db = $this->setup2FADatabase();
            
            // Charger la bibliothèque Google Authenticator
            error_log("[generer2FASecret] Chargement de la bibliothèque Google Authenticator");
            try {
                require_once __DIR__ . '/../vendor/autoload.php';
                $googleAuth = new \Sonata\GoogleAuthenticator\GoogleAuthenticator();
                
                // Générer un secret compatible TOTP
                $secret = $googleAuth->generateSecret();
                error_log("[generer2FASecret] Nouveau secret généré: " . $secret);
                
                // Stocker temporairement le secret dans la session pour la validation
                $_SESSION['temp_2fa_secret'] = $secret;
            } catch (Exception $e) {
                error_log("[generer2FASecret] Exception lors de la génération du secret: " . $e->getMessage());
                $_SESSION['error'] = "Erreur lors de la génération du secret 2FA: " . $e->getMessage();
                header('Location: /WaveNet/views/frontoffice/gerer2FA.php');
                exit;
            }
        }
        
        // Préparer les données pour le QR code
        $userName = $_SESSION['user_nom'] . ' ' . $_SESSION['user_prenom'];
        $siteName = 'WaveNet';
        error_log("[generer2FASecret] Préparation QR code pour: " . $userName . " @ " . $siteName);
        
        try {
            // Créer l'URL pour le QR code
            require_once __DIR__ . '/../vendor/autoload.php';
            $qrCodeUrl = \Sonata\GoogleAuthenticator\GoogleQrUrl::generate($userName, $secret, $siteName);
            error_log("[generer2FASecret] QR code URL généré (longueur: " . strlen($qrCodeUrl) . ")");
            
            // Afficher la page de configuration 2FA
            error_log("[generer2FASecret] Affichage de la page de configuration 2FA");
            require __DIR__ . '/../views/frontoffice/activer2FA.php';
            exit;
        } catch (Exception $e) {
            error_log("[generer2FASecret] Exception lors de la génération du QR code: " . $e->getMessage());
            $_SESSION['error'] = "Erreur lors de la génération du secret 2FA: " . $e->getMessage();
            header('Location: /WaveNet/views/frontoffice/gerer2FA.php');
            exit;
        }
    }
    
    /**
     * Méthode pour vérifier et activer le 2FA
     */
    public function activer2FA() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        error_log("[activer2FA] Début de la méthode activer2FA");
        
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['temp_2fa_secret'])) {
            error_log("[activer2FA] Session incomplète: user_id=" . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'non défini') . ", temp_2fa_secret=" . (isset($_SESSION['temp_2fa_secret']) ? 'défini' : 'non défini'));
            header('Location: /WaveNet/views/frontoffice/userDashboard.php');
            exit;
        }
        
        error_log("[activer2FA] ID utilisateur: " . $_SESSION['user_id'] . ", Secret temporaire présent");
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $codeVerification = $_POST['code_verification'] ?? '';
            error_log("[activer2FA] Méthode POST, code de vérification reçu: " . (!empty($codeVerification) ? 'Oui' : 'Non'));
            
            if (empty($codeVerification)) {
                $_SESSION['error'] = "Veuillez entrer le code de vérification.";
                header('Location: /WaveNet/views/frontoffice/activer2FA.php');
                exit;
            }
            
            // Vérifier l'heure du serveur et la journaliser
            $serverTime = time();
            $formattedTime = date('Y-m-d H:i:s', $serverTime);
            error_log("[activer2FA] Heure du serveur: " . $formattedTime . " (timestamp: " . $serverTime . ")");
            
            // Vérifier le code TOTP
            error_log("[activer2FA] Vérification du code TOTP");
            require_once __DIR__ . '/../vendor/autoload.php';
            $googleAuth = new \Sonata\GoogleAuthenticator\GoogleAuthenticator();
            
            // Capturer le secret de session pour déboguer
            $secret = $_SESSION['temp_2fa_secret'];
            error_log("[activer2FA] Utilisation du secret de session: " . $secret);
            error_log("[activer2FA] Code de vérification soumis: " . $codeVerification);
            
            // Essayons avec des fenêtres de temps différentes pour diagnostiquer une désynchronisation d'horloge
            $window = 2; // 2 périodes de 30 secondes avant et après
            $isCodeValid = $googleAuth->checkCode($secret, $codeVerification, $window);
            error_log("[activer2FA] Résultat de la vérification du code avec fenêtre " . $window . ": " . ($isCodeValid ? 'valide' : 'invalide'));
            
            // Si invalide avec fenêtre standard, essayons avec une fenêtre plus large
            if (!$isCodeValid) {
                $largerWindow = 4; // 4 périodes de 30 secondes avant et après
                $isCodeValidLargerWindow = $googleAuth->checkCode($secret, $codeVerification, $largerWindow);
                error_log("[activer2FA] Résultat de la vérification du code avec fenêtre élargie " . $largerWindow . ": " . 
                          ($isCodeValidLargerWindow ? 'valide' : 'invalide'));
                
                // Si le code est valide avec la fenêtre plus large mais pas avec la fenêtre standard,
                // cela indique un problème de synchronisation d'horloge
                if ($isCodeValidLargerWindow) {
                    error_log("[activer2FA] Possible désynchronisation d'horloge détectée");
                    $isCodeValid = true;
                }
            }
            
            if ($isCodeValid) {
                // Code valide, activer 2FA pour l'utilisateur
                error_log("[activer2FA] Code valide, préparation mise à jour BDD");
                $db = $this->setup2FADatabase();
                
                // Vérifier structure de la table avant UPDATE
                try {
                    $tableInfo = $db->query("DESCRIBE UTILISATEUR")->fetchAll(PDO::FETCH_COLUMN);
                    error_log("[activer2FA] Structure de la table UTILISATEUR: " . implode(", ", $tableInfo));
                } catch (PDOException $e) {
                    error_log("[activer2FA] Erreur lors de la récupération de la structure: " . $e->getMessage());
                }
                
                error_log("[activer2FA] Préparation requête UPDATE");
                try {
                    $stmt = $db->prepare("UPDATE UTILISATEUR SET twofa_enabled = 1, twofa_secret = :secret WHERE id_utilisateur = :id");
                    error_log("[activer2FA] Requête préparée");
                    
                    $result = $stmt->execute([
                        'secret' => $secret,
                        'id' => $_SESSION['user_id']
                    ]);
                    
                    error_log("[activer2FA] Résultat de l'UPDATE: " . ($result ? 'succès' : 'échec') . ", Lignes affectées: " . $stmt->rowCount());
                    
                    if ($result) {
                        // Supprimer le secret temporaire de la session
                        unset($_SESSION['temp_2fa_secret']);
                        
                        // Vérifier que la mise à jour a bien été effectuée
                        $verif = $db->prepare("SELECT twofa_enabled, twofa_secret FROM UTILISATEUR WHERE id_utilisateur = :id");
                        $verif->execute(['id' => $_SESSION['user_id']]);
                        $userData = $verif->fetch(PDO::FETCH_ASSOC);
                        error_log("[activer2FA] Vérification après mise à jour: twofa_enabled=" . ($userData['twofa_enabled'] ?? 'NULL') . ", twofa_secret " . (empty($userData['twofa_secret']) ? 'vide' : 'présent'));
                        
                        $_SESSION['success'] = "L'authentification à deux facteurs a été activée avec succès.";
                        header('Location: /WaveNet/views/frontoffice/userDashboard.php');
                        exit;
                    } else {
                        error_log("[activer2FA] Erreur SQL: " . implode(', ', $stmt->errorInfo()));
                        $_SESSION['error'] = "Erreur lors de l'activation de l'authentification à deux facteurs.";
                    }
                } catch (PDOException $e) {
                    error_log("[activer2FA] Exception PDO: " . $e->getMessage());
                    $_SESSION['error'] = "Exception lors de l'activation: " . $e->getMessage();
                }
            } else {
                $_SESSION['error'] = "Code de vérification incorrect. Veuillez réessayer.";
            }
            
            header('Location: /WaveNet/views/frontoffice/activer2FA.php');
            exit;
        } else {
            error_log("[activer2FA] Méthode GET - Affichage du formulaire d'activation avec QR code");
            
            // Pour les requêtes GET, il faut regénérer le QR code à afficher
            $secret = $_SESSION['temp_2fa_secret'];
            $userName = $_SESSION['user_nom'] . ' ' . $_SESSION['user_prenom'];
            $siteName = 'WaveNet';
            
            // Charger la bibliothèque pour générer le QR code
            require_once __DIR__ . '/../vendor/autoload.php';
            $qrCodeUrl = \Sonata\GoogleAuthenticator\GoogleQrUrl::generate($userName, $secret, $siteName);
            
            // Afficher le formulaire d'activation
            require __DIR__ . '/../views/frontoffice/activer2FA.php';
            exit;
        }
    }
    
    /**
     * Méthode pour désactiver le 2FA
     */
    public function desactiver2FA() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: /WaveNet/views/frontoffice/login.php');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $codeVerification = $_POST['code_verification'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if (empty($codeVerification) || empty($password)) {
                $_SESSION['error'] = "Veuillez remplir tous les champs.";
                header('Location: /WaveNet/views/frontoffice/desactiver2FA.php');
                exit;
            }
            
            $db = $this->setup2FADatabase();
            
            // Vérifier le mot de passe
            $stmt = $db->prepare("SELECT mot_de_passe, twofa_secret FROM UTILISATEUR WHERE id_utilisateur = :id");
            $stmt->execute(['id' => $_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || !password_verify($password, $user['mot_de_passe'])) {
                $_SESSION['error'] = "Mot de passe incorrect.";
                header('Location: /WaveNet/views/frontoffice/desactiver2FA.php');
                exit;
            }
            
            // Vérifier le code TOTP
            require_once __DIR__ . '/../vendor/autoload.php';
            $googleAuth = new \Sonata\GoogleAuthenticator\GoogleAuthenticator();
            
            if ($googleAuth->checkCode($user['twofa_secret'], $codeVerification)) {
                // Désactiver 2FA
                $stmt = $db->prepare("UPDATE UTILISATEUR SET twofa_enabled = 0, twofa_secret = NULL WHERE id_utilisateur = :id");
                $result = $stmt->execute(['id' => $_SESSION['user_id']]);
                
                if ($result) {
                    $_SESSION['success'] = "L'authentification à deux facteurs a été désactivée avec succès.";
                    header('Location: /WaveNet/views/frontoffice/userDashboard.php');
                    exit;
                } else {
                    $_SESSION['error'] = "Erreur lors de la désactivation de l'authentification à deux facteurs.";
                }
            } else {
                $_SESSION['error'] = "Code de vérification incorrect.";
            }
            
            header('Location: /WaveNet/views/frontoffice/desactiver2FA.php');
            exit;
        } else {
            // Afficher le formulaire de désactivation
            require __DIR__ . '/../views/frontoffice/desactiver2FA.php';
            exit;
        }
    }
    
    /**
     * Méthode pour gérer la vérification 2FA lors de la connexion
     */
    public function verifier2FA() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        error_log("[verifier2FA] Début de la méthode verifier2FA");
        
        // Vérifier si l'ID temporaire existe pour attendre la vérification 2FA
        if (!isset($_SESSION['temp_user_id'])) {
            error_log("[verifier2FA] Aucun ID temporaire trouvé, redirection vers login");
            header('Location: /WaveNet/views/frontoffice/login.php');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $codeVerification = $_POST['code_verification'] ?? '';
            
            if (empty($codeVerification)) {
                $_SESSION['error'] = "Veuillez entrer le code de vérification.";
                header('Location: /WaveNet/views/frontoffice/verifier2FA.php');
                exit;
            }
            
            require_once __DIR__ . '/../views/includes/config.php';
            $db = connectDB();
            
            // Récupérer le secret de l'utilisateur
            $stmt = $db->prepare("SELECT id_utilisateur, nom, prenom, niveau, twofa_secret FROM UTILISATEUR WHERE id_utilisateur = :id");
            $stmt->execute(['id' => $_SESSION['temp_user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                unset($_SESSION['temp_user_id']);
                $_SESSION['login_error'] = "Utilisateur non trouvé.";
                header('Location: /WaveNet/views/frontoffice/login.php');
                exit;
            }
            
            // Vérifier le code TOTP
            require_once __DIR__ . '/../vendor/autoload.php';
            $googleAuth = new \Sonata\GoogleAuthenticator\GoogleAuthenticator();
            
            if ($googleAuth->checkCode($user['twofa_secret'], $codeVerification)) {
                // Authentification réussie, connecter l'utilisateur
                $_SESSION['user_id'] = $user['id_utilisateur'];
                $_SESSION['user_nom'] = $user['nom'];
                $_SESSION['user_prenom'] = $user['prenom'];
                $_SESSION['user_niveau'] = $user['niveau'];
                
                // Supprimer les variables temporaires
                unset($_SESSION['temp_user_id']);
                unset($_SESSION['auth_requires_2fa']);
                
                error_log("[verifier2FA] 2FA validé avec succès pour l'utilisateur " . $user['id_utilisateur']);
                
                // Rediriger selon le niveau de l'utilisateur
                if ($user['niveau'] === 'admin') {
                    header('Location: /WaveNet/views/backoffice/index.php');
                } else {
                    header('Location: /WaveNet/views/frontoffice/userDashboard.php');
                }
                exit;
            } else {
                // Journaliser l'échec de vérification 2FA
                require_once __DIR__ . '/../models/security_functions.php';
                logConnection($_SESSION['temp_user_id'], false, "Code 2FA invalide");
                
                $_SESSION['error'] = "Code de vérification incorrect. Veuillez réessayer.";
                header('Location: /WaveNet/views/frontoffice/verifier2FA.php');
                exit;
            }
        } else {
            // Afficher le formulaire de vérification 2FA
            error_log("[verifier2FA] Affichage du formulaire 2FA pour l'utilisateur " . $_SESSION['temp_user_id']);
            require __DIR__ . '/../views/frontoffice/verifier2FA.php';
            exit;
        }
    }
    
    /**
     * Méthode pour afficher la page de gestion 2FA
     */
    public function gerer2FA() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        error_log("[gerer2FA] Début de la méthode gerer2FA");
        
        if (!isset($_SESSION['user_id'])) {
            error_log("[gerer2FA] Utilisateur non connecté");
            header('Location: /WaveNet/views/frontoffice/login.php');
            exit;
        }
        
        error_log("[gerer2FA] ID utilisateur: " . $_SESSION['user_id']);
        $db = $this->setup2FADatabase();
        
        // Récupérer le statut 2FA de l'utilisateur
        error_log("[gerer2FA] Récupération du statut 2FA");
        try {
            // Récupérer toutes les informations de l'utilisateur pour vérifier les données 2FA
            $stmt = $db->prepare("SELECT * FROM UTILISATEUR WHERE id_utilisateur = :id");
            $stmt->execute(['id' => $_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            error_log("[gerer2FA] Données utilisateur complètes: " . json_encode(array_intersect_key($user, array_flip(['id_utilisateur', 'twofa_enabled', 'twofa_secret']))));
            
            // Récupérer spécifiquement la valeur de twofa_enabled
            $stmt = $db->prepare("SELECT twofa_enabled FROM UTILISATEUR WHERE id_utilisateur = :id");
            $stmt->execute(['id' => $_SESSION['user_id']]);
            $twofaData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            error_log("[gerer2FA] Résultat de la requête spécifique: " . json_encode($twofaData));
            
            $twofa_enabled = isset($twofaData['twofa_enabled']) ? (bool)$twofaData['twofa_enabled'] : false;
            error_log("[gerer2FA] Statut 2FA: " . ($twofa_enabled ? 'activé' : 'désactivé') . " (valeur brute: " . ($twofaData['twofa_enabled'] ?? 'NULL') . ")");
            
            // Vérifier si le secret est présent pour les comptes qui ont 2FA activé
            if ($twofa_enabled) {
                $secretCheck = $db->prepare("SELECT twofa_secret FROM UTILISATEUR WHERE id_utilisateur = :id");
                $secretCheck->execute(['id' => $_SESSION['user_id']]);
                $secretData = $secretCheck->fetch(PDO::FETCH_ASSOC);
                error_log("[gerer2FA] Secret 2FA présent: " . (!empty($secretData['twofa_secret']) ? 'Oui' : 'Non'));
                
                // Si le secret est vide mais 2FA est activé, cela indique un problème
                if (empty($secretData['twofa_secret'])) {
                    error_log("[gerer2FA] ANOMALIE: 2FA activé mais secret manquant");
                    // Corriger automatiquement en désactivant 2FA
                    $stmt = $db->prepare("UPDATE UTILISATEUR SET twofa_enabled = 0 WHERE id_utilisateur = :id");
                    $stmt->execute(['id' => $_SESSION['user_id']]);
                    error_log("[gerer2FA] Correction automatique: 2FA désactivé en raison d'un secret manquant");
                    $twofa_enabled = false;
                }
            }
            
            // Créer un jeton anti-CSRF pour le formulaire
            $csrf_token = bin2hex(random_bytes(32));
            $_SESSION['csrf_token'] = $csrf_token;
            error_log("[gerer2FA] Jeton CSRF généré pour le formulaire");
            
        } catch (PDOException $e) {
            error_log("[gerer2FA] Erreur lors de la récupération du statut 2FA: " . $e->getMessage());
            $twofa_enabled = false;
        }
        
        // Afficher la page de gestion 2FA
        error_log("[gerer2FA] Affichage de la page de gestion 2FA avec twofa_enabled = " . ($twofa_enabled ? 'true' : 'false'));
        require __DIR__ . '/../views/frontoffice/gerer2FA.php';
        exit;
    }

    /**
     * Vérifie les identifiants de l'utilisateur (première étape de connexion)
     */
    public function checkCredentials() {
        error_log("[checkCredentials] Fonction démarrée."); // LOG
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Récupérer les données du formulaire (envoyées par AJAX maintenant)
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $remember = isset($_POST['remember']) ? true : false;
        error_log("[checkCredentials] Données reçues - Email: " . $email . ", Remember: " . ($remember ? 'Oui' : 'Non')); // LOG

        // Valider les données
        if (empty($email) || empty($password)) {
            error_log("[checkCredentials] ERREUR: Champs vides."); // LOG
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Veuillez remplir tous les champs.']);
            exit;
        }

        try {
            require_once __DIR__ . '/../views/includes/config.php';
            require_once __DIR__ . '/../models/Utilisateur.php'; // Assurez-vous que Utilisateur est inclus
            $db = connectDB();

            // Vérifier si l'utilisateur existe
            $stmt = $db->prepare("SELECT * FROM UTILISATEUR WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log("[checkCredentials] Utilisateur trouvé pour {$email}: " . ($user ? 'Oui' : 'Non')); // LOG

            if (!$user) {
                password_verify('dummy', '$2y$10$dummyhashfordummypassword'); // Anti-timing attack
                // $this->handleFailedLogin(); // handleFailedLogin redirige, ne convient pas pour AJAX
                error_log("[checkCredentials] ERREUR: Utilisateur non trouvé."); // LOG
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Email ou mot de passe incorrect.']);
                exit;
            }

            // Vérifier si le compte est actif (colonne `bloque` semble utilisée)
            if (isset($user['bloque']) && $user['bloque'] == 1) {
                 error_log("[checkCredentials] ERREUR: Compte bloqué pour {$email}."); // LOG
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Votre compte a été bloqué. Veuillez contacter l\'administrateur.']);
                exit;
            }

            // Vérifier le mot de passe
            $passwordValid = password_verify($password, $user['mot_de_passe']);
             error_log("[checkCredentials] Vérification mot de passe pour {$email}. Valide: " . ($passwordValid ? 'Oui' : 'Non')); // LOG
            if (!$passwordValid) {
                // $this->handleFailedLogin(); // Ne convient pas pour AJAX
                error_log("[checkCredentials] ERREUR: Mot de passe incorrect pour {$email}."); // LOG
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Email ou mot de passe incorrect.']);
                exit;
            }

            // --- Identifiants valides, vérifier 2FA --- 
            error_log("[checkCredentials] Identifiants valides pour {$email}. Vérification 2FA..."); // LOG
            $twofa_enabled = false;
             // Vérifier si les colonnes 2FA existent
             $stmt2FAcheck = $db->query("SHOW COLUMNS FROM UTILISATEUR LIKE 'twofa_enabled'");
             if ($stmt2FAcheck && $stmt2FAcheck->rowCount() > 0) {
                 $stmt2FA = $db->prepare("SELECT twofa_enabled FROM UTILISATEUR WHERE id_utilisateur = :id");
                 $stmt2FA->execute(['id' => $user['id_utilisateur']]);
                 $twofaData = $stmt2FA->fetch(PDO::FETCH_ASSOC);
                 $twofa_enabled = isset($twofaData['twofa_enabled']) && $twofaData['twofa_enabled'] == 1;
             }
             error_log("[checkCredentials] Statut 2FA pour {$email}: " . ($twofa_enabled ? 'Activé' : 'Désactivé')); // LOG

            if ($twofa_enabled) {
                // 2FA est activé -> Stocker l'ID temporairement et demander la vérification 2FA
                $_SESSION['temp_user_id'] = $user['id_utilisateur']; // Utilisé par verifier2FA()
                 $_SESSION['2fa_remember'] = $remember; // Garder l'option remember
                error_log("[checkCredentials] 2FA activé. Redirection vers vérification 2FA nécessaire pour user ID: " . $user['id_utilisateur']); // LOG
                
                // Indiquer au JS qu'il faut aller à l'étape 2FA
                header('Content-Type: application/json');
                // L'URL de vérification 2FA peut être construite ici ou dans le JS
                echo json_encode(['status' => 'success', 'next_step' => '2fa', 'redirect_url' => '/WaveNet/controller/UserController.php?action=verifier2FA']); 
                exit;
            } else {
                // Pas de 2FA -> Finaliser la connexion directement
                error_log("[checkCredentials] Pas de 2FA. Appel de finalizeLoginAndRespond pour user ID: " . $user['id_utilisateur']); // LOG
                // Utiliser la méthode qui renvoie JSON pour AJAX
                $this->finalizeLoginAndRespond($user, $remember);
                // finalizeLoginAndRespond fait un exit() donc pas besoin ici
            }
            
            // L'ancien code qui retournait show_captcha est supprimé car plus pertinent dans ce flux
            /*
            // Stocker l'ID utilisateur temporairement pour le CAPTCHA
            $_SESSION['tmp_user_id'] = $user['id_utilisateur'];
            // Stocker les informations de l'utilisateur pour la prochaine étape
            $_SESSION['login_credentials'] = [
                'user_id' => $user['id_utilisateur'],
                'remember' => $remember
            ];
            // Définir l'étape comme CAPTCHA mais renvoyer au format JSON pour traitement AJAX
            $_SESSION['login_step'] = 'captcha';
            // Retourner une réponse JSON pour indiquer de montrer le CAPTCHA
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'show_captcha' => true]);
            exit;
            */

        } catch (Exception $e) {
            error_log("[checkCredentials] EXCEPTION: " . $e->getMessage()); // LOG
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Une erreur interne est survenue. Veuillez réessayer.']);
            exit;
        }
    }

    /**
     * Traite la connexion après validation du CAPTCHA
     */
    public function processLogin() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Vérifier si les informations d'identification existent
        if (!isset($_SESSION['login_credentials'])) {
            $_SESSION['error'] = "Session expirée. Veuillez vous reconnecter.";
            header('Location: /WaveNet/views/frontoffice/login.php');
            exit;
        }

        // Vérifier si le CAPTCHA a été validé
        require_once __DIR__ . '/CaptchaController.php';
        $captchaController = new CaptchaController();
        if (!$captchaController->checkToken()) {
            $_SESSION['error'] = "Validation CAPTCHA expirée. Veuillez réessayer.";
            unset($_SESSION['login_step']);
            unset($_SESSION['login_credentials']);
            header('Location: /WaveNet/views/frontoffice/login.php');
            exit;
        }

        // Récupérer les informations d'identification
        $userId = $_SESSION['login_credentials']['user_id'];
        $remember = $_SESSION['login_credentials']['remember'];

        try {
            require_once __DIR__ . '/../views/includes/config.php';
            $db = connectDB();

            // Récupérer les informations utilisateur
            $stmt = $db->prepare("SELECT * FROM UTILISATEUR WHERE id_utilisateur = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $_SESSION['error'] = "Utilisateur introuvable. Veuillez vous reconnecter.";
                unset($_SESSION['login_step']);
                unset($_SESSION['login_credentials']);
                header('Location: /WaveNet/views/frontoffice/login.php');
                exit;
            }

            // Marquer le CAPTCHA comme utilisé
            $captchaController->useToken();

            // Vérifier si l'authentification à deux facteurs est activée
            $has2FA = $this->check2FAEnabled($userId);
            
            if ($has2FA) {
                // Rediriger vers la page 2FA
                $_SESSION['2fa_user_id'] = $userId;
                $_SESSION['2fa_remember'] = $remember;
                header('Location: /WaveNet/views/frontoffice/verify2FA.php');
                exit;
            } else {
                // Connexion réussie
                $this->finalizeLogin($user, $remember);
            }

        } catch (Exception $e) {
            error_log("Erreur lors du traitement de la connexion: " . $e->getMessage());
            $_SESSION['error'] = "Une erreur est survenue. Veuillez réessayer.";
            header('Location: /WaveNet/views/frontoffice/login.php');
            exit;
        }
    }

    /**
     * Finalise la connexion après toutes les étapes de vérification
     */
    private function finalizeLogin($user, $remember = false) {
        // Mettre à jour la dernière connexion
        try {
            require_once __DIR__ . '/../views/includes/config.php';
            $db = connectDB();
            $stmt = $db->prepare("UPDATE UTILISATEUR SET derniere_connexion = NOW() WHERE id_utilisateur = ?");
            $stmt->execute([$user['id_utilisateur']]);
        } catch (Exception $e) {
            error_log("Erreur lors de la mise à jour de la dernière connexion: " . $e->getMessage());
        }

        // Créer une session utilisateur
        $_SESSION['user_id'] = $user['id_utilisateur'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['nom'] . ' ' . $user['prenom'];

        // Créer un cookie "Se souvenir de moi" si demandé
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $expires = time() + (30 * 24 * 60 * 60); // 30 jours

            try {
                // Stocker le token en base de données
                $stmt = $db->prepare("INSERT INTO REMEMBER_ME_TOKENS (id_utilisateur, token, expiration) VALUES (?, ?, ?)");
                $stmt->execute([$user['id_utilisateur'], $token, date('Y-m-d H:i:s', $expires)]);

                // Créer le cookie
                setcookie('remember_token', $token, $expires, '/', '', true, true);
            } catch (Exception $e) {
                error_log("Erreur lors de la création du token 'Se souvenir de moi': " . $e->getMessage());
            }
        }

        // Nettoyer les variables de session temporaires
        unset($_SESSION['login_step']);
        unset($_SESSION['login_credentials']);
        unset($_SESSION['tmp_user_id']);
        unset($_SESSION['captcha_attempts']);

        // Rediriger en fonction du rôle
        if ($user['role'] === 'admin') {
            header('Location: /WaveNet/views/backoffice/index.php');
        } else {
            header('Location: /WaveNet/views/frontoffice/userDashboard.php');
        }
        exit;
    }

    /**
     * Gère les tentatives de connexion échouées
     */
    private function handleFailedLogin() {
        // Incrémenter le compteur d'échecs
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = 1;
        } else {
            $_SESSION['login_attempts']++;
        }

        // Si trop de tentatives, bloquer temporairement
        if ($_SESSION['login_attempts'] >= 5) {
            $_SESSION['login_blocked_until'] = time() + (15 * 60); // 15 minutes
            $_SESSION['error'] = "Trop de tentatives échouées. Veuillez réessayer dans 15 minutes.";
        } else {
            $_SESSION['error'] = "Email ou mot de passe incorrect.";
        }

        header('Location: /WaveNet/views/frontoffice/login.php');
        exit;
    }

    /**
     * Vérifie si l'authentification 2FA est activée pour un utilisateur
     */
    private function check2FAEnabled($userId) {
        try {
            require_once __DIR__ . '/../views/includes/config.php';
            $db = connectDB();
            
            $stmt = $db->prepare("SELECT * FROM AUTHENTICATION_2FA WHERE id_utilisateur = ? AND statut = 'actif'");
            $stmt->execute([$userId]);
            
            return $stmt->fetch() !== false;
        } catch (Exception $e) {
            error_log("Erreur lors de la vérification 2FA: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Finalise la connexion après validation du captcha côté client.
     * Récupère les infos de la session et connecte l'utilisateur.
     */
    public function finalizeLoginAfterCaptcha() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        header('Content-Type: application/json');

        // Vérifier si les informations d'identification temporaires existent
        if (!isset($_SESSION['login_credentials']) || !isset($_SESSION['login_credentials']['user_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Session expirée ou invalide. Veuillez réessayer.']);
            exit;
        }

        $userId = $_SESSION['login_credentials']['user_id'];
        $remember = $_SESSION['login_credentials']['remember'] ?? false;

        try {
            require_once __DIR__ . '/../views/includes/config.php';
            $db = connectDB();

            // Récupérer les informations utilisateur complètes
            $stmt = $db->prepare("SELECT * FROM UTILISATEUR WHERE id_utilisateur = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                unset($_SESSION['login_credentials']); // Nettoyer session
                echo json_encode(['status' => 'error', 'message' => 'Utilisateur introuvable.']);
                exit;
            }

            // À ce stade, le captcha a été validé côté client.
            // On peut ajouter une vérification serveur optionnelle ici si besoin
            // en passant le token captcha ou une info dans la requête AJAX.

            // Finaliser la connexion (création de session, cookie, etc.)
            $this->finalizeLoginAndRespond($user, $remember); // Appel de la fonction renommée
            
            // Ne pas envoyer de sortie ici car finalizeLoginAndRespond gère la réponse
            
        } catch (Exception $e) {
            error_log("Erreur lors de finalizeLoginAfterCaptcha: " . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Une erreur interne est survenue lors de la connexion.']);
            exit;
        }
    }

    /**
     * Finalise la connexion après toutes les étapes de vérification
     * Renvoie JSON pour AJAX.
     */
    private function finalizeLoginAndRespond($user, $remember = false) { // Renommée pour éviter conflit
        // ... (Mise à jour derniere_connexion si colonne existe)
         try {
             require_once __DIR__ . '/../views/includes/config.php';
             $db = connectDB();
             // Vérifier si la colonne existe avant de tenter la mise à jour
             $stmtCheck = $db->query("SHOW COLUMNS FROM UTILISATEUR LIKE 'derniere_connexion'");
             if ($stmtCheck && $stmtCheck->rowCount() > 0) {
                 $stmt = $db->prepare("UPDATE UTILISATEUR SET derniere_connexion = NOW() WHERE id_utilisateur = ?");
                 $stmt->execute([$user['id_utilisateur']]);
             }
         } catch (Exception $e) {
             error_log("Erreur facultative lors de la mise à jour de la dernière connexion: " . $e->getMessage());
         }

        // Créer une session utilisateur
        $_SESSION['user_id'] = $user['id_utilisateur'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['niveau'] ?? 'client'; // Assurer que role/niveau est défini
        $_SESSION['user_nom'] = $user['nom'];
        $_SESSION['user_prenom'] = $user['prenom'];
        $_SESSION['user_niveau'] = $user['niveau'] ?? 'client'; // Stocker explicitement niveau aussi

        // Créer un cookie "Se souvenir de moi" si demandé
        if ($remember) {
           // ... (code existant pour le cookie remember me)
             $token = bin2hex(random_bytes(32));
             $expires = time() + (30 * 24 * 60 * 60); // 30 jours
 
             try {
                 // S'assurer que $db est disponible
                 if (!isset($db)) {
                    require_once __DIR__ . '/../views/includes/config.php';
                    $db = connectDB();
                 }
                 // Utiliser ON DUPLICATE KEY UPDATE pour gérer les tokens existants
                 $stmt = $db->prepare("INSERT INTO REMEMBER_ME_TOKENS (id_utilisateur, token, expiration) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE token = VALUES(token), expiration = VALUES(expiration)");
                 $stmt->execute([$user['id_utilisateur'], $token, date('Y-m-d H:i:s', $expires)]);
                 setcookie('remember_token', $token, $expires, '/', '', true, true);
             } catch (Exception $e) {
                 error_log("Erreur lors de la création du token 'Se souvenir de moi': " . $e->getMessage());
             }
        }

        // Nettoyer les variables de session temporaires
        unset($_SESSION['login_step']);
        unset($_SESSION['login_credentials']);
        unset($_SESSION['tmp_user_id']);
        unset($_SESSION['captcha_attempts']); // Nettoyer aussi les tentatives
        unset($_SESSION['captcha_token']); // Et le token captcha

        // Déterminer l'URL de redirection
        $redirect_url = '/WaveNet/views/frontoffice/userDashboard.php'; // Défaut client
        if ($user['niveau'] === 'admin') {
            $redirect_url = '/WaveNet/views/backoffice/index.php';
        }

        // Renvoyer une réponse JSON pour AJAX
        // S'assurer qu'aucune sortie n'a été envoyée avant header()
        if (!headers_sent()) {
             header('Content-Type: application/json');
        }
        echo json_encode(['status' => 'success', 'message' => 'Connexion réussie.', 'redirect_url' => $redirect_url]);
        exit; // Important de terminer ici pour la réponse JSON
    }

    /**
     * Gère la demande de réinitialisation de mot de passe.
     * Vérifie l'email, génère un token, le stocke et envoie un email.
     */
    public function handleForgotPasswordRequest() {
        error_log("[handleForgotPasswordRequest] Fonction démarrée."); // LOG AJOUTÉ
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /WaveNet/views/frontoffice/forgot_password_request.php');
            exit;
        }

        $email = $_POST['email'] ?? null;
        error_log("[handleForgotPasswordRequest] Email reçu: " . ($email ?? 'aucun')); // LOG AJOUTÉ

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Veuillez fournir une adresse e-mail valide.";
            header('Location: /WaveNet/views/frontoffice/forgot_password_request.php');
            exit;
        }

        try {
            require_once __DIR__ . '/../views/includes/config.php';
            require_once __DIR__ . '/../models/Utilisateur.php';
            $db = connectDB();

            $user = Utilisateur::findByEmail($db, $email);
            error_log("[handleForgotPasswordRequest] Recherche utilisateur pour {$email} terminée. Trouvé: " . ($user ? 'Oui' : 'Non')); // LOG AJOUTÉ

            if ($user) {
                error_log("[handleForgotPasswordRequest] Utilisateur trouvé, génération token..."); // LOG AJOUTÉ
                $token = bin2hex(random_bytes(32));
                $expires_at = new DateTime('+30 minutes');
                $expires_at_formatted = $expires_at->format('Y-m-d H:i:s');

                $stmtDelete = $db->prepare("DELETE FROM password_resets WHERE email = ?");
                $stmtDelete->execute([$email]);
                error_log("[handleForgotPasswordRequest] Anciens tokens supprimés (si existants)."); // LOG AJOUTÉ

                $stmtInsert = $db->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
                $inserted = $stmtInsert->execute([$email, $token, $expires_at_formatted]);
                error_log("[handleForgotPasswordRequest] Insertion nouveau token. Succès: " . ($inserted ? 'Oui' : 'Non')); // LOG AJOUTÉ

                $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/WaveNet/controller/UserController.php?action=showResetPasswordForm&token=" . $token;

                // --- Configuration et envoi de l'email avec PHPMailer --- 
                require_once __DIR__ . '/../vendor/autoload.php';

                $mail = new PHPMailer(true);
                error_log("[handleForgotPasswordRequest] Objet PHPMailer créé."); // LOG AJOUTÉ

                try {
                    // Paramètres du serveur SMTP pour Gmail
                    // $mail->SMTPDebug = SMTP::DEBUG_SERVER; // <<< COMMENTÉ MAINTENANT QUE ÇA MARCHE
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'mezazighcharaf@gmail.com'; // <-- METTRE VOTRE ADRESSE GMAIL ICI
                    $mail->Password   = 'ypzrvgxootlwsgta'; // <-- METTRE LE MOT DE PASSE D\'APPLICATION (16 caractères)
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;       // Utiliser TLS
                    $mail->Port       = 587;                                  // Port pour TLS
                    $mail->CharSet    = 'UTF-8'; // Assurer l\'encodage correct des caractères

                    // Destinataires
                    // L\'adresse \'From\' doit être la même que \'Username\' pour Gmail
                    $mail->setFrom('mezazighcharaf@gmail.com', 'WaveNet Assistance'); // <-- METTRE VOTRE ADRESSE GMAIL ET UN NOM D\'EXPÉDITEUR
                    $mail->addAddress($email);     // L\'adresse email de l\'utilisateur fournie en paramètre

                    // Contenu
                    $mail->isHTML(true);
                    $mail->Subject = 'Réinitialisation de votre mot de passe WaveNet';
                    $mail->Body    = "Bonjour,<br><br>Vous avez demandé une réinitialisation de mot de passe. Cliquez sur le lien ci-dessous pour choisir un nouveau mot de passe :<br><a href='{$resetLink}'>Réinitialiser mon mot de passe</a><br><br>Ce lien expirera dans 30 minutes.<br><br>Si vous n'avez pas demandé cette réinitialisation, veuillez ignorer cet e-mail.<br><br>Cordialement,<br>L'équipe WaveNet";
                    $mail->AltBody = "Bonjour,\n\nVous avez demandé une réinitialisation de mot de passe. Copiez et collez le lien suivant dans votre navigateur pour choisir un nouveau mot de passe :\n{$resetLink}\n\nCe lien expirera dans 30 minutes.\n\nSi vous n'avez pas demandé cette réinitialisation, veuillez ignorer cet e-mail.\n\nCordialement,\nL'équipe WaveNet";

                    $mail->send();
                    error_log("[handleForgotPasswordRequest] E-mail de réinitialisation envoyé à {$email}");

                } catch (Exception $e) {
                    error_log("[handleForgotPasswordRequest] Erreur lors de l'envoi de l'e-mail à {$email}: {$mail->ErrorInfo}");
                    // Ne pas révéler l'erreur à l'utilisateur pour la sécurité, mais la logger.
                    // Vous pourriez stocker une erreur générique en session si l'envoi est critique
                    // $_SESSION['error'] = "Impossible d'envoyer l'e-mail de réinitialisation pour le moment. Veuillez réessayer plus tard.";
                    // header('Location: /WaveNet/views/frontoffice/forgot_password_request.php');
                    // exit; 
                }
                // --- Fin de la configuration PHPMailer ---

            } else {
                error_log("[handleForgotPasswordRequest] Tentative de réinitialisation pour email inconnu: {$email}");
            }

            // Toujours afficher un message de succès générique
            $_SESSION['message'] = "Si un compte correspondant à votre adresse e-mail existe, un message contenant les instructions de réinitialisation vient d'être envoyé.";
            header('Location: /WaveNet/views/frontoffice/forgot_password_request.php');
            exit;

        } catch (Exception $e) {
            error_log("Erreur lors de handleForgotPasswordRequest: " . $e->getMessage());
            $_SESSION['error'] = "Une erreur technique est survenue. Veuillez réessayer.";
            header('Location: /WaveNet/views/frontoffice/forgot_password_request.php');
            exit;
        }
    }

    /**
     * Affiche le formulaire de réinitialisation de mot de passe si le token est valide.
     */
    public function showResetPasswordForm() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = $_GET['token'] ?? null;

        if (empty($token)) {
            $_SESSION['error'] = "Token de réinitialisation manquant ou invalide.";
            header('Location: /WaveNet/views/frontoffice/login.php');
            exit;
        }

        try {
            require_once __DIR__ . '/../views/includes/config.php';
            $db = connectDB();

            // Vérifier si le token existe et n'est pas expiré
            $stmt = $db->prepare("SELECT email, expires_at FROM password_resets WHERE token = ?");
            $stmt->execute([$token]);
            $resetRequest = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$resetRequest) {
                $_SESSION['error'] = "Token de réinitialisation invalide.";
                header('Location: /WaveNet/views/frontoffice/login.php');
                exit;
            }

            // Vérifier l'expiration
            $expires_at = new DateTime($resetRequest['expires_at']);
            $now = new DateTime();

            if ($now > $expires_at) {
                // Supprimer le token expiré
                $stmtDelete = $db->prepare("DELETE FROM password_resets WHERE token = ?");
                $stmtDelete->execute([$token]);

                $_SESSION['error'] = "Le lien de réinitialisation a expiré après 30 minutes. Veuillez refaire une demande.";
                header('Location: /WaveNet/views/frontoffice/forgot_password_request.php');
                exit;
            }

            // Le token est valide, stocker le token dans la session pour le formulaire
            // (Alternative: passer le token en variable à la vue)
            $_SESSION['reset_token'] = $token; // On pourrait aussi le mettre dans un champ caché direct
            
            // Inclure la vue du formulaire de réinitialisation
            require __DIR__ . '/../views/frontoffice/reset_password.php';

        } catch (Exception $e) {
            error_log("Erreur lors de showResetPasswordForm: " . $e->getMessage());
            $_SESSION['error'] = "Une erreur technique est survenue lors de la vérification du lien.";
            header('Location: /WaveNet/views/frontoffice/login.php');
            exit;
        }
    }

    /**
     * Traite la soumission du formulaire de réinitialisation de mot de passe.
     * Vérifie le token, les mots de passe, met à jour l'utilisateur et supprime le token.
     */
    public function handleResetPassword() {
        error_log("[handleResetPassword] Fonction démarrée."); // LOG
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log("[handleResetPassword] ERREUR: Méthode non POST."); // LOG
            header('Location: /WaveNet/views/frontoffice/login.php');
            exit;
        }

        $token = $_POST['token'] ?? null;
        $newPassword = $_POST['new_password'] ?? null;
        $confirmPassword = $_POST['confirm_password'] ?? null;
        error_log("[handleResetPassword] Token reçu: " . ($token ? 'Oui' : 'Non') . ", Nv MDP reçu: " . ($newPassword ? 'Oui' : 'Non') ); // LOG

        // 1. Validation de base des entrées
        if (empty($token) || empty($newPassword) || empty($confirmPassword)) {
            $_SESSION['error'] = "Veuillez remplir tous les champs.";
            error_log("[handleResetPassword] ERREUR: Champs vides."); // LOG
            // Rediriger vers le formulaire avec le token pour ne pas le perdre
            header('Location: /WaveNet/controller/UserController.php?action=showResetPasswordForm&token=' . urlencode($token ?? ''));
            exit;
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['error'] = "Les mots de passe ne correspondent pas.";
            error_log("[handleResetPassword] ERREUR: Mots de passe non identiques."); // LOG
            header('Location: /WaveNet/controller/UserController.php?action=showResetPasswordForm&token=' . urlencode($token));
            exit;
        }

        // Vérifier la robustesse du mot de passe
        list($isStrongPassword, $passwordError) = $this->checkPasswordStrength($newPassword);
        if (!$isStrongPassword) {
            $_SESSION['error'] = $passwordError;
            error_log("[handleResetPassword] ERREUR: Mot de passe insuffisamment robuste: " . $passwordError); // LOG
            header('Location: /WaveNet/controller/UserController.php?action=showResetPasswordForm&token=' . urlencode($token));
            exit;
        }

        if (strlen($newPassword) < 8) { // Exemple: minimum 8 caractères
            $_SESSION['error'] = "Le mot de passe doit contenir au moins 8 caractères.";
            error_log("[handleResetPassword] ERREUR: Mot de passe trop court."); // LOG
            header('Location: /WaveNet/controller/UserController.php?action=showResetPasswordForm&token=' . urlencode($token));
            exit;
        }

        try {
            require_once __DIR__ . '/../views/includes/config.php';
            require_once __DIR__ . '/../models/Utilisateur.php';
            $db = connectDB();
            error_log("[handleResetPassword] Connexion DB OK."); // LOG

            // 2. Vérifier à nouveau le token (sécurité supplémentaire)
            $stmt = $db->prepare("SELECT email, expires_at FROM password_resets WHERE token = ?");
            $stmt->execute([$token]);
            $resetRequest = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log("[handleResetPassword] Vérification token BDD. Trouvé: " . ($resetRequest ? 'Oui' : 'Non') ); // LOG

            if (!$resetRequest) {
                $_SESSION['error'] = "Token de réinitialisation invalide ou déjà utilisé.";
                error_log("[handleResetPassword] ERREUR: Token non trouvé/invalide en BDD."); // LOG
                header('Location: /WaveNet/views/frontoffice/login.php');
                exit;
            }

            // 3. Vérifier l'expiration
            $expires_at = new DateTime($resetRequest['expires_at']);
            $now = new DateTime();
            $expired = $now > $expires_at;
            error_log("[handleResetPassword] Vérification expiration token. Expiré: " . ($expired ? 'Oui' : 'Non') ); // LOG

            if ($expired) {
                // Supprimer le token expiré
                $stmtDelete = $db->prepare("DELETE FROM password_resets WHERE token = ?");
                $stmtDelete->execute([$token]);
                $_SESSION['error'] = "Le lien de réinitialisation a expiré après 30 minutes. Veuillez refaire une demande.";
                error_log("[handleResetPassword] ERREUR: Token expiré."); // LOG
                header('Location: /WaveNet/views/frontoffice/forgot_password_request.php');
                exit;
            }
            
            $email = $resetRequest['email'];
            error_log("[handleResetPassword] Token valide pour email: " . $email); // LOG

            // Vérifier si le nouveau mot de passe est identique à l'ancien
            $stmtGetUser = $db->prepare("SELECT mot_de_passe FROM UTILISATEUR WHERE email = ?");
            $stmtGetUser->execute([$email]);
            $userData = $stmtGetUser->fetch(PDO::FETCH_ASSOC);
            
            if ($userData && password_verify($newPassword, $userData['mot_de_passe'])) {
                $_SESSION['error'] = "Le nouveau mot de passe ne peut pas être identique à l'ancien. Veuillez choisir un mot de passe différent.";
                error_log("[handleResetPassword] ERREUR: Tentative de réutilisation du même mot de passe."); // LOG
                header('Location: /WaveNet/controller/UserController.php?action=showResetPasswordForm&token=' . urlencode($token));
                exit;
            }

            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            error_log("[handleResetPassword] Hashage du nouveau mot de passe effectué."); // LOG
            $stmtUpdate = $db->prepare("UPDATE UTILISATEUR SET mot_de_passe = ? WHERE email = ?");
            $success = $stmtUpdate->execute([$hashedPassword, $email]);
            error_log("[handleResetPassword] Résultat de l'exécution UPDATE: " . ($success ? 'Succès (true)' : 'Échec (false)') ); // LOG
            // Log PDO error if execution failed
            if (!$success) {
                 error_log("[handleResetPassword] ERREUR PDO UPDATE: " . print_r($stmtUpdate->errorInfo(), true));
            }

            if ($success) {
                error_log("[handleResetPassword] Mise à jour MDP réussie pour {$email}."); // LOG
                // 5. Supprimer le token de la base de données
                $stmtDelete = $db->prepare("DELETE FROM password_resets WHERE token = ?");
                $deleted = $stmtDelete->execute([$token]);
                error_log("[handleResetPassword] Résultat suppression token: " . ($deleted ? 'Succès' : 'Échec') ); // LOG

                // Nettoyer le token de la session
                unset($_SESSION['reset_token']);

                // Rediriger vers la page de connexion avec un message de succès
                $_SESSION['success'] = "Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.";
                error_log("[handleResetPassword] Redirection vers login avec message succès."); // LOG
                header('Location: /WaveNet/views/frontoffice/login.php');
                exit;
            } else {
                 // L'erreur PDO est déjà loggée au-dessus
                error_log("[handleResetPassword] ERREUR: La mise à jour du mot de passe a échoué (success=false)."); // LOG
                throw new Exception("Erreur lors de la mise à jour du mot de passe."); // Sera attrapée par le catch externe
            }

        } catch (Exception $e) {
            error_log("[handleResetPassword] EXCEPTION: " . $e->getMessage()); // LOG
            $_SESSION['error'] = "Une erreur technique est survenue lors de la réinitialisation du mot de passe.";
            // Essayer de préserver le token si possible pour que l'utilisateur puisse réessayer
            $redirectToken = isset($token) ? urlencode($token) : '';
            header('Location: /WaveNet/controller/UserController.php?action=showResetPasswordForm&token=' . $redirectToken);
            exit;
        }
    }

    /**
     * Vérifie la robustesse du mot de passe selon les critères spécifiés
     * @param string $password Le mot de passe à vérifier
     * @return array [bool $isValid, string $errorMessage]
     */
    private function checkPasswordStrength($password) {
        $errors = [];
        
        // Vérifier la longueur minimum
        if (strlen($password) < 8) {
            $errors[] = "Le mot de passe doit contenir au moins 8 caractères";
        }
        
        // Vérifier la présence d'une majuscule
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins une lettre majuscule";
        }
        
        // Vérifier la présence d'un chiffre
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins un chiffre";
        }
        
        // Vérifier la présence d'un caractère spécial
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins un caractère spécial";
        }
        
        if (empty($errors)) {
            return [true, ""];
        } else {
            return [false, implode(". ", $errors)];
        }
    }

    /**
     * Permet à un administrateur de se connecter en tant qu'un autre utilisateur (impersonation)
     * Cette méthode est réservée aux administrateurs
     */
    public function impersonate() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Vérifier que l'utilisateur est admin
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_niveau']) || $_SESSION['user_niveau'] !== 'admin') {
            $_SESSION['error'] = "Vous n'avez pas les droits pour effectuer cette action.";
            header('Location: /WaveNet/views/frontoffice/login.php');
            exit;
        }
        
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!$id) {
            $_SESSION['error'] = "Utilisateur invalide.";
            header('Location: /WaveNet/views/backoffice/listeUtilisateurs.php');
            exit;
        }
        
        // Vérifier que l'utilisateur n'essaie pas de s'impersonate lui-même
        if ($_SESSION['user_id'] == $id) {
            $_SESSION['error'] = "Vous ne pouvez pas vous connecter en tant que vous-même.";
            header('Location: /WaveNet/views/backoffice/listeUtilisateurs.php');
            exit;
        }
        
        try {
            // Récupérer le DB
            require_once __DIR__ . '/../views/includes/config.php';
            $db = connectDB();
            
            // Vérifier que l'utilisateur cible existe
            require_once __DIR__ . '/../models/Utilisateur.php';
            $targetUser = Utilisateur::findById($db, $id);
            
            if (!$targetUser) {
                $_SESSION['error'] = "L'utilisateur cible n'existe pas.";
                header('Location: /WaveNet/views/backoffice/listeUtilisateurs.php');
                exit;
            }
            
            // Stocker l'ID de l'administrateur
            $adminId = $_SESSION['user_id'];
            $adminNom = $_SESSION['user_nom'];
            $adminPrenom = $_SESSION['user_prenom'];
            $adminNiveau = $_SESSION['user_niveau'];
            
            // Enregistrer dans les logs
            $stmt = $db->prepare("INSERT INTO logs_impersonation (id_admin, id_user, date_debut) VALUES (?, ?, NOW())");
            $success = $stmt->execute([$adminId, $id]);
            
            if (!$success) {
                // Gérer l'erreur sans bloquer la fonctionnalité
                error_log("Erreur lors de l'enregistrement de l'impersonation dans les logs: " . implode(', ', $stmt->errorInfo()));
            }
            
            // Récupérer l'ID du log pour la mise à jour ultérieure
            $logId = $db->lastInsertId();
            
            // Remplacer les variables de session par celles de l'utilisateur cible
            $_SESSION['impersonator_id'] = $adminId;
            $_SESSION['impersonator_nom'] = $adminNom;
            $_SESSION['impersonator_prenom'] = $adminPrenom;
            $_SESSION['impersonator_niveau'] = $adminNiveau;
            $_SESSION['impersonation_log_id'] = $logId;
            
            $_SESSION['user_id'] = $targetUser->getId();
            $_SESSION['user_nom'] = $targetUser->getNom();
            $_SESSION['user_prenom'] = $targetUser->getPrenom();
            $_SESSION['user_niveau'] = $targetUser->getNiveau();
            $_SESSION['user_email'] = $targetUser->getEmail();
            
            // Ajouter un message pour confirmer l'impersonation
            $_SESSION['impersonation_active'] = true;
            $_SESSION['success'] = "Vous êtes maintenant connecté en tant que " . $targetUser->getPrenom() . " " . $targetUser->getNom() . ".";
            
            // Rediriger vers le tableau de bord de l'utilisateur
            header('Location: /WaveNet/views/frontoffice/userDashboard.php');
            exit;
            
        } catch (Exception $e) {
            error_log("Erreur lors de l'impersonation: " . $e->getMessage());
            $_SESSION['error'] = "Une erreur est survenue lors de l'impersonation.";
            header('Location: /WaveNet/views/backoffice/listeUtilisateurs.php');
            exit;
        }
    }
    
    /**
     * Permet à un administrateur de revenir à son propre compte après une impersonation
     */
    public function stopImpersonation() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Vérifier qu'une impersonation est active
        if (!isset($_SESSION['impersonator_id'])) {
            header('Location: /WaveNet/views/frontoffice/userDashboard.php');
            exit;
        }
        
        try {
            // Récupérer le DB
            require_once __DIR__ . '/../views/includes/config.php';
            $db = connectDB();
            
            // Mettre à jour la date de fin dans les logs
            if (isset($_SESSION['impersonation_log_id'])) {
                $stmt = $db->prepare("UPDATE logs_impersonation SET date_fin = NOW() WHERE id = ?");
                $stmt->execute([$_SESSION['impersonation_log_id']]);
            }
            
            // Récupérer les infos de l'admin
            $adminId = $_SESSION['impersonator_id'];
            $adminNom = $_SESSION['impersonator_nom'];
            $adminPrenom = $_SESSION['impersonator_prenom'];
            $adminNiveau = $_SESSION['impersonator_niveau'];
            
            // Restaurer les variables de session de l'admin
            $_SESSION['user_id'] = $adminId;
            $_SESSION['user_nom'] = $adminNom;
            $_SESSION['user_prenom'] = $adminPrenom;
            $_SESSION['user_niveau'] = $adminNiveau;
            
            // Supprimer les variables d'impersonation
            unset($_SESSION['impersonator_id']);
            unset($_SESSION['impersonator_nom']);
            unset($_SESSION['impersonator_prenom']);
            unset($_SESSION['impersonator_niveau']);
            unset($_SESSION['impersonation_log_id']);
            unset($_SESSION['impersonation_active']);
            
            $_SESSION['success'] = "Vous êtes revenu à votre compte administrateur.";
            
            // Rediriger vers le tableau de bord administrateur
            header('Location: /WaveNet/views/backoffice/listeUtilisateurs.php');
            exit;
            
        } catch (Exception $e) {
            error_log("Erreur lors de l'arrêt de l'impersonation: " . $e->getMessage());
            $_SESSION['error'] = "Une erreur est survenue lors du retour à votre compte.";
            header('Location: /WaveNet/views/frontoffice/userDashboard.php');
            exit;
        }
    }

    // After setup2FADatabase() and before verifier2FA() method
    /**
     * Méthode pour vérifier si la 2FA est terminée pour la session actuelle
     * À utiliser dans les pages pour rediriger vers la vérification si nécessaire
     */
    public static function check2FAVerified() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Si l'utilisateur a un ID temporaire en attente de 2FA, c'est qu'il n'a pas encore vérifié son 2FA
        if (isset($_SESSION['temp_user_id']) && !isset($_SESSION['user_id'])) {
            header('Location: /WaveNet/controller/UserController.php?action=verifier2FA');
            exit;
        }
        
        return true;
    }

    /**
     * Envoie un email de vérification à l'utilisateur
     */
    public function sendEmailVerification() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: /WaveNet/views/frontoffice/login.php');
            exit;
        }
        
        require_once __DIR__ . '/../views/includes/config.php';
        require_once __DIR__ . '/../models/Utilisateur.php';
        $db = connectDB();
        
        // Récupérer l'utilisateur
        $user = Utilisateur::findById($db, $_SESSION['user_id']);
        if (!$user) {
            $_SESSION['error_message'] = "Utilisateur non trouvé.";
            header('Location: /WaveNet/views/frontoffice/account_activity.php');
            exit;
        }
        
        $email = $user->getEmail();
        $userId = $_SESSION['user_id'];
        
        try {
            // Générer un token de vérification
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
            
            // Vérifier si la table email_verification existe
            $tableCheck = $db->query("SHOW TABLES LIKE 'email_verification'");
            if ($tableCheck->rowCount() === 0) {
                // Créer la table si elle n'existe pas
                $db->exec("CREATE TABLE email_verification (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    id_utilisateur INT NOT NULL,
                    token VARCHAR(64) NOT NULL,
                    expires_at DATETIME NOT NULL,
                    FOREIGN KEY (id_utilisateur) REFERENCES UTILISATEUR(id_utilisateur) ON DELETE CASCADE
                )");
            }
            
            // Vérifier si la colonne email_verified existe dans la table UTILISATEUR
            $columnCheck = $db->query("SHOW COLUMNS FROM UTILISATEUR LIKE 'email_verified'");
            if ($columnCheck->rowCount() === 0) {
                // Ajouter la colonne si elle n'existe pas
                $db->exec("ALTER TABLE UTILISATEUR ADD COLUMN email_verified TINYINT(1) DEFAULT 0");
            }
            
            // Supprimer les anciens tokens pour cet utilisateur
            $stmt = $db->prepare("DELETE FROM email_verification WHERE id_utilisateur = ?");
            $stmt->execute([$userId]);
            
            // Insérer le nouveau token
            $stmt = $db->prepare("INSERT INTO email_verification (id_utilisateur, token, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $token, $expires]);
            
            // Construire l'URL de vérification
            $verificationUrl = "http://" . $_SERVER['HTTP_HOST'] . "/WaveNet/views/frontoffice/verify_email.php?token=" . $token;
            
            // Envoyer l'email avec PHPMailer
            require_once __DIR__ . '/../vendor/autoload.php';
            
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            
            try {
                // Paramètres du serveur SMTP
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'mezazighcharaf@gmail.com'; // Remplacer par votre adresse Gmail
                $mail->Password = 'ypzrvgxootlwsgta'; // Remplacer par votre mot de passe d'application
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                $mail->CharSet = 'UTF-8';
                
                // Destinataires
                $mail->setFrom('mezazighcharaf@gmail.com', 'WaveNet');
                $mail->addAddress($email, $user->getPrenom() . ' ' . $user->getNom());
                
                // Contenu
                $mail->isHTML(true);
                $mail->Subject = 'Vérification de votre adresse email - WaveNet';
                $mail->Body = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                        <div style='background-color: #4CAF50; padding: 20px; text-align: center; color: white;'>
                            <h1>Vérification de votre email</h1>
                        </div>
                        <div style='padding: 20px; background-color: #f9f9f9;'>
                            <p>Bonjour " . htmlspecialchars($user->getPrenom()) . ",</p>
                            <p>Merci d'avoir rejoint WaveNet. Pour compléter votre inscription et vérifier votre adresse email, veuillez cliquer sur le bouton ci-dessous :</p>
                            <div style='text-align: center; margin: 30px 0;'>
                                <a href='" . $verificationUrl . "' style='background-color: #4CAF50; color: white; padding: 12px 20px; text-decoration: none; border-radius: 4px; font-weight: bold;'>Vérifier mon email</a>
                            </div>
                            <p>Ou copiez et collez ce lien dans votre navigateur :</p>
                            <p style='word-break: break-all;'>" . $verificationUrl . "</p>
                            <p>Ce lien expirera dans 24 heures.</p>
                            <p>Si vous n'avez pas demandé cette vérification, vous pouvez ignorer cet email.</p>
                        </div>
                        <div style='padding: 20px; text-align: center; font-size: 12px; color: #666;'>
                            <p>Cet email a été envoyé automatiquement. Merci de ne pas y répondre.</p>
                            <p>&copy; " . date('Y') . " WaveNet. Tous droits réservés.</p>
                        </div>
                    </div>
                ";
                $mail->AltBody = "Bonjour " . $user->getPrenom() . ",\n\n"
                    . "Merci d'avoir rejoint WaveNet. Pour compléter votre inscription et vérifier votre adresse email, veuillez cliquer sur le lien ci-dessous :\n\n"
                    . $verificationUrl . "\n\n"
                    . "Ce lien expirera dans 24 heures.\n\n"
                    . "Si vous n'avez pas demandé cette vérification, vous pouvez ignorer cet email.\n\n"
                    . "Cet email a été envoyé automatiquement. Merci de ne pas y répondre.";
                
                $mail->send();
                
                $_SESSION['success_message'] = "Un email de vérification a été envoyé à votre adresse. Veuillez vérifier votre boîte de réception et vos spams.";
            } catch (Exception $e) {
                error_log("Erreur lors de l'envoi de l'email: " . $mail->ErrorInfo);
                $_SESSION['error_message'] = "Erreur lors de l'envoi de l'email de vérification. Veuillez réessayer plus tard.";
            }
            
        } catch (Exception $e) {
            error_log("Erreur lors de la génération du token: " . $e->getMessage());
            $_SESSION['error_message'] = "Une erreur s'est produite. Veuillez réessayer plus tard.";
        }
        
        header('Location: /WaveNet/views/frontoffice/account_activity.php');
        exit;
    }

    /**
     * Vérifie l'email d'un utilisateur avec le token fourni
     */
    public function verifyEmail() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $token = $_GET['token'] ?? '';
        
        if (empty($token)) {
            $_SESSION['error_message'] = "Aucun token de vérification fourni.";
            header('Location: /WaveNet/views/frontoffice/userDashboard.php');
            exit;
        }
        
        require_once __DIR__ . '/../views/includes/config.php';
        $db = connectDB();
        
        try {
            // Vérifier si la table email_verification existe
            $tableCheck = $db->query("SHOW TABLES LIKE 'email_verification'");
            if ($tableCheck->rowCount() === 0) {
                throw new Exception("Système de vérification d'email non configuré.");
            }
            
            // Vérifier le token
            $stmt = $db->prepare("SELECT id_utilisateur, expires_at FROM email_verification WHERE token = ?");
            $stmt->execute([$token]);
            $verification = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$verification) {
                throw new Exception("Token de vérification invalide ou déjà utilisé.");
            }
            
            // Vérifier si le token a expiré
            $expiresAt = new DateTime($verification['expires_at']);
            $now = new DateTime();
            
            if ($now > $expiresAt) {
                // Supprimer le token expiré
                $db->prepare("DELETE FROM email_verification WHERE token = ?")->execute([$token]);
                throw new Exception("Le lien de vérification a expiré. Veuillez demander un nouveau lien.");
            }
            
            // Marquer l'email comme vérifié
            $userId = $verification['id_utilisateur'];
            $stmt = $db->prepare("UPDATE UTILISATEUR SET email_verified = 1 WHERE id_utilisateur = ?");
            $stmt->execute([$userId]);
            
            // Supprimer le token utilisé
            $db->prepare("DELETE FROM email_verification WHERE token = ?")->execute([$token]);
            
            $_SESSION['success_message'] = "Votre adresse email a été vérifiée avec succès.";
            
            // Si l'utilisateur est connecté, le rediriger vers le tableau de bord
            if (isset($_SESSION['user_id'])) {
                header('Location: /WaveNet/views/frontoffice/userDashboard.php');
            } else {
                // Sinon, le rediriger vers la page de connexion
                header('Location: /WaveNet/views/frontoffice/login.php');
            }
            exit;
            
        } catch (Exception $e) {
            $_SESSION['error_message'] = $e->getMessage();
            header('Location: /WaveNet/views/frontoffice/userDashboard.php');
            exit;
        }
    }
}

// Direct access router - handle action parameter when controller is called directly
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    // For debugging
    $requested_action = $_GET['action'] ?? 'none (GET)'; // Check GET first
    if ($requested_action === 'none (GET)') {
        $requested_action = $_POST['action'] ?? 'none (POST)'; // Then check POST
    }
    error_log("[UserController.php Direct Router] Action détectée: " . $requested_action);
    
    // Instantiate the controller
    $controller = new UserController();
    
    // Check if an action parameter is defined
    // Use the detected action
    if ($requested_action !== 'none (GET)' && $requested_action !== 'none (POST)') {
        // Call the appropriate method based on the action parameter
        if (method_exists($controller, $requested_action)) {
            error_log("[UserController.php Direct Router] Appel de la méthode: " . $requested_action);
            $controller->$requested_action();
        } else {
            // Handle invalid action
            error_log("[UserController.php Direct Router] Action invalide: " . $requested_action);
            // Decide on a default action or error page
            // header('Location: /WaveNet/views/frontoffice/login.php'); // Example redirect
            // exit;
        }
    } else {
        // No action specified, redirect to default page or show error
        error_log("[UserController.php Direct Router] Aucune action spécifiée.");
        // header('Location: /WaveNet/views/frontoffice/login.php'); // Example redirect
        // exit;
    }
}
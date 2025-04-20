<?php
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
                'points_verts' => 0,
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
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /WaveNet/views/frontoffice/login.php');
            exit;
        }
        require_once __DIR__ . '/../models/Utilisateur.php';
        require_once __DIR__ . '/../views/includes/config.php';
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
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /WaveNet/views/frontoffice/login.php');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../models/Utilisateur.php';
            require_once __DIR__ . '/../views/includes/config.php';
            $user = Utilisateur::findById($db, $_SESSION['user_id']);
            if ($user) {
                $nom = $_POST['nom'] ?? $user['nom'];
                $prenom = $_POST['prenom'] ?? $user['prenom'];
                $email = $_POST['email'] ?? $user['email'];
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
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /WaveNet/views/frontoffice/login.php');
            exit;
        }
        require_once __DIR__ . '/../models/Utilisateur.php';
        require_once __DIR__ . '/../views/includes/config.php';
        $stmt = $db->prepare("DELETE FROM UTILISATEUR WHERE id_utilisateur = :id");
        $stmt->execute(['id' => $_SESSION['user_id']]);
        session_destroy();
        header('Location: /WaveNet/views/frontoffice/register.php');
        exit;
    }
    public function listerUtilisateurs() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /WaveNet/views/frontoffice/login.php');
            exit;
        }
        require_once __DIR__ . '/../models/Utilisateur.php';
        require_once __DIR__ . '/../views/includes/config.php';
        $user = Utilisateur::findById($db, $_SESSION['user_id']);
        if ($user && $user['niveau'] === 'admin') {
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
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /WaveNet/views/frontoffice/login.php');
            exit;
        }
        require_once __DIR__ . '/../models/Utilisateur.php';
        require_once __DIR__ . '/../views/includes/config.php';
        $user = Utilisateur::findById($db, $_SESSION['user_id']);
        if ($user && $user['niveau'] === 'admin') {
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
                    $_SESSION['user_id'] = $user->getId();
                    $_SESSION['user_nom'] = $user->getNom();
                    $_SESSION['user_prenom'] = $user->getPrenom();
                    $_SESSION['user_niveau'] = $user->getNiveau();
                    if ($user->getNiveau() === 'admin') {
                        header('Location: /WaveNet/views/backoffice/index.php');
                    } else {
                        header('Location: /WaveNet/views/frontoffice/userDashboard.php');
                    }
                    exit;
                } else {
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
            header('Location: /WaveNet/views/frontoffice/login.php');
            exit;
        }
    }
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
            require_once __DIR__ . '/../models/Utilisateur.php';
            require_once __DIR__ . '/../views/includes/config.php';
            try {
                $db = connectDB();
                if (!$db) {
                    throw new Exception("Erreur de connexion à la base de données.");
                }
                $existingUser = Utilisateur::findByEmail($db, $email);
                if ($existingUser) {
                    $_SESSION['register_error'] = "Cet email est déjà utilisé.";
                    header('Location: /WaveNet/views/frontoffice/register.php');
                    exit;
                }
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
                    'points_verts' => 0,
                    'id_quartier' => $id_quartier
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
            $user = Utilisateur::findById($db, $userId);
            if (!$user) {
                $_SESSION['error_messages'] = ["Utilisateur non trouvé."];
                header("Location: /WaveNet/views/frontoffice/editProfile.php");
                exit;
            }
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
                $db->exec("ALTER TABLE UTILISATEUR ADD COLUMN newsletter TINYINT(1) DEFAULT 0, ADD COLUMN evenements TINYINT(1) DEFAULT 0");
            }
            if (method_exists($user, 'setNewsletter')) {
                $user->setNewsletter($newsletter);
            }
            if (method_exists($user, 'setEvenements')) {
                $user->setEvenements($evenements);
            }
            if (!empty($newPassword) && !empty($currentPassword) && password_verify($currentPassword, $user->getMotDePasse())) {
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
    public static function route() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $controller = new UserController();
        $action = isset($_GET['action']) ? $_GET['action'] : 'default';
        switch ($action) {
            case 'login':
                $controller->login();
                break;
            case 'register':
                $controller->register();
                break;
            case 'logout':
                $controller->logout();
                break;
            case 'supprimerUtilisateurAdmin':
                $controller->supprimerUtilisateurAdmin();
                break;
            case 'bloquerUtilisateur':
                $controller->bloquerUtilisateur();
                break;
            case 'debloquerUtilisateur':
                $controller->debloquerUtilisateur();
                break;
            case 'ajouterTransport':
                $controller->ajouterTransport();
                break;
            case 'updateProfile':
                $controller->updateProfile();
                break;
            default:
                header("Location: /WaveNet/views/index.php");
                exit;
        }
    }
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = array();
        session_destroy();
        header('Location: /WaveNet/views/index.php');
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
    public function debloquerUtilisateur() {
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
        $stmt = $db->prepare("SELECT id_utilisateur FROM UTILISATEUR WHERE id_utilisateur = :id");
        $stmt->execute(['id' => $id]);
        if (!$stmt->fetch()) {
            $_SESSION['error'] = "Utilisateur non trouvé.";
            header('Location: /WaveNet/views/backoffice/listeUtilisateurs.php');
            exit;
        }
        try {
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
    public function ajouterTransport() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            header("Location: /WaveNet/views/frontoffice/login.php");
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idUtilisateur = isset($_POST['id_utilisateur']) ? intval($_POST['id_utilisateur']) : $_SESSION['user_id'];
            $typeTransport = isset($_POST['type_transport']) ? $_POST['type_transport'] : null;
            $distanceParcourue = isset($_POST['distance_parcourue']) ? floatval($_POST['distance_parcourue']) : 0;
            $frequence = isset($_POST['frequence']) ? intval($_POST['frequence']) : 0;
            $dateDerniereUtilisation = isset($_POST['date_derniere_utilisation']) && !empty($_POST['date_derniere_utilisation']) 
                ? $_POST['date_derniere_utilisation'] 
                : date('Y-m-d'); 
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
            if (empty($erreurs)) {
                require_once __DIR__ . '/../views/includes/config.php';
                $db = connectDB();
                require_once __DIR__ . '/../models/Transport.php';
                try {
                    $tableExists = $db->query("SHOW TABLES LIKE 'TRANSPORT_TYPE'")->rowCount() > 0;
                    if (!$tableExists) {
                        $db->exec("CREATE TABLE TRANSPORT_TYPE (
                            id_type INT AUTO_INCREMENT PRIMARY KEY,
                            nom VARCHAR(100) NOT NULL,
                            eco_index FLOAT NOT NULL
                        )");
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
                    $queryEcoIndex = $db->prepare("SELECT eco_index FROM TRANSPORT_TYPE WHERE nom = ?");
                    $queryEcoIndex->execute([$typeTransport]);
                    $ecoIndexData = $queryEcoIndex->fetch(PDO::FETCH_ASSOC);
                    if ($ecoIndexData) {
                        $ecoIndex = floatval($ecoIndexData['eco_index']);
                    } else {
                        $ecoIndex = 5.0; 
                        $insertNewType = $db->prepare("INSERT INTO TRANSPORT_TYPE (nom, eco_index) VALUES (?, ?)");
                        $insertNewType->execute([$typeTransport, $ecoIndex]);
                    }
                    $transportData = [
                        'id_utilisateur' => $idUtilisateur,
                        'type_transport' => $typeTransport,
                        'distance_parcourue' => $distanceParcourue,
                        'frequence' => $frequence,
                        'eco_index' => $ecoIndex,
                        'date_derniere_utilisation' => $dateDerniereUtilisation
                    ];
                    if (Transport::create($db, $transportData)) {
                        $pointsVerts = ceil($ecoIndex * $distanceParcourue * $frequence / 10);
                        require_once __DIR__ . '/../models/Utilisateur.php';
                        $utilisateur = Utilisateur::findById($db, $idUtilisateur);
                        if ($utilisateur) {
                            $pointsActuels = $utilisateur->getPointsVerts();
                            $utilisateur->setPointsVerts($pointsActuels + $pointsVerts);
                            $utilisateur->update($db);
                            $_SESSION['user_points'] = $pointsActuels + $pointsVerts;
                            $_SESSION['success_message'] = "Transport ajouté avec succès ! Vous avez gagné $pointsVerts points verts.";
                        } else {
                            $_SESSION['success_message'] = "Transport ajouté avec succès !";
                        }
                        header("Location: /WaveNet/views/frontoffice/manageTransport.php");
                        exit;
                    } else {
                        $erreurs[] = "Erreur lors de l'ajout du transport.";
                    }
                } catch (Exception $e) {
                    $erreurs[] = "Erreur: " . $e->getMessage();
                }
            }
            if (!empty($erreurs)) {
                $_SESSION['error_messages'] = $erreurs;
                header("Location: /WaveNet/views/frontoffice/manageTransport.php");
                exit;
            }
        } else {
            header("Location: /WaveNet/views/frontoffice/manageTransport.php");
            exit;
        }
    }
}
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    error_log("UserController direct access - Action: " . ($_GET['action'] ?? 'none'));
    $controller = new UserController();
    if (isset($_GET['action'])) {
        $action = $_GET['action'];
        if (method_exists($controller, $action)) {
            error_log("Calling method: " . $action);
            $controller->$action();
        } else {
            error_log("Invalid action: " . $action);
            header('Location: /WaveNet/views/frontoffice/register.php');
            exit;
        }
    } else {
        error_log("No action specified");
        header('Location: /WaveNet/views/frontoffice/register.php');
        exit;
    }
}

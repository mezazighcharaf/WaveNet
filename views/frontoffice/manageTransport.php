<?php
if (session_status() === PHP_SESSION_NONE) { 
    session_start();
}
/* Commenté pour déboguer
if (!isset($_SESSION['user_id'])) {
    header("Location: /WaveNet/views/frontoffice/login.php");
    exit;
}
*/

// Ajout temporaire pour le débogage - simuler un utilisateur connecté
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // Utilisez un ID d'utilisateur valide dans votre base de données
}

$pageTitle = 'Gérer mes transports';
$activePage = 'transport'; 
require_once '../../views/includes/config.php';
$db = connectDB();
if (!$db) {
    error_log("Erreur: Impossible d'établir une connexion à la base de données.");
    die("Erreur de connexion à la base de données. Veuillez réessayer plus tard.");
}
require_once '../../models/Utilisateur.php';
require_once '../../models/Transport.php'; 
$userId = $_SESSION['user_id'];
try {
    $userDbData = Utilisateur::findById($db, $userId);
    if (!$userDbData) {
        header("Location: /WaveNet/views/frontoffice/login.php?error=user_not_found");
        exit;
    }
} catch (Exception $e) {
    error_log("Erreur lors de la récupération des données utilisateur (manageTransport): " . $e->getMessage());
    die("Une erreur est survenue.");
}
$transports = [];
try {
    $transports = Transport::findByUserId($db, $userId);
} catch (Exception $e) {
    error_log("Erreur lors de la récupération des données de transport réelles: " . $e->getMessage());
}
$transportTypes = [];
try {
    $tableExists = $db->query("SHOW TABLES LIKE 'TRANSPORT_TYPE'")->rowCount() > 0;
    if ($tableExists) {
        $query = $db->query("SELECT nom, eco_index FROM TRANSPORT_TYPE ORDER BY eco_index DESC");
        $transportTypes = $query->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    error_log("Erreur lors de la récupération des types de transport: " . $e->getMessage());
}
$errorMessages = $_SESSION['error_messages'] ?? null;
$successMessage = $_SESSION['success_message'] ?? null;
unset($_SESSION['error_messages'], $_SESSION['success_message']);
require_once '../includes/userHeader.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | WaveNet</title>
    <link rel="stylesheet" href="../../views/assets/css/style11.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <!-- Élément de fond -->
    <div class="page-background"></div>
    <!-- HERO SECTION -->
    <section class="hero" style="min-height: 35vh;">
        <div class="hero-container">
            <div class="hero-content">
                <h1 class="hero-title">Gestion de mes <span style="color: var(--accent-green);">transports</span></h1>
                <p class="hero-text">Enregistrez vos habitudes de transport et suivez votre impact écologique au quotidien.</p>
            </div>
            <div class="hero-image-container">
                <img src="../assets/img/transport_ecologique.jpg" alt="Transport écologique" class="hero-image">
            </div>
        </div>
    </section>
    <!-- PAGE CONTENT -->
    <div class="container" style="margin-top: -3rem; position: relative; z-index: 10; margin-bottom: 3rem;">
        <div class="transport-section">
            <?php if (!empty($errorMessages)): ?>
                <div id="transport-message" style="margin-bottom: 1.5rem;">
                    <div class="transport-summary" style="border-left: 4px solid #d32f2f; background-color: rgba(244, 67, 54, 0.1);">
                        <h3 style="color: #d32f2f;">Des erreurs sont survenues</h3>
                        <ul>
                            <?php foreach ((array)$errorMessages as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (!empty($successMessage)): ?>
                <div id="transport-message" style="margin-bottom: 1.5rem;">
                    <div class="transport-summary" style="border-left: 4px solid #2e7d32; background-color: rgba(76, 175, 80, 0.1);">
                        <h3 style="color: #2e7d32;">Succès</h3>
                        <p><?= htmlspecialchars($successMessage) ?></p>
                    </div>
                </div>
            <?php endif; ?>
            <!-- Transport summary -->
            <div class="transport-dashboard">
                <!-- Liste des transports -->
                <div class="transport-card">
                    <div class="card-header">
                        <h2><i class="fas fa-list"></i> Mes transports</h2>
                    </div>
                    <?php if (empty($transports)): ?>
                        <div class="empty-state">
                            <i class="fas fa-route empty-icon"></i>
                            <p>Aucun transport enregistré</p>
                            <p class="empty-hint">Utilisez le formulaire ci-dessous pour commencer</p>
                        </div>
                    <?php else: ?>
                        <div class="transport-table-container">
                            <table class="transport-table">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Distance</th>
                                        <th>Fréquence</th>
                                        <th>Éco-index</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transports as $t): ?>
                                        <?php 
                                            $ecoClass = 'medium';
                                            if (floatval($t['eco_index']) >= 7) {
                                                $ecoClass = 'high';
                                            } elseif (floatval($t['eco_index']) <= 4) {
                                                $ecoClass = 'low';
                                            }
                                        ?>
                                        <tr>
                                            <td>
                                                <span class="transport-type">
                                                    <?php
                                                        $icon = 'walking';
                                                        switch(strtolower($t['type_transport'])) {
                                                            case 'vélo': $icon = 'bicycle'; break;
                                                            case 'voiture thermique': $icon = 'car'; break;
                                                            case 'voiture électrique': $icon = 'charging-station'; break;
                                                            case 'transport en commun (bus)': $icon = 'bus'; break;
                                                            case 'transport en commun (tram/métro)': $icon = 'subway'; break;
                                                            case 'covoiturage': $icon = 'users'; break;
                                                            case 'trottinette électrique': $icon = 'bolt'; break;
                                                        }
                                                    ?>
                                                    <i class="fas fa-<?= $icon ?>"></i>
                                                    <?= htmlspecialchars($t['type_transport']) ?>
                                                </span>
                                            </td>
                                            <td><span class="badge badge-info"><?= number_format(floatval($t['distance_parcourue']), 1) ?> km</span></td>
                                            <td><span class="badge badge-primary"><?= intval($t['frequence']) ?> fois/sem</span></td>
                                            <td><span class="eco-index <?= $ecoClass ?>"><?= number_format(floatval($t['eco_index']), 1) ?></span></td>
                                            <td class="actions">
                                                <button class="btn-icon edit-transport" data-id="<?= $t['id_transport'] ?>" data-type="<?= htmlspecialchars($t['type_transport']) ?>" data-distance="<?= $t['distance_parcourue'] ?>" data-frequence="<?= $t['frequence'] ?>" title="Modifier"><i class="fas fa-edit"></i></button>
                                                <a href="/WaveNet/controller/TransportController.php?action=supprimerTransport&id=<?= $t['id_transport'] ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce transport ?')" class="btn-icon" title="Supprimer"><i class="fas fa-trash-alt"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php 
                            $totalDistance = 0;
                            $avgEcoIndex = 0;
                            $bestTransport = ['eco_index' => 0, 'name' => ''];
                            foreach ($transports as $t) {
                                $totalDistance += floatval($t['distance_parcourue']) * intval($t['frequence']);
                                $avgEcoIndex += floatval($t['eco_index']);
                                if (floatval($t['eco_index']) > $bestTransport['eco_index']) {
                                    $bestTransport['eco_index'] = floatval($t['eco_index']);
                                    $bestTransport['name'] = $t['type_transport'];
                                }
                            }
                            $avgEcoIndex = count($transports) > 0 ? $avgEcoIndex / count($transports) : 0;
                        ?>
                        <div class="stats-summary">
                            <div class="stat-item">
                                <div class="stat-icon"><i class="fas fa-route"></i></div>
                                <div class="stat-content">
                                    <div class="stat-value"><?= number_format($totalDistance, 1) ?> km</div>
                                    <div class="stat-label">Distance parcourue par semaine</div>
                                </div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-icon"><i class="fas fa-leaf"></i></div>
                                <div class="stat-content">
                                    <div class="stat-value"><?= number_format($avgEcoIndex, 1) ?>/10</div>
                                    <div class="stat-label">Éco-index moyen</div>
                                </div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-icon"><i class="fas fa-award"></i></div>
                                <div class="stat-content">
                                    <div class="stat-value eco-transport"><?= htmlspecialchars($bestTransport['name'] ?: 'Aucun') ?></div>
                                    <div class="stat-label">Votre transport le plus écologique</div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <!-- Formulaire d'ajout simplifié -->
                <div class="transport-card">
                    <div class="card-header">
                        <h2><i class="fas fa-plus-circle"></i> Ajouter un transport</h2>
                    </div>
                    <?php if (isset($_SESSION['error_messages'])): ?>
                        <div class="alert alert-danger">
                            <ul>
                                <?php foreach ($_SESSION['error_messages'] as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php unset($_SESSION['error_messages']); ?>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success">
                            <?= htmlspecialchars($_SESSION['success_message']) ?>
                        </div>
                        <?php unset($_SESSION['success_message']); ?>
                    <?php endif; ?>
                    <form action="/WaveNet/controller/UserController.php?action=ajouterTransport" method="post" class="simplified-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="type_transport">Type</label>
                                <select id="type_transport" name="type_transport" required class="form-control">
                                    <option value="" disabled selected>Choisir...</option>
                                    <option value="Marche">Marche</option>
                                    <option value="Vélo">Vélo</option>
                                    <option value="Trottinette électrique">Trottinette électrique</option>
                                    <option value="Transport en commun (Bus)">Bus</option>
                                    <option value="Transport en commun (Tram/Métro)">Tram/Métro</option>
                                    <option value="Covoiturage">Covoiturage</option>
                                    <option value="Voiture électrique">Voiture électrique</option>
                                    <option value="Voiture thermique">Voiture thermique</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="distance_parcourue">Distance (km)</label>
                                <input type="number" id="distance_parcourue" name="distance_parcourue" step="0.1" min="0.1" required class="form-control" placeholder="Ex: 5.5">
                            </div>
                            <div class="form-group">
                                <label for="frequence">Fréquence/semaine</label>
                                <input type="number" id="frequence" name="frequence" min="1" max="21" required class="form-control" placeholder="Ex: 5">
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus-circle"></i> Ajouter
                            </button>
                        </div>
                    </form>
                    <div class="eco-tip">
                        <i class="fas fa-leaf eco-tip-icon"></i>
                        <p>Les transports écologiques vous rapportent plus de points verts !</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Formulaire Modifier Transport (Modal) -->
    <div class="modal" id="editTransportModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Modifier le transport</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <form action="/WaveNet/controller/TransportController.php?action=modifierTransport" method="post" class="simplified-form">
                    <input type="hidden" id="edit_id_transport" name="id_transport" value="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_type_transport">Type</label>
                            <select id="edit_type_transport" name="type_transport" required class="form-control">
                                <option value="" disabled>Choisir...</option>
                                <option value="Marche">Marche</option>
                                <option value="Vélo">Vélo</option>
                                <option value="Trottinette électrique">Trottinette électrique</option>
                                <option value="Transport en commun (Bus)">Bus</option>
                                <option value="Transport en commun (Tram/Métro)">Tram/Métro</option>
                                <option value="Covoiturage">Covoiturage</option>
                                <option value="Voiture électrique">Voiture électrique</option>
                                <option value="Voiture thermique">Voiture thermique</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_distance_parcourue">Distance (km)</label>
                            <input type="number" id="edit_distance_parcourue" name="distance_parcourue" step="0.1" min="0.1" required class="form-control">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_frequence">Fréquence (fois/semaine)</label>
                            <input type="number" id="edit_frequence" name="frequence" min="1" max="7" required class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="edit_date_derniere_utilisation">Dernière utilisation</label>
                            <input type="date" id="edit_date_derniere_utilisation" name="date_derniere_utilisation" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary close-modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <style>
    .transport-dashboard {
        display: flex;
        flex-direction: column;
        gap: 2rem;
        margin-bottom: 2rem;
        max-width: 1000px;
        margin: 0 auto 2rem;
    }
    .transport-card {
        background-color: var(--white);
        border-radius: var(--border-radius);
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    .card-header {
        background-color: var(--light-green);
        padding: 1rem 1.5rem;
        border-bottom: 2px solid var(--accent-green);
    }
    .card-header h2 {
        color: var(--dark-green);
        font-size: 1.25rem;
        margin: 0;
        display: flex;
        align-items: center;
    }
    .card-header h2 i {
        margin-right: 0.5rem;
        color: var(--accent-green);
    }
    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 3rem 1rem;
        color: var(--gray-500);
        text-align: center;
    }
    .empty-icon {
        font-size: 3rem;
        color: var(--gray-300);
        margin-bottom: 1rem;
    }
    .empty-hint {
        margin-top: 0.5rem;
        font-size: 0.9rem;
    }
    .transport-table-container {
        padding: 0.5rem;
        overflow-x: auto;
    }
    .transport-table {
        width: 100%;
        border-collapse: collapse;
    }
    .transport-table th {
        background-color: var(--light-green);
        color: var(--dark-green);
        font-weight: 600;
        text-align: left;
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
    }
    .transport-table td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--gray-200);
        font-size: 0.95rem;
    }
    .transport-type {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .transport-type i {
        color: var(--accent-green);
    }
    .badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .badge-info {
        background-color: rgba(0, 123, 255, 0.1);
        color: #0066cc;
    }
    .badge-primary {
        background-color: rgba(110, 168, 76, 0.1);
        color: #4c7c3a;
    }
    .eco-index {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 50%;
        font-weight: 700;
        font-size: 0.85rem;
    }
    .eco-index.high {
        background-color: rgba(76, 175, 80, 0.2);
        color: #2e7d32;
    }
    .eco-index.medium {
        background-color: rgba(255, 193, 7, 0.2);
        color: #f57c00;
    }
    .eco-index.low {
        background-color: rgba(244, 67, 54, 0.2);
        color: #d32f2f;
    }
    .btn-icon {
        background: none;
        border: none;
        color: var(--gray-500);
        cursor: pointer;
        padding: 0.25rem;
        transition: color 0.2s;
    }
    .btn-icon:hover {
        color: var(--accent-green);
    }
    .btn-icon i {
        font-size: 1rem;
    }
    .stats-summary {
        display: flex;
        flex-direction: column;
        padding: 1.5rem;
        background-color: var(--light-green);
        border-top: 1px solid var(--gray-200);
    }
    .stat-item {
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
        padding: 0.75rem;
        background-color: rgba(255, 255, 255, 0.7);
        border-radius: var(--border-radius);
    }
    .stat-item:last-child {
        margin-bottom: 0;
    }
    .stat-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 3rem;
        height: 3rem;
        background-color: var(--accent-green);
        color: white;
        border-radius: 50%;
        margin-right: 1rem;
        font-size: 1.25rem;
    }
    .stat-content {
        flex: 1;
    }
    .stat-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--dark-green);
        margin-bottom: 0.25rem;
    }
    .stat-label {
        font-size: 0.85rem;
        color: var(--gray-500);
    }
    .eco-transport {
        color: var(--accent-green);
    }
    .simplified-form {
        padding: 1.5rem;
    }
    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }
    .form-group {
        margin-bottom: 1rem;
    }
    .form-group label {
        display: block;
        font-weight: 500;
        margin-bottom: 0.5rem;
        color: var(--text-color);
        font-size: 0.9rem;
    }
    .form-control {
        width: 100%;
        padding: 0.6rem 0.75rem;
        border: 1px solid var(--gray-300);
        border-radius: var(--border-radius);
        font-size: 0.95rem;
        transition: all 0.2s;
    }
    .form-control:focus {
        border-color: var(--accent-green);
        box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.2);
        outline: none;
    }
    .form-actions {
        margin-top: 1.5rem;
        text-align: right;
    }
    .btn-primary {
        background-color: var(--accent-green);
        color: white;
        border: none;
        padding: 0.6rem 1.25rem;
        border-radius: var(--border-radius);
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    .btn-primary:hover {
        background-color: var(--dark-green);
        transform: translateY(-2px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    .eco-tip {
        display: flex;
        align-items: center;
        padding: 1rem;
        margin: 1rem 1.5rem;
        background-color: rgba(76, 175, 80, 0.1);
        border-radius: var(--border-radius);
    }
    .eco-tip-icon {
        color: var(--accent-green);
        font-size: 1.25rem;
        margin-right: 0.75rem;
    }
    .eco-tip p {
        margin: 0;
        font-size: 0.85rem;
        color: var(--dark-green);
    }
    .alert {
        padding: 1rem;
        margin: 1rem 1.5rem;
        border-radius: var(--border-radius);
    }
    .alert-danger {
        background-color: rgba(244, 67, 54, 0.1);
        border-left: 3px solid #d32f2f;
        color: #d32f2f;
    }
    .alert-success {
        background-color: rgba(76, 175, 80, 0.1);
        border-left: 3px solid #2e7d32;
        color: #2e7d32;
    }
    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
        .stats-summary {
            padding: 1rem;
        }
        .stat-item {
            padding: 0.5rem;
        }
        .stat-icon {
            width: 2.5rem;
            height: 2.5rem;
            font-size: 1rem;
        }
    }
    /* Styles pour la modal */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.4);
        overflow: auto;
    }
    
    .modal-content {
        background-color: white;
        margin: 10% auto;
        border-radius: 5px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        width: 80%;
        max-width: 600px;
        animation: modalfade 0.3s;
    }
    
    @keyframes modalfade {
        from {opacity: 0; transform: translateY(-30px);}
        to {opacity: 1; transform: translateY(0);}
    }
    
    .modal-header {
        padding: 15px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .modal-header h3 {
        margin: 0;
        color: var(--dark-green);
    }
    
    .close-modal {
        color: #aaa;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }
    
    .close-modal:hover {
        color: var(--dark-green);
    }
    
    .modal-body {
        padding: 15px;
    }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gestion des messages d'erreur et de succès
            <?php if (!empty($errorMessages)): ?>
                <?php foreach ($errorMessages as $error): ?>
                    showError("<?= addslashes($error) ?>");
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if (!empty($successMessage)): ?>
                showSuccess("<?= addslashes($successMessage) ?>");
            <?php endif; ?>

            // Modal pour modifier un transport
            const modal = document.getElementById('editTransportModal');
            const editButtons = document.querySelectorAll('.edit-transport');
            const closeButtons = document.querySelectorAll('.close-modal');
            
            // Ouvrir la modal
            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Récupérer les données du transport
                    const id = this.getAttribute('data-id');
                    const type = this.getAttribute('data-type');
                    const distance = this.getAttribute('data-distance');
                    const frequence = this.getAttribute('data-frequence');
                    
                    // Remplir le formulaire
                    document.getElementById('edit_id_transport').value = id;
                    document.getElementById('edit_type_transport').value = type;
                    document.getElementById('edit_distance_parcourue').value = distance;
                    document.getElementById('edit_frequence').value = frequence;
                    
                    // Afficher la modal
                    modal.style.display = 'block';
                });
            });
            
            // Fermer la modal
            closeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    modal.style.display = 'none';
                });
            });
            
            // Fermer la modal si on clique en dehors
            window.addEventListener('click', function(event) {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            });
        });
        
        function showError(message) {
            const alert = document.createElement('div');
            alert.className = 'alert alert-danger';
            alert.innerHTML = message;
            document.querySelector('.transport-dashboard').prepend(alert);
            
            // Faire défiler vers l'alerte
            alert.scrollIntoView({ behavior: 'smooth', block: 'start' });
            
            // Supprimer l'alerte après 5 secondes
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }, 5000);
        }
        
        function showSuccess(message) {
            const alert = document.createElement('div');
            alert.className = 'alert alert-success';
            alert.innerHTML = message;
            document.querySelector('.transport-dashboard').prepend(alert);
            
            // Faire défiler vers l'alerte
            alert.scrollIntoView({ behavior: 'smooth', block: 'start' });
            
            // Supprimer l'alerte après 5 secondes
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }, 5000);
        }
    </script>
<?php
require_once '../includes/footer.php';
?>
</body>
</html> 

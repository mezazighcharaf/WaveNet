<?php
// File: /WaveNet/controller/ExportController.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../views/includes/config.php';
require_once __DIR__ . '/../models/Utilisateur.php';

class ExportController {
    
    /**
     * Checks if the user is authenticated and authorized to export data
     */
    private static function checkAuthentication() {
        // Verify user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Non autorisé. Utilisateur non connecté.']);
            exit;
        }

        // Get the requested user ID from the URL parameter
        $requestedUserId = isset($_GET['user_id']) ? intval($_GET['user_id']) : $_SESSION['user_id'];

        // Verify the logged-in user is requesting their own data
        // or is an admin (if you have such functionality)
        if ($requestedUserId !== $_SESSION['user_id'] && $_SESSION['user_niveau'] !== 'admin') {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Non autorisé. Vous ne pouvez exporter que vos propres données.']);
            exit;
        }

        return $requestedUserId;
    }

    /**
     * Main method to handle data export requests
     */
    public static function exportUserData() {
        // Ensure all required tables exist
        // setupRGPDTables(); // Retrait de cette ligne qui cause l'erreur
        
        // Check authentication and get the user ID
        $userId = self::checkAuthentication();
        
        // Get the requested format
        $format = isset($_GET['format']) ? strtolower($_GET['format']) : 'json';
        
        // Get all user data
        $userData = self::collectUserData($userId);
        
        // Export in the requested format
        if ($format === 'json') {
            self::exportAsJson($userData);
        } elseif ($format === 'pdf') {
            self::exportAsPdf($userData);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Format non pris en charge. Utilisez "json" ou "pdf".']);
            exit;
        }
    }

    /**
     * Collects all user data from various tables
     */
    private static function collectUserData($userId) {
        $db = connectDB();
        $data = [];
        
        // 1. Collect user profile information
        try {
            $stmt = $db->prepare("SELECT id_utilisateur, nom, prenom, email, 
                                twofa_enabled, email_verified
                                FROM UTILISATEUR WHERE id_utilisateur = ?");
            $stmt->execute([$userId]);
            $data['profile'] = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Si le résultat est null, créer un objet vide
            if (!$data['profile']) {
                $data['profile'] = [];
            }
            
            // Ajouter la date de l'export pour l'information utilisateur
            $data['profile']['date_export'] = date('Y-m-d H:i:s');
            
            // Remove sensitive information
            unset($data['profile']['mot_de_passe']);
            unset($data['profile']['twofa_secret']);
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des données utilisateur: " . $e->getMessage());
            $data['profile'] = [
                'id_utilisateur' => $userId,
                'date_export' => date('Y-m-d H:i:s')
            ];
        }
        
        // 2. Connection history
        try {
            $stmt = $db->prepare("SELECT id, date_connexion, ip_address, user_agent, 
                                city, country, success, failure_reason 
                                FROM connexion_logs 
                                WHERE id_utilisateur = ? 
                                ORDER BY date_connexion DESC");
            $stmt->execute([$userId]);
            $data['connections'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération de l'historique des connexions: " . $e->getMessage());
            $data['connections'] = [];
        }
        
        // 3. Password change history
        try {
            $stmt = $db->prepare("SELECT id, date_changement 
                                FROM password_history 
                                WHERE id_utilisateur = ? 
                                ORDER BY date_changement DESC");
            $stmt->execute([$userId]);
            $data['password_history'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération de l'historique des mots de passe: " . $e->getMessage());
            $data['password_history'] = [];
        }
        
        // 4. Email verification status
        try {
            $stmt = $db->prepare("SELECT id, expires_at 
                                FROM email_verification 
                                WHERE id_utilisateur = ?");
            $stmt->execute([$userId]);
            $data['email_verification'] = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération du statut de vérification d'email: " . $e->getMessage());
            $data['email_verification'] = null;
        }
        
        // 5. 2FA setup history (if you have such a table)
        try {
            // Check if the table exists
            $tableCheck = $db->query("SHOW TABLES LIKE 'twofa_history'");
            if ($tableCheck->rowCount() > 0) {
                $stmt = $db->prepare("SELECT id, action, date_action 
                                    FROM twofa_history 
                                    WHERE id_utilisateur = ? 
                                    ORDER BY date_action DESC");
                $stmt->execute([$userId]);
                $data['twofa_history'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $data['twofa_history'] = [];
            }
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération de l'historique 2FA: " . $e->getMessage());
            $data['twofa_history'] = [];
        }
        
        // 6. Add export metadata
        $data['export_metadata'] = [
            'export_date' => date('Y-m-d H:i:s'),
            'export_format' => isset($_GET['format']) ? strtolower($_GET['format']) : 'json',
            'export_reason' => 'RGPD Data Request'
        ];
        
        return $data;
    }

    /**
     * Exports data as JSON
     */
    private static function exportAsJson($data) {
        // Set headers for JSON download
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="personal_data_export_' . date('Y-m-d') . '.json"');
        header('Pragma: no-cache');
        
        // Format the JSON with pretty print for readability
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Exports data as PDF using FPDF
     */
    private static function exportAsPdf($data) {
        // Si FPDF n'est pas disponible, retourner un JSON avec un message d'erreur
        if (!file_exists(__DIR__ . '/../vendor/fpdf/fpdf.php')) {
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'La bibliothèque FPDF n\'est pas installée. Veuillez choisir le format JSON ou demander à l\'administrateur d\'installer FPDF.',
                'message' => 'Pour installer FPDF, téléchargez-le depuis fpdf.org et placez-le dans le dossier vendor/fpdf/'
            ]);
            exit;
        }
        
        // Include FPDF
        require_once __DIR__ . '/../vendor/fpdf/fpdf.php';
        
        // Create PDF
        $pdf = new FPDF();
        $pdf->AddPage();
        
        // Add title
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'Export des données personnelles', 0, 1, 'C');
        $pdf->Ln(10);
        
        // Add export info
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Informations sur l\'export', 0, 1, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(60, 8, 'Date d\'export:', 0, 0, 'L');
        $pdf->Cell(0, 8, $data['export_metadata']['export_date'], 0, 1, 'L');
        $pdf->Cell(60, 8, 'Raison de l\'export:', 0, 0, 'L');
        $pdf->Cell(0, 8, $data['export_metadata']['export_reason'], 0, 1, 'L');
        $pdf->Ln(5);
        
        // Add profile info
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Informations du profil', 0, 1, 'L');
        $pdf->SetFont('Arial', '', 10);
        
        foreach ($data['profile'] as $key => $value) {
            // Make key more readable
            $label = ucfirst(str_replace('_', ' ', $key));
            
            // Format boolean values
            if ($value === 1 || $value === true) {
                $value = 'Oui';
            } elseif ($value === 0 || $value === false) {
                $value = 'Non';
            } elseif ($value === null) {
                $value = 'Non défini';
            }
            
            $pdf->Cell(60, 8, $label . ':', 0, 0, 'L');
            $pdf->Cell(0, 8, utf8_decode($value), 0, 1, 'L');
        }
        $pdf->Ln(5);
        
        // Add connection history
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Historique des connexions', 0, 1, 'L');
        
        if (empty($data['connections'])) {
            $pdf->SetFont('Arial', 'I', 10);
            $pdf->Cell(0, 8, 'Aucun historique de connexion disponible.', 0, 1, 'L');
        } else {
            // Table header
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(45, 8, 'Date', 1, 0, 'L');
            $pdf->Cell(35, 8, 'Adresse IP', 1, 0, 'L');
            $pdf->Cell(50, 8, 'Localisation', 1, 0, 'L');
            $pdf->Cell(30, 8, 'Statut', 1, 1, 'L');
            
            // Table content
            $pdf->SetFont('Arial', '', 9);
            foreach ($data['connections'] as $conn) {
                // Format data for PDF
                $date = date('d/m/Y H:i', strtotime($conn['date_connexion']));
                $ip = $conn['ip_address'];
                
                $location = [];
                if (!empty($conn['city']) && $conn['city'] != 'Unknown') {
                    $location[] = $conn['city'];
                }
                if (!empty($conn['country']) && $conn['country'] != 'Unknown') {
                    $location[] = $conn['country'];
                }
                $locationText = !empty($location) ? implode(', ', $location) : 'Inconnue';
                
                $status = $conn['success'] ? 'Réussie' : 'Échec';
                
                // Add row to table
                $pdf->Cell(45, 7, $date, 1, 0, 'L');
                $pdf->Cell(35, 7, $ip, 1, 0, 'L');
                $pdf->Cell(50, 7, utf8_decode($locationText), 1, 0, 'L');
                $pdf->Cell(30, 7, utf8_decode($status), 1, 1, 'L');
                
                // Add a new page if we're close to the bottom
                if ($pdf->GetY() > 250) {
                    $pdf->AddPage();
                }
            }
        }
        $pdf->Ln(5);
        
        // Add password history
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Historique des changements de mot de passe', 0, 1, 'L');
        
        if (empty($data['password_history'])) {
            $pdf->SetFont('Arial', 'I', 10);
            $pdf->Cell(0, 8, 'Aucun historique de changement de mot de passe disponible.', 0, 1, 'L');
        } else {
            // Table header
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(0, 8, 'Date de changement', 1, 1, 'L');
            
            // Table content
            $pdf->SetFont('Arial', '', 10);
            foreach ($data['password_history'] as $history) {
                $date = date('d/m/Y H:i', strtotime($history['date_changement']));
                $pdf->Cell(0, 7, $date, 1, 1, 'L');
            }
        }
        
        // Output PDF
        $fileName = 'personal_data_export_' . date('Y-m-d') . '.pdf';
        $pdf->Output('D', $fileName);
        exit;
    }
}

// Handle the request
if (isset($_GET['action']) && $_GET['action'] === 'export') {
    ExportController::exportUserData();
} 
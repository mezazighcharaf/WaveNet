<?php
require_once __DIR__ . '/../model/Database.php';
require_once __DIR__ . '/../model/Defi.php';
require_once __DIR__ . '/../vendor/autoload.php'; // Pour charger PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class DefiController {
    private $db;
    private $defi;
    
    // Configuration SMTP - À MODIFIER avec vos informations
    private $smtpHost = 'smtp.gmail.com';      // Serveur SMTP de Gmail
    private $smtpUsername = 'bouaziz.aicha2006@gmail.com'; // Remplacez par votre adresse Gmail
    private $smtpPassword = 'ejlasltgjujzqplu';      // Remplacez par votre mot de passe d'application
    private $smtpPort = 587;                   // Port TLS pour Gmail
    private $emailFrom = 'Urbaverse <bouaziz.aicha2006@gmail.com>'; // Remplacez par votre email
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->defi = new Defi($this->db);
    }
    
    // Retrieve all defis
    public function getAllDefis() {
        return $this->defi->readAll();
    }
    
    // Retrieve a single defi by ID
    public function getDefi($id) {
        $this->defi->Id_Defi = $id;
        if($this->defi->readOne()) {
            return $this->defi;
        }
        return null;
    }
    
    // Create a new defi
    public function createDefi($data) {
        // Set defi property values
        $this->defi->Titre_D = $data['Titre_D'];
        $this->defi->Description_D = $data['Description_D'];
        $this->defi->Objectif = $data['Objectif'];
        $this->defi->Points_verts = $data['Points_verts'];
        $this->defi->Statut_D = $data['Statut_D'];
        $this->defi->Date_Debut = $data['Date_Debut'];
        $this->defi->Date_Fin = $data['Date_Fin'];
        $this->defi->Difficulte = $data['Difficulte'];
        $this->defi->Id_Quartier = $data['Id_Quartier'];
        
        // Create the defi
        if($this->defi->create()) {
            // Log que nous allons essayer d'envoyer des notifications
            error_log("Défi créé avec succès, tentative d'envoi de notifications...");
            
            // Envoyer un email à tous les utilisateurs concernant le nouveau défi
            try {
                $this->notifyUsersAboutNewDefi($data);
                error_log("Notifications envoyées avec succès");
            } catch (Exception $e) {
                error_log("Erreur lors de l'envoi des notifications: " . $e->getMessage());
            }
            
            return true;
        }
        return false;
    }
    
    // Update an existing defi
    public function updateDefi($id, $data) {
        // Set defi ID and property values
        $this->defi->Id_Defi = $id;
        $this->defi->Titre_D = $data['Titre_D'];
        $this->defi->Description_D = $data['Description_D'];
        $this->defi->Objectif = $data['Objectif'];
        $this->defi->Points_verts = $data['Points_verts'];
        $this->defi->Statut_D = $data['Statut_D'];
        $this->defi->Date_Debut = $data['Date_Debut'];
        $this->defi->Date_Fin = $data['Date_Fin'];
        $this->defi->Difficulte = $data['Difficulte'];
        $this->defi->Id_Quartier = $data['Id_Quartier'];
        
        // Update the defi
        if($this->defi->update()) {
            return true;
        }
        return false;
    }
    
    // Delete a defi
    public function deleteDefi($id) {
        // Récupérer les infos du défi avant de le supprimer
        $defiInfo = $this->getDefi($id);
        error_log("Tentative de suppression du défi ID: " . $id);
        
        $this->defi->Id_Defi = $id;
        if($this->defi->delete()) {
            // Vérifier si la table est vide et réinitialiser l'auto-increment si c'est le cas
            $this->resetAutoIncrementIfEmpty();
            
            // Notifier les utilisateurs de la suppression du défi
            if ($defiInfo) {
                error_log("Défi supprimé avec succès, tentative d'envoi de notifications...");
                try {
                    $this->notifyUsersAboutDeletedDefi($defiInfo);
                    error_log("Notifications de suppression envoyées avec succès");
                } catch (Exception $e) {
                    error_log("Erreur lors de l'envoi des notifications de suppression: " . $e->getMessage());
                }
            } else {
                error_log("Défi supprimé mais impossible de récupérer ses informations pour les notifications");
            }
            
            return true;
        }
        error_log("Échec de la suppression du défi ID: " . $id);
        return false;
    }
    
    // Réinitialiser l'auto-increment si la table est vide
    private function resetAutoIncrementIfEmpty() {
        $stmt = $this->defi->readAll();
        if($stmt->rowCount() == 0) {
            // La table est vide, réinitialiser l'auto-increment
            try {
                $query = "ALTER TABLE defi AUTO_INCREMENT = 1";
                $stmt = $this->db->prepare($query);
                $stmt->execute();
            } catch(PDOException $e) {
                // Simplement ignorer l'erreur si la réinitialisation échoue
            }
        }
    }
    
    /**
     * Met à jour automatiquement les statuts des défis en fonction de la date actuelle
     */
    public function updateDefiStatuses() {
        $today = date('Y-m-d');
        
        try {
            // Pour tester, affichons la date actuelle
            error_log("Date actuelle: " . $today);
            
            // Mettre à jour les défis "À venir" qui doivent commencer aujourd'hui ou avant
            $query1 = "UPDATE defi 
                      SET Statut_D = 'Actif' 
                      WHERE Statut_D = 'À venir' AND Date_Debut <= :today";
            $stmt1 = $this->db->prepare($query1);
            $stmt1->bindParam(':today', $today);
            $stmt1->execute();
            
            // Mettre à jour les défis "Actif" qui sont terminés
            $query2 = "UPDATE defi 
                      SET Statut_D = 'Terminé' 
                      WHERE Statut_D = 'Actif' AND Date_Fin < :today";
            $stmt2 = $this->db->prepare($query2);
            $stmt2->bindParam(':today', $today);
            $stmt2->execute();
            
            return true;
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour des statuts de défis: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Met à jour les statuts des défis avec une date spécifique (pour tests)
     */
    public function updateDefiStatusesWithDate($testDate) {
        try {
            // Mettre à jour les défis "À venir" qui doivent commencer à la date de test ou avant
            $query1 = "UPDATE defi 
                      SET Statut_D = 'Actif' 
                      WHERE Statut_D = 'À venir' AND Date_Debut <= :testDate";
            $stmt1 = $this->db->prepare($query1);
            $stmt1->bindParam(':testDate', $testDate);
            $stmt1->execute();
            
            // Mettre à jour les défis "Actif" qui sont terminés à la date de test
            $query2 = "UPDATE defi 
                      SET Statut_D = 'Terminé' 
                      WHERE Statut_D = 'Actif' AND Date_Fin < :testDate";
            $stmt2 = $this->db->prepare($query2);
            $stmt2->bindParam(':testDate', $testDate);
            $stmt2->execute();
            
            return true;
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour des statuts de défis avec date test: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupère tous les emails des utilisateurs
     * @return array Liste des emails
     */
    private function getAllUserEmails() {
        try {
            $query = "SELECT email FROM utilisateur";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            $emails = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $emails[] = $row['email'];
            }
            
            error_log("Emails récupérés: " . count($emails) . " adresses trouvées");
            
            // Si aucun email n'est trouvé, ajouter au moins une adresse de test
            if (empty($emails)) {
                error_log("Aucun email trouvé dans la base, ajout d'une adresse de test");
                $emails[] = 'bouaziz.aicha2006@gmail.com'; // Adresse de test pour les notifications
            }
            
            return $emails;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des emails: " . $e->getMessage());
            // Renvoyer une adresse email de secours pour les tests
            return ['bouaziz.aicha2006@gmail.com'];
        }
    }
    
    /**
     * Envoie un email via PHPMailer
     * 
     * @param string $to Adresse du destinataire
     * @param string $subject Sujet du mail
     * @param string $textBody Corps du message en texte brut
     * @param string $htmlBody Corps du message en HTML (optionnel)
     * @return array Résultat de l'envoi
     */
    private function sendEmail($to, $subject, $textBody, $htmlBody = null) {
        error_log("Tentative d'envoi d'email à: " . $to . " avec sujet: " . $subject);
        
        // Créer une nouvelle instance de PHPMailer
        $mail = new PHPMailer(true);
        
        try {
            // Configuration du serveur
            $mail->isSMTP();                                      // Utiliser SMTP
            $mail->Host       = $this->smtpHost;                  // Serveur SMTP
            $mail->SMTPAuth   = true;                             // Activer l'authentification SMTP
            $mail->Username   = $this->smtpUsername;              // Nom d'utilisateur SMTP
            $mail->Password   = $this->smtpPassword;              // Mot de passe SMTP
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;   // Activer le chiffrement TLS
            $mail->Port       = $this->smtpPort;                  // Port TCP

            // Expéditeur et destinataires
            $mail->setFrom($this->smtpUsername, 'Urbaverse');
            $mail->addAddress($to);                               // Ajouter un destinataire
            $mail->addReplyTo('noreply@urbaverse.com', 'Noreply');

            // Contenu
            $mail->isHTML($htmlBody !== null);                    // Format HTML si htmlBody est fourni
            $mail->Subject = $subject;
            
            if ($htmlBody) {
                $mail->Body    = $htmlBody;
                $mail->AltBody = $textBody;
            } else {
                $mail->Body = $textBody;
            }

            // Envoyer l'email
            $mail->send();
            
            error_log("Email envoyé avec succès à: " . $to);
            return [
                'success' => true,
                'message' => 'Email envoyé avec succès'
            ];
        } catch (Exception $e) {
            error_log("Erreur lors de l'envoi d'email: " . $mail->ErrorInfo);
            return [
                'success' => false,
                'message' => "Erreur lors de l'envoi: " . $mail->ErrorInfo
            ];
        }
    }
    
    /**
     * Notifie tous les utilisateurs d'un nouveau défi
     * 
     * @param array $defiData Données du nouveau défi
     */
    private function notifyUsersAboutNewDefi($defiData) {
        // Récupérer tous les emails des utilisateurs
        $emails = $this->getAllUserEmails();
        
        if (empty($emails)) {
            error_log("Aucun email d'utilisateur trouvé pour la notification");
            return;
        }
        
        // Créer le contenu de l'email
        $subject = "Nouveau défi disponible : {$defiData['Titre_D']}";
        
        $textBody = "Bonjour,\n\n";
        $textBody .= "Un nouveau défi a été ajouté sur Urbaverse !\n\n";
        $textBody .= "Titre : {$defiData['Titre_D']}\n";
        $textBody .= "Description : {$defiData['Description_D']}\n";
        $textBody .= "Objectif : {$defiData['Objectif']}\n";
        $textBody .= "Points verts à gagner : {$defiData['Points_verts']}\n";
        $textBody .= "Difficulté : {$defiData['Difficulte']}\n";
        $textBody .= "Dates : du {$defiData['Date_Debut']} au {$defiData['Date_Fin']}\n\n";
        $textBody .= "Connectez-vous à Urbaverse pour participer à ce défi !\n\n";
        $textBody .= "L'équipe Urbaverse";
        
        $htmlBody = "<html><body>";
        $htmlBody .= "<h2>Nouveau défi disponible sur Urbaverse !</h2>";
        $htmlBody .= "<p><strong>Titre :</strong> {$defiData['Titre_D']}</p>";
        $htmlBody .= "<p><strong>Description :</strong> {$defiData['Description_D']}</p>";
        $htmlBody .= "<p><strong>Objectif :</strong> {$defiData['Objectif']}</p>";
        $htmlBody .= "<p><strong>Points verts à gagner :</strong> {$defiData['Points_verts']}</p>";
        $htmlBody .= "<p><strong>Difficulté :</strong> {$defiData['Difficulte']}</p>";
        $htmlBody .= "<p><strong>Dates :</strong> du {$defiData['Date_Debut']} au {$defiData['Date_Fin']}</p>";
        $htmlBody .= "<p>Connectez-vous à <a href='http://urbaverse.com'>Urbaverse</a> pour participer à ce défi !</p>";
        $htmlBody .= "<p>L'équipe Urbaverse</p>";
        $htmlBody .= "</body></html>";
        
        // Envoyer directement une notification à l'adresse email vérifiée pour test
        // Dans un environnement de production, vous enverriez à tous les emails
        $testEmail = 'bouaziz.aicha2006@gmail.com';
        $this->sendEmail($testEmail, $subject, $textBody, $htmlBody);
        
        // Pour envoyer à tous les utilisateurs, décommenter le code ci-dessous
        /*
        foreach ($emails as $email) {
            $this->sendEmail($email, $subject, $textBody, $htmlBody);
        }
        */
    }
    
    /**
     * Notifie tous les utilisateurs de la suppression d'un défi
     * 
     * @param object $defiInfo Informations sur le défi supprimé
     */
    private function notifyUsersAboutDeletedDefi($defiInfo) {
        // Récupérer tous les emails des utilisateurs
        $emails = $this->getAllUserEmails();
        
        if (empty($emails)) {
            error_log("Aucun email d'utilisateur trouvé pour la notification de suppression");
            return;
        }
        
        // Créer le contenu de l'email
        $subject = "Défi supprimé : {$defiInfo->Titre_D}";
        
        $textBody = "Bonjour,\n\n";
        $textBody .= "Nous vous informons que le défi suivant a été supprimé d'Urbaverse :\n\n";
        $textBody .= "Titre : {$defiInfo->Titre_D}\n";
        $textBody .= "Description : {$defiInfo->Description_D}\n\n";
        $textBody .= "Si vous participiez à ce défi, nous vous invitons à découvrir nos autres défis disponibles.\n\n";
        $textBody .= "L'équipe Urbaverse";
        
        $htmlBody = "<html><body>";
        $htmlBody .= "<h2>Un défi a été supprimé d'Urbaverse</h2>";
        $htmlBody .= "<p><strong>Titre :</strong> {$defiInfo->Titre_D}</p>";
        $htmlBody .= "<p><strong>Description :</strong> {$defiInfo->Description_D}</p>";
        $htmlBody .= "<p>Si vous participiez à ce défi, nous vous invitons à découvrir nos autres défis disponibles.</p>";
        $htmlBody .= "<p>L'équipe Urbaverse</p>";
        $htmlBody .= "</body></html>";
        
        // Pour l'instant, envoi à une adresse test uniquement
        $testEmail = 'bouaziz.aicha2006@gmail.com';
        $this->sendEmail($testEmail, $subject, $textBody, $htmlBody);
        
        // Pour envoyer à tous les utilisateurs, décommenter le code ci-dessous
        /*
        foreach ($emails as $email) {
            $this->sendEmail($email, $subject, $textBody, $htmlBody);
        }
        */
    }
}
?>
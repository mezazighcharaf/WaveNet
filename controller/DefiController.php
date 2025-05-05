<?php
require_once __DIR__ . '/../model/Database.php';
require_once __DIR__ . '/../model/Defi.php';

class DefiController {
    private $db;
    private $defi;
    private $mailgunApiKey = '4586632d807b5817dd553a1a4290b25c-a908eefc-2dd5cb02'; // Clé API Mailgun
    private $mailgunDomain = 'sandboxb65dcf16ce40469291b43a43241697d7.mailgun.org'; // Domaine vérifié sur Mailgun
    private $mailgunFrom = 'Urbaverse <postmaster@sandboxb65dcf16ce40469291b43a43241697d7.mailgun.org>'; // Nom d'expéditeur modifié
    private $isMailgunEU = false; // Utiliser l'endpoint US
    
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
     * Envoie un email via Mailgun API
     * 
     * @param string $to Adresse du destinataire
     * @param string $subject Sujet du mail
     * @param string $textBody Corps du message en texte brut
     * @param string $htmlBody Corps du message en HTML (optionnel)
     * @return array Résultat de l'envoi
     */
    private function sendEmail($to, $subject, $textBody, $htmlBody = null) {
        error_log("Tentative d'envoi d'email à: " . $to . " avec sujet: " . $subject);
        
        // URL de l'API Mailgun (utilisation de l'endpoint US)
        $url = "https://api.mailgun.net/v3/{$this->mailgunDomain}/messages";
        
        error_log("URL de l'API Mailgun: " . $url);
        
        // Préparation des données avec des en-têtes supplémentaires pour éviter les spams
        $data = array(
            'from'    => $this->mailgunFrom,
            'to'      => $to,
            'subject' => $subject,
            'text'    => $textBody,
            'h:Reply-To' => 'noreply@urbaverse.com',
            'h:X-Mailer' => 'Urbaverse Notification System',
            'h:X-Priority' => '1'  // Haute priorité
        );
        
        // Ajout du corps HTML si fourni
        if ($htmlBody) {
            $data['html'] = $htmlBody;
        }
        
        // Initialisation de cURL
        $ch = curl_init();
        
        // Configuration de cURL exactement comme dans le test qui fonctionnait
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "api:{$this->mailgunApiKey}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Pour le développement uniquement
        curl_setopt($ch, CURLOPT_POST, true); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        
        // Exécution de la requête
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        error_log("Code de réponse HTTP: " . $httpCode);
        
        // Gestion des erreurs
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            error_log("Erreur cURL lors de l'envoi d'email: " . $error);
            return [
                'success' => false,
                'message' => "Erreur cURL: " . $error,
                'http_code' => 0
            ];
        }
        
        curl_close($ch);
        
        // Décodage de la réponse JSON
        $responseData = json_decode($response, true);
        error_log("Réponse Mailgun: " . $response);
        
        $result = [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'message' => isset($responseData['message']) ? $responseData['message'] : 'Aucun message',
            'http_code' => $httpCode
        ];
        
        error_log("Résultat d'envoi d'email: " . json_encode($result));
        
        return $result;
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
        
        // Envoyer directement une notification à l'adresse email vérifiée
        $user = array(
            'id' => 1,
            'prenom' => 'Aicha',
            'nom' => 'Bouaziz',
            'email' => 'bouaziz.aicha2006@gmail.com'
        );
        $this->sendEmail($user['email'], $subject, $textBody, $htmlBody);
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
        
        // Envoyer un email à chaque utilisateur
        foreach ($emails as $email) {
            $this->sendEmail($email, $subject, $textBody, $htmlBody);
        }
    }
}
?>
<?php
require_once __DIR__ . '/../model/Database.php';
require_once __DIR__ . '/../model/Etape.php';

class EtapeController {
    private $db;
    private $etape;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->etape = new Etape($this->db);
    }

    public function getEtapesByDefi($id_defi) {
        return $this->etape->readAllByDefi($id_defi);
    }

    public function getEtape($id) {
        $this->etape->Id_Etape = $id;
        if ($this->etape->readOne()) {
            return $this->etape;
        }
        return null;
    }

    // In the createEtape method, change Points_E to Points_Bonus
    public function createEtape($data) {
        try {
            // Récupérer le statut du défi associé
            $defiController = new DefiController();
            $defi = $defiController->getDefi($data['Id_Defi']);
            $statutDefi = $defi ? $defi->Statut_D : 'À venir';

            $query = "INSERT INTO etape (Titre_E, Description_E, Ordre, Points_Bonus, Statut_E, Id_Defi) 
                      VALUES (:titre, :description, :ordre, :points, :statut, :id_defi)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':titre', $data['Titre_E']);
            $stmt->bindParam(':description', $data['Description_E']);
            $stmt->bindParam(':ordre', $data['Ordre']);
            $stmt->bindParam(':points', $data['Points_Bonus']);
            $stmt->bindParam(':statut', $statutDefi);
            $stmt->bindParam(':id_defi', $data['Id_Defi']);
            $result = $stmt->execute();
            // Mettre à jour les statuts des étapes après création
            $this->updateEtapeStatuses();
            return $result;
        } catch(PDOException $e) {
            error_log("Error creating etape: " . $e->getMessage());
            return false;
        }
    }

    public function deleteEtape($id) {
        $this->etape->Id_Etape = $id;
        return $this->etape->delete();
    }

    // Replace the current getAllEtapes method with this one
    public function getAllEtapes() {
        return $this->etape->readAll();
    }

    public function getEtapeById($id) {
        try {
            $query = "SELECT * FROM etape WHERE Id_etape = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error getting etape by ID: " . $e->getMessage());
            return false;
        }
    }

    public function updateEtape($data) {
        try {
            // Récupérer le statut du défi associé
            $defiController = new DefiController();
            $defi = $defiController->getDefi($data['Id_Defi']);
            $statutDefi = $defi ? $defi->Statut_D : 'À venir';

            $query = "UPDATE etape 
                      SET Titre_E = :titre, 
                          Description_E = :description, 
                          Ordre = :ordre, 
                          Points_Bonus = :points, 
                          Statut_E = :statut, 
                          Id_Defi = :id_defi 
                      WHERE Id_etape = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $data['Id_etape']);
            $stmt->bindParam(':titre', $data['Titre_E']);
            $stmt->bindParam(':description', $data['Description_E']);
            $stmt->bindParam(':ordre', $data['Ordre']);
            $stmt->bindParam(':points', $data['Points_Bonus']);
            $stmt->bindParam(':statut', $statutDefi);
            $stmt->bindParam(':id_defi', $data['Id_Defi']);
            $result = $stmt->execute();
            // Mettre à jour les statuts des étapes après modification
            $this->updateEtapeStatuses();
            return $result;
        } catch(PDOException $e) {
            error_log("Error updating etape: " . $e->getMessage());
            return false;
        }
    }

    // Ajouter cette méthode à votre classe EtapeController
    
    /**
     * Met à jour automatiquement les statuts des étapes en fonction du statut de leur défi
     */
    public function updateEtapeStatuses() {
        try {
            // Mettre à jour les étapes pour qu'elles soient actives si leur défi est actif
            $query1 = "UPDATE etape e 
                      JOIN defi d ON e.Id_Defi = d.Id_Defi 
                      SET e.Statut_E = 'Actif' 
                      WHERE d.Statut_D = 'Actif' AND e.Statut_E = 'À venir'";
            $stmt1 = $this->db->prepare($query1);
            $stmt1->execute();
            
            // Mettre à jour les étapes pour qu'elles soient terminées si leur défi est terminé
            $query2 = "UPDATE etape e 
                      JOIN defi d ON e.Id_Defi = d.Id_Defi 
                      SET e.Statut_E = 'Terminé' 
                      WHERE d.Statut_D = 'Terminé'";
            $stmt2 = $this->db->prepare($query2);
            $stmt2->execute();
            
            return true;
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour des statuts d'étapes: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère le nom d'un défi par son ID
     */
    public function getDefiNameById($id_defi) {
        try {
            $query = "SELECT Titre_D FROM defi WHERE Id_Defi = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id_defi);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['Titre_D'] : 'Défi inconnu';
        } catch(PDOException $e) {
            error_log("Error getting defi name: " . $e->getMessage());
            return 'Défi inconnu';
        }
    }

    /**
     * Valide une étape pour un utilisateur
     * @param int $etapeId ID de l'étape
     * @param int $userId ID de l'utilisateur
     * @param string $imagePath Chemin de l'image de preuve
     * @return array|false Retourne un tableau avec les points gagnés ou false en cas d'échec
     */
    public function validateEtape($etapeId, $userId, $imagePath) {
        try {
            // Commencer une transaction
            $this->db->beginTransaction();

            // 1. Vérifier si l'étape existe et récupérer ses points
            $query = "SELECT Points_Bonus FROM etape WHERE Id_etape = :etape_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':etape_id', $etapeId);
            $stmt->execute();
            $etape = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$etape) {
                throw new Exception("Étape non trouvée");
            }

            // 2. Vérifier si l'utilisateur n'a pas déjà validé cette étape
            $query = "SELECT * FROM validation_etape WHERE Id_etape = :etape_id AND Id_utilisateur = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':etape_id', $etapeId);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();

            if ($stmt->fetch()) {
                throw new Exception("Cette étape a déjà été validée");
            }

            // 3. Enregistrer la validation
            $query = "INSERT INTO validation_etape (Id_etape, Id_utilisateur, Date_validation, Image_preuve) 
                     VALUES (:etape_id, :user_id, NOW(), :image_path)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':etape_id', $etapeId);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':image_path', $imagePath);
            $stmt->execute();

            // 4. Mettre à jour les points de l'utilisateur
            $query = "UPDATE utilisateur 
                     SET Points_verts = Points_verts + :points 
                     WHERE Id_Utilisateur = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':points', $etape['Points_Bonus']);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();

            // Valider la transaction
            $this->db->commit();

            return [
                'success' => true,
                'points' => $etape['Points_Bonus']
            ];

        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            $this->db->rollBack();
            error_log("Erreur lors de la validation de l'étape: " . $e->getMessage());
            return false;
        }
    }
}
?>

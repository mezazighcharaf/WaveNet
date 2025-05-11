<?php

class Transport {
    private $id_transport;
    private $id_utilisateur;
    private $type_transport;
    private $distance_parcourue;
    private $frequence;
    private $eco_index;
    private $date_derniere_utilisation;

    public function __construct($id_utilisateur, $type_transport, $distance_parcourue, $frequence, $eco_index, $date_derniere_utilisation = null, $id_transport = null) {
        $this->id_transport = $id_transport;
        $this->id_utilisateur = $id_utilisateur;
        $this->type_transport = $type_transport;
        $this->distance_parcourue = $distance_parcourue;
        $this->frequence = $frequence;
        $this->eco_index = $eco_index; // L'eco_index est stocké directement dans la table
        $this->date_derniere_utilisation = $date_derniere_utilisation;
    }

    public function getIdTransport() { return $this->id_transport; }
    public function getIdUtilisateur() { return $this->id_utilisateur; }
    public function getTypeTransport() { return $this->type_transport; }
    public function getDistanceParcourue() { return $this->distance_parcourue; }
    public function getFrequence() { return $this->frequence; }
    public function getEcoIndex() { return $this->eco_index; }
    public function getDateDerniereUtilisation() { return $this->date_derniere_utilisation; }

    /**
     * Récupère tous les enregistrements de transport pour un utilisateur donné.
     *
     * @param PDO $db L'objet de connexion PDO.
     * @param int $userId L'ID de l'utilisateur.
     * @return array Un tableau d'objets Transport.
     */
    public static function findByUserId(PDO $db, int $userId): array {
        $stmt = $db->prepare("SELECT * FROM TRANSPORT WHERE id_utilisateur = :id_utilisateur ORDER BY date_derniere_utilisation DESC");
        $stmt->execute(['id_utilisateur' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC); }

    /**
     *
     * @param PDO $db L'objet de connexion PDO.
     * @param array $data Un tableau associatif contenant les données du transport.
     *                     Doit contenir: id_utilisateur, type_transport, distance_parcourue, frequence, eco_index, date_derniere_utilisation (optionnel)
     * @return bool True si la création réussit, false sinon.
     */
    public static function create(PDO $db, array $data): bool {
        // Il faut déterminer l'eco_index basé sur le type_transport ici,
        // ou s'assurer qu'il est fourni dans $data.
        // Pour l'instant, on suppose qu'il est fourni.
        if (!isset($data['eco_index'])) {
             // Assignation simple basée sur le type (à améliorer)
             $eco_indexes = [
                'Marche' => 10.00,
                'Vélo' => 9.50,
                'Transport en commun (Bus)' => 7.00,
                'Transport en commun (Tram/Métro)' => 7.50,
                'Voiture électrique' => 6.00,
                'Covoiturage' => 5.00,
                'Voiture thermique' => 2.00,
                'Trottinette électrique' => 8.00
             ];
             $data['eco_index'] = $eco_indexes[ucfirst(strtolower($data['type_transport']))] ?? 5.00; // Default index
        }
        
        $sql = "INSERT INTO TRANSPORT (id_utilisateur, type_transport, distance_parcourue, frequence, eco_index, date_derniere_utilisation) 
                VALUES (:id_utilisateur, :type_transport, :distance_parcourue, :frequence, :eco_index, :date_derniere_utilisation)";
        
        $stmt = $db->prepare($sql);
        
        try {
            $success = $stmt->execute([
                ':id_utilisateur' => $data['id_utilisateur'],
                ':type_transport' => $data['type_transport'],
                ':distance_parcourue' => $data['distance_parcourue'],
                ':frequence' => $data['frequence'],
                ':eco_index' => $data['eco_index'],
                ':date_derniere_utilisation' => $data['date_derniere_utilisation'] ?? null
            ]);
            return $success;
        } catch (PDOException $e) {
            error_log("Erreur lors de la création du transport : " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupérer un transport par son ID
     * 
     * @param PDO $db L'objet de connexion PDO
     * @param int $id L'ID du transport
     * @return Transport|null Le transport ou null s'il n'existe pas
     */
    public static function findById(PDO $db, int $id) {
        $stmt = $db->prepare("SELECT * FROM TRANSPORT WHERE id_transport = :id_transport");
        $stmt->execute(['id_transport' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            return new Transport(
                $result['id_utilisateur'],
                $result['type_transport'],
                $result['distance_parcourue'],
                $result['frequence'],
                $result['eco_index'],
                $result['date_derniere_utilisation'],
                $result['id_transport']
            );
        }
        
        return null;
    }
    
    /**
     * Mettre à jour un transport
     * 
     * @param PDO $db L'objet de connexion PDO
     * @return bool True si la mise à jour réussit, false sinon
     */
    public function update(PDO $db): bool {
        $sql = "UPDATE TRANSPORT SET 
                type_transport = :type_transport,
                distance_parcourue = :distance_parcourue,
                frequence = :frequence,
                eco_index = :eco_index,
                date_derniere_utilisation = :date_derniere_utilisation
                WHERE id_transport = :id_transport";
                
        $stmt = $db->prepare($sql);
        
        try {
            return $stmt->execute([
                ':type_transport' => $this->type_transport,
                ':distance_parcourue' => $this->distance_parcourue,
                ':frequence' => $this->frequence,
                ':eco_index' => $this->eco_index,
                ':date_derniere_utilisation' => $this->date_derniere_utilisation,
                ':id_transport' => $this->id_transport
            ]);
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour du transport : " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Supprimer un transport par son ID
     * 
     * @param PDO $db L'objet de connexion PDO
     * @param int $id L'ID du transport à supprimer
     * @return bool True si la suppression réussit, false sinon
     */
    public static function delete(PDO $db, int $id): bool {
        $stmt = $db->prepare("DELETE FROM TRANSPORT WHERE id_transport = :id_transport");
        
        try {
            return $stmt->execute([':id_transport' => $id]);
        } catch (PDOException $e) {
            error_log("Erreur lors de la suppression du transport : " . $e->getMessage());
            return false;
        }
    }
}
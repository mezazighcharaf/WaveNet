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
        $this->eco_index = $eco_index; 
        $this->date_derniere_utilisation = $date_derniere_utilisation;
    }
    public function getIdTransport() { return $this->id_transport; }
    public function getIdUtilisateur() { return $this->id_utilisateur; }
    public function getTypeTransport() { return $this->type_transport; }
    public function getDistanceParcourue() { return $this->distance_parcourue; }
    public function getFrequence() { return $this->frequence; }
    public function getEcoIndex() { return $this->eco_index; }
    public function getDateDerniereUtilisation() { return $this->date_derniere_utilisation; }
    public static function findByUserId(PDO $db, int $userId): array {
        $stmt = $db->prepare("SELECT * FROM TRANSPORT WHERE id_utilisateur = :id_utilisateur ORDER BY date_derniere_utilisation DESC");
        $stmt->execute(['id_utilisateur' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC); 
    }
    public static function create(PDO $db, array $data): bool {
        if (!isset($data['eco_index'])) {
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
             $data['eco_index'] = $eco_indexes[ucfirst(strtolower($data['type_transport']))] ?? 5.00; 
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
}

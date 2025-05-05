<?php
class Quartier {
    // Attributs correspondant exactement à la structure de la table
    private $idq;
    private $nomq;
    private $Ville;
    private $scoreeco;
    private $Classement;
    private $localisation;
    private $latitude;
    private $longitude;

    public function __construct($nomq = null, $Ville = null, $scoreeco = null, $Classement = null, $localisation = null, $latitude = null, $longitude = null) {
        $this->nomq = $nomq;
        $this->Ville = $Ville;
        $this->scoreeco = $scoreeco;
        $this->Classement = $Classement;
        $this->localisation = $localisation;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    // Getters
    public function getIdq() { return $this->idq; }
    public function getNomq() { return $this->nomq; }
    public function getVille() { return $this->Ville; }
    public function getScoreeco() { return $this->scoreeco; }
    public function getClassement() { return $this->Classement; }
    public function getLocalisation() { return $this->localisation; }
    public function getLatitude() { return $this->latitude; }
    public function getLongitude() { return $this->longitude; }

    // Setters
    public function setNomq($nomq) { $this->nomq = $nomq; }
    public function setVille($Ville) { $this->Ville = $Ville; }
    public function setScoreeco($scoreeco) { $this->scoreeco = $scoreeco; }
    public function setClassement($Classement) { $this->Classement = $Classement; }
    public function setLocalisation($localisation) { $this->localisation = $localisation; }
    public function setLatitude($latitude) { $this->latitude = $latitude; }
    public function setLongitude($longitude) { $this->longitude = $longitude; }

    // Pour compatibilité avec l'ancien code
    public function getId() { return $this->idq; }
    public function getNomQuartier() { return $this->nomq; }
    public function setNomQuartier($nom) { $this->nomq = $nom; }

    // Méthodes statiques
    public static function findById($db, $id) {
        $stmt = $db->prepare("SELECT * FROM QUARTIER WHERE idq = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            $quartier = new Quartier();
            $quartier->idq = $data['idq'];
            $quartier->nomq = $data['nomq'];
            
            // Vérifier et définir les champs supplémentaires
            if (isset($data['ville'])) $quartier->Ville = $data['ville'];
            if (isset($data['scoreeco'])) $quartier->scoreeco = $data['scoreeco'];
            if (isset($data['classement'])) $quartier->Classement = $data['classement'];
            if (isset($data['localisation'])) $quartier->localisation = $data['localisation'];
            if (isset($data['latitude'])) $quartier->latitude = $data['latitude'];
            if (isset($data['longitude'])) $quartier->longitude = $data['longitude'];
            
            return $quartier;
        }
        
        return null;
    }

    public static function findAll($db) {
        try {
            $stmt = $db->query("SELECT * FROM QUARTIER");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $quartiers = [];
            foreach ($data as $row) {
                $quartier = new Quartier();
                $quartier->idq = $row['idq'];
                $quartier->nomq = $row['nomq'];
                
                // Vérifier et définir les champs supplémentaires
                if (isset($row['ville'])) $quartier->Ville = $row['ville'];
                if (isset($row['scoreeco'])) $quartier->scoreeco = $row['scoreeco'];
                if (isset($row['classement'])) $quartier->Classement = $row['classement'];
                if (isset($row['localisation'])) $quartier->localisation = $row['localisation'];
                if (isset($row['latitude'])) $quartier->latitude = $row['latitude'];
                if (isset($row['longitude'])) $quartier->longitude = $row['longitude'];
                
                $quartiers[] = $quartier;
            }
            
            return $quartiers;
        } catch (PDOException $e) {
            error_log("Erreur dans findAll: " . $e->getMessage());
            return [];
        }
    }

    public static function create($db, $data) {
        try {
            // Construction de la requête
            $sql = "INSERT INTO QUARTIER (nomq, ville, scoreeco, classement, localisation, latitude, longitude) 
                    VALUES (:nomq, :ville, :scoreeco, :classement, :localisation, :latitude, :longitude)";
            
            $stmt = $db->prepare($sql);
            
            return $stmt->execute([
                'nomq' => $data['nomq'],
                'ville' => $data['Ville'] ?? null,
                'scoreeco' => $data['scoreeco'] ?? null,
                'classement' => $data['Classement'] ?? null,
                'localisation' => $data['localisation'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null
            ]);
        } catch (PDOException $e) {
            error_log("Erreur dans create: " . $e->getMessage());
            return false;
        }
    }

    public static function update($db, $id, $data) {
        try {
            $sql = "UPDATE QUARTIER SET 
                    nomq = :nomq,
                    ville = :ville,
                    scoreeco = :scoreeco,
                    classement = :classement,
                    localisation = :localisation,
                    latitude = :latitude,
                    longitude = :longitude
                    WHERE idq = :id";
            
            $stmt = $db->prepare($sql);
            
            return $stmt->execute([
                'nomq' => $data['nomq'],
                'ville' => $data['Ville'] ?? null,
                'scoreeco' => $data['scoreeco'] ?? null,
                'classement' => $data['Classement'] ?? null,
                'localisation' => $data['localisation'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'id' => $id
            ]);
        } catch (PDOException $e) {
            error_log("Erreur dans update: " . $e->getMessage());
            return false;
        }
    }

    // Récupérer les quartiers pour le formulaire d'inscription (format simple)
    public static function getQuartiersForSelect($db) {
        try {
            $stmt = $db->query("SELECT idq, nomq FROM QUARTIER ORDER BY nomq");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur dans getQuartiersForSelect: " . $e->getMessage());
            return [];
        }
    }
}

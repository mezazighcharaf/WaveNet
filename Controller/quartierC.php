<?php
include_once(__DIR__ . '/../views/includes/config.php');
include_once(__DIR__ . '/../models/quartier.php');

class Config {
    public static function getConnection() {
        return connectDB();
    }
}

class quartierC {
    /*public function ajouterQuartier($quartier){
        $sql = "INSERT INTO quartier (idq, nomq, ville, localisation, scoreeco, classement, latitude, longitude)
        VALUES (:idq, :nomq, :ville, :localisation, :scoreeco, :classement, :latitude, :longitude)";
        $db = Config::getConnection();
    
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'idq' => $quartier->getID_quartier(),
                'nomq' => $quartier->getNom(),
                'ville' => $quartier->getVille(),
                'localisation' => $quartier->getLocalisation(),
                'scoreeco' => $quartier->getscore_ecologique(),
                'classement' => $quartier->getClassement(),
                'latitude' => $quartier->getLatitude(),
                'longitude' => $quartier->getLongitude()
            ]);
        } catch(PDOException $e){
            echo "Erreur :" . $e->getMessage();
        }
    }*/
    

    public function afficherQuartier(){
        $sql="SELECT * FROM quartier";
        $db = Config::getConnection();

        try{
            return $db->query($sql)->fetchAll();
        } catch (PDOException $e) {
            die("Erreur : " .$e->getMessage());
        }
    }

    public function supprimerQuartier($id) {
        $sql = "DELETE FROM quartier WHERE idq = :id";
        $db = Config::getConnection();

        try {
            $req = $db->prepare($sql);
            $req->bindValue(':id', $id);
            $req->execute();
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }
    

    public function modifierQuartier($quartier) {
        $sql = "UPDATE quartier 
                SET nomq = :nomq, ville = :ville, scoreeco = :scoreeco, classement = :classement, localisation = :localisation,
                latitude = :latitude, longitude = :longitude
                WHERE idq = :idq";
        $db = Config::getConnection();
    
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'idq' => $quartier->getID_quartier(),
                'nomq' => $quartier->getNom(),
                'ville' => $quartier->getVille(),
                'scoreeco' => $quartier->getscore_ecologique(),
                'classement' => $quartier->getClassement(),
                'localisation' => $quartier->getLocalisation(),
                'latitude' => $quartier->getLatitude(),
                'longitude' => $quartier->getLongitude()
            ]);
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }
    
    public function recupererQuartierparId($idq) {
        $sql = "SELECT * FROM quartier WHERE idq = :idq";
        $db = Config::getConnection();

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute(['idq' => $idq]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }
    public function rechercherQuartierParNom($nomq) {
        $sql = "SELECT * FROM quartier WHERE LOWER(nomq) = LOWER(:nomq)";
        $db = Config::getConnection();
    
        try {
            $query = $db->prepare($sql);
            $query->execute(['nomq' => trim($nomq)]);
            return $query->fetchAll();
        } catch (PDOException $e) {
            die("Erreur : " . $e->getMessage());
        }
    }
    
    public function ajouterQuartierAutoIncrement($nomq, $ville, $scoreeco, $classement, $localisation, $latitude, $longitude) {
        $sql = "INSERT INTO quartier (nomq, ville, scoreeco, classement, localisation, latitude, longitude) 
                VALUES (:nomq, :ville, :scoreeco, :classement, :localisation, :latitude, :longitude)";
        $db = Config::getConnection();
        
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'nomq' => $nomq,
                'ville' => $ville,
                'scoreeco' => $scoreeco,
                'classement' => $classement,
                'localisation' => $localisation,
                'latitude' => $latitude,
                'longitude' => $longitude
            ]);
            
            return $db->lastInsertId();
        } catch (PDOException $e) {
            echo "Erreur lors de l'ajout du quartier : " . $e->getMessage();
            return false;
        }
    }
}
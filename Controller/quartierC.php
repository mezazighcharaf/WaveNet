<?php
include_once(__DIR__ . '/../config/config.php');
include_once(__DIR__ . '/../Model/quartier.php');


class quartierC {
    public function ajouterQuartier($quartier){
        $sql = "INSERT INTO quartier (idq, nomq, ville, scoreeco, classement)
                VALUES (:idq, :nomq, :ville, :scoreeco, :classement)";
        $db = Config::getConnection();
    
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'idq' => $quartier->getID_quartier(),
                'nomq' => $quartier->getNom(),
                'ville' => $quartier->getVille(),
                'scoreeco' => $quartier->getscore_ecologique(),
                'classement' => $quartier->getClassement(),
            ]);
        } catch(PDOException $e){
            echo "Erreur :" . $e->getMessage();
        }
    }
    

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
                SET nomq = :nomq, ville = :ville, scoreeco = :scoreeco, classement = :classement 
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
            ]);
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }
    
    public function recupererQuartierparId($idq) {
        $sql = "SELECT * FROM quartier WHERE id = :id";
        $db = Config::getConnection();

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute(['id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }
    
}
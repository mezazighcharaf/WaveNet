<?php
include_once(__DIR__ . '/../config/config.php');
include_once(__DIR__ . '/../Model/infra.php');

class infraC {
    public function getQuartiers() {
        $sql = "SELECT idq, nomq, ville FROM quartier";
        $db = Config::getConnection();
    
        try {
            return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Erreur : " . $e->getMessage());
        }
    }

    public function ajouterInfrastructure($infrastructure) {
        $sql = "INSERT INTO infrastructure (id_infra, type, statut, idq)
                VALUES (:id_infra, :type, :statut, :idq)";
        $db = Config::getConnection();
    
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id_infra' => $infrastructure->getIdInfra(),
                'type' => $infrastructure->getType(),
                'statut' => $infrastructure->getStatut(),
                'idq' => $infrastructure->getIdq()
            ]);
        } catch(PDOException $e) {
            echo "Erreur :" . $e->getMessage();
        }
    }
        
    

    public function afficherInfrastructure() {
        $sql = "SELECT * FROM infrastructure";
        $db = Config::getConnection();

        try {
            return $db->query($sql)->fetchAll();
        } catch (PDOException $e) {
            die("Erreur : " . $e->getMessage());
        }
    }

    public function supprimerInfrastructure($id) {
        $sql = "DELETE FROM infrastructure WHERE id_infra = :id";
        $db = Config::getConnection();

        try {
            $req = $db->prepare($sql);
            $req->bindValue(':id', $id);
            $req->execute();
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }
    

    public function modifierInfrastructure($infrastructure) {
        $sql = "UPDATE infrastructure 
                SET type = :type, statut = :statut 
                WHERE id_infra = :id_infra";
        $db = Config::getConnection();
    
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id_infra' => $infrastructure->getIdInfra(),
                'type' => $infrastructure->getType(),
                'statut' => $infrastructure->getStatut()
            ]);
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }
    
    
    public function recupererInfrastructureParId($id_infra) {
        $sql = "SELECT * FROM infrastructure WHERE id_infra = :id_infra";
        $db = Config::getConnection();
        
        try {
            $query = $db->prepare($sql);
            $query->execute(['id_infra' => $id_infra]);
            return $query->fetch(PDO::FETCH_ASSOC); // Ajoutez FETCH_ASSOC ici
        } catch (PDOException $e) {
            die("Erreur : " . $e->getMessage());
        }
    }
    


    public function getStatsInfrastructures() {
        $sql = "SELECT type, COUNT(*) as count, 
                ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM infrastructure), 2) as percentage
                FROM infrastructure
                GROUP BY type
                ORDER BY count DESC";
        
        $db = Config::getConnection();
        try {
            return $db->query($sql)->fetchAll();
        } catch (PDOException $e) {
            die("Erreur : " . $e->getMessage());
        }
    }
    public function getColorForType($type) {
        $colors = [
            'piste cyclable' => '#4CAF50',
            'route' => '#2196F3',
            'pont' => '#FFC107',
        ];
        
        return $colors[$type] ?? '#9E9E9E'; 
    }
    
    public function rechercherInfrastructureParType($type) {
        $sql = "SELECT * FROM infrastructure WHERE LOWER(type) = LOWER(:type)";
        $db = Config::getConnection();
    
        try {
            $query = $db->prepare($sql);
            $query->execute(['type' => trim($type)]);
            return $query->fetchAll();
        } catch (PDOException $e) {
            die("Erreur : " . $e->getMessage());
        }
    }
}
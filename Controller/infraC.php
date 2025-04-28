<?php
include_once(__DIR__ . '/../config/config.php');
include_once(__DIR__ . '/../Model/infra.php');

class infraC {
    public function ajouterInfrastructure($infrastructure) {
        $sql = "INSERT INTO infrastructure (id_infra, type, statut)
                VALUES (:id_infra, :type, :statut)";
        $db = Config::getConnection();
    
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id_infra' => $infrastructure->getIdInfra(),
                'type' => $infrastructure->getType(),
                'statut' => $infrastructure->getStatut()
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
            $stmt = $db->prepare($sql);
            $stmt->execute(['id_infra' => $id_infra]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
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
            // Ajoutez d'autres types et couleurs au besoin
        ];
        
        return $colors[$type] ?? '#9E9E9E'; // Couleur par défaut si type non trouvé
    }
    
    // Vous pouvez ajouter d'autres méthodes spécifiques aux infrastructures ici
}
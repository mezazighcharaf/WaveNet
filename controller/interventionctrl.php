<?php
    
    include_once __DIR__ . '/../views/includes/config.php';
    include_once __DIR__ . '/../models/intervention.php';

    class InterventionC {

        public function afficherIntervention() {
            $sql = "SELECT i.*, s.titre as signalement_titre
                    FROM intervention i
                    LEFT JOIN signalement s ON i.id_signalement = s.id_signalement
                    ORDER BY i.date_intervention DESC";
            
        }

        public function ajouterIntervention(Intervention $intervention) {
            $sql = "INSERT INTO intervention (statut, id_signalement, date_intervention)
                    VALUES (:statut, :id_signalement, NOW())";
            $db = Config::getConnection();
            try {
                $query = $db->prepare($sql);
                $query->execute([
                    ':statut' => $intervention->getStatut() ?? 'non traité',
                    ':id_signalement' => $intervention->getIdSignalement()
                 ]);
                 return $db->lastInsertId();
            } catch (Exception $e) {
                error_log('Erreur lors de l\'ajout de l\'intervention: ' . $e->getMessage());
                return false;
            }
        }

        public function deleteIntervention($id) {
            $sql = "DELETE FROM intervention WHERE id_intervention = :id";
            $db = Config::getConnection();
            $req = $db->prepare($sql);
            $req->bindValue(':id', $id, PDO::PARAM_INT);
            try {
                return $req->execute();
            } catch (Exception $e) {
                 error_log('Erreur lors de la suppression de l\'intervention: ' . $e->getMessage());
                 return false;
            }
        }

        public function updateIntervention(Intervention $intervention) {
            $sql = 'UPDATE intervention SET
                        date_intervention = :date_intervention,
                        statut = :statut,
                        id_signalement = :id_signalement
                    WHERE id_intervention = :id_intervention';
            $db = Config::getConnection();
            try {
                $query = $db->prepare($sql);
                return $query->execute([
                    'id_intervention' => $intervention->getIdIntervention(),
                    'date_intervention' => $intervention->getDateIntervention() ?? date('Y-m-d H:i:s'),
                    'statut' => $intervention->getStatut(),
                    'id_signalement' => $intervention->getIdSignalement(),
                ]);
            } catch (PDOException $e) {
                 error_log('Erreur lors de la mise à jour de l\'intervention: ' . $e->getMessage());
                 return false;
            }
        }

        public function getInterventionById($id) {
            $sql = "SELECT i.*, s.titre as signalement_titre
                    FROM intervention i
                    LEFT JOIN signalement s ON i.id_signalement = s.id_signalement
                    WHERE i.id_intervention = :id";
            $db = Config::getConnection();
            try {
                $query = $db->prepare($sql);
                $query->bindValue(':id', $id, PDO::PARAM_INT);
                $query->execute();
                $intervention = $query->fetch(PDO::FETCH_ASSOC);
                return $intervention;
            } catch (Exception $e) {
                 error_log('Erreur lors de la récupération de l\'intervention par ID: ' . $e->getMessage());
                return false;
            }
        }
    }
    ?>
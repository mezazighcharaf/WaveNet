<?php
class Utilisateur {
    private $id_utilisateur;
    private $nom;
    private $prenom;
    private $email;
    private $mot_de_passe;
    private $niveau;
    private $points_verts;
    private $id_quartier;
    private $newsletter = 0;
    private $evenements = 0;
    public function __construct($nom, $prenom, $email, $mot_de_passe, $niveau = 'client', $points_verts = 0, $id_quartier = null) {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        $this->mot_de_passe = $mot_de_passe;
        $this->niveau = $niveau;
        $this->points_verts = $points_verts;
        $this->id_quartier = $id_quartier;
    }
    public function getId() { return $this->id_utilisateur; }
    public function getNom() { return $this->nom; }
    public function getPrenom() { return $this->prenom; }
    public function getEmail() { return $this->email; }
    public function getMotDePasse() { return $this->mot_de_passe; }
    public function getNiveau() { return $this->niveau; }
    public function getPointsVerts() { return $this->points_verts; }
    public function getIdQuartier() { return $this->id_quartier; }
    public function getNewsletter() {
        return $this->newsletter;
    }
    public function setNewsletter($newsletter) {
        $this->newsletter = $newsletter;
        return $this;
    }
    public function getEvenements() {
        return $this->evenements;
    }
    public function setEvenements($evenements) {
        $this->evenements = $evenements;
        return $this;
    }
    public function setNom($nom) { $this->nom = $nom; }
    public function setPrenom($prenom) { $this->prenom = $prenom; }
    public function setEmail($email) { $this->email = $email; }
    public function setMotDePasse($mot_de_passe) { $this->mot_de_passe = $mot_de_passe; }
    public function setNiveau($niveau) { $this->niveau = $niveau; }
    public function setPointsVerts($points) { $this->points_verts = $points; }
    public function setIdQuartier($id) { $this->id_quartier = $id; }
    public static function findByEmail($db, $email) {
        $stmt = $db->prepare("SELECT * FROM UTILISATEUR WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $user = new Utilisateur(
                $result['nom'],
                $result['prenom'],
                $result['email'],
                $result['mot_de_passe'],
                $result['niveau'],
                $result['points_verts'],
                $result['id_quartier']
            );
            $user->id_utilisateur = $result['id_utilisateur'];
            return $user;
        }
        return null;
    }
    public static function create($db, $data) {
        try {
            error_log("Tentative d'insertion utilisateur: " . json_encode($data, JSON_UNESCAPED_UNICODE));
            $stmt = $db->prepare("INSERT INTO UTILISATEUR (nom, prenom, email, mot_de_passe, niveau, points_verts, id_quartier) VALUES (:nom, :prenom, :email, :mot_de_passe, :niveau, :points_verts, :id_quartier)");
            $params = [
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'email' => $data['email'],
                'mot_de_passe' => '***hidden***', 
                'niveau' => $data['niveau'] ?? 'client',
                'points_verts' => $data['points_verts'] ?? 0,
                'id_quartier' => $data['id_quartier'] ?? null
            ];
            error_log("Paramètres d'exécution: " . json_encode($params, JSON_UNESCAPED_UNICODE));
            $execParams = [
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'email' => $data['email'],
                'mot_de_passe' => $data['mot_de_passe'],
                'niveau' => $data['niveau'] ?? 'client',
                'points_verts' => $data['points_verts'] ?? 0,
                'id_quartier' => $data['id_quartier'] ?? null
            ];
            $result = $stmt->execute($execParams);
            if ($result) {
                $newId = $db->lastInsertId();
                error_log("Utilisateur créé avec succès, ID: " . $newId);
                return $newId;
            }
            error_log("Erreur lors de l'exécution de la requête: " . json_encode($stmt->errorInfo()));
            return false;
        } catch (PDOException $e) {
            error_log("Erreur PDO dans create: " . $e->getMessage());
            error_log("Code erreur PDO: " . $e->getCode());
            if ($e->getCode() == '23000') {
                if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    error_log("Email déjà utilisé");
                } elseif (strpos($e->getMessage(), 'foreign key constraint') !== false) {
                    error_log("Erreur de clé étrangère - vérifier id_quartier");
                }
            }
            throw new Exception("Erreur lors de la création de l'utilisateur: " . $e->getMessage());
        }
    }
    public static function findById($db, $id) {
        $stmt = $db->prepare("SELECT * FROM UTILISATEUR WHERE id_utilisateur = :id");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $user = new Utilisateur(
                $result['nom'],
                $result['prenom'],
                $result['email'],
                $result['mot_de_passe'],
                $result['niveau'],
                $result['points_verts'],
                $result['id_quartier']
            );
            $user->id_utilisateur = $result['id_utilisateur'];
            return $user;
        }
        return null;
    }
    public function update($db) {
        try {
            $checkColumns = $db->query("SHOW COLUMNS FROM UTILISATEUR LIKE 'newsletter'");
            $hasNewsletterColumn = $checkColumns->rowCount() > 0;
            $sql = "UPDATE UTILISATEUR SET 
                    nom = :nom, 
                    prenom = :prenom, 
                    email = :email, 
                    id_quartier = :id_quartier, 
                    mot_de_passe = :mot_de_passe";
            if ($hasNewsletterColumn) {
                $sql .= ", newsletter = :newsletter, evenements = :evenements";
            }
            $sql .= " WHERE id_utilisateur = :id_utilisateur";
            $stmt = $db->prepare($sql);
            $params = [
                'nom' => $this->nom,
                'prenom' => $this->prenom,
                'email' => $this->email,
                'id_quartier' => $this->id_quartier,
                'mot_de_passe' => $this->mot_de_passe,
                'id_utilisateur' => $this->id_utilisateur
            ];
            if ($hasNewsletterColumn) {
                $params['newsletter'] = $this->newsletter ? 1 : 0;
                $params['evenements'] = $this->evenements ? 1 : 0;
            }
            $stmt->execute($params);
            return true;
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour de l'utilisateur: " . $e->getMessage());
            return false;
        }
    }
}

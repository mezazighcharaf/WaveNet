<?php
// Script pour vérifier et corriger la structure de la table connexion_logs

// Activer l'affichage des erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connexion à la base de données
require_once __DIR__ . '/views/includes/config.php';

try {
    $db = connectDB();
    echo "Connexion à la base de données réussie.<br>";
    
    // Vérifier si la table connexion_logs existe
    $tableExists = false;
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        if (strtolower($table) === 'connexion_logs') {
            $tableExists = true;
            echo "Table connexion_logs trouvée.<br>";
            break;
        }
    }
    
    // Si la table n'existe pas, la créer
    if (!$tableExists) {
        echo "Table connexion_logs non trouvée. Création de la table...<br>";
        
        $createTable = "CREATE TABLE connexion_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_utilisateur INT NOT NULL,
            date_connexion DATETIME DEFAULT CURRENT_TIMESTAMP,
            ip_address VARCHAR(45),
            user_agent TEXT,
            country VARCHAR(100),
            city VARCHAR(100),
            latitude DECIMAL(10, 7),
            longitude DECIMAL(10, 7),
            success TINYINT(1) DEFAULT 1,
            failure_reason VARCHAR(255),
            INDEX (id_utilisateur),
            INDEX (date_connexion)
        )";
        
        $db->exec($createTable);
        echo "Table connexion_logs créée avec succès.<br>";
    } else {
        // Vérifier la structure de la table
        echo "Vérification de la structure de la table connexion_logs...<br>";
        
        $columns = $db->query("DESCRIBE connexion_logs")->fetchAll(PDO::FETCH_ASSOC);
        $columnNames = array_column($columns, 'Field');
        
        echo "Colonnes existantes: " . implode(", ", $columnNames) . "<br>";
        
        // Vérifier les colonnes requises
        $requiredColumns = [
            'id_utilisateur' => 'INT NOT NULL',
            'date_connexion' => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
            'ip_address' => 'VARCHAR(45)',
            'user_agent' => 'TEXT',
            'country' => 'VARCHAR(100)',
            'city' => 'VARCHAR(100)',
            'latitude' => 'DECIMAL(10, 7)',
            'longitude' => 'DECIMAL(10, 7)',
            'success' => 'TINYINT(1) DEFAULT 1',
            'failure_reason' => 'VARCHAR(255)'
        ];
        
        $missingColumns = [];
        
        foreach ($requiredColumns as $column => $type) {
            if (!in_array($column, $columnNames)) {
                $missingColumns[$column] = $type;
            }
        }
        
        // Ajouter les colonnes manquantes
        if (!empty($missingColumns)) {
            echo "Colonnes manquantes détectées. Ajout des colonnes manquantes...<br>";
            
            foreach ($missingColumns as $column => $type) {
                $db->exec("ALTER TABLE connexion_logs ADD COLUMN $column $type");
                echo "Colonne $column ajoutée.<br>";
            }
            
            echo "Structure de la table mise à jour avec succès.<br>";
        } else {
            echo "Toutes les colonnes requises sont présentes dans la table.<br>";
        }
        
        // Vérifier les index
        echo "Vérification des index...<br>";
        $indexes = $db->query("SHOW INDEX FROM connexion_logs")->fetchAll(PDO::FETCH_ASSOC);
        $indexNames = [];
        
        foreach ($indexes as $index) {
            $indexNames[] = $index['Column_name'];
        }
        
        $requiredIndexes = ['id_utilisateur', 'date_connexion'];
        $missingIndexes = [];
        
        foreach ($requiredIndexes as $index) {
            if (!in_array($index, $indexNames)) {
                $missingIndexes[] = $index;
            }
        }
        
        // Ajouter les index manquants
        if (!empty($missingIndexes)) {
            echo "Index manquants détectés. Ajout des index manquants...<br>";
            
            foreach ($missingIndexes as $index) {
                $db->exec("CREATE INDEX idx_$index ON connexion_logs ($index)");
                echo "Index sur $index ajouté.<br>";
            }
            
            echo "Index de la table mis à jour avec succès.<br>";
        } else {
            echo "Tous les index requis sont présents dans la table.<br>";
        }
    }
    
    // Test d'insertion
    echo "Test d'insertion dans la table connexion_logs...<br>";
    
    $stmt = $db->prepare("INSERT INTO connexion_logs 
                         (id_utilisateur, ip_address, user_agent, country, city, latitude, longitude, success) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        1, // ID utilisateur de test
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT'] ?? 'CLI',
        'Test',
        'Test',
        0,
        0,
        1
    ]);
    
    echo "Insertion de test réussie. ID: " . $db->lastInsertId() . "<br>";
    
    // Afficher les entrées récentes
    echo "Dernières entrées dans la table connexion_logs:<br>";
    
    $entries = $db->query("SELECT * FROM connexion_logs ORDER BY date_connexion DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<pre>";
    print_r($entries);
    echo "</pre>";
    
    echo "Vérification terminée. La table connexion_logs est prête à être utilisée.";
    
} catch (PDOException $e) {
    echo "Erreur: " . $e->getMessage() . "<br>";
} 
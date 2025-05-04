<?php
// Correction des chemins d'inclusion
include_once(__DIR__ . '/../../config/config.php');  // Remonter de 2 niveaux depuis FrontOffice
include_once(__DIR__ . '/../../Model/quartier.php'); // Remonter de 2 niveaux depuis FrontOffice

$pdo = Config::getConnection();

$sql = "SELECT idq, localisation, ville FROM quartier WHERE latitude IS NULL OR longitude IS NULL";
$stmt = $pdo->query($sql);
$quartiers = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($quartiers as $quartier) {
    $adresse = urlencode($quartier['localisation'] . ', ' . $quartier['ville'] . ', Tunisie');
    $url = "https://nominatim.openstreetmap.org/search?q={$adresse}&format=json&limit=1";

    $options = [
        "http" => [
            "header" => "User-Agent: QuartierApp/1.0\r\n"
        ]
    ];
    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    $data = json_decode($response, true);

    if (!empty($data)) {
        $lat = $data[0]['lat'];
        $lon = $data[0]['lon'];

        $update = $pdo->prepare("UPDATE quartier SET latitude = ?, longitude = ? WHERE idq = ?");
        $update->execute([$lat, $lon, $quartier['idq']]);

        echo "✅ Quartier {$quartier['idq']} mis à jour : ($lat, $lon)<br>";
        sleep(1);
    } else {
        echo "⚠️ Échec pour le quartier {$quartier['idq']} - {$quartier['localisation']}<br>";
    }
}
?>
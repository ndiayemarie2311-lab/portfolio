<?php
// get_avis.php — Retourne les avis approuvés en JSON

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'config.php';

$conn = getConnexion();

$result = $conn->query("SELECT nom, role, message, note, date_ajout FROM avis WHERE approuve = 1 ORDER BY date_ajout DESC");

$avis = [];
while ($row = $result->fetch_assoc()) {
    $avis[] = $row;
}

echo json_encode(['success' => true, 'avis' => $avis]);

$conn->close();
?>
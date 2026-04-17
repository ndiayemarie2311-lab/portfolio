<?php
// ajouter_avis.php — Reçoit et sauvegarde un avis client

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once 'config.php';

// Vérifier que c'est bien une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

// Récupérer et nettoyer les données
$nom     = trim($_POST['nom'] ?? '');
$role    = trim($_POST['role'] ?? '');
$message = trim($_POST['message'] ?? '');
$note    = intval($_POST['note'] ?? 5);

// Validation
if (empty($nom) || empty($role) || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Tous les champs sont obligatoires.']);
    exit;
}

if (strlen($nom) > 100 || strlen($role) > 100) {
    echo json_encode(['success' => false, 'message' => 'Nom ou rôle trop long.']);
    exit;
}

if ($note < 1 || $note > 5) {
    $note = 5;
}

// Sauvegarder en base de données
$conn = getConnexion();

$stmt = $conn->prepare("INSERT INTO avis (nom, role, message, note, approuve) VALUES (?, ?, ?, ?, 0)");
$stmt->bind_param("sssi", $nom, $role, $message, $note);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Merci pour votre avis ! Il sera publié après validation.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de l\'enregistrement. Veuillez réessayer.'
    ]);
}

$stmt->close();
$conn->close();
?>
<?php
// api/register.php
require_once 'config.php';

$data = json_decode(file_get_contents("php://input"), true);

$prenom   = trim($data['prenom'] ?? '');
$nom      = trim($data['nom'] ?? '');
$email    = trim($data['email'] ?? '');
$tel      = trim($data['tel'] ?? '');
$formation= trim($data['formation'] ?? '');

if (!$prenom || !$nom || !$email || !$tel || !$formation) {
    respond(['success' => false, 'message' => 'Tous les champs sont obligatoires.'], 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(['success' => false, 'message' => 'Email invalide.'], 400);
}

$db = getDB();

// Vérifier doublon
$stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    respond(['success' => false, 'message' => 'Cet email est déjà utilisé.'], 409);
}

// Insérer
$date = date('d/m/Y');
$stmt = $db->prepare("INSERT INTO users (prenom, nom, email, tel, formation, status, date_inscription) VALUES (?, ?, ?, ?, ?, 'pending', ?)");
$stmt->bind_param("ssssss", $prenom, $nom, $email, $tel, $formation, $date);

if ($stmt->execute()) {
    respond([
        'success'   => true,
        'message'   => 'Inscription envoyée avec succès.',
        'prenom'    => $prenom,
        'nom'       => $nom,
        'email'     => $email,
        'tel'       => $tel,
        'formation' => $formation,
        'status'    => 'pending'
    ]);
} else {
    respond(['success' => false, 'message' => 'Erreur lors de l\'inscription.'], 500);
}
?>

<?php
// api/login.php
require_once 'config.php';

$data  = json_decode(file_get_contents("php://input"), true);
$email = trim($data['email'] ?? '');
$pw    = trim($data['password'] ?? '');

if (!$email || !$pw) {
    respond(['success' => false, 'message' => 'Email et mot de passe requis.'], 400);
}

// Admin principal
if ($email === 'visiondudigital@gmail.com' && $pw === 'Admin@VDD2025') {
    respond([
        'success' => true,
        'role'    => 'admin',
        'prenom'  => 'Mouhamed Lamine',
        'nom'     => 'Ndao',
        'email'   => $email
    ]);
}

$db = getDB();

// Chercher dans mentors
$stmt = $db->prepare("SELECT * FROM mentors WHERE email = ? AND password = ?");
$stmt->bind_param("ss", $email, $pw);
$stmt->execute();
$mentor = $stmt->get_result()->fetch_assoc();
if ($mentor) {
    respond([
        'success'   => true,
        'role'      => 'mentor',
        'prenom'    => $mentor['prenom'],
        'nom'       => $mentor['nom'],
        'email'     => $mentor['email'],
        'formation' => $mentor['formation']
    ]);
}

// Chercher dans users
$stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND password = ?");
$stmt->bind_param("ss", $email, $pw);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    respond(['success' => false, 'message' => 'Email ou mot de passe incorrect.'], 401);
}
if ($user['status'] === 'blocked') {
    respond(['success' => false, 'message' => 'Compte suspendu. Contactez l\'administrateur.'], 403);
}
if ($user['status'] === 'pending') {
    respond(['success' => false, 'message' => 'Compte en attente d\'activation. L\'administrateur vous contactera par WhatsApp.'], 403);
}

// Mettre à jour last_login
$today = date('d/m/Y');
$db->prepare("UPDATE users SET last_login = ? WHERE email = ?")->bind_param("ss", $today, $email)->execute();

respond([
    'success'   => true,
    'role'      => 'user',
    'prenom'    => $user['prenom'],
    'nom'       => $user['nom'],
    'email'     => $user['email'],
    'tel'       => $user['tel'],
    'formation' => $user['formation'],
    'status'    => $user['status']
]);
?>

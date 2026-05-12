<?php
// api/setup.php
// Exécuter une seule fois pour créer les tables sur Railway
require_once 'config.php';

$db = getDB();

$queries = [
"CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  prenom VARCHAR(100) NOT NULL,
  nom VARCHAR(100) NOT NULL,
  email VARCHAR(150) UNIQUE NOT NULL,
  tel VARCHAR(30),
  formation VARCHAR(100),
  password VARCHAR(255),
  status ENUM('pending','active','blocked') DEFAULT 'pending',
  date_inscription VARCHAR(20),
  last_login VARCHAR(20)
)",
"CREATE TABLE IF NOT EXISTS mentors (
  id INT AUTO_INCREMENT PRIMARY KEY,
  prenom VARCHAR(100) NOT NULL,
  nom VARCHAR(100) NOT NULL,
  email VARCHAR(150) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  formation VARCHAR(100),
  tel VARCHAR(30),
  date_ajout VARCHAR(20)
)",
"CREATE TABLE IF NOT EXISTS notifications_admin (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titre VARCHAR(255),
  message TEXT,
  cible VARCHAR(50),
  date_envoi VARCHAR(20),
  nb_destinataires INT DEFAULT 0
)"
];

$results = [];
foreach ($queries as $q) {
    if ($db->query($q)) {
        $results[] = "✅ OK";
    } else {
        $results[] = "❌ Erreur: " . $db->error;
    }
}

respond([
    'success' => true,
    'message' => 'Tables créées avec succès !',
    'details' => $results
]);
?>

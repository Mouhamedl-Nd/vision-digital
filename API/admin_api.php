<?php
// api/admin.php
require_once 'config.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$data   = json_decode(file_get_contents("php://input"), true) ?? [];

$db = getDB();

switch ($action) {

    // ── Lister tous les utilisateurs ──────────────────────────
    case 'get_users':
        $result = $db->query("SELECT id,prenom,nom,email,tel,formation,status,date_inscription,last_login FROM users ORDER BY id DESC");
        $users = [];
        while ($row = $result->fetch_assoc()) $users[] = $row;
        respond(['success' => true, 'users' => $users]);

    // ── Créer un compte utilisateur ───────────────────────────
    case 'create_user':
        $prenom   = trim($data['prenom'] ?? '');
        $nom      = trim($data['nom'] ?? '');
        $email    = trim($data['email'] ?? '');
        $tel      = trim($data['tel'] ?? '');
        $formation= trim($data['formation'] ?? '');
        $pw       = trim($data['password'] ?? '');

        if (!$prenom||!$nom||!$email||!$formation||!$pw) {
            respond(['success'=>false,'message'=>'Tous les champs sont obligatoires.'], 400);
        }

        // Vérifier doublon
        $stmt = $db->prepare("SELECT id FROM users WHERE email=?");
        $stmt->bind_param("s",$email); $stmt->execute();
        if ($stmt->get_result()->num_rows>0) {
            respond(['success'=>false,'message'=>'Email déjà utilisé.'], 409);
        }

        $date = date('d/m/Y');
        $stmt = $db->prepare("INSERT INTO users (prenom,nom,email,tel,formation,password,status,date_inscription) VALUES (?,?,?,?,?,?,'active',?)");
        $stmt->bind_param("sssssss",$prenom,$nom,$email,$tel,$formation,$pw,$date);

        if ($stmt->execute()) {
            respond(['success'=>true,'message'=>'Compte créé avec succès.','prenom'=>$prenom,'nom'=>$nom,'email'=>$email,'password'=>$pw,'formation'=>$formation,'tel'=>$tel]);
        } else {
            respond(['success'=>false,'message'=>'Erreur création compte.'], 500);
        }

    // ── Activer un compte ─────────────────────────────────────
    case 'activate_user':
        $email = trim($data['email'] ?? '');
        $stmt = $db->prepare("UPDATE users SET status='active' WHERE email=?");
        $stmt->bind_param("s",$email); $stmt->execute();
        respond(['success'=>true,'message'=>'Compte activé.']);

    // ── Bloquer un compte ─────────────────────────────────────
    case 'block_user':
        $email = trim($data['email'] ?? '');
        $stmt = $db->prepare("UPDATE users SET status='blocked' WHERE email=?");
        $stmt->bind_param("s",$email); $stmt->execute();
        respond(['success'=>true,'message'=>'Compte bloqué.']);

    // ── Supprimer un utilisateur ──────────────────────────────
    case 'delete_user':
        $email = trim($data['email'] ?? '');
        $stmt = $db->prepare("DELETE FROM users WHERE email=?");
        $stmt->bind_param("s",$email); $stmt->execute();
        respond(['success'=>true,'message'=>'Utilisateur supprimé.']);

    // ── Définir mot de passe ──────────────────────────────────
    case 'set_password':
        $email = trim($data['email'] ?? '');
        $pw    = trim($data['password'] ?? '');
        $stmt  = $db->prepare("UPDATE users SET password=?, status='active' WHERE email=?");
        $stmt->bind_param("ss",$pw,$email); $stmt->execute();
        respond(['success'=>true,'message'=>'Mot de passe défini.']);

    // ── Lister mentors ────────────────────────────────────────
    case 'get_mentors':
        $result = $db->query("SELECT id,prenom,nom,email,formation,tel,date_ajout FROM mentors ORDER BY id DESC");
        $mentors = [];
        while ($row = $result->fetch_assoc()) $mentors[] = $row;
        respond(['success'=>true,'mentors'=>$mentors]);

    // ── Ajouter mentor ────────────────────────────────────────
    case 'add_mentor':
        $prenom   = trim($data['prenom'] ?? '');
        $nom      = trim($data['nom'] ?? '');
        $email    = trim($data['email'] ?? '');
        $pw       = trim($data['password'] ?? '');
        $formation= trim($data['formation'] ?? '');
        $tel      = trim($data['tel'] ?? '');

        if (!$prenom||!$nom||!$email||!$pw||!$formation) {
            respond(['success'=>false,'message'=>'Champs obligatoires manquants.'],400);
        }

        $stmt = $db->prepare("SELECT id FROM mentors WHERE email=?");
        $stmt->bind_param("s",$email); $stmt->execute();
        if ($stmt->get_result()->num_rows>0) {
            respond(['success'=>false,'message'=>'Ce mentor existe déjà.'],409);
        }

        $date = date('d/m/Y');
        $stmt = $db->prepare("INSERT INTO mentors (prenom,nom,email,password,formation,tel,date_ajout) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param("sssssss",$prenom,$nom,$email,$pw,$formation,$tel,$date);
        if ($stmt->execute()) {
            respond(['success'=>true,'message'=>'Mentor ajouté.']);
        } else {
            respond(['success'=>false,'message'=>'Erreur ajout mentor.'],500);
        }

    // ── Supprimer mentor ──────────────────────────────────────
    case 'delete_mentor':
        $id = intval($data['id'] ?? 0);
        $stmt = $db->prepare("DELETE FROM mentors WHERE id=?");
        $stmt->bind_param("i",$id); $stmt->execute();
        respond(['success'=>true,'message'=>'Mentor supprimé.']);

    // ── Stats globales ────────────────────────────────────────
    case 'get_stats':
        $total   = $db->query("SELECT COUNT(*) as n FROM users")->fetch_assoc()['n'];
        $active  = $db->query("SELECT COUNT(*) as n FROM users WHERE status='active'")->fetch_assoc()['n'];
        $pending = $db->query("SELECT COUNT(*) as n FROM users WHERE status='pending'")->fetch_assoc()['n'];
        $blocked = $db->query("SELECT COUNT(*) as n FROM users WHERE status='blocked'")->fetch_assoc()['n'];
        respond(['success'=>true,'total'=>$total,'active'=>$active,'pending'=>$pending,'blocked'=>$blocked]);

    default:
        respond(['success'=>false,'message'=>'Action inconnue: '.$action],400);
}
?>

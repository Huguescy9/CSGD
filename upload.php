<?php
session_start();
require_once 'config.php';

// Vérifie si l'utilisateur est authentifié
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Authentication requise'
    ]);
    exit;
}

try {
    // Connexion à la base de données
    $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Traitement de l'upload du fichier des employés
    if(isset($_FILES['employeeFile'])) {
        $file = $_FILES['employeeFile'];
        if($file['type'] != 'text/csv') {
            throw new Exception('Format de fichier invalide');
        }
        
        // Lecture du fichier CSV
        $handle = fopen($file['tmp_name'], "r");
        $headers = fgetcsv($handle, 1000, ",");
        
        // Traitement de chaque ligne
        while(($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $stmt = $db->prepare("INSERT INTO employees (nom, prenom, matricule, email, service, metier, password) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)");
            $password = generateSecurePassword();
            $stmt->execute([
                $data[0], // nom
                $data[1], // prenom
                $data[2], // matricule
                $data[3], // email
                $data[4], // service
                $data[5], // metier
                password_hash($password, PASSWORD_DEFAULT)
            ]);
        }
        fclose($handle);
    }
    
    // Traitement de l'upload du fichier de matériel
    if(isset($_FILES['equipmentFile'])) {
        $file = $_FILES['equipmentFile'];
        if($file['type'] != 'text/csv') {
            throw new Exception('Format de fichier invalide');
        }
        
        // Lecture du fichier CSV
        $handle = fopen($file['tmp_name'], "r");
        $headers = fgetcsv($handle, 1000, ",");
        
        // Traitement de chaque ligne
        while(($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $stmt = $db->prepare("INSERT INTO equipment (matricule, type, serie, date_achat, reference) 
                                VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $data[0], // matricule
                $data[1], // type
                $data[2], // serie
                $data[3], // date_achat
                $data[4]  // reference
            ]);
        }
        fclose($handle);
    }
    
    // Renvoie une réponse de succès
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'redirect' => 'index.html'
    ]);
    
} catch(Exception $e) {
    // En cas d'erreur, renvoie un message d'erreur
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// Fonction pour générer un mot de passe sécurisé
function generateSecurePassword($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    $password = '';
    for($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $password;
}
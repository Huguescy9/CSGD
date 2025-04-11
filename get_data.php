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

header('Content-Type: application/json');

try {
    // Connexion à la base de données
    $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Récupère le type de données demandé (employees ou equipment)
    $type = $_GET['type'] ?? null;
    
    if ($type === 'employees') {
        // Récupère tous les employés, triés par nom et prénom
        $stmt = $db->query("SELECT * FROM employees ORDER BY nom, prenom");
        $data = ['employees' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    } 
    else if ($type === 'equipment') {
        // Récupère tout le matériel, trié par matricule
        $stmt = $db->query("SELECT * FROM equipment ORDER BY matricule");
        $data = ['equipment' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }
    else {
        // Si aucun type n'est spécifié, récupère les deux types de données
        $stmt = $db->query("SELECT * FROM employees ORDER BY nom, prenom");
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt = $db->query("SELECT * FROM equipment ORDER BY matricule");
        $equipment = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $data = [
            'employees' => $employees,
            'equipment' => $equipment
        ];
    }
    
    // Renvoie les données au format JSON
    echo json_encode([
        'success' => true,
        ...$data
    ]);
    
} catch(Exception $e) {
    // En cas d'erreur, renvoie un message d'erreur
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
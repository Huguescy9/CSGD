<?php
session_start();

// Vérifie si l'utilisateur est authentifié
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Non authentifié'
    ]);
    exit;
}

// Renvoie les informations de l'utilisateur connecté
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'username' => $_SESSION['username']
]);
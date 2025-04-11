<?php
session_start();

// Vérifie si l'utilisateur est authentifié en contrôlant les variables de session
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    // Si non authentifié, renvoie une réponse JSON négative
    header('Content-Type: application/json');
    echo json_encode(['authenticated' => false]);
    exit;
}

// Si authentifié, renvoie une réponse JSON positive
header('Content-Type: application/json');
echo json_encode(['authenticated' => true]);


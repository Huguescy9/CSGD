<?php
session_start();
require_once 'config.php';

// Vérifie si la requête est de type POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupère les identifiants depuis le formulaire
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        // Connexion à la base de données
        $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Recherche l'utilisateur dans la base de données
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Vérifie si l'utilisateur existe et si le mot de passe correspond
        if ($user && password_verify($password, $user['password'])) {
            // Création des variables de session pour l'utilisateur authentifié
            $_SESSION['authenticated'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // Met à jour la date de dernière connexion
            $stmt = $db->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$user['id']]);
            
            // Redirection vers la page principale
            header('Location: index.html');
            exit;
        }
    } catch(PDOException $e) {
        // Log l'erreur silencieusement, sans exposer les détails à l'utilisateur
        error_log("Erreur d'authentification: " . $e->getMessage());
    }

    // En cas d'échec d'authentification, redirige vers la page de connexion avec une erreur
    header('Location: login.php?error=1');
    exit;
}

// Si la méthode n'est pas POST, redirige vers la page de connexion
header('Location: login.php');
exit;
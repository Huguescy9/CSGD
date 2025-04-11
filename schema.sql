CREATE DATABASE IF NOT EXISTS gestion_si;
USE gestion_si;

CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    matricule VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    service VARCHAR(100) NOT NULL,
    metier VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    matricule VARCHAR(50) NOT NULL,
    type VARCHAR(100) NOT NULL,
    serie VARCHAR(100) NOT NULL UNIQUE,
    date_achat DATE NOT NULL,
    reference VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (matricule) REFERENCES employees(matricule)
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    role VARCHAR(20) DEFAULT 'user',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Création d'un index sur le matricule pour la table equipment
CREATE INDEX idx_matricule ON equipment(matricule);

-- Insertion de l'utilisateur admin par défaut (mot de passe: admin)
INSERT INTO users (username, password, role) 
VALUES ('admin', '$2y$10$o5NTH0cv1QFuZgih5r59GeqNvA0rA/a5EJjcbDuckqafB7yWjoMbW', 'admin');


<div align="center">

# Gestion administrative SI

**Gestion des employés et du matériel informatique d'un service SI**

[![PHP](https://img.shields.io/badge/PHP-8-777BB4?logo=php&logoColor=white)](https://www.php.net)
[![MySQL](https://img.shields.io/badge/MySQL-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?logo=bootstrap&logoColor=white)](https://getbootstrap.com)
[![jQuery](https://img.shields.io/badge/jQuery-3.7-0769AD?logo=jquery&logoColor=white)](https://jquery.com)
[![DataTables](https://img.shields.io/badge/DataTables-1.13-1F6FEB)](https://datatables.net)
[![FPDF](https://img.shields.io/badge/PDF-FPDF-E5252A)](http://www.fpdf.org)

[Fonctionnalités](#fonctionnalités) · [Installation](#installation) · [Format CSV](#format-des-fichiers-csv) · [Base de données](#base-de-données)

</div>

---

Application web de gestion administrative pour un service informatique : import des **employés** et du **matériel** depuis des fichiers CSV, consultation dans des tableaux filtrables, export CSV et génération de **fiches PDF** (fiche de bienvenue avec mot de passe provisoire, fiche de remise de matériel à signer).

## Fonctionnalités

- **Authentification** par session PHP (mots de passe hachés avec `password_hash`).
- **Import CSV** des employés et du matériel.
- **Tableaux interactifs** (DataTables) : recherche, tri, pagination, sélection multiple, interface en français.
- **Filtre par période** : date d'embauche (employés) et date d'achat (matériel).
- **Export CSV** des tableaux.
- **Génération de PDF** (FPDF) pour les lignes sélectionnées :
  - *Fiche de bienvenue* : informations de l'employé et mot de passe provisoire.
  - *Fiche de remise de matériel* : liste du matériel attribué à un matricule, avec zones de signature.

## Technologies

| Couche | Outils |
|---|---|
| Back-end | PHP 8 (PDO), MySQL / MariaDB |
| Front-end | HTML, CSS, JavaScript, Bootstrap 5.3, jQuery 3.7, DataTables 1.13, Bootstrap Datepicker, Moment.js |
| PDF | [setasign/fpdf](https://github.com/Setasign/FPDF) via Composer |

## Structure du projet

```
├── index.html          # Tableau de bord (imports, tableaux, exports, PDF)
├── app.js              # Logique front : DataTables, filtres, appels AJAX
├── styles.css          # Styles de l'interface
├── login.php           # Page de connexion
├── authenticate.php    # Vérification des identifiants et ouverture de session
├── check_auth.php      # Indique si la session est authentifiée (JSON)
├── get_user_info.php   # Renvoie le nom de l'utilisateur connecté (JSON)
├── logout.php          # Fermeture de session
├── upload.php          # Import des fichiers CSV en base
├── get_data.php        # Lecture des employés / du matériel (JSON)
├── generate_pdf.php    # Génération des fiches PDF
├── config.example.php  # Modèle de configuration (à copier en config.php)
├── schema.sql          # Création de la base et compte admin par défaut
├── composer.json       # Dépendance FPDF
└── assets/logo.png     # Logo
```

## Installation

### Prérequis

- PHP 8.0 ou plus récent, avec l'extension `pdo_mysql`
- MySQL ou MariaDB
- Composer
- Un serveur web (Apache, Nginx, ou une pile comme XAMPP / WAMP / MAMP)

### Étapes

1. **Cloner le dépôt** dans le dossier de votre serveur web :
   ```bash
   git clone <url-du-depot>
   cd CSGD
   ```

2. **Installer les dépendances** :
   ```bash
   composer install
   ```

3. **Créer la base de données** :
   ```bash
   mysql -u root -p < schema.sql
   ```
   Le script crée la base `gestion_si`, les tables `employees`, `equipment` et `users`, et un compte administrateur.

4. **Configurer la connexion** : copier le fichier d'exemple, puis renseigner vos identifiants.
   ```bash
   cp config.example.php config.php
   ```
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'gestion_si');
   define('DB_USER', 'votre_utilisateur');
   define('DB_PASS', 'votre_mot_de_passe');
   ```

5. **Ouvrir l'application** : `http://localhost/CSGD/login.php`

### Compte par défaut

| Identifiant | Mot de passe |
|---|---|
| `admin` | `admin` |

> Changez ce mot de passe dès la première mise en service.

## Format des fichiers CSV

Les fichiers doivent être encodés en UTF-8, séparés par des virgules, avec une **ligne d'en-tête** (elle est ignorée à l'import).

**Employés**

```csv
nom,prenom,matricule,email,service,metier
Dupont,Marie,EMP001,marie.dupont@exemple.fr,Comptabilité,Comptable
Martin,Lucas,EMP002,lucas.martin@exemple.fr,Informatique,Technicien
```

**Matériel** (le matricule doit correspondre à un employé existant, la date est au format `AAAA-MM-JJ`)

```csv
matricule,type,serie,date_achat,reference
EMP001,Ordinateur portable,SN-48213,2025-03-12,Dell Latitude 5440
EMP002,Écran,SN-77120,2025-02-28,Dell P2423
```

Contraintes d'unicité : `matricule` et `email` pour les employés, `serie` pour le matériel.

## Utilisation

1. Se connecter avec un compte de la table `users`.
2. Importer le CSV des employés, puis celui du matériel.
3. Dans l'onglet **Employés** ou **Matériel**, filtrer et rechercher les lignes voulues.
4. Cocher les lignes, puis cliquer sur **Générer fiches de bienvenue** ou **Générer fiches de matériel** pour télécharger le PDF.
5. **Exporter en CSV** pour récupérer le contenu du tableau.

## Base de données

| Table | Rôle |
|---|---|
| `employees` | Employés (nom, prénom, matricule, email, service, métier, mot de passe haché, date de création) |
| `equipment` | Matériel lié à un employé par son `matricule` (type, n° de série, date d'achat, référence) |
| `users` | Comptes d'accès à l'application (identifiant, mot de passe haché, rôle, dernière connexion) |

## Sécurité

- `config.php` est ignoré par git (`.gitignore`) : seuls les identifiants d'exemple de `config.example.php` sont publiés.
- Modifiez le mot de passe du compte `admin` par défaut.
- Les fiches de bienvenue contiennent un mot de passe provisoire : traitez-les comme des documents confidentiels.
- En production, servez l'application en HTTPS.

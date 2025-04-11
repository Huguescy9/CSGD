<?php 
session_start();
require_once 'config.php';

// Vérifie si l'utilisateur est authentifié
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('HTTP/1.1 401 Unauthorized');
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Authentication requise']);
    exit;
}

// Inclut la bibliothèque FPDF
require_once 'vendor/autoload.php';

// Classe personnalisée étendant FPDF pour la génération de PDF
class CustomPDF extends FPDF {
    // Définit l'en-tête du PDF
    function Header() {
        $this->Image('assets/logo.png', 10, 6, 35); // logo légèrement remonté
        $this->SetFont('Arial', 'B', 20);
        $this->SetTextColor(33, 37, 41);
        $this->Cell(0, 10, utf8_decode('Service informatique'), 0, 1, 'C');
        
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(220, 53, 69);
        $this->Cell(0, 10, utf8_decode('DOCUMENT CONFIDENTIEL'), 0, 1, 'C'); // sur TOUTES les pages
        
        $this->SetDrawColor(200, 200, 200);
        $this->Ln(20);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(20);
    }

    // Définit le pied de page du PDF
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }

    // Crée l'en-tête du tableau
    function TableHeader($headers) {
        $this->SetFillColor(52, 58, 64);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 10);
        foreach ($headers as $header) {
            $this->Cell(40, 8, utf8_decode($header), 1, 0, 'C', true);
        }
        $this->Ln();
    }

    // Crée une ligne du tableau
    function TableRow($data) {
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(0, 0, 0);
        foreach ($data as $item) {
            $this->Cell(40, 7, utf8_decode($item), 1);
        }
        $this->Ln();
    }

    function FancySectionTitle($title) {
        $this->SetFillColor(222, 226, 230);
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(33, 37, 41);
        $this->Cell(0, 10, utf8_decode($title), 0, 1, 'L', true);
    }
}

try {
    // Récupère les données JSON envoyées
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['type']) || !isset($input['data'])) {
        throw new Exception('Données invalides');
    }

    $type = $input['type'];
    $data = $input['data'];

    $pdf = new CustomPDF();
    // Génère le PDF selon le type demandé (welcome = fiche de bienvenue, equipment = fiche de matériel)
    if ($type === 'welcome') {
        // Génère une fiche de bienvenue pour chaque employé
        foreach ($data as $employee) {
            $pdf->AddPage();
            // En-tête de la fiche
            $pdf->SetFillColor(13, 110, 253);
            $pdf->SetTextColor(255);
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->Cell(0, 12, utf8_decode('Fiche de bienvenue - nouvel employé'), 0, 1, 'C', true);
            $pdf->Ln(10);

            // Informations de l'employé
            $pdf->FancySectionTitle('Informations personnelles');

            $pdf->SetFont('Arial', '', 11);
            $pdf->SetTextColor(0);
            $pdf->SetFillColor(248, 249, 250);
            foreach ([
                ['Nom', $employee['nom']],
                ['Prénom', $employee['prenom']],
                ['Matricule', $employee['matricule']],
                ['Email', $employee['email']],
                ['Service', $employee['service']]
            ] as $row) {
                $pdf->SetFont('Arial', 'B', 11);
                $pdf->Cell(40, 8, utf8_decode($row[0]) . ' : ', 0, 0);
                $pdf->SetFont('Arial', '', 11);
                $pdf->Cell(0, 8, utf8_decode($row[1]), 0, 1);
            }
            
            // Section mot de passe provisoire
            $pdf->Ln(10);
            $pdf->FancySectionTitle('Informations de connexion');

            $password = generateSecurePassword(8);
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(60, 8, 'Mot de passe provisoire : ', 0);
            $pdf->SetFont('Courier', 'B', 12);
            $pdf->SetFillColor(222, 255, 222);
            $pdf->Cell(50, 8, $password, 1, 1, 'C', true);
            
            // Note de sécurité
            $pdf->Ln(10);
            $pdf->SetFont('Arial', 'I', 10);
            $pdf->SetTextColor(80, 80, 80);
            $pdf->MultiCell(0, 5, utf8_decode("Pour des raisons de sécurité, veuillez changer ce mot de passe lors de votre première connexion."));
        }
    } 
    else if ($type === 'equipment') {
        // Groupe le matériel par matricule d'employé
        $grouped = [];
        foreach ($data as $item) {
            $grouped[$item['matricule']][] = $item;
        }
        // Génère une fiche par employé avec son matériel
        foreach ($grouped as $matricule => $equipments) {
            $pdf->AddPage();
            // En-tête
            $pdf->SetFillColor(25, 135, 84);
            $pdf->SetTextColor(255);
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->Cell(0, 12, utf8_decode('Fiche de remise de matériel'), 0, 1, 'C', true);
            $pdf->Ln(8);
            
            // Matricule de l'employé
            $pdf->SetTextColor(0);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, 'Matricule : ' . $matricule, 0, 1, 'L');
            $pdf->Ln(3);

            // Tableau des équipements
            $headers = ['Type', 'N° Série', "Date d'achat", 'Référence'];
            $pdf->TableHeader($headers);

            foreach ($equipments as $equipment) {
                $pdf->TableRow([
                    $equipment['type'],
                    $equipment['serie'],
                    $equipment['date_achat'],
                    $equipment['reference']
                ]);
            }

            // Zone de signature
            $pdf->Ln(20);
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(0, 10, utf8_decode('Date de remise : ______________________'), 0, 1);
            $pdf->Ln(10);
            $pdf->Cell(90, 10, utf8_decode('Signature de l\'employé :'), 0, 0);
            $pdf->Cell(90, 10, 'Signature du responsable :', 0, 1);
            $pdf->Cell(90, 20, '________________________', 0, 0);
            $pdf->Cell(90, 20, '________________________', 0, 1);
        }
    }

    // Génère le PDF et force le téléchargement
    $pdf->Output('D', $type . '_' . date('Y-m-d') . '.pdf');

} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}

// Fonction pour générer un mot de passe sécurisé
function generateSecurePassword($length = 8) {
    $chars = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $password;
}

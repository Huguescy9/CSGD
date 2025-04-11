<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Gestion administrative SI</title>
    <!-- Importation des ressources nécessaires -->
    <link rel="icon" type="image/png" href="favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body class="login-page">
    <!-- Conteneur principal du formulaire de connexion -->
    <div class="login-container">
        <div class="login-form-container">
            <!-- En-tête avec logo -->
            <div class="text-center mb-4">
                <img src="assets/logo.png" alt="Logo" class="login-logo">
                <h2 class="mt-3">Connexion</h2>
            </div>
            
            <!-- Affichage du message d'erreur si nécessaire -->
            <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Identifiants incorrects
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Formulaire de connexion -->
            <form id="loginForm" action="authenticate.php" method="post" class="needs-validation" novalidate>
                <!-- Champ nom d'utilisateur -->
                <div class="form-group mb-3">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-user"></i>
                        </span>
                        <input type="text" 
                               class="form-control" 
                               id="username" 
                               name="username" 
                               placeholder="Nom d'utilisateur"
                               required>
                        <div class="invalid-feedback">
                            Veuillez saisir votre nom d'utilisateur
                        </div>
                    </div>
                </div>

                <!-- Champ mot de passe avec bouton pour afficher/masquer -->
                <div class="form-group mb-3">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password" 
                               placeholder="Mot de passe"
                               required>
                        <button class="btn btn-outline-secondary toggle-password" type="button">
                            <i class="fas fa-eye"></i>
                        </button>
                        <div class="invalid-feedback">
                            Veuillez saisir votre mot de passe
                        </div>
                    </div>
                </div>

                <!-- Bouton de connexion avec état de chargement -->
                <button type="submit" class="btn btn-primary w-100 mb-3">
                    <span class="button-text">Se connecter</span>
                    <div class="button-loader d-none">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Connexion...
                    </div>
                </button>

            </form>
        </div>
    </div>

    <!-- Scripts JavaScript -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animation de chargement de la page
            document.body.classList.add('loaded');

            // Validation du formulaire
            const form = document.getElementById('loginForm');
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                } else {
                    const buttonText = form.querySelector('.button-text');
                    const buttonLoader = form.querySelector('.button-loader');
                    buttonText.classList.add('d-none');
                    buttonLoader.classList.remove('d-none');
                }
                form.classList.add('was-validated');
            });

            // Gestion du bouton afficher/masquer mot de passe
            const togglePassword = document.querySelector('.toggle-password');
            const password = document.querySelector('#password');
            
            togglePassword.addEventListener('click', function() {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
        });
    </script>
</body>
</html>
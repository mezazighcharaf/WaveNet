<?php
session_start();
$pageTitle = "Demande de réinitialisation de mot de passe";
$activePage = "login"; // Ou une autre si vous préférez

require_once __DIR__ . '/../includes/header.php'; 
?>

<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="report-form">
                <h2>Mot de passe oublié</h2>
                <p>Entrez votre adresse e-mail. Si un compte est associé, vous recevrez des instructions pour réinitialiser votre mot de passe.</p>
                
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success">
                        <p><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <p><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
                    </div>
                <?php endif; ?>

                <form action="/WaveNet/controller/UserController.php?action=handleForgotPasswordRequest" method="post">
                    <div class="form-group mb-3">
                        <label for="email">Adresse e-mail</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Envoyer les instructions</button>
                </form>
                <div class="mt-3 text-center">
                    <p><a href="login.php">Retour à la connexion</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php'; 
?> 
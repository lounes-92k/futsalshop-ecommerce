<?php 
session_start();
include '../templates/header.php'; 
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h3 class="mb-0">
                    <i class="fas fa-sign-in-alt"></i> Connexion
                </h3>
            </div>
            <div class="card-body">
                
                <form action="../../controllers/UserController.php?action=login" method="POST">
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required placeholder="votre@email.com">
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-sign-in-alt"></i> Se connecter
                    </button>
                </form>
                
                <hr>
                
                <div class="text-center">
                    <p class="mb-2 text-muted small">Comptes de test :</p>
                    <p class="mb-1 small"><strong>Admin :</strong> admin@futsal.com / admin123</p>
                    <p class="mb-0 small"><strong>Client :</strong> jean.dupont@email.com / test123</p>
                </div>
            </div>
            <div class="card-footer text-center">
                Pas encore de compte ? <a href="register.php">Inscrivez-vous ici</a>
            </div>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>
<?php 
session_start();
include '../templates/header.php'; 
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h3 class="mb-0">
                    <i class="fas fa-user-plus"></i> Créer un compte
                </h3>
            </div>
            <div class="card-body">
                
                <form action="../../controllers/UserController.php?action=register" method="POST">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nom" class="form-label">Nom *</label>
                            <input type="text" class="form-control" name="nom" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="prenom" class="form-label">Prénom *</label>
                            <input type="text" class="form-control" name="prenom" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Mot de passe *</label>
                            <input type="password" class="form-control" name="password" required minlength="6">
                            <small class="text-muted">Minimum 6 caractères</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirmer le mot de passe *</label>
                            <input type="password" name="password_confirm" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="adresse" class="form-label">Adresse (optionnel)</label>
                        <textarea class="form-control" name="adresse" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="telephone" class="form-label">Téléphone (optionnel)</label>
                        <input type="tel" class="form-control" name="telephone" placeholder="06 12 34 56 78">
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-check"></i> S'inscrire
                    </button>
                </form>
            </div>
            <div class="card-footer text-center">
                Déjà inscrit ? <a href="login.php">Connectez-vous ici</a>
            </div>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>
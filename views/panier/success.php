<?php
session_start();
include '../templates/header.php';

$commande_id = isset($_SESSION['commande_id']) ? $_SESSION['commande_id'] : null;
?>

<div class="container">
    <div class="text-center py-5">
        <div class="mb-4">
            <i class="fas fa-check-circle text-success" style="font-size: 100px;"></i>
        </div>
        
        <h1 class="display-4 mb-3">Commande confirmée !</h1>
        
        <?php if($commande_id): ?>
            <div class="alert alert-success d-inline-block">
                <h4>Commande #<?= $commande_id ?></h4>
            </div>
        <?php endif; ?>
        
        <p class="lead mb-4">
            Merci pour votre commande ! <br>
            Un email de confirmation vous a été envoyé.
        </p>
        
        <div class="row justify-content-center mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="fas fa-info-circle text-primary"></i> Prochaines étapes
                        </h5>
                        <ul class="list-unstyled text-start">
                            <li class="mb-2">
                                <i class="fas fa-check text-success"></i> 
                                Votre commande a été enregistrée
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-clock text-warning"></i> 
                                Elle sera traitée dans les 24 heures
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-truck text-info"></i> 
                                Livraison prévue sous 3-5 jours ouvrés
                            </li>
                            <li>
                                <i class="fas fa-envelope text-primary"></i> 
                                Vous recevrez un email de suivi
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="../users/commandes.php" class="btn btn-success btn-lg">
                <i class="fas fa-box"></i> Voir mes commandes
            </a>
            <a href="../produits/index.php" class="btn btn-outline-success btn-lg">
                <i class="fas fa-store"></i> Continuer mes achats
            </a>
        </div>
        
        <div class="mt-5 pt-4 border-top">
            <h5 class="mb-3">Besoin d'aide ?</h5>
            <p class="text-muted">
                <i class="fas fa-phone"></i> +33 1 23 45 67 89<br>
                <i class="fas fa-envelope"></i> contact@futsalshop.fr
            </p>
        </div>
    </div>
</div>

<?php 
// Nettoyer la session
unset($_SESSION['commande_id']);
include '../templates/footer.php'; 
?>
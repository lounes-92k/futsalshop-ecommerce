<?php
session_start();

include_once '../../config/database.php';
include_once '../../models/Produit.php';

$database = new Database();
$db = $database->getConnection();
$produitModel = new Produit($db);

// Récupérer les détails des produits dans le panier
$panierItems = array();
$total = 0;

if(isset($_SESSION['panier']) && !empty($_SESSION['panier'])) {
    foreach($_SESSION['panier'] as $produit_id => $quantite) {
        $produit = $produitModel->lireUn($produit_id);
        if($produit) {
            $produit['quantite'] = $quantite;
            $produit['sous_total'] = $produit['prix'] * $quantite;
            $total += $produit['sous_total'];
            $panierItems[] = $produit;
        }
    }
}

include '../templates/header.php';
?>

<h2 class="mb-4">
    <i class="fas fa-shopping-cart"></i> Mon Panier
</h2>

<?php if(empty($panierItems)): ?>
    <!-- Panier vide -->
    <div class="text-center py-5">
        <i class="fas fa-shopping-cart fa-5x text-muted mb-4"></i>
        <h3>Votre panier est vide</h3>
        <p class="text-muted">Découvrez nos produits et ajoutez-les à votre panier</p>
        <a href="../produits/index.php" class="btn btn-success btn-lg mt-3">
            <i class="fas fa-store"></i> Voir les produits
        </a>
    </div>

<?php else: ?>
    <!-- Panier avec produits -->
    <div class="row">
        <!-- Liste des produits -->
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Articles dans votre panier</h5>
                </div>
                <div class="card-body p-0">
                    <?php foreach($panierItems as $item): ?>
                        <div class="d-flex align-items-center border-bottom p-3">
                            <!-- Image -->
                            <div class="me-3">
                                <?php 
                                    $img = !empty($item['image']) ? "../../public/images/produits/".$item['image'] : "https://via.placeholder.com/80";
                                ?>
                                <img src="<?= $img ?>" alt="<?= $item['nom'] ?>" 
                                     style="width: 80px; height: 80px; object-fit: contain;">
                            </div>
                            
                            <!-- Détails -->
                            <div class="flex-grow-1">
                                <h5 class="mb-1"><?= htmlspecialchars($item['nom']) ?></h5>
                                <p class="text-muted small mb-1">
                                    <i class="fas fa-tag"></i> <?= htmlspecialchars($item['marque']) ?> • 
                                    Taille: <?= htmlspecialchars($item['taille']) ?>
                                </p>
                                <p class="mb-0 text-success fw-bold">
                                    <?= number_format($item['prix'], 2) ?> €
                                </p>
                            </div>
                            
                            <!-- Quantité -->
                            <div class="mx-3">
                                <form action="../../controllers/PanierController.php?action=update" method="POST" class="d-inline">
                                    <input type="hidden" name="produit_id" value="<?= $item['id'] ?>">
                                    <div class="input-group" style="width: 120px;">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" 
                                                onclick="this.nextElementSibling.stepDown(); this.form.submit();">-</button>
                                        <input type="number" name="quantite" class="form-control form-control-sm text-center" 
                                               value="<?= $item['quantite'] ?>" min="0" max="<?= $item['stock'] ?>"
                                               onchange="this.form.submit()">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" 
                                                onclick="this.previousElementSibling.stepUp(); this.form.submit();">+</button>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- Sous-total -->
                            <div class="text-end" style="min-width: 100px;">
                                <p class="mb-0 fw-bold"><?= number_format($item['sous_total'], 2) ?> €</p>
                            </div>
                            
                            <!-- Supprimer -->
                            <div class="ms-3">
                                <a href="../../controllers/PanierController.php?action=remove&id=<?= $item['id'] ?>" 
                                   class="btn btn-outline-danger btn-sm"
                                   onclick="return confirm('Retirer cet article du panier ?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Boutons d'action -->
            <div class="d-flex gap-2">
                <a href="../produits/index.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Continuer mes achats
                </a>
                <a href="../../controllers/PanierController.php?action=clear" 
                   class="btn btn-outline-danger"
                   onclick="return confirm('Vider tout le panier ?')">
                    <i class="fas fa-trash"></i> Vider le panier
                </a>
            </div>
        </div>
        
        <!-- Récapitulatif -->
        <div class="col-md-4">
            <div class="card shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Récapitulatif</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Sous-total</span>
                        <span><?= number_format($total, 2) ?> €</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Livraison</span>
                        <span class="text-success fw-bold">Gratuite</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Total</strong>
                        <strong class="text-success fs-4"><?= number_format($total, 2) ?> €</strong>
                    </div>
                    
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="checkout.php" class="btn btn-success w-100 btn-lg">
                            <i class="fas fa-lock"></i> Valider la commande
                        </a>
                    <?php else: ?>
                        <a href="../users/login.php" class="btn btn-success w-100 btn-lg">
                            <i class="fas fa-sign-in-alt"></i> Se connecter pour commander
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include '../templates/footer.php'; ?>
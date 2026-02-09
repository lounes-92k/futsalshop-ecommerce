<?php 
require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../../models/user.php';
$userModel = new User();
$user = $userModel->getUserById($_SESSION['user_id']);
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-check-circle"></i> Validation de la commande</h1>
    </div>
    
    <div class="checkout-container">
        <div class="checkout-form">
            <h2>Informations de livraison</h2>
            
            <form action="index.php?controller=panier&action=checkout" method="POST">
                <div class="form-group">
                    <label for="adresse">
                        <i class="fas fa-map-marker-alt"></i> Adresse de livraison *
                    </label>
                    <textarea id="adresse" 
                              name="adresse" 
                              rows="4" 
                              required><?php echo htmlspecialchars($user['adresse'] ?? ''); ?></textarea>
                    <small>Veuillez vérifier votre adresse de livraison</small>
                </div>
                
                <div class="payment-info">
                    <h3><i class="fas fa-credit-card"></i> Paiement</h3>
                    <p>Le paiement sera effectué à la livraison (paiement sécurisé)</p>
                </div>
                
                <button type="submit" class="btn btn-primary btn-large">
                    <i class="fas fa-check"></i> Confirmer la commande
                </button>
                
                <a href="index.php?controller=panier&action=index" class="btn btn-secondary btn-large">
                    <i class="fas fa-arrow-left"></i> Retour au panier
                </a>
            </form>
        </div>
        
        <div class="checkout-summary">
            <h2>Votre commande</h2>
            
            <?php foreach ($panierItems as $item): ?>
                <div class="summary-item">
                    <img src="public/images/produits/<?php echo htmlspecialchars($item['image']); ?>" 
                         alt="<?php echo htmlspecialchars($item['nom']); ?>">
                    <div class="item-info">
                        <h4><?php echo htmlspecialchars($item['nom']); ?></h4>
                        <p>Quantité : <?php echo $item['quantite']; ?></p>
                    </div>
                    <div class="item-price">
                        <?php echo number_format($item['sous_total'], 2, ',', ' '); ?> €
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div class="summary-divider"></div>
            
            <div class="summary-line">
                <span>Sous-total</span>
                <span><?php echo number_format($total, 2, ',', ' '); ?> €</span>
            </div>
            
            <div class="summary-line">
                <span>Livraison</span>
                <span>Gratuite</span>
            </div>
            
            <div class="summary-divider"></div>
            
            <div class="summary-line total">
                <span>Total à payer</span>
                <span><?php echo number_format($total, 2, ',', ' '); ?> €</span>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
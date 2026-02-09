<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-shopping-cart"></i> Mon Panier</h1>
    </div>
    
    <?php if (empty($panierItems)): ?>
        <div class="empty-cart">
            <i class="fas fa-shopping-cart"></i>
            <h2>Votre panier est vide</h2>
            <p>Découvrez nos produits et ajoutez-les à votre panier</p>
            <a href="index.php?controller=produit&action=index" class="btn btn-primary">
                Voir les produits
            </a>
        </div>
    <?php else: ?>
        <div class="cart-container">
            <div class="cart-items">
                <?php foreach ($panierItems as $item): ?>
                    <div class="cart-item">
                        <div class="item-image">
                            <img src="public/images/produits/<?php echo htmlspecialchars($item['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['nom']); ?>">
                        </div>
                        
                        <div class="item-details">
                            <h3><?php echo htmlspecialchars($item['nom']); ?></h3>
                            <p class="item-info">
                                <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($item['marque']); ?></span>
                                <span><i class="fas fa-ruler"></i> <?php echo htmlspecialchars($item['taille']); ?></span>
                            </p>
                            <p class="item-price">
                                <?php echo number_format($item['prix'], 2, ',', ' '); ?> €
                            </p>
                        </div>
                        
                        <div class="item-actions">
                            <form action="index.php?controller=panier&action=update" method="POST" class="quantity-form">
                                <input type="hidden" name="produit_id" value="<?php echo $item['id']; ?>">
                                <label>Quantité :</label>
                                <input type="number" 
                                       name="quantite" 
                                       value="<?php echo $item['quantite']; ?>" 
                                       min="1" 
                                       max="<?php echo $item['stock']; ?>"
                                       onchange="this.form.submit()">
                            </form>
                            
                            <p class="item-subtotal">
                                Sous-total : <strong><?php echo number_format($item['sous_total'], 2, ',', ' '); ?> €</strong>
                            </p>
                            
                            <form action="index.php?controller=panier&action=remove" method="POST">
                                <input type="hidden" name="produit_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="btn-remove" onclick="return confirm('Voulez-vous vraiment supprimer cet article ?')">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="cart-summary">
                <h2>Récapitulatif</h2>
                
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
                    <span>Total</span>
                    <span><?php echo number_format($total, 2, ',', ' '); ?> €</span>
                </div>
                
                <a href="index.php?controller=panier&action=checkout" class="btn btn-primary btn-block">
                    <i class="fas fa-check"></i> Valider la commande
                </a>
                
                <a href="index.php?controller=produit&action=index" class="btn btn-secondary btn-block">
                    <i class="fas fa-arrow-left"></i> Continuer mes achats
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
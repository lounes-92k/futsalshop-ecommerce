<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-receipt"></i> Détails de la commande #<?php echo $commande['id']; ?></h1>
    </div>
    
    <div class="commande-details-container">
        <div class="commande-info-card">
            <h2>Informations de la commande</h2>
            
            <div class="info-grid">
                <div class="info-item">
                    <span class="label"><i class="fas fa-hashtag"></i> Numéro :</span>
                    <span class="value">#<?php echo $commande['id']; ?></span>
                </div>
                
                <div class="info-item">
                    <span class="label"><i class="fas fa-calendar"></i> Date :</span>
                    <span class="value"><?php echo date('d/m/Y à H:i', strtotime($commande['date_commande'])); ?></span>
                </div>
                
                <div class="info-item">
                    <span class="label"><i class="fas fa-info-circle"></i> Statut :</span>
                    <span class="value">
                        <?php
                        $statutClass = '';
                        $statutText = '';
                        $statutIcon = '';
                        switch($commande['statut']) {
                            case 'en_attente':
                                $statutClass = 'statut-warning';
                                $statutText = 'En attente de confirmation';
                                $statutIcon = 'fa-clock';
                                break;
                            case 'confirmee':
                                $statutClass = 'statut-info';
                                $statutText = 'Confirmée - En préparation';
                                $statutIcon = 'fa-check-circle';
                                break;
                            case 'expediee':
                                $statutClass = 'statut-primary';
                                $statutText = 'Expédiée - En cours de livraison';
                                $statutIcon = 'fa-truck';
                                break;
                            case 'livree':
                                $statutClass = 'statut-success';
                                $statutText = 'Livrée avec succès';
                                $statutIcon = 'fa-check-double';
                                break;
                            case 'annulee':
                                $statutClass = 'statut-danger';
                                $statutText = 'Annulée';
                                $statutIcon = 'fa-times-circle';
                                break;
                        }
                        ?>
                        <span class="statut-badge <?php echo $statutClass; ?>">
                            <i class="fas <?php echo $statutIcon; ?>"></i>
                            <?php echo $statutText; ?>
                        </span>
                    </span>
                </div>
                
                <div class="info-item">
                    <span class="label"><i class="fas fa-euro-sign"></i> Total :</span>
                    <span class="value total-price"><?php echo number_format($commande['total'], 2, ',', ' '); ?> €</span>
                </div>
            </div>
            
            <div class="delivery-address">
                <h3><i class="fas fa-map-marker-alt"></i> Adresse de livraison</h3>
                <p><?php echo nl2br(htmlspecialchars($commande['adresse_livraison'])); ?></p>
            </div>
        </div>
        
        <div class="commande-products">
            <h2>Produits commandés</h2>
            
            <div class="products-list">
                <?php foreach ($details as $item): ?>
                    <div class="product-item">
                        <div class="product-img">
                            <img src="public/images/produits/<?php echo htmlspecialchars($item['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['produit_nom']); ?>">
                        </div>
                        
                        <div class="product-details">
                            <h3><?php echo htmlspecialchars($item['produit_nom']); ?></h3>
                            <p class="product-meta">
                                <span><i class="fas fa-boxes"></i> Quantité : <?php echo $item['quantite']; ?></span>
                                <span><i class="fas fa-euro-sign"></i> Prix unitaire : <?php echo number_format($item['prix_unitaire'], 2, ',', ' '); ?> €</span>
                            </p>
                        </div>
                        
                        <div class="product-total">
                            <?php echo number_format($item['prix_unitaire'] * $item['quantite'], 2, ',', ' '); ?> €
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="order-summary">
                <div class="summary-line">
                    <span>Sous-total</span>
                    <span><?php echo number_format($commande['total'], 2, ',', ' '); ?> €</span>
                </div>
                <div class="summary-line">
                    <span>Livraison</span>
                    <span class="free">Gratuite</span>
                </div>
                <div class="summary-divider"></div>
                <div class="summary-line total">
                    <span>Total</span>
                    <span><?php echo number_format($commande['total'], 2, ',', ' '); ?> €</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="actions-section">
        <a href="index.php?controller=user&action=commandes" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour à mes commandes
        </a>
        
        <?php if ($commande['statut'] === 'livree'): ?>
            <button onclick="alert('Merci pour votre avis ! Cette fonctionnalité sera bientôt disponible.')" class="btn btn-primary">
                <i class="fas fa-star"></i> Laisser un avis
            </button>
        <?php endif; ?>
    </div>
</div>

<style>
.commande-details-container {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 30px;
    margin-bottom: 30px;
}

.commande-info-card,
.commande-products {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.commande-info-card h2,
.commande-products h2 {
    margin-bottom: 25px;
    color: var(--dark-color);
    border-bottom: 2px solid var(--light-color);
    padding-bottom: 15px;
}

.info-grid {
    display: flex;
    flex-direction: column;
    gap: 20px;
    margin-bottom: 30px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 10px;
}

.info-item .label {
    font-weight: 600;
    color: #666;
}

.info-item .value {
    font-weight: bold;
    color: var(--dark-color);
}

.total-price {
    font-size: 24px;
    color: var(--primary-color);
}

.statut-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 25px;
    font-weight: 600;
    font-size: 14px;
}

.statut-warning {
    background: #fff3cd;
    color: #856404;
}

.statut-info {
    background: #d1ecf1;
    color: #0c5460;
}

.statut-primary {
    background: #cfe2ff;
    color: #084298;
}

.statut-success {
    background: #d4edda;
    color: #155724;
}

.statut-danger {
    background: #f8d7da;
    color: #721c24;
}

.delivery-address {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    margin-top: 20px;
}

.delivery-address h3 {
    margin-bottom: 10px;
    color: var(--dark-color);
    font-size: 16px;
}

.products-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
    margin-bottom: 30px;
}

.product-item {
    display: grid;
    grid-template-columns: 100px 1fr auto;
    gap: 20px;
    padding: 20px;
    border: 2px solid var(--light-color);
    border-radius: 10px;
    transition: all 0.3s;
}

.product-item:hover {
    border-color: var(--primary-color);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.product-img img {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 10px;
}

.product-details h3 {
    margin-bottom: 10px;
    color: var(--dark-color);
}

.product-meta {
    display: flex;
    flex-direction: column;
    gap: 8px;
    color: #666;
    font-size: 14px;
}

.product-total {
    font-size: 20px;
    font-weight: bold;
    color: var(--primary-color);
    display: flex;
    align-items: center;
}

.order-summary {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 10px;
}

.summary-line {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    font-size: 16px;
}

.summary-line.total {
    font-size: 24px;
    font-weight: bold;
    color: var(--primary-color);
}

.summary-divider {
    border-top: 2px solid var(--border-color);
    margin: 15px 0;
}

.free {
    color: var(--success-color);
    font-weight: 600;
}

.actions-section {
    display: flex;
    gap: 15px;
    justify-content: center;
}

@media (max-width: 968px) {
    .commande-details-container {
        grid-template-columns: 1fr;
    }
    
    .product-item {
        grid-template-columns: 80px 1fr;
    }
    
    .product-total {
        grid-column: 2;
        justify-content: flex-end;
    }
}
</style>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-box"></i> Mes Commandes</h1>
    </div>
    
    <?php if (empty($commandes)): ?>
        <div class="empty-state">
            <i class="fas fa-box-open"></i>
            <h2>Aucune commande</h2>
            <p>Vous n'avez pas encore passé de commande</p>
            <a href="index.php?controller=produit&action=index" class="btn btn-primary">
                Découvrir nos produits
            </a>
        </div>
    <?php else: ?>
        <div class="commandes-list">
            <?php foreach ($commandes as $commande): ?>
                <div class="commande-card">
                    <div class="commande-header">
                        <div>
                            <h3>Commande #<?php echo $commande['id']; ?></h3>
                            <p class="commande-date">
                                <i class="fas fa-calendar"></i>
                                <?php echo date('d/m/Y à H:i', strtotime($commande['date_commande'])); ?>
                            </p>
                        </div>
                        <div>
                            <?php
                            $statutClass = '';
                            $statutText = '';
                            switch($commande['statut']) {
                                case 'en_attente':
                                    $statutClass = 'statut-warning';
                                    $statutText = 'En attente';
                                    break;
                                case 'confirmee':
                                    $statutClass = 'statut-info';
                                    $statutText = 'Confirmée';
                                    break;
                                case 'expediee':
                                    $statutClass = 'statut-primary';
                                    $statutText = 'Expédiée';
                                    break;
                                case 'livree':
                                    $statutClass = 'statut-success';
                                    $statutText = 'Livrée';
                                    break;
                                case 'annulee':
                                    $statutClass = 'statut-danger';
                                    $statutText = 'Annulée';
                                    break;
                            }
                            ?>
                            <span class="statut-badge <?php echo $statutClass; ?>">
                                <?php echo $statutText; ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="commande-body">
                        <div class="commande-info">
                            <p><strong>Total :</strong> <?php echo number_format($commande['total'], 2, ',', ' '); ?> €</p>
                            <p><strong>Adresse de livraison :</strong><br>
                               <?php echo nl2br(htmlspecialchars($commande['adresse_livraison'])); ?>
                            </p>
                        </div>
                        
                        <div class="commande-actions">
                            <a href="index.php?controller=user&action=commandeDetails&id=<?php echo $commande['id']; ?>" 
                               class="btn btn-primary">
                                <i class="fas fa-eye"></i> Voir les détails
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 15px;
}

.empty-state i {
    font-size: 80px;
    color: var(--border-color);
    margin-bottom: 20px;
}

.commandes-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.commande-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.commande-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid var(--light-color);
}

.commande-header h3 {
    color: var(--dark-color);
    margin-bottom: 5px;
}

.commande-date {
    color: #666;
    font-size: 14px;
}

.statut-badge {
    display: inline-block;
    padding: 8px 20px;
    border-radius: 20px;
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

.commande-body {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}

.commande-info p {
    margin-bottom: 10px;
}
</style>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
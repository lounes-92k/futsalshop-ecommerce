<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-box"></i> Gestion des Produits</h1>
        <a href="index.php?controller=admin&action=addProduit" class="btn btn-primary">
            <i class="fas fa-plus"></i> Ajouter un produit
        </a>
    </div>
    
    <div class="admin-panel">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Nom</th>
                        <th>Catégorie</th>
                        <th>Prix</th>
                        <th>Stock</th>
                        <th>Marque</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produits as $produit): ?>
                        <tr>
                            <td><?php echo $produit['id']; ?></td>
                            <td>
                                <img src="public/images/produits/<?php echo htmlspecialchars($produit['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($produit['nom']); ?>" 
                                     class="table-image">
                            </td>
                            <td><?php echo htmlspecialchars($produit['nom']); ?></td>
                            <td><?php echo htmlspecialchars($produit['categorie_nom']); ?></td>
                            <td><?php echo number_format($produit['prix'], 2, ',', ' '); ?> €</td>
                            <td>
                                <span class="<?php echo $produit['stock'] < 5 ? 'text-danger' : 'text-success'; ?>">
                                    <?php echo $produit['stock']; ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($produit['marque']); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="index.php?controller=produit&action=show&id=<?php echo $produit['id']; ?>" 
                                       class="btn-sm btn-info" 
                                       title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="index.php?controller=admin&action=editProduit&id=<?php echo $produit['id']; ?>" 
                                       class="btn-sm btn-primary" 
                                       title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="index.php?controller=admin&action=deleteProduit&id=<?php echo $produit['id']; ?>" 
                                       class="btn-sm btn-danger" 
                                       title="Supprimer"
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.admin-panel {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.table-image {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.btn-info {
    background: #17a2b8;
    color: white;
}

.btn-info:hover {
    background: #138496;
}
</style>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
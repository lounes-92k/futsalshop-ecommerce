<?php
session_start();

// 1. Inclusion des fichiers nécessaires
include_once '../../config/database.php';
include_once '../../models/Produit.php';

// 2. Connexion à la Base de Données
$database = new Database();
$db = $database->getConnection();

// 3. Préparation du modèle
$produit = new Produit($db);

// 4. Filtre par catégorie
$categorie_id = isset($_GET['categorie_id']) ? $_GET['categorie_id'] : null;

if($categorie_id){
    $stmt = $produit->lireParCategorie($categorie_id);
    $titre_page = "Catégorie sélectionnée";
} else {
    $stmt = $produit->lireTout();
    $titre_page = "Tous les produits";
}

include '../templates/header.php';
?>

<div class="row">
    <!-- Sidebar Catégories -->
    <div class="col-md-3 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-filter"></i> Catégories</h5>
            </div>
            <div class="list-group list-group-flush">
                <a href="index.php" class="list-group-item list-group-item-action <?= !$categorie_id ? 'active' : '' ?>">
                    <i class="fas fa-th"></i> Tous les produits
                </a>
                <a href="index.php?categorie_id=1" class="list-group-item list-group-item-action <?= $categorie_id == 1 ? 'active' : '' ?>">
                    <i class="fas fa-running"></i> Chaussures
                </a>
                <a href="index.php?categorie_id=2" class="list-group-item list-group-item-action <?= $categorie_id == 2 ? 'active' : '' ?>">
                    <i class="fas fa-futbol"></i> Ballons
                </a>
                <a href="index.php?categorie_id=3" class="list-group-item list-group-item-action <?= $categorie_id == 3 ? 'active' : '' ?>">
                    <i class="fas fa-tshirt"></i> Maillots
                </a>
                <a href="index.php?categorie_id=4" class="list-group-item list-group-item-action <?= $categorie_id == 4 ? 'active' : '' ?>">
                    <i class="fas fa-shield-alt"></i> Accessoires
                </a>
            </div>
        </div>
    </div>
    
    <!-- Produits -->
    <div class="col-md-9">
        <h2 class="mb-4 text-success">
            <i class="fas fa-store"></i> <?= $titre_page ?>
        </h2>
        
        <div class="row">
            <?php
            if($stmt->rowCount() > 0){
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                    extract($row);
                    $img_src = !empty($image) ? "../../public/images/produits/".$image : "https://via.placeholder.com/300x400?text=Pas+d'image";
                    ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm product-card">
                            <div class="position-relative">
                                <img src="<?= $img_src ?>" class="card-img-top p-3" alt="<?= $nom ?>" 
                                     style="height: 250px; object-fit: contain;">
                                <?php if($stock < 5 && $stock > 0): ?>
                                    <span class="position-absolute top-0 end-0 badge bg-warning text-dark m-2">
                                        Stock limité
                                    </span>
                                <?php elseif($stock == 0): ?>
                                    <span class="position-absolute top-0 end-0 badge bg-danger m-2">
                                        Rupture
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-body">
                                <span class="badge bg-success mb-2">
                                    <?= $categorie_nom ?>
                                </span>
                                <h5 class="card-title"><?= $nom ?></h5>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-tag"></i> <?= $marque ?>
                                </p>
                                <p class="card-text text-muted small">
                                    <?= substr($description, 0, 60) ?>...
                                </p>
                                <h4 class="text-success fw-bold">
                                    <?= number_format($prix, 2) ?> €
                                </h4>
                            </div>
                            
                            <div class="card-footer bg-white border-top-0 d-grid gap-2">
                                <a href="show.php?id=<?= $id ?>" class="btn btn-outline-success">
                                    <i class="fas fa-eye"></i> Voir détails
                                </a>
                                <?php if($stock > 0): ?>
                                    <button class="btn btn-success">
                                        <i class="fas fa-shopping-cart"></i> Ajouter au panier
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-secondary" disabled>
                                        <i class="fas fa-times"></i> Rupture de stock
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<div class="col-12"><div class="alert alert-warning">
                      <i class="fas fa-info-circle"></i> Aucun produit trouvé dans cette catégorie.
                      </div></div>';
            }
            ?>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>
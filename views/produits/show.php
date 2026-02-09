<?php
session_start();
include_once '../../config/database.php';
include_once '../../models/Produit.php';

// 1. On récupère l'ID depuis l'URL
$id = isset($_GET['id']) ? $_GET['id'] : die('ERREUR : ID manquant.');

// 2. On va chercher les infos
$database = new Database();
$db = $database->getConnection();
$produitModel = new Produit($db);
$produit = $produitModel->lireUn($id);

// Si le produit n'existe pas
if(!$produit){
    die('Produit introuvable.');
}

include '../templates/header.php';
?>

<a href="index.php" class="btn btn-outline-secondary mb-4">
    <i class="fas fa-arrow-left"></i> Retour au catalogue
</a>

<div class="row">
    <!-- Image du produit -->
    <div class="col-md-5">
        <?php 
            $img_src = !empty($produit['image']) ? "../../public/images/produits/".$produit['image'] : "https://via.placeholder.com/400x550?text=Image+Manquante";
        ?>
        <img src="<?= $img_src ?>" class="img-fluid rounded shadow" alt="<?= $produit['nom'] ?>">
    </div>
    
    <!-- Détails du produit -->
    <div class="col-md-7">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/cours/e-commerce/index.php">Accueil</a></li>
                <li class="breadcrumb-item"><a href="index.php">Produits</a></li>
                <li class="breadcrumb-item active"><?= $produit['nom'] ?></li>
            </ol>
        </nav>
        
        <span class="badge bg-success mb-2"><?= $produit['categorie_nom'] ?></span>
        
        <h1 class="display-5 fw-bold"><?= $produit['nom'] ?></h1>
        
        <div class="mb-3">
            <span class="badge bg-secondary me-2">
                <i class="fas fa-tag"></i> <?= $produit['marque'] ?>
            </span>
            <span class="badge bg-secondary">
                <i class="fas fa-ruler"></i> Taille: <?= $produit['taille'] ?>
            </span>
        </div>
        
        <h3 class="text-success my-3">
            <?= number_format($produit['prix'], 2) ?> €
        </h3>
        
        <div class="card bg-light mb-4">
            <div class="card-body">
                <h5>Description</h5>
                <p class="lead mb-0"><?= nl2br($produit['description']) ?></p>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-body">
                <h5>Informations produit</h5>
                <ul class="list-unstyled mb-0">
                    <li><i class="fas fa-boxes text-success"></i> <strong>Stock :</strong> 
                        <?php if($produit['stock'] > 0): ?>
                            <span class="text-success"><?= $produit['stock'] ?> disponible(s)</span>
                        <?php else: ?>
                            <span class="text-danger">Rupture de stock</span>
                        <?php endif; ?>
                    </li>
                    <li><i class="fas fa-tag text-success"></i> <strong>Marque :</strong> <?= $produit['marque'] ?></li>
                    <li><i class="fas fa-ruler text-success"></i> <strong>Taille :</strong> <?= $produit['taille'] ?></li>
                </ul>
            </div>
        </div>
        
        <hr>
        
        <!-- Ajout au panier -->
        <div class="d-flex align-items-center gap-3">
            <?php if($produit['stock'] > 0): ?>
                <div class="input-group" style="width: 150px;">
                    <button class="btn btn-outline-secondary" type="button">-</button>
                    <input type="number" class="form-control text-center" value="1" min="1" max="<?= $produit['stock'] ?>">
                    <button class="btn btn-outline-secondary" type="button">+</button>
                </div>
                
                <button type="button" class="btn btn-success btn-lg flex-grow-1">
                    <i class="fas fa-shopping-cart"></i> Ajouter au panier
                </button>
            <?php else: ?>
                <button class="btn btn-secondary btn-lg" disabled>
                    <i class="fas fa-times"></i> Rupture de stock
                </button>
            <?php endif; ?>
        </div>
        
        <!-- Garanties -->
        <div class="row mt-4">
            <div class="col-4 text-center">
                <i class="fas fa-shipping-fast fa-2x text-success mb-2"></i>
                <p class="small mb-0">Livraison rapide</p>
            </div>
            <div class="col-4 text-center">
                <i class="fas fa-undo fa-2x text-success mb-2"></i>
                <p class="small mb-0">Retour 30 jours</p>
            </div>
            <div class="col-4 text-center">
                <i class="fas fa-shield-alt fa-2x text-success mb-2"></i>
                <p class="small mb-0">Paiement sécurisé</p>
            </div>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>
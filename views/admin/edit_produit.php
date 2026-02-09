<?php
session_start();

// Vérification Admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Accès interdit.");
}

include_once '../../config/database.php';
include_once '../../models/Produit.php';

$database = new Database();
$db = $database->getConnection();
$produitModel = new Produit($db);

// On récupère l'ID du produit à modifier
$id = isset($_GET['id']) ? $_GET['id'] : die('ID manquant');
$produit = $produitModel->lireUn($id);

include '../templates/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow border-primary">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">
                    <i class="fas fa-edit"></i> Modifier : <?= $produit['nom'] ?>
                </h3>
            </div>
            <div class="card-body">
                
                <!-- Image actuelle -->
                <div class="text-center mb-4">
                    <p class="text-muted mb-2">Image actuelle :</p>
                    <?php 
                        $img = !empty($produit['image']) ? "../../public/images/produits/".$produit['image'] : "https://via.placeholder.com/200"; 
                    ?>
                    <img src="<?= $img ?>" alt="<?= $produit['nom'] ?>" 
                         style="max-width: 200px;" class="img-thumbnail">
                </div>
                
                <form action="../../controllers/AdminController.php?action=edit" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= $produit['id'] ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Nom du produit *</label>
                        <input type="text" class="form-control" name="nom" 
                               value="<?= htmlspecialchars($produit['nom']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Marque *</label>
                        <input type="text" class="form-control" name="marque" 
                               value="<?= htmlspecialchars($produit['marque']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Catégorie *</label>
                        <select class="form-select" name="categorie_id" required>
                            <option value="1" <?= $produit['categorie_nom'] == 'Chaussures' ? 'selected' : '' ?>>Chaussures</option>
                            <option value="2" <?= $produit['categorie_nom'] == 'Ballons' ? 'selected' : '' ?>>Ballons</option>
                            <option value="3" <?= $produit['categorie_nom'] == 'Maillots' ? 'selected' : '' ?>>Maillots</option>
                            <option value="4" <?= $produit['categorie_nom'] == 'Accessoires' ? 'selected' : '' ?>>Accessoires</option>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Prix (€) *</label>
                            <input type="number" step="0.01" class="form-control" name="prix" 
                                   value="<?= $produit['prix'] ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Stock *</label>
                            <input type="number" class="form-control" name="stock" 
                                   value="<?= $produit['stock'] ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Taille *</label>
                            <input type="text" class="form-control" name="taille" 
                                   value="<?= htmlspecialchars($produit['taille']) ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($produit['description']) ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Changer l'image (Laisser vide pour garder l'actuelle)</label>
                        <input type="file" class="form-control" name="image" accept="image/*">
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save"></i> Enregistrer les modifications
                    </button>
                </form>
            </div>
            <div class="card-footer text-center">
                <a href="dashboard.php" class="text-muted">
                    <i class="fas fa-arrow-left"></i> Annuler et retour au dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>
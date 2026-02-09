<?php
session_start();

// Vérification Admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Accès interdit.");
}

include '../templates/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow border-success">
            <div class="card-header bg-success text-white">
                <h3 class="mb-0">
                    <i class="fas fa-plus-circle"></i> Ajouter un produit
                </h3>
            </div>
            <div class="card-body">
                <form action="../../controllers/AdminController.php?action=add" method="POST" enctype="multipart/form-data">
                    
                    <div class="mb-3">
                        <label class="form-label">Nom du produit *</label>
                        <input type="text" class="form-control" name="nom" required 
                               placeholder="Ex: Nike Tiempo Legend 9">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Marque *</label>
                        <input type="text" class="form-control" name="marque" required 
                               placeholder="Ex: Nike, Adidas, Puma">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Catégorie *</label>
                        <select class="form-select" name="categorie_id" required>
                            <option value="">-- Choisir une catégorie --</option>
                            <option value="1">Chaussures</option>
                            <option value="2">Ballons</option>
                            <option value="3">Maillots</option>
                            <option value="4">Accessoires</option>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Prix (€) *</label>
                            <input type="number" step="0.01" class="form-control" name="prix" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Stock *</label>
                            <input type="number" class="form-control" name="stock" value="1" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Taille *</label>
                            <input type="text" class="form-control" name="taille" required 
                                   placeholder="Ex: 42, M, Taille 4">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3" 
                                  placeholder="Description détaillée du produit..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Image du produit</label>
                        <input type="file" class="form-control" name="image" accept="image/*">
                        <small class="text-muted">Formats acceptés : JPG, PNG, GIF</small>
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-save"></i> Ajouter le produit
                    </button>
                </form>
            </div>
            <div class="card-footer text-center">
                <a href="dashboard.php" class="text-muted">
                    <i class="fas fa-arrow-left"></i> Retour au dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>
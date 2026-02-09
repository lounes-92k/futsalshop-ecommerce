<?php
session_start();

// Vérification Admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: /cours/e-commerce/index.php");
    exit();
}

include_once '../../config/database.php';
include_once '../../models/Produit.php';

$database = new Database();
$db = $database->getConnection();
$produit = new Produit($db);
$stmt = $produit->lireTout();

include '../templates/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-tachometer-alt"></i> Dashboard Admin</h1>
    <a href="add_produit.php" class="btn btn-success">
        <i class="fas fa-plus"></i> Ajouter un produit
    </a>
</div>

<div class="card shadow">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="fas fa-box"></i> Gestion des Produits</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Image</th>
                        <th>Nom</th>
                        <th>Catégorie</th>
                        <th>Marque</th>
                        <th>Taille</th>
                        <th>Prix</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td>
                            <?php 
                                $img = !empty($row['image']) ? "../../public/images/produits/".$row['image'] : "https://via.placeholder.com/50"; 
                            ?>
                            <img src="<?= $img ?>" alt="img" width="50" height="50" style="object-fit: contain;">
                        </td>
                        <td class="fw-bold"><?= $row['nom'] ?></td>
                        <td><span class="badge bg-success"><?= $row['categorie_nom'] ?></span></td>
                        <td><?= $row['marque'] ?></td>
                        <td><?= $row['taille'] ?></td>
                        <td><?= number_format($row['prix'], 2) ?> €</td>
                        <td>
                            <?php if($row['stock'] < 5): ?>
                                <span class="badge bg-danger"><?= $row['stock'] ?></span>
                            <?php else: ?>
                                <span class="badge bg-success"><?= $row['stock'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="../produits/show.php?id=<?= $row['id'] ?>" 
                                   class="btn btn-sm btn-info" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="edit_produit.php?id=<?= $row['id'] ?>" 
                                   class="btn btn-sm btn-primary" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="../../controllers/AdminController.php?action=delete&id=<?= $row['id'] ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?');"
                                   title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>
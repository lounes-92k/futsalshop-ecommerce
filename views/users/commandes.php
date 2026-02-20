<?php
session_start();

// Vérifier que l'utilisateur est connecté
if(!isset($_SESSION['user_id'])) {
    header("Location: ../users/login.php");
    exit();
}

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Récupérer les commandes de l'utilisateur
$query = "SELECT * FROM commandes WHERE user_id = :user_id ORDER BY date_commande DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../templates/header.php';
?>

<div class="container">
    <h2 class="mb-4">
        <i class="fas fa-box"></i> Mes Commandes
    </h2>
    
    <?php if(empty($commandes)): ?>
        <div class="text-center py-5">
            <i class="fas fa-inbox fa-5x text-muted mb-4"></i>
            <h3>Aucune commande</h3>
            <p class="text-muted">Vous n'avez pas encore passé de commande</p>
            <a href="../produits/index.php" class="btn btn-success mt-3">
                <i class="fas fa-store"></i> Découvrir nos produits
            </a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach($commandes as $commande): ?>
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white d-flex justify-content-between">
                            <span><strong>Commande #<?= $commande['id'] ?></strong></span>
                            <span><?= date('d/m/Y', strtotime($commande['date_commande'])) ?></span>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Montant total :</span>
                                <strong class="text-success"><?= number_format($commande['total'], 2) ?> €</strong>
                            </div>
                            <div class="mb-3">
                                <span class="badge bg-<?= $commande['statut'] == 'confirmee' ? 'success' : 'warning' ?>">
                                    <?= ucfirst($commande['statut']) ?>
                                </span>
                            </div>
                            <p class="small text-muted mb-0">
                                <strong>Livraison :</strong><br>
                                <?= nl2br(htmlspecialchars($commande['adresse_livraison'])) ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../templates/footer.php'; ?>
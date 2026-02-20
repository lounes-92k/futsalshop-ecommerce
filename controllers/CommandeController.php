<?php
session_start();

// Vérifier que l'utilisateur est connecté
if(!isset($_SESSION['user_id'])) {
    header("Location: ../views/users/login.php");
    exit();
}

// Vérifier que le panier n'est pas vide
if(!isset($_SESSION['panier']) || empty($_SESSION['panier'])) {
    header("Location: ../views/panier/index.php");
    exit();
}

include_once '../config/database.php';
include_once '../models/Produit.php';

$database = new Database();
$db = $database->getConnection();
$produitModel = new Produit($db);

// Récupérer les données POST
$adresse = isset($_POST['adresse']) ? $_POST['adresse'] : '';
$mode_paiement = isset($_POST['mode_paiement']) ? $_POST['mode_paiement'] : 'paypal';

// Calculer le total
$total = 0;
foreach($_SESSION['panier'] as $produit_id => $quantite) {
    $produit = $produitModel->lireUn($produit_id);
    if($produit) {
        $total += $produit['prix'] * $quantite;
    }
}

// Ajouter la TVA
$total_ttc = $total * 1.2;

try {
    // Démarrer une transaction
    $db->beginTransaction();
    
    // Insérer la commande
    $query = "INSERT INTO commandes (user_id, total, statut, adresse_livraison, mode_paiement) 
              VALUES (:user_id, :total, 'en_attente', :adresse, :mode_paiement)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->bindParam(':total', $total_ttc);
    $stmt->bindParam(':adresse', $adresse);
    $stmt->bindParam(':mode_paiement', $mode_paiement);
    $stmt->execute();
    
    $commande_id = $db->lastInsertId();
    
    // Insérer les lignes de commande
    $query = "INSERT INTO lignes_commande (commande_id, produit_id, quantite, prix_unitaire) 
              VALUES (:commande_id, :produit_id, :quantite, :prix_unitaire)";
    $stmt = $db->prepare($query);
    
    foreach($_SESSION['panier'] as $produit_id => $quantite) {
        $produit = $produitModel->lireUn($produit_id);
        if($produit) {
            $stmt->bindParam(':commande_id', $commande_id);
            $stmt->bindParam(':produit_id', $produit_id);
            $stmt->bindParam(':quantite', $quantite);
            $stmt->bindParam(':prix_unitaire', $produit['prix']);
            $stmt->execute();
        }
    }
    
    // Valider la transaction
    $db->commit();
    
    // Vider le panier
    $_SESSION['panier'] = array();
    
    // Message de succès
    $_SESSION['success'] = "Commande #$commande_id enregistrée avec succès !";
    $_SESSION['commande_id'] = $commande_id;
    
    // Rediriger vers la page de succès
    header("Location: ../views/panier/success.php");
    exit();
    
} catch(Exception $e) {
    // Annuler la transaction en cas d'erreur
    $db->rollBack();
    $_SESSION['error'] = "Erreur lors de l'enregistrement de la commande.";
    header("Location: ../views/panier/checkout.php");
    exit();
}
?>
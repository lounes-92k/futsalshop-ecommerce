<?php
session_start();

// Ce contrôleur gère le panier en SESSION (sans base de données)

$action = isset($_GET['action']) ? $_GET['action'] : '';

// --- AJOUTER AU PANIER ---
if ($action == 'add' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $produit_id = $_POST['produit_id'];
    $quantite = isset($_POST['quantite']) ? (int)$_POST['quantite'] : 1;
    
    // Initialiser le panier s'il n'existe pas
    if(!isset($_SESSION['panier'])) {
        $_SESSION['panier'] = array();
    }
    
    // Si le produit existe déjà dans le panier, on ajoute la quantité
    if(isset($_SESSION['panier'][$produit_id])) {
        $_SESSION['panier'][$produit_id] += $quantite;
    } else {
        $_SESSION['panier'][$produit_id] = $quantite;
    }
    
    $_SESSION['success'] = "Produit ajouté au panier !";
    header("Location: ../views/panier/index.php");
    exit();
}

// --- METTRE À JOUR LA QUANTITÉ ---
elseif ($action == 'update' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $produit_id = $_POST['produit_id'];
    $quantite = (int)$_POST['quantite'];
    
    if($quantite > 0) {
        $_SESSION['panier'][$produit_id] = $quantite;
    } else {
        unset($_SESSION['panier'][$produit_id]);
    }
    
    header("Location: ../views/panier/index.php");
    exit();
}

// --- SUPPRIMER DU PANIER ---
elseif ($action == 'remove') {
    
    $produit_id = isset($_GET['id']) ? $_GET['id'] : (isset($_POST['produit_id']) ? $_POST['produit_id'] : null);
    
    if($produit_id && isset($_SESSION['panier'][$produit_id])) {
        unset($_SESSION['panier'][$produit_id]);
        $_SESSION['success'] = "Produit retiré du panier.";
    }
    
    header("Location: ../views/panier/index.php");
    exit();
}

// --- VIDER LE PANIER ---
elseif ($action == 'clear') {
    
    $_SESSION['panier'] = array();
    $_SESSION['success'] = "Panier vidé.";
    header("Location: ../views/panier/index.php");
    exit();
}

// Par défaut, rediriger vers le panier
else {
    header("Location: ../views/panier/index.php");
    exit();
}
?>
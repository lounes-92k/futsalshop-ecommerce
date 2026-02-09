<?php
session_start();
include_once '../config/database.php';
include_once '../models/Produit.php';

// VÉRIFICATION DE SÉCURITÉ : Est-ce un admin ?
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error'] = "Accès refusé. Réservé aux administrateurs.";
    header("Location: /cours/e-commerce/index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$produit = new Produit($db);

$action = isset($_GET['action']) ? $_GET['action'] : '';

// --- AJOUTER UN PRODUIT ---
if ($action == 'add' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. Récupération des champs textes
    $produit->nom = $_POST['nom'];
    $produit->description = $_POST['description'];
    $produit->prix = $_POST['prix'];
    $produit->stock = $_POST['stock'];
    $produit->categorie_id = $_POST['categorie_id'];
    $produit->marque = $_POST['marque'];
    $produit->taille = $_POST['taille'];
    
    // 2. Gestion de l'Image
    $image_nom = "";
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
        $target_dir = "../public/images/produits/";
        // On crée un nom unique
        $image_nom = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_nom;
        
        // On déplace le fichier
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
    }
    
    $produit->image = $image_nom;
    
    // 3. Enregistrement en BDD
    if($produit->creer()){
        $_SESSION['success'] = "Produit ajouté avec succès !";
        header("Location: /cours/e-commerce/views/produits/index.php");
    } else {
        $_SESSION['error'] = "Impossible d'ajouter le produit.";
        header("Location: /cours/e-commerce/views/admin/add_produit.php");
    }
}

// --- SUPPRIMER UN PRODUIT ---
elseif ($action == 'delete') {
    $id = isset($_GET['id']) ? $_GET['id'] : die('ERREUR : ID manquant.');
    
    if($produit->supprimer($id)){
        $_SESSION['success'] = "Produit supprimé avec succès.";
    } else {
        $_SESSION['error'] = "Impossible de supprimer le produit.";
    }
    
    header("Location: /cours/e-commerce/views/admin/dashboard.php");
}

// --- MODIFIER UN PRODUIT ---
elseif ($action == 'edit' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $produit->id = $_POST['id'];
    $produit->nom = $_POST['nom'];
    $produit->description = $_POST['description'];
    $produit->prix = $_POST['prix'];
    $produit->stock = $_POST['stock'];
    $produit->categorie_id = $_POST['categorie_id'];
    $produit->marque = $_POST['marque'];
    $produit->taille = $_POST['taille'];
    
    // Gestion de la nouvelle image (si envoyée)
    $image_nom = null;
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
        $target_dir = "../public/images/produits/";
        $image_nom = time() . "_" . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $image_nom);
        $produit->image = $image_nom;
    }
    
    if($produit->update()){
        $_SESSION['success'] = "Produit modifié avec succès !";
        header("Location: /cours/e-commerce/views/admin/dashboard.php");
    } else {
        $_SESSION['error'] = "Erreur lors de la modification.";
        header("Location: /cours/e-commerce/views/admin/edit_produit.php?id=" . $produit->id);
    }
}
?>
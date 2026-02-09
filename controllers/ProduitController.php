<?php
// Ne PAS mettre session_start() ici, c'est déjà fait dans index.php
require_once __DIR__ . '/../models/produit.php';

class ProduitController {
    // ... reste du code inchangé
    
    public function __construct() {
        $this->produitModel = new Produit();
    }
    
    // Afficher tous les produits
    public function index() {
        $categorieId = isset($_GET['categorie']) ? $_GET['categorie'] : null;
        $search = isset($_GET['search']) ? $_GET['search'] : null;
        
        if ($search) {
            $produits = $this->produitModel->searchProduits($search);
        } else {
            $produits = $this->produitModel->getAllProduits($categorieId);
        }
        
        $categories = $this->produitModel->getAllCategories();
        
        require_once __DIR__ . '/../views/produits/index.php';
    }
    
    // Afficher les détails d'un produit
    public function show() {
        if (!isset($_GET['id'])) {
            header('Location: index.php?controller=produit&action=index');
            exit();
        }
        
        $produit = $this->produitModel->getProduitById($_GET['id']);
        
        if (!$produit) {
            header('Location: index.php?controller=produit&action=index');
            exit();
        }
        
        require_once __DIR__ . '/../views/produits/show.php';
    }
}
?>
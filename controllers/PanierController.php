<?php
// Ne PAS mettre session_start() ici, c'est déjà fait dans index.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Commande.php';

class PanierController {
    // ... reste du code inchangé
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    // Afficher le panier
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?controller=user&action=login');
            exit();
        }
        
        $panierItems = $this->getPanierItems($_SESSION['user_id']);
        $total = $this->calculateTotal($panierItems);
        
        require_once __DIR__ . '/../views/panier/index.php';
    }
    
    // Ajouter un produit au panier
    public function add() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['message'] = "Vous devez être connecté pour ajouter au panier";
            header('Location: index.php?controller=user&action=login');
            exit();
        }
        
        if (!isset($_POST['produit_id']) || !isset($_POST['quantite'])) {
            header('Location: index.php?controller=produit&action=index');
            exit();
        }
        
        $userId = $_SESSION['user_id'];
        $produitId = $_POST['produit_id'];
        $quantite = $_POST['quantite'];
        
        try {
            // Vérifier si le produit est déjà dans le panier
            $query = "SELECT * FROM panier WHERE user_id = :user_id AND produit_id = :produit_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':produit_id', $produitId);
            $stmt->execute();
            
            if ($stmt->fetch()) {
                // Mettre à jour la quantité
                $query = "UPDATE panier SET quantite = quantite + :quantite 
                          WHERE user_id = :user_id AND produit_id = :produit_id";
            } else {
                // Ajouter un nouveau produit
                $query = "INSERT INTO panier (user_id, produit_id, quantite) 
                          VALUES (:user_id, :produit_id, :quantite)";
            }
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':produit_id', $produitId);
            $stmt->bindParam(':quantite', $quantite);
            $stmt->execute();
            
            $_SESSION['success'] = "Produit ajouté au panier";
        } catch(PDOException $e) {
            $_SESSION['error'] = "Erreur lors de l'ajout au panier";
        }
        
        header('Location: index.php?controller=panier&action=index');
        exit();
    }
    
    // Mettre à jour la quantité d'un produit
    public function update() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?controller=user&action=login');
            exit();
        }
        
        if (isset($_POST['produit_id']) && isset($_POST['quantite'])) {
            $userId = $_SESSION['user_id'];
            $produitId = $_POST['produit_id'];
            $quantite = $_POST['quantite'];
            
            if ($quantite > 0) {
                $query = "UPDATE panier SET quantite = :quantite 
                          WHERE user_id = :user_id AND produit_id = :produit_id";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':quantite', $quantite);
                $stmt->bindParam(':user_id', $userId);
                $stmt->bindParam(':produit_id', $produitId);
                $stmt->execute();
            } else {
                $this->remove();
                return;
            }
        }
        
        header('Location: index.php?controller=panier&action=index');
        exit();
    }
    
    // Supprimer un produit du panier
    public function remove() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?controller=user&action=login');
            exit();
        }
        
        $produitId = isset($_POST['produit_id']) ? $_POST['produit_id'] : (isset($_GET['id']) ? $_GET['id'] : null);
        
        if ($produitId) {
            $query = "DELETE FROM panier WHERE user_id = :user_id AND produit_id = :produit_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->bindParam(':produit_id', $produitId);
            $stmt->execute();
        }
        
        header('Location: index.php?controller=panier&action=index');
        exit();
    }
    
    // Valider la commande
    public function checkout() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?controller=user&action=login');
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $adresse = $_POST['adresse'];
            $panierItems = $this->getPanierItems($_SESSION['user_id']);
            $total = $this->calculateTotal($panierItems);
            
            $commandeModel = new Commande();
            $commandeId = $commandeModel->createCommande($_SESSION['user_id'], $total, $adresse, $panierItems);
            
            if ($commandeId) {
                $_SESSION['success'] = "Commande validée avec succès !";
                header('Location: index.php?controller=user&action=commandes');
            } else {
                $_SESSION['error'] = "Erreur lors de la validation de la commande";
                header('Location: index.php?controller=panier&action=index');
            }
            exit();
        }
        
        $panierItems = $this->getPanierItems($_SESSION['user_id']);
        $total = $this->calculateTotal($panierItems);
        
        require_once __DIR__ . '/../views/panier/checkout.php';
    }
    
    // Récupérer les articles du panier
    private function getPanierItems($userId) {
        $query = "SELECT p.*, pa.quantite, (p.prix * pa.quantite) as sous_total 
                  FROM panier pa 
                  JOIN produits p ON pa.produit_id = p.id 
                  WHERE pa.user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Calculer le total du panier
    private function calculateTotal($items) {
        $total = 0;
        foreach ($items as $item) {
            $total += $item['sous_total'];
        }
        return $total;
    }
}
?>
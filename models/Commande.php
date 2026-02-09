<?php
require_once __DIR__ . '/../config/database.php';

class Commande {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    // Créer une nouvelle commande
    public function createCommande($userId, $total, $adresseLivraison, $panierItems) {
        try {
            $this->db->beginTransaction();
            
            // Insérer la commande
            $query = "INSERT INTO commandes (user_id, total, adresse_livraison) 
                      VALUES (:user_id, :total, :adresse_livraison)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':total', $total);
            $stmt->bindParam(':adresse_livraison', $adresseLivraison);
            $stmt->execute();
            
            $commandeId = $this->db->lastInsertId();
            
            // Insérer les détails de commande
            $query = "INSERT INTO commande_details (commande_id, produit_id, quantite, prix_unitaire) 
                      VALUES (:commande_id, :produit_id, :quantite, :prix_unitaire)";
            $stmt = $this->db->prepare($query);
            
            foreach ($panierItems as $item) {
                $stmt->bindParam(':commande_id', $commandeId);
                $stmt->bindParam(':produit_id', $item['produit_id']);
                $stmt->bindParam(':quantite', $item['quantite']);
                $stmt->bindParam(':prix_unitaire', $item['prix']);
                $stmt->execute();
                
                // Mettre à jour le stock
                $updateQuery = "UPDATE produits SET stock = stock - :quantite WHERE id = :id";
                $updateStmt = $this->db->prepare($updateQuery);
                $updateStmt->bindParam(':quantite', $item['quantite']);
                $updateStmt->bindParam(':id', $item['produit_id']);
                $updateStmt->execute();
            }
            
            // Vider le panier
            $query = "DELETE FROM panier WHERE user_id = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            $this->db->commit();
            return $commandeId;
        } catch(PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    // Récupérer les commandes d'un utilisateur
    public function getCommandesByUser($userId) {
        try {
            $query = "SELECT * FROM commandes WHERE user_id = :user_id ORDER BY date_commande DESC";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            return [];
        }
    }
    
    // Récupérer une commande par ID
    public function getCommandeById($id) {
        try {
            $query = "SELECT c.*, u.nom, u.prenom, u.email 
                      FROM commandes c 
                      JOIN users u ON c.user_id = u.id 
                      WHERE c.id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch();
        } catch(PDOException $e) {
            return null;
        }
    }
    
    // Récupérer les détails d'une commande
    public function getCommandeDetails($commandeId) {
        try {
            $query = "SELECT cd.*, p.nom as produit_nom, p.image 
                      FROM commande_details cd 
                      JOIN produits p ON cd.produit_id = p.id 
                      WHERE cd.commande_id = :commande_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':commande_id', $commandeId);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            return [];
        }
    }
    
    // Récupérer toutes les commandes (admin)
    public function getAllCommandes() {
        try {
            $query = "SELECT c.*, u.nom, u.prenom, u.email 
                      FROM commandes c 
                      JOIN users u ON c.user_id = u.id 
                      ORDER BY c.date_commande DESC";
            $stmt = $this->db->query($query);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            return [];
        }
    }
    
    // Mettre à jour le statut d'une commande (admin)
    public function updateStatut($id, $statut) {
        try {
            $query = "UPDATE commandes SET statut = :statut WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':statut', $statut);
            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }
}
?>
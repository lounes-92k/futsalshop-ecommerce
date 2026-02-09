<?php
class Produit {
    private $conn;
    private $table = "produits";

    // Propriétés
    public $id;
    public $nom;
    public $description;
    public $prix;
    public $image;
    public $categorie_id;
    public $stock;
    public $marque;
    public $taille;
    public $categorie_nom; 

    public function __construct($db) {
        $this->conn = $db;
    }

    // 1. Lire TOUS les produits
    public function lireTout() {
        $query = "SELECT c.nom as categorie_nom, p.id, p.nom, p.description, p.prix, p.image, p.stock, p.marque, p.taille 
                  FROM " . $this->table . " p
                  LEFT JOIN categories c ON p.categorie_id = c.id
                  ORDER BY p.date_ajout DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // 2. Lire les produits par CATÉGORIE
    public function lireParCategorie($categorie_id) {
        $query = "SELECT c.nom as categorie_nom, p.id, p.nom, p.description, p.prix, p.image, p.stock, p.marque, p.taille 
                  FROM " . $this->table . " p
                  LEFT JOIN categories c ON p.categorie_id = c.id
                  WHERE p.categorie_id = :categorie_id
                  ORDER BY p.nom ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':categorie_id', $categorie_id);
        $stmt->execute();
        return $stmt;
    }

    // 3. Lire UN SEUL produit (pour la fiche détaillée)
    public function lireUn($id) {
        $query = "SELECT c.nom as categorie_nom, p.id, p.nom, p.description, p.prix, p.image, p.stock, p.marque, p.taille 
                  FROM " . $this->table . " p
                  LEFT JOIN categories c ON p.categorie_id = c.id
                  WHERE p.id = ? 
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    
    // 4. Créer un produit (Back-office)
    public function creer() {
        $query = "INSERT INTO " . $this->table . " 
                  SET nom=:nom, description=:description, prix=:prix, stock=:stock, 
                      categorie_id=:categorie_id, image=:image, marque=:marque, taille=:taille";

        $stmt = $this->conn->prepare($query);

        // Nettoyage
        $this->nom = htmlspecialchars(strip_tags($this->nom));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->prix = htmlspecialchars(strip_tags($this->prix));
        $this->stock = htmlspecialchars(strip_tags($this->stock));
        $this->categorie_id = htmlspecialchars(strip_tags($this->categorie_id));
        $this->image = htmlspecialchars(strip_tags($this->image));
        $this->marque = htmlspecialchars(strip_tags($this->marque));
        $this->taille = htmlspecialchars(strip_tags($this->taille));

        // Liaison
        $stmt->bindParam(":nom", $this->nom);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":prix", $this->prix);
        $stmt->bindParam(":stock", $this->stock);
        $stmt->bindParam(":categorie_id", $this->categorie_id);
        $stmt->bindParam(":image", $this->image);
        $stmt->bindParam(":marque", $this->marque);
        $stmt->bindParam(":taille", $this->taille);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // 5. Supprimer un produit
    public function supprimer($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // 6. Mettre à jour un produit
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET nom = :nom, 
                      prix = :prix, 
                      description = :description, 
                      stock = :stock, 
                      categorie_id = :categorie_id,
                      marque = :marque,
                      taille = :taille
                      " . ($this->image ? ", image = :image" : "") . " 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Nettoyage
        $this->nom = htmlspecialchars(strip_tags($this->nom));
        $this->prix = htmlspecialchars(strip_tags($this->prix));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->stock = htmlspecialchars(strip_tags($this->stock));
        $this->categorie_id = htmlspecialchars(strip_tags($this->categorie_id));
        $this->marque = htmlspecialchars(strip_tags($this->marque));
        $this->taille = htmlspecialchars(strip_tags($this->taille));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Liaison
        $stmt->bindParam(':nom', $this->nom);
        $stmt->bindParam(':prix', $this->prix);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':stock', $this->stock);
        $stmt->bindParam(':categorie_id', $this->categorie_id);
        $stmt->bindParam(':marque', $this->marque);
        $stmt->bindParam(':taille', $this->taille);
        $stmt->bindParam(':id', $this->id);

        // Si on a changé l'image, on lie la nouvelle, sinon on ne touche pas à l'ancienne
        if($this->image) {
            $this->image = htmlspecialchars(strip_tags($this->image));
            $stmt->bindParam(':image', $this->image);
        }

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
} 

?>
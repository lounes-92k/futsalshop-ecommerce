<?php
// models/user.php
class User {
    private $conn;
    private $table_name = "users";
    
    // Propriétés de l'utilisateur
    public $id;
    public $email;
    public $password;
    public $nom;
    public $prenom;
    public $role;
    public $adresse;
    public $telephone;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Fonction pour créer un utilisateur (Inscription)
    public function create() {
        // Requête d'insertion
        $query = "INSERT INTO " . $this->table_name . " 
                  SET nom=:nom, prenom=:prenom, email=:email, password=:password, 
                      adresse=:adresse, telephone=:telephone, role='client'";
        
        $stmt = $this->conn->prepare($query);
        
        // Nettoyage des données
        $this->nom = htmlspecialchars(strip_tags($this->nom));
        $this->prenom = htmlspecialchars(strip_tags($this->prenom));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->adresse = htmlspecialchars(strip_tags($this->adresse));
        $this->telephone = htmlspecialchars(strip_tags($this->telephone));
        
        // Hashage du mot de passe (Sécurité)
        $password_hash = password_hash($this->password, PASSWORD_DEFAULT);
        
        // Liaison des valeurs
        $stmt->bindParam(":nom", $this->nom);
        $stmt->bindParam(":prenom", $this->prenom);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $password_hash);
        $stmt->bindParam(":adresse", $this->adresse);
        $stmt->bindParam(":telephone", $this->telephone);
        
        // Exécution
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    // Fonction pour vérifier si l'email existe déjà
    public function emailExists() {
        $query = "SELECT id, nom, prenom, password, role, adresse, telephone 
                  FROM " . $this->table_name . " 
                  WHERE email = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->nom = $row['nom'];
            $this->prenom = $row['prenom'];
            $this->role = $row['role'];
            $this->adresse = $row['adresse'];
            $this->telephone = $row['telephone'];
            $this->password = $row['password']; // Le hash stocké en BDD
            return true;
        }
        return false;
    }
}
?>
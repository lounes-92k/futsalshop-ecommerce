<?php
include 'config/database.php';

$database = new Database();
$db = $database->getConnection();

if($db) {
    echo "✅ Connexion BDD OK !<br>";
    
    // Test lecture users
    $query = "SELECT * FROM users";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    echo "Nombre d'utilisateurs : " . $stmt->rowCount() . "<br><br>";
    
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: " . $row['id'] . "<br>";
        echo "Email: " . $row['email'] . "<br>";
        echo "Role: " . $row['role'] . "<br>";
        echo "Hash password (20 premiers caractères): " . substr($row['password'], 0, 20) . "<br>";
        echo "---<br>";
    }
} else {
    echo "❌ Erreur de connexion !";
}
?>
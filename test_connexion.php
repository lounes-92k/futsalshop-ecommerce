<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'config/database.php';

$database = new Database();
$db = $database->getConnection();

if($db) {
    echo "✅ Connexion BDD OK !<br>";
    
    $query = "SELECT * FROM users";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    echo "Nombre d'utilisateurs : " . $stmt->rowCount() . "<br><br>";
    
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: " . $row['id'] . "<br>";
        echo "Email: " . $row['email'] . "<br>";
        echo "Role: " . $row['role'] . "<br>";
        echo "---<br>";
    }
} else {
    echo "❌ Erreur de connexion !";
}
?>
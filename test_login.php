<?php
include 'config/database.php';
include 'models/user.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

// Test avec l'email que tu utilises
$email = "admin@futsal.com"; // Change si tu utilises un autre email
$password = "admin123";

$user->email = $email;

echo "Test de connexion avec : $email / $password<br><br>";

if($user->emailExists()) {
    echo "✅ Email trouvé en base !<br>";
    echo "Nom: " . $user->nom . "<br>";
    echo "Role: " . $user->role . "<br>";
    echo "Hash en BDD (20 premiers caractères): " . substr($user->password, 0, 20) . "<br><br>";
    
    if(password_verify($password, $user->password)) {
        echo "✅✅ MOT DE PASSE CORRECT !<br>";
        echo "La connexion devrait fonctionner.";
    } else {
        echo "❌ MOT DE PASSE INCORRECT !<br>";
        echo "Le hash ne correspond pas.";
    }
} else {
    echo "❌ Email non trouvé en base de données !";
}
?>
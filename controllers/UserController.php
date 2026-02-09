<?php
// controllers/UserController.php
session_start();

// 1. On inclut la connexion et le modèle
include_once '../config/database.php';
include_once '../models/user.php';

// 2. On se connecte à la BDD
$database = new Database();
$db = $database->getConnection();

// 3. On prépare l'objet User
$user = new User($db);

// 4. On récupère l'action demandée (register, login ou logout) via l'URL
$action = isset($_GET['action']) ? $_GET['action'] : '';

// --- INSCRIPTION --- //
if ($action == 'register' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // On récupère les deux mots de passe
    $password = $_POST['password'];
    $password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';

   // 1. VÉRIFICATION : Est-ce qu'ils correspondent ?
    if ($password !== $password_confirm) {
        $_SESSION['error'] = "Les mots de passe ne correspondent pas.";
        header("Location: ../views/users/register.php");
        exit(); 
    }

    // Si ça correspond, on continue normalement
    $user->nom = $_POST['nom'];
    $user->prenom = $_POST['prenom'];
    $user->email = $_POST['email'];
    $user->password = $password;
    $user->adresse = isset($_POST['adresse']) ? $_POST['adresse'] : '';
    $user->telephone = isset($_POST['telephone']) ? $_POST['telephone'] : '';

    // Vérification des champs vides
    if(empty($user->nom) || empty($user->prenom) || empty($user->email) || empty($user->password)){
        $_SESSION['error'] = "Tous les champs obligatoires doivent être remplis.";
        header("Location: ../views/users/register.php");
        exit();
    }

    // Vérification email existant
    if($user->emailExists()){
        $_SESSION['error'] = "Cet email est déjà utilisé.";
        header("Location: ../views/users/register.php");
    } else {
        if($user->create()){
            $_SESSION['success'] = "Compte créé ! Vous pouvez vous connecter.";
            header("Location: ../views/users/login.php");
        } else {
            $_SESSION['error'] = "Une erreur est survenue.";
            header("Location: ../views/users/register.php");
        }
    }
}

// --- CONNEXION (LOGIN) ---
elseif ($action == 'login' && $_SERVER['REQUEST_METHOD'] == 'POST') {

    $email = $_POST['email'];
    $password = $_POST['password'];

    // On assigne l'email à l'objet User pour chercher en BDD
    $user->email = $email;

    // 1. On vérifie si l'email existe
    if($user->emailExists()){
        
        // 2. On compare le mot de passe saisi avec le hash en BDD
        if(password_verify($password, $user->password)){
            
            // SUCCÈS : On stocke les infos en Session
            $_SESSION['user_id'] = $user->id;
            $_SESSION['user_nom'] = $user->nom;
            $_SESSION['user_prenom'] = $user->prenom;
            $_SESSION['user_role'] = $user->role; // 'admin' ou 'client'

            // Redirection selon le rôle
            if($user->role == 'admin'){
                header("Location: ../views/admin/dashboard.php");
            } else {
                header("Location: ../index.php");
            }
        } else {
            $_SESSION['error'] = "Mot de passe incorrect.";
            header("Location: ../views/users/login.php");
        }
    } else {
        $_SESSION['error'] = "Aucun compte trouvé avec cet email.";
        header("Location: ../views/users/login.php");
    }
}

// --- DÉCONNEXION (LOGOUT) ---
elseif ($action == 'logout') {
    session_destroy();
    header("Location: ../views/users/login.php");
}

// Si aucune action reconnue, retour à l'accueil
else {
    header("Location: ../index.php");
}
?>
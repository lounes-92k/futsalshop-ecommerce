<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FutsalShop - Équipement de Futsal</title>
    
    <!-- Bootswatch Theme -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/flatly/bootstrap.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body { 
            min-height: 100vh; 
            display: flex; 
            flex-direction: column; 
        }
        .container { flex: 1; }
        .product-card:hover {
            transform: translateY(-5px);
            transition: transform 0.3s;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-success mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold" href="/cours/e-commerce/index.php">
        <i class="fas fa-futbol"></i> FutsalShop
    </a>
    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center">
        
        <li class="nav-item">
          <a class="nav-link" href="/cours/e-commerce/views/produits/index.php">
              <i class="fas fa-store"></i> Catalogue
          </a>
        </li>
        
        <li class="nav-item">
          <a class="nav-link position-relative" href="#">
            <i class="fas fa-shopping-cart"></i> Panier
          </a>
        </li>
        
        <li class="nav-item mx-2 text-light d-none d-lg-block">|</li>
        
        <?php if(isset($_SESSION['user_id'])): ?>
            
            <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link btn btn-warning text-dark fw-bold btn-sm mx-2 px-3" 
                       href="/cours/e-commerce/views/admin/dashboard.php">
                        <i class="fas fa-cog"></i> Admin
                    </a>
                </li>
            <?php endif; ?>
            
            <li class="nav-item">
                <span class="nav-link text-light">
                    Bonjour <strong><?= htmlspecialchars($_SESSION['user_prenom']) ?></strong>
                </span>
            </li>
            
            <li class="nav-item">
                <a class="nav-link btn btn-outline-light btn-sm ms-2" 
                   href="/cours/e-commerce/controllers/UserController.php?action=logout">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </li>
        <?php else: ?>
            <li class="nav-item">
                <a class="nav-link" href="/cours/e-commerce/views/users/register.php">
                    <i class="fas fa-user-plus"></i> Inscription
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link btn btn-outline-light btn-sm ms-2" 
                   href="/cours/e-commerce/views/users/login.php">
                    <i class="fas fa-sign-in-alt"></i> Connexion
                </a>
            </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<?php 
// Affichage des messages
if(isset($_SESSION['success'])) {
    echo '<div class="container"><div class="alert alert-success alert-dismissible fade show">' 
         . htmlspecialchars($_SESSION['success']) . 
         '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div></div>';
    unset($_SESSION['success']);
}
if(isset($_SESSION['error'])) {
    echo '<div class="container"><div class="alert alert-danger alert-dismissible fade show">' 
         . htmlspecialchars($_SESSION['error']) . 
         '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div></div>';
    unset($_SESSION['error']);
}
?>

<div class="container">
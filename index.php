<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>FutsalShop - Accueil</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/flatly/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
            padding: 100px 0;
            text-align: center;
        }
        .category-card {
            transition: transform 0.3s;
            cursor: pointer;
        }
        .category-card:hover {
            transform: translateY(-10px);
        }
    </style>
</head>
<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark bg-success">
  <div class="container">
    <a class="navbar-brand fw-bold" href="/cours/e-commerce/index.php">
        <i class="fas fa-futbol"></i> FutsalShop
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link" href="views/produits/index.php">
            <i class="fas fa-store"></i> Catalogue
          </a>
        </li>
        
        <?php if(isset($_SESSION['user_id'])): ?>
            <?php if($_SESSION['user_role'] == 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link btn btn-warning text-dark btn-sm mx-2" href="views/admin/dashboard.php">
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
                <a class="nav-link btn btn-outline-light btn-sm" href="controllers/UserController.php?action=logout">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </li>
        <?php else: ?>
            <li class="nav-item">
                <a class="nav-link" href="views/users/register.php">
                    <i class="fas fa-user-plus"></i> Inscription
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link btn btn-outline-light btn-sm" href="views/users/login.php">
                    <i class="fas fa-sign-in-alt"></i> Connexion
                </a>
            </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- Hero Section -->
<div class="hero-section">
    <div class="container">
        <h1 class="display-3 fw-bold mb-3">
            <i class="fas fa-futbol"></i> Bienvenue sur FutsalShop
        </h1>
        <p class="lead fs-4 mb-4">
            Votre boutique spécialisée en équipement de futsal
        </p>
        <p class="mb-4">Chaussures • Ballons • Maillots • Accessoires</p>
        <a href="views/produits/index.php" class="btn btn-light btn-lg px-5">
            <i class="fas fa-store"></i> Découvrir nos produits
        </a>
    </div>
</div>

<!-- Catégories -->
<div class="container my-5">
    <h2 class="text-center mb-5">Nos Catégories</h2>
    <div class="row g-4">
        <div class="col-md-3">
            <a href="views/produits/index.php?categorie_id=1" class="text-decoration-none">
                <div class="card category-card shadow border-0">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-running fa-3x text-success mb-3"></i>
                        <h5 class="card-title">Chaussures</h5>
                        <p class="text-muted">Chaussures de futsal</p>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="col-md-3">
            <a href="views/produits/index.php?categorie_id=2" class="text-decoration-none">
                <div class="card category-card shadow border-0">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-futbol fa-3x text-success mb-3"></i>
                        <h5 class="card-title">Ballons</h5>
                        <p class="text-muted">Ballons officiels</p>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="col-md-3">
            <a href="views/produits/index.php?categorie_id=3" class="text-decoration-none">
                <div class="card category-card shadow border-0">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-tshirt fa-3x text-success mb-3"></i>
                        <h5 class="card-title">Maillots</h5>
                        <p class="text-muted">Équipements textiles</p>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="col-md-3">
            <a href="views/produits/index.php?categorie_id=4" class="text-decoration-none">
                <div class="card category-card shadow border-0">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-shield-alt fa-3x text-success mb-3"></i>
                        <h5 class="card-title">Accessoires</h5>
                        <p class="text-muted">Protections & plus</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="bg-dark text-light text-center py-4 mt-5">
    <div class="container">
        <p class="mb-0">© 2025 FutsalShop - Projet E-Commerce BTS</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FutsalShop - Équipement de Futsal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/flatly/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { min-height: 100vh; display: flex; flex-direction: column; }
        .container { flex: 1; }
        .product-card:hover { transform: translateY(-5px); transition: transform 0.3s; }

        /* BARRE PROMO DÉFILANTE */
        .promo-bar {
            background: linear-gradient(90deg, #1a1a2e, #16213e, #0f3460);
            color: white;
            padding: 9px 0;
            overflow: hidden;
            border-bottom: 2px solid #2ecc71;
        }
        .promo-track {
            display: flex;
            width: max-content;
            animation: scrollPromo 35s linear infinite;
        }
        .promo-track:hover { animation-play-state: paused; cursor: pointer; }
        .promo-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0 40px;
            white-space: nowrap;
            font-size: 13px;
            font-weight: 500;
        }
        .promo-code {
            background: #2ecc71;
            color: #1a1a2e;
            padding: 2px 10px;
            border-radius: 12px;
            font-weight: 800;
            font-size: 12px;
            letter-spacing: 1.5px;
        }
        .promo-sep { color: #2ecc71; padding: 0 5px; }
        @keyframes scrollPromo {
            0%   { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
    </style>
</head>
<body>

<!-- BARRE PROMO DÉFILANTE -->
<div class="promo-bar">
    <div class="promo-track">
        <?php
        $promos = [
            ['icon' => '🎁', 'text' => 'Code promo :', 'code' => 'FUTSAL2026', 'suite' => '-10% sur tout le site !'],
            ['icon' => '🚚', 'text' => 'Livraison GRATUITE dès 50€ d\'achat', 'code' => null, 'suite' => ''],
            ['icon' => '⚽', 'text' => 'Nouvelle collection Printemps 2026 disponible !', 'code' => null, 'suite' => ''],
            ['icon' => '🔥', 'text' => 'Soldes :', 'code' => null, 'suite' => 'Jusqu\'à -30% sur les chaussures'],
            ['icon' => '💥', 'text' => 'Code flash :', 'code' => 'FLASH20', 'suite' => '-20% aujourd\'hui seulement !'],
            ['icon' => '🏆', 'text' => 'Nike • Adidas • Puma • Select • Molten', 'code' => null, 'suite' => ''],
            ['icon' => '🎯', 'text' => 'Pack complet :', 'code' => 'PACK2026', 'suite' => '-15% sur les packs !'],
            ['icon' => '⭐', 'text' => 'Satisfait ou remboursé 30 jours — Paiement 100% sécurisé', 'code' => null, 'suite' => ''],
        ];
        $allPromos = array_merge($promos, $promos);
        foreach ($allPromos as $promo): ?>
            <div class="promo-item">
                <span><?= $promo['icon'] ?></span>
                <span><?= $promo['text'] ?></span>
                <?php if ($promo['code']): ?>
                    <span class="promo-code"><?= $promo['code'] ?></span>
                <?php endif; ?>
                <?php if ($promo['suite']): ?>
                    <span><?= $promo['suite'] ?></span>
                <?php endif; ?>
            </div>
            <span class="promo-sep">✦</span>
        <?php endforeach; ?>
    </div>
</div>

<!-- NAVBAR -->
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
          <a class="nav-link position-relative" href="/cours/e-commerce/views/panier/index.php">
            <i class="fas fa-shopping-cart"></i> Panier
            <?php 
              $nb_articles = 0;
              if(isset($_SESSION['panier'])) {
                  $nb_articles = array_sum($_SESSION['panier']);
              }
              if($nb_articles > 0): 
            ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    <?= $nb_articles ?>
                </span>
            <?php endif; ?>
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
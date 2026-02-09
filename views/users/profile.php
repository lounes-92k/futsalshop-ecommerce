<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-user-circle"></i> Mon Profil</h1>
    </div>
    
    <div class="profile-container">
        <div class="profile-card">
            <h2>Informations personnelles</h2>
            
            <form action="index.php?controller=user&action=profile" method="POST" class="profile-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nom">
                            <i class="fas fa-user"></i> Nom
                        </label>
                        <input type="text" 
                               id="nom" 
                               name="nom" 
                               value="<?php echo htmlspecialchars($user['nom']); ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="prenom">
                            <i class="fas fa-user"></i> Prénom
                        </label>
                        <input type="text" 
                               id="prenom" 
                               name="prenom" 
                               value="<?php echo htmlspecialchars($user['prenom']); ?>" 
                               required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email
                    </label>
                    <input type="email" 
                           id="email" 
                           value="<?php echo htmlspecialchars($user['email']); ?>" 
                           disabled>
                    <small>L'email ne peut pas être modifié</small>
                </div>
                
                <div class="form-group">
                    <label for="adresse">
                        <i class="fas fa-map-marker-alt"></i> Adresse
                    </label>
                    <textarea id="adresse" 
                              name="adresse" 
                              rows="3"><?php echo htmlspecialchars($user['adresse'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="telephone">
                        <i class="fas fa-phone"></i> Téléphone
                    </label>
                    <input type="tel" 
                           id="telephone" 
                           name="telephone" 
                           value="<?php echo htmlspecialchars($user['telephone'] ?? ''); ?>">
                </div>
                
                <button type="submit" class="btn btn-primary btn-large">
                    <i class="fas fa-save"></i> Enregistrer les modifications
                </button>
            </form>
        </div>
        
        <div class="profile-sidebar">
            <div class="profile-info-card">
                <div class="profile-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <h3><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></h3>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
                <p class="member-since">
                    <i class="fas fa-calendar"></i>
                    Membre depuis <?php echo date('d/m/Y', strtotime($user['date_creation'])); ?>
                </p>
            </div>
            
            <div class="profile-links">
                <a href="index.php?controller=user&action=commandes" class="profile-link">
                    <i class="fas fa-box"></i> Mes commandes
                </a>
                <a href="index.php?controller=panier&action=index" class="profile-link">
                    <i class="fas fa-shopping-cart"></i> Mon panier
                </a>
                <a href="index.php?controller=user&action=logout" class="profile-link logout">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.profile-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
}

.profile-card {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.profile-card h2 {
    margin-bottom: 30px;
    color: var(--dark-color);
}

.profile-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.profile-sidebar {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.profile-info-card {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    text-align: center;
}

.profile-avatar {
    width: 100px;
    height: 100px;
    margin: 0 auto 20px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.profile-avatar i {
    font-size: 60px;
    color: white;
}

.profile-info-card h3 {
    margin-bottom: 5px;
    color: var(--dark-color);
}

.member-since {
    margin-top: 15px;
    color: #666;
    font-size: 14px;
}

.profile-links {
    background: white;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.profile-link {
    padding: 15px;
    color: var(--text-color);
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 10px;
}

.profile-link:hover {
    background: var(--light-color);
    transform: translateX(5px);
}

.profile-link.logout {
    color: var(--danger-color);
}

.profile-link.logout:hover {
    background: #fee;
}

@media (max-width: 768px) {
    .profile-container {
        grid-template-columns: 1fr;
    }
}
</style>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
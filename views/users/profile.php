<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include_once '../../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Récupérer les infos utilisateur
$query = "SELECT * FROM users WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Traitement des formulaires
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Modifier les infos personnelles ---
    if (isset($_POST['action']) && $_POST['action'] === 'update_profil') {
        $nom    = htmlspecialchars(strip_tags($_POST['nom']));
        $prenom = htmlspecialchars(strip_tags($_POST['prenom']));
        $adresse   = htmlspecialchars(strip_tags($_POST['adresse']));
        $telephone = htmlspecialchars(strip_tags($_POST['telephone']));

        $q = "UPDATE users SET nom=:nom, prenom=:prenom, adresse=:adresse, telephone=:telephone WHERE id=:id";
        $s = $db->prepare($q);
        $s->bindParam(':nom', $nom);
        $s->bindParam(':prenom', $prenom);
        $s->bindParam(':adresse', $adresse);
        $s->bindParam(':telephone', $telephone);
        $s->bindParam(':id', $_SESSION['user_id']);

        if ($s->execute()) {
            $_SESSION['user_prenom'] = $prenom;
            $success = "✅ Informations mises à jour avec succès !";
            $user['nom'] = $nom; $user['prenom'] = $prenom;
            $user['adresse'] = $adresse; $user['telephone'] = $telephone;
        } else {
            $error = "❌ Erreur lors de la mise à jour.";
        }
    }

    // --- Modifier l'email ---
    elseif (isset($_POST['action']) && $_POST['action'] === 'update_email') {
        $new_email = htmlspecialchars(strip_tags($_POST['new_email']));
        $password_check = $_POST['password_email'];

        if (password_verify($password_check, $user['password'])) {
            // Vérifier si email déjà utilisé
            $check = $db->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
            $check->bindParam(':email', $new_email);
            $check->bindParam(':id', $_SESSION['user_id']);
            $check->execute();

            if ($check->rowCount() > 0) {
                $error = "❌ Cet email est déjà utilisé par un autre compte.";
            } else {
                $q = "UPDATE users SET email=:email WHERE id=:id";
                $s = $db->prepare($q);
                $s->bindParam(':email', $new_email);
                $s->bindParam(':id', $_SESSION['user_id']);
                if ($s->execute()) {
                    $success = "✅ Email modifié avec succès !";
                    $user['email'] = $new_email;
                }
            }
        } else {
            $error = "❌ Mot de passe incorrect.";
        }
    }

    // --- Changer le mot de passe ---
    elseif (isset($_POST['action']) && $_POST['action'] === 'update_password') {
        $old_password = $_POST['old_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if (!password_verify($old_password, $user['password'])) {
            $error = "❌ Ancien mot de passe incorrect.";
        } elseif (strlen($new_password) < 6) {
            $error = "❌ Le nouveau mot de passe doit faire au moins 6 caractères.";
        } elseif ($new_password !== $confirm_password) {
            $error = "❌ Les nouveaux mots de passe ne correspondent pas.";
        } else {
            $hash = password_hash($new_password, PASSWORD_DEFAULT);
            $q = "UPDATE users SET password=:password WHERE id=:id";
            $s = $db->prepare($q);
            $s->bindParam(':password', $hash);
            $s->bindParam(':id', $_SESSION['user_id']);
            if ($s->execute()) {
                $success = "✅ Mot de passe modifié avec succès !";
            }
        }
    }
}

// Récupérer le nombre de commandes
$qCmd = "SELECT COUNT(*) as total FROM commandes WHERE user_id = :id";
$sCmd = $db->prepare($qCmd);
$sCmd->bindParam(':id', $_SESSION['user_id']);
$sCmd->execute();
$nbCommandes = $sCmd->fetch(PDO::FETCH_ASSOC)['total'];

include '../templates/header.php';
?>

<div class="row">

    <!-- SIDEBAR PROFIL -->
    <div class="col-md-3 mb-4">
        <div class="card shadow-sm text-center mb-3">
            <div class="card-body py-4">
                <!-- Avatar -->
                <div style="width:80px;height:80px;background:linear-gradient(135deg,#2ecc71,#27ae60);
                            border-radius:50%;display:flex;align-items:center;justify-content:center;
                            margin:0 auto 15px;font-size:36px;color:white;">
                    <?= strtoupper(substr($user['prenom'], 0, 1)) ?>
                </div>
                <h5 class="fw-bold mb-1"><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h5>
                <p class="text-muted small mb-2"><?= htmlspecialchars($user['email']) ?></p>
                <span class="badge bg-<?= $user['role'] === 'admin' ? 'warning text-dark' : 'success' ?>">
                    <i class="fas fa-<?= $user['role'] === 'admin' ? 'crown' : 'user' ?>"></i>
                    <?= ucfirst($user['role']) ?>
                </span>
                <hr>
                <div class="d-flex justify-content-center gap-4">
                    <div class="text-center">
                        <div class="fw-bold text-success fs-5"><?= $nbCommandes ?></div>
                        <div class="text-muted" style="font-size:12px;">Commandes</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menu navigation -->
        <div class="list-group shadow-sm">
            <a href="#infos" onclick="showTab('infos')" 
               class="list-group-item list-group-item-action active" id="tab-infos">
                <i class="fas fa-user me-2"></i> Mes informations
            </a>
            <a href="#email" onclick="showTab('email')" 
               class="list-group-item list-group-item-action" id="tab-email">
                <i class="fas fa-envelope me-2"></i> Changer l'email
            </a>
            <a href="#password" onclick="showTab('password')" 
               class="list-group-item list-group-item-action" id="tab-password">
                <i class="fas fa-lock me-2"></i> Changer le mot de passe
            </a>
            <a href="commandes.php" 
               class="list-group-item list-group-item-action">
                <i class="fas fa-box me-2"></i> Mes commandes
            </a>
            <a href="/cours/e-commerce/controllers/UserController.php?action=logout" 
               class="list-group-item list-group-item-action text-danger"
               onclick="return confirm('Voulez-vous vraiment vous déconnecter ?')">
                <i class="fas fa-sign-out-alt me-2"></i> <strong>Se déconnecter</strong>
            </a>
        </div>
    </div>

    <!-- CONTENU PRINCIPAL -->
    <div class="col-md-9">

        <!-- Messages -->
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= $success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- ===== TAB 1 : INFOS PERSONNELLES ===== -->
        <div id="section-infos" class="tab-section">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-user"></i> Mes informations personnelles</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_profil">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nom</label>
                                <input type="text" name="nom" class="form-control" 
                                       value="<?= htmlspecialchars($user['nom']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Prénom</label>
                                <input type="text" name="prenom" class="form-control" 
                                       value="<?= htmlspecialchars($user['prenom']) ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Adresse de livraison</label>
                            <textarea name="adresse" class="form-control" rows="3"><?= htmlspecialchars($user['adresse'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Téléphone</label>
                            <input type="tel" name="telephone" class="form-control" 
                                   value="<?= htmlspecialchars($user['telephone'] ?? '') ?>"
                                   placeholder="06 12 34 56 78">
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Enregistrer les modifications
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- ===== TAB 2 : CHANGER EMAIL ===== -->
        <div id="section-email" class="tab-section" style="display:none;">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-envelope"></i> Changer mon adresse email</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Email actuel : <strong><?= htmlspecialchars($user['email']) ?></strong>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_email">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nouvel email</label>
                            <input type="email" name="new_email" class="form-control" 
                                   placeholder="nouveau@email.com" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Confirmez votre mot de passe</label>
                            <input type="password" name="password_email" class="form-control" 
                                   placeholder="Votre mot de passe actuel" required>
                            <div class="form-text">Pour des raisons de sécurité, confirmez votre mot de passe.</div>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-envelope"></i> Modifier l'email
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- ===== TAB 3 : CHANGER MOT DE PASSE ===== -->
        <div id="section-password" class="tab-section" style="display:none;">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-lock"></i> Changer mon mot de passe</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_password">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Ancien mot de passe</label>
                            <div class="input-group">
                                <input type="password" name="old_password" class="form-control" 
                                       id="old_pwd" placeholder="Votre mot de passe actuel" required>
                                <button type="button" class="btn btn-outline-secondary" 
                                        onclick="togglePwd('old_pwd')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nouveau mot de passe</label>
                            <div class="input-group">
                                <input type="password" name="new_password" class="form-control" 
                                       id="new_pwd" placeholder="Minimum 6 caractères" 
                                       required minlength="6" oninput="checkStrength(this.value)">
                                <button type="button" class="btn btn-outline-secondary" 
                                        onclick="togglePwd('new_pwd')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <!-- Indicateur de force -->
                            <div class="mt-2">
                                <div class="progress" style="height:6px;">
                                    <div id="strengthBar" class="progress-bar" style="width:0%"></div>
                                </div>
                                <small id="strengthText" class="text-muted"></small>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Confirmer le nouveau mot de passe</label>
                            <div class="input-group">
                                <input type="password" name="confirm_password" class="form-control" 
                                       id="confirm_pwd" placeholder="Répétez le nouveau mot de passe" required>
                                <button type="button" class="btn btn-outline-secondary" 
                                        onclick="togglePwd('confirm_pwd')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-lock"></i> Modifier le mot de passe
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
// Changer d'onglet
function showTab(tab) {
    document.querySelectorAll('.tab-section').forEach(s => s.style.display = 'none');
    document.querySelectorAll('.list-group-item').forEach(t => t.classList.remove('active'));
    document.getElementById('section-' + tab).style.display = 'block';
    document.getElementById('tab-' + tab).classList.add('active');
    return false;
}

// Voir/masquer mot de passe
function togglePwd(id) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}

// Indicateur de force du mot de passe
function checkStrength(val) {
    const bar  = document.getElementById('strengthBar');
    const text = document.getElementById('strengthText');
    let score = 0;
    if (val.length >= 6)  score++;
    if (val.length >= 10) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const levels = [
        { w: '20%', cls: 'bg-danger',  txt: 'Très faible' },
        { w: '40%', cls: 'bg-warning', txt: 'Faible' },
        { w: '60%', cls: 'bg-info',    txt: 'Moyen' },
        { w: '80%', cls: 'bg-primary', txt: 'Fort' },
        { w: '100%',cls: 'bg-success', txt: 'Très fort' },
    ];
    const lvl = levels[Math.min(score, 4)];
    bar.style.width = lvl.w;
    bar.className = 'progress-bar ' + lvl.cls;
    text.textContent = lvl.txt;
}

// Ouvrir le bon onglet si erreur/succès
<?php if ($error || $success): ?>
    <?php
    $activeTab = 'infos';
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'update_email')    $activeTab = 'email';
        if ($_POST['action'] === 'update_password') $activeTab = 'password';
    }
    ?>
    showTab('<?= $activeTab ?>');
<?php endif; ?>
</script>

<?php include '../templates/footer.php'; ?>
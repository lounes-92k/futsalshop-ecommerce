<?php
session_start();

// Vérifier que l'utilisateur est connecté
if(!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Vous devez être connecté pour passer commande.";
    header("Location: ../users/login.php");
    exit();
}

// Vérifier que le panier n'est pas vide
if(!isset($_SESSION['panier']) || empty($_SESSION['panier'])) {
    header("Location: index.php");
    exit();
}

include_once '../../config/database.php';
include_once '../../models/Produit.php';
include_once '../../models/user.php';

$database = new Database();
$db = $database->getConnection();
$produitModel = new Produit($db);
$userModel = new User($db);

// Récupérer les infos utilisateur
$user = $userModel->emailExists();
if(!$user) {
    $query = "SELECT * FROM users WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Calculer le total du panier
$panierItems = array();
$total = 0;

foreach($_SESSION['panier'] as $produit_id => $quantite) {
    $produit = $produitModel->lireUn($produit_id);
    if($produit) {
        $produit['quantite'] = $quantite;
        $produit['sous_total'] = $produit['prix'] * $quantite;
        $total += $produit['sous_total'];
        $panierItems[] = $produit;
    }
}

include '../templates/header.php';
?>

<div class="row">
    <!-- Formulaire de paiement -->
    <div class="col-md-7">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0"><i class="fas fa-credit-card"></i> Informations de paiement</h4>
            </div>
            <div class="card-body">
                
                <!-- Adresse de livraison -->
                <div class="mb-4">
                    <h5 class="border-bottom pb-2">
                        <i class="fas fa-map-marker-alt"></i> Adresse de livraison
                    </h5>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="adresse_type" id="adresse_compte" checked>
                        <label class="form-check-label" for="adresse_compte">
                            <strong>Utiliser mon adresse enregistrée</strong>
                            <?php if(!empty($user['adresse'])): ?>
                                <p class="text-muted small mb-0 mt-1"><?= nl2br(htmlspecialchars($user['adresse'])) ?></p>
                            <?php else: ?>
                                <p class="text-danger small mb-0 mt-1">Aucune adresse enregistrée</p>
                            <?php endif; ?>
                        </label>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="adresse_type" id="adresse_autre">
                        <label class="form-check-label" for="adresse_autre">
                            <strong>Utiliser une autre adresse</strong>
                        </label>
                    </div>
                    
                    <div id="nouvelle_adresse" style="display: none;">
                        <textarea class="form-control" rows="3" placeholder="Entrez votre adresse complète..."></textarea>
                    </div>
                </div>
                
                <hr>
                
                <!-- Méthodes de paiement -->
                <div class="mb-4">
                    <h5 class="border-bottom pb-2">
                        <i class="fas fa-wallet"></i> Choisissez votre mode de paiement
                    </h5>
                    
                    <!-- PayPal -->
                    <div class="payment-method mb-3">
                        <input type="radio" class="btn-check" name="payment" id="paypal" checked>
                        <label class="btn btn-outline-primary w-100 text-start d-flex align-items-center" for="paypal">
                            <i class="fab fa-paypal fa-2x me-3" style="color: #003087;"></i>
                            <div>
                                <strong>PayPal</strong>
                                <p class="mb-0 small text-muted">Payez rapidement et en toute sécurité</p>
                            </div>
                        </label>
                    </div>
                    
                    <!-- Apple Pay -->
                    <div class="payment-method mb-3">
                        <input type="radio" class="btn-check" name="payment" id="apple-pay">
                        <label class="btn btn-outline-primary w-100 text-start d-flex align-items-center" for="apple-pay">
                            <i class="fab fa-apple fa-2x me-3"></i>
                            <div>
                                <strong>Apple Pay</strong>
                                <p class="mb-0 small text-muted">Paiement rapide avec Touch ID ou Face ID</p>
                            </div>
                        </label>
                    </div>
                    
                    <!-- Google Pay -->
                    <div class="payment-method mb-3">
                        <input type="radio" class="btn-check" name="payment" id="google-pay">
                        <label class="btn btn-outline-primary w-100 text-start d-flex align-items-center" for="google-pay">
                            <i class="fab fa-google fa-2x me-3" style="color: #4285F4;"></i>
                            <div>
                                <strong>Google Pay</strong>
                                <p class="mb-0 small text-muted">Payez avec votre compte Google</p>
                            </div>
                        </label>
                    </div>
                    
                    <!-- Carte bancaire -->
                    <div class="payment-method mb-3">
                        <input type="radio" class="btn-check" name="payment" id="card">
                        <label class="btn btn-outline-primary w-100 text-start d-flex align-items-center" for="card">
                            <i class="fas fa-credit-card fa-2x me-3 text-success"></i>
                            <div>
                                <strong>Carte bancaire</strong>
                                <p class="mb-0 small text-muted">Visa, Mastercard, American Express</p>
                            </div>
                        </label>
                    </div>
                    
                    <!-- Informations carte bancaire (cachées par défaut) -->
                    <div id="card-details" style="display: none;">
                        <div class="card bg-light p-3 mt-3">
                            <div class="mb-3">
                                <label class="form-label small">Numéro de carte</label>
                                <input type="text" class="form-control" placeholder="1234 5678 9012 3456" maxlength="19">
                            </div>
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label small">Date d'expiration</label>
                                    <input type="text" class="form-control" placeholder="MM/AA" maxlength="5">
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label small">CVV</label>
                                    <input type="text" class="form-control" placeholder="123" maxlength="3">
                                </div>
                            </div>
                            <div class="mb-0">
                                <label class="form-label small">Nom sur la carte</label>
                                <input type="text" class="form-control" placeholder="JEAN DUPONT">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Virement bancaire -->
                    <div class="payment-method mb-3">
                        <input type="radio" class="btn-check" name="payment" id="bank-transfer">
                        <label class="btn btn-outline-primary w-100 text-start d-flex align-items-center" for="bank-transfer">
                            <i class="fas fa-university fa-2x me-3 text-info"></i>
                            <div>
                                <strong>Virement bancaire</strong>
                                <p class="mb-0 small text-muted">Paiement différé sous 2-3 jours</p>
                            </div>
                        </label>
                    </div>
                </div>
                
                <!-- Sécurité -->
                <div class="alert alert-success d-flex align-items-center">
                    <i class="fas fa-shield-alt fa-2x me-3"></i>
                    <div>
                        <strong>Paiement 100% sécurisé</strong>
                        <p class="mb-0 small">Vos informations sont cryptées et protégées</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Récapitulatif de commande -->
    <div class="col-md-5">
        <div class="card shadow-sm sticky-top" style="top: 20px;">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-shopping-bag"></i> Récapitulatif de commande</h5>
            </div>
            <div class="card-body">
                
                <!-- Liste des produits -->
                <div class="mb-3" style="max-height: 300px; overflow-y: auto;">
                    <?php foreach($panierItems as $item): ?>
                        <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                            <div class="position-relative me-3">
                                <?php 
                                    $img = !empty($item['image']) ? "../../public/images/produits/".$item['image'] : "https://via.placeholder.com/60";
                                ?>
                                <img src="<?= $img ?>" alt="<?= $item['nom'] ?>" 
                                     style="width: 60px; height: 60px; object-fit: contain;">
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success">
                                    <?= $item['quantite'] ?>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0"><?= htmlspecialchars($item['nom']) ?></h6>
                                <small class="text-muted"><?= htmlspecialchars($item['taille']) ?></small>
                            </div>
                            <div class="text-end">
                                <strong><?= number_format($item['sous_total'], 2) ?> €</strong>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Calculs -->
                <div class="mb-2 d-flex justify-content-between">
                    <span>Sous-total</span>
                    <span><?= number_format($total, 2) ?> €</span>
                </div>
                <div class="mb-2 d-flex justify-content-between">
                    <span>Livraison</span>
                    <span class="text-success fw-bold">Gratuite</span>
                </div>
                <div class="mb-3 d-flex justify-content-between">
                    <span>TVA (20%)</span>
                    <span><?= number_format($total * 0.2, 2) ?> €</span>
                </div>
                
                <hr>
                
                <div class="d-flex justify-content-between mb-4">
                    <strong class="fs-5">Total TTC</strong>
                    <strong class="fs-4 text-success"><?= number_format($total * 1.2, 2) ?> €</strong>
                </div>
                
                <!-- Bouton de paiement -->
                <button type="button" class="btn btn-success btn-lg w-100 mb-3" onclick="validerCommande()">
                    <i class="fas fa-lock"></i> Valider et payer
                </button>
                
                <a href="index.php" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-arrow-left"></i> Retour au panier
                </a>
                
                <!-- Garanties -->
                <div class="mt-4 pt-3 border-top">
                    <p class="small text-muted mb-2">
                        <i class="fas fa-check-circle text-success"></i> Livraison rapide sous 48h
                    </p>
                    <p class="small text-muted mb-2">
                        <i class="fas fa-check-circle text-success"></i> Retour gratuit sous 30 jours
                    </p>
                    <p class="small text-muted mb-0">
                        <i class="fas fa-check-circle text-success"></i> Garantie satisfait ou remboursé
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Afficher/masquer nouvelle adresse
document.getElementById('adresse_autre').addEventListener('change', function() {
    document.getElementById('nouvelle_adresse').style.display = 'block';
});
document.getElementById('adresse_compte').addEventListener('change', function() {
    document.getElementById('nouvelle_adresse').style.display = 'none';
});

// Afficher/masquer détails carte bancaire
document.getElementById('card').addEventListener('change', function() {
    document.getElementById('card-details').style.display = 'block';
});
document.querySelectorAll('input[name="payment"]').forEach(function(radio) {
    if(radio.id !== 'card') {
        radio.addEventListener('change', function() {
            document.getElementById('card-details').style.display = 'none';
        });
    }
});

// Validation de commande
function validerCommande() {
    // Vérifier l'adresse
    const adresseType = document.querySelector('input[name="adresse_type"]:checked');
    if(!adresseType) {
        alert('⚠️ Veuillez sélectionner une adresse de livraison');
        return;
    }
    
    // Vérifier le mode de paiement
    const payment = document.querySelector('input[name="payment"]:checked');
    if(!payment) {
        alert('⚠️ Veuillez choisir un mode de paiement');
        return;
    }
    
    const paymentId = payment.id;
    const paymentName = payment.nextElementSibling.querySelector('strong').textContent;
    
    // Animation de chargement
    const btn = event.target;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Redirection en cours...';
    btn.disabled = true;
    
    // Préparer les données de commande
    const montantTotal = <?= number_format($total * 1.2, 2, '.', '') ?>;
    
    // REDIRECTION SELON LE MODE DE PAIEMENT
    switch(paymentId) {
        case 'paypal':
            // Redirection vers PayPal
            redirectToPayPal(montantTotal);
            break;
            
        case 'apple-pay':
            // Redirection vers Apple Pay
            redirectToApplePay(montantTotal);
            break;
            
        case 'google-pay':
            // Redirection vers Google Pay
            redirectToGooglePay(montantTotal);
            break;
            
        case 'card':
            // Pour carte bancaire, utiliser Stripe
            redirectToStripe(montantTotal);
            break;
            
        case 'bank-transfer':
            // Afficher les instructions de virement
            showBankTransferInfo(montantTotal);
            break;
    }
}

// FONCTION PAYPAL - Redirection vers la vraie page PayPal
function redirectToPayPal(montant) {
    // URL de test PayPal Sandbox (tu peux créer un compte sur developer.paypal.com)
    const paypalUrl = 'https://www.paypal.com/checkoutnow?token=DEMO';
    
    // OU pour la vraie intégration, tu peux utiliser ce format :
    // const paypalUrl = `https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=ton-email@paypal.com&item_name=Commande FutsalShop&amount=${montant}&currency_code=EUR&return=http://localhost/cours/e-commerce/views/panier/success.php&cancel_return=http://localhost/cours/e-commerce/views/panier/index.php`;
    
    Swal.fire({
        title: 'Redirection vers PayPal',
        html: `
            <p>Vous allez être redirigé vers PayPal pour finaliser votre paiement de <strong>${montant} €</strong></p>
            <p class="small text-muted">Cliquez sur "Continuer" pour ouvrir PayPal</p>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Continuer vers PayPal',
        cancelButtonText: 'Annuler',
        confirmButtonColor: '#003087'
    }).then((result) => {
        if (result.isConfirmed) {
            // Ouvrir PayPal dans un nouvel onglet
            window.open('https://www.paypal.com', '_blank');
            
            // OU rediriger dans la même page
            // window.location.href = paypalUrl;
            
            // Message pour la démo
            setTimeout(() => {
                Swal.fire({
                    icon: 'info',
                    title: 'Mode Démo',
                    html: `
                        <p>En production, vous seriez redirigé vers PayPal avec ces informations :</p>
                        <ul class="text-start">
                            <li>Montant : <strong>${montant} €</strong></li>
                            <li>Marchand : FutsalShop</li>
                            <li>Description : Commande équipement futsal</li>
                        </ul>
                        <p class="small text-muted mt-3">Pour l'activer en vrai, il faut un compte PayPal Business</p>
                    `,
                    confirmButtonText: 'OK'
                });
            }, 1000);
        }
    });
}

// FONCTION GOOGLE PAY - Redirection vers Google Pay
function redirectToGooglePay(montant) {
    Swal.fire({
        title: 'Redirection vers Google Pay',
        html: `
            <div class="text-center">
                <i class="fab fa-google fa-3x mb-3" style="color: #4285F4;"></i>
                <p>Vous allez être redirigé vers Google Pay pour payer <strong>${montant} €</strong></p>
                <p class="small text-muted">Assurez-vous d'être connecté à votre compte Google</p>
            </div>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Ouvrir Google Pay',
        cancelButtonText: 'Annuler',
        confirmButtonColor: '#4285F4'
    }).then((result) => {
        if (result.isConfirmed) {
            // Ouvrir Google Pay
            window.open('https://pay.google.com', '_blank');
            
            // Message pour la démo
            setTimeout(() => {
                Swal.fire({
                    icon: 'info',
                    title: 'Mode Démo',
                    html: `
                        <p>En production, Google Pay s'ouvrirait avec :</p>
                        <ul class="text-start">
                            <li>Montant : <strong>${montant} €</strong></li>
                            <li>Marchand : FutsalShop</li>
                            <li>Paiement en 1 clic</li>
                        </ul>
                        <p class="small text-muted mt-3">Nécessite l'API Google Pay pour l'intégration complète</p>
                    `,
                    confirmButtonText: 'OK'
                });
            }, 1000);
        }
    });
}

// FONCTION APPLE PAY - Ouverture Apple Pay
function redirectToApplePay(montant) {
    Swal.fire({
        title: 'Apple Pay',
        html: `
            <div class="text-center">
                <i class="fab fa-apple fa-3x mb-3"></i>
                <p>Paiement de <strong>${montant} €</strong> via Apple Pay</p>
                <p class="small text-muted">Utilisez Touch ID ou Face ID pour confirmer</p>
            </div>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Ouvrir Apple Pay',
        cancelButtonText: 'Annuler'
    }).then((result) => {
        if (result.isConfirmed) {
            // Apple Pay nécessite Safari et un appareil Apple
            if (window.ApplePaySession && ApplePaySession.canMakePayments()) {
                // Lancer Apple Pay (nécessite configuration)
                alert('Apple Pay serait lancé ici avec ' + montant + ' €');
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Apple Pay non disponible',
                    html: `
                        <p>Apple Pay nécessite :</p>
                        <ul class="text-start">
                            <li>Un appareil Apple (iPhone, iPad, Mac)</li>
                            <li>Le navigateur Safari</li>
                            <li>Une carte enregistrée dans Wallet</li>
                        </ul>
                        <p class="small text-muted mt-3">Essayez un autre mode de paiement</p>
                    `
                });
            }
        }
    });
}

// FONCTION STRIPE - Pour carte bancaire
function redirectToStripe(montant) {
    Swal.fire({
        title: 'Paiement par carte',
        html: `
            <div class="text-center">
                <i class="fas fa-credit-card fa-3x mb-3 text-success"></i>
                <p>Redirection vers le paiement sécurisé</p>
                <p>Montant : <strong>${montant} €</strong></p>
            </div>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Continuer',
        cancelButtonText: 'Annuler',
        confirmButtonColor: '#28a745'
    }).then((result) => {
        if (result.isConfirmed) {
            // En production, rediriger vers Stripe Checkout
            // window.location.href = 'https://checkout.stripe.com/...';
            
            // Ouvrir Stripe dans un nouvel onglet
            window.open('https://stripe.com', '_blank');
            
            setTimeout(() => {
                Swal.fire({
                    icon: 'info',
                    title: 'Mode Démo',
                    html: `
                        <p>En production, Stripe ouvrirait une page sécurisée pour :</p>
                        <ul class="text-start">
                            <li>Saisir les coordonnées bancaires</li>
                            <li>Validation 3D Secure</li>
                            <li>Confirmation instantanée</li>
                        </ul>
                        <p class="small text-muted mt-3">Nécessite un compte Stripe (gratuit)</p>
                    `,
                    confirmButtonText: 'OK'
                });
            }, 1000);
        }
    });
}

// FONCTION VIREMENT BANCAIRE
function showBankTransferInfo(montant) {
    Swal.fire({
        title: 'Virement bancaire',
        html: `
            <div class="text-start">
                <p>Pour finaliser votre commande de <strong>${montant} €</strong>, effectuez un virement à :</p>
                <div class="card bg-light p-3 my-3">
                    <p class="mb-1"><strong>Bénéficiaire :</strong> FutsalShop SARL</p>
                    <p class="mb-1"><strong>IBAN :</strong> FR76 1234 5678 9012 3456 7890 123</p>
                    <p class="mb-1"><strong>BIC :</strong> BNPAFRPP</p>
                    <p class="mb-1"><strong>Référence :</strong> CMD<?= time() ?></p>
                    <p class="mb-0"><strong>Montant :</strong> ${montant} €</p>
                </div>
                <p class="small text-muted">⚠️ N'oubliez pas d'indiquer la référence de commande dans le libellé du virement</p>
                <p class="small">Votre commande sera expédiée dès réception du paiement (2-3 jours ouvrés)</p>
            </div>
        `,
        icon: 'info',
        confirmButtonText: 'J\'ai noté les informations',
        confirmButtonColor: '#17a2b8'
    }).then((result) => {
        if (result.isConfirmed) {
            // Vider le panier
            window.location.href = 'index.php?commande=en_attente';
        }
    });
}

// Formater le numéro de carte
document.querySelector('input[placeholder="1234 5678 9012 3456"]')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\s/g, '');
    let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
    e.target.value = formattedValue;
});

// Formater la date d'expiration
document.querySelector('input[placeholder="MM/AA"]')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if(value.length >= 2) {
        value = value.slice(0,2) + '/' + value.slice(2,4);
    }
    e.target.value = value;
});
</script>

<!-- SweetAlert2 pour les notifications -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php include '../templates/footer.php'; ?>
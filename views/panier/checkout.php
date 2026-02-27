<?php
session_start();

if(!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Vous devez être connecté pour passer commande.";
    header("Location: ../users/login.php");
    exit();
}

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

$user = $userModel->emailExists();
if(!$user) {
    $query = "SELECT * FROM users WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

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

// ── Transporteurs disponibles ──────────────────────────────────────────────
$transporteurs = [
    'point_relais' => [
        'nom'         => 'Point Relais',
        'service'     => 'Colissimo / Chronopost',
        'delai'       => '2 – 3 jours ouvrés',
        'prix'        => 3.99,
        'badge'       => 'eco',
        'badge_label' => '🌱 Éco',
        'icon'        => 'fas fa-map-marker-alt',
        'desc'        => 'Choisissez votre point de retrait sur la carte',
        'has_relais'  => true,
    ],
    'colissimo_domicile' => [
        'nom'         => 'Colissimo',
        'service'     => 'Domicile sans signature',
        'delai'       => '2 – 3 jours ouvrés',
        'prix'        => 4.99,
        'badge'       => '',
        'badge_label' => '',
        'icon'        => 'fas fa-home',
        'desc'        => 'Livraison dans votre boîte aux lettres',
    ],
    'ups_standard' => [
        'nom'         => 'UPS',
        'service'     => 'Standard France',
        'delai'       => '2 – 3 jours ouvrés',
        'prix'        => 6.50,
        'badge'       => '',
        'badge_label' => '',
        'icon'        => 'fas fa-box',
        'desc'        => 'Livraison à domicile avec suivi en temps réel',
    ],
    'chronopost_13h' => [
        'nom'         => 'Chronopost',
        'service'     => 'Express 13H',
        'delai'       => 'Lendemain avant 13h',
        'prix'        => 11.50,
        'badge'       => 'fast',
        'badge_label' => '⚡ Rapide',
        'icon'        => 'fas fa-bolt',
        'desc'        => 'Livraison garantie le lendemain avant 13h',
    ],
    'dhl_express' => [
        'nom'         => 'DHL Express',
        'service'     => 'Express Domestic',
        'delai'       => 'Lendemain avant 12h',
        'prix'        => 14.90,
        'badge'       => 'express',
        'badge_label' => '🔴 Express',
        'icon'        => 'fas fa-shipping-fast',
        'desc'        => 'Livraison express garantie le lendemain matin',
    ],
];

$seuilGratuit     = 49.00;
$livraisonGratuite = ($total >= $seuilGratuit);

include '../templates/header.php';
?>

<style>
/* ─── Transporteur cards ─── */
.liv-card {
    cursor: pointer;
    border: 2px solid #dee2e6;
    border-radius: 12px;
    padding: 13px 16px;
    margin-bottom: 9px;
    background: #fff;
    transition: border-color .18s, box-shadow .18s, background .18s;
    user-select: none;
}
.liv-card:hover  { border-color: #198754; box-shadow: 0 2px 10px rgba(25,135,84,.13); }
.liv-card.active { border-color: #198754; background: #f0faf4; box-shadow: 0 2px 14px rgba(25,135,84,.18); }
.liv-card input[type="radio"] { accent-color: #198754; width:17px; height:17px; }
.badge-eco     { background:#d1fae5; color:#065f46; font-size:11px; border-radius:20px; padding:2px 9px; font-weight:600; }
.badge-fast    { background:#fff7ed; color:#92400e; font-size:11px; border-radius:20px; padding:2px 9px; font-weight:600; }
.badge-express { background:#fee2e2; color:#991b1b; font-size:11px; border-radius:20px; padding:2px 9px; font-weight:600; }
.liv-price     { font-size:17px; font-weight:700; color:#198754; white-space:nowrap; min-width:68px; text-align:right; }
.liv-delay     { font-size:12px; color:#6c757d; margin-top:3px; }

/* ─── Point relais ─── */
#modal-relais .modal-dialog { max-width: 860px; }
.relais-map {
    background: #e8f4f8;
    border-radius: 10px;
    height: 320px;
    position: relative;
    overflow: hidden;
    border: 1px solid #c8dce6;
}
.relais-map-bg {
    width:100%; height:100%;
    background: linear-gradient(135deg,#daeaf5 0%,#c8dde8 50%,#d5e8d4 100%);
    position: absolute;
}
/* Routes simulées */
.relais-map::before {
    content:'';
    position:absolute;
    top:45%; left:0; right:0; height:6px;
    background:rgba(255,255,255,.7);
    transform: rotate(-3deg);
}
.relais-map::after {
    content:'';
    position:absolute;
    top:0; bottom:0; left:35%; width:5px;
    background:rgba(255,255,255,.6);
    transform: rotate(2deg);
}
.map-pin {
    position: absolute;
    cursor: pointer;
    transform: translate(-50%,-100%);
    transition: transform .15s;
    z-index: 10;
}
.map-pin:hover { transform: translate(-50%,-100%) scale(1.2); }
.map-pin.selected .pin-dot { background: #198754; border-color:#0f5132; }
.pin-dot {
    width:28px; height:28px;
    background:#dc3545;
    border:3px solid #8b0000;
    border-radius:50% 50% 50% 0;
    transform: rotate(-45deg);
    box-shadow: 0 2px 6px rgba(0,0,0,.3);
    transition: background .15s, border-color .15s;
}
.pin-dot.green { background:#198754; border-color:#0f5132; }
.pin-label {
    position:absolute;
    bottom: 34px;
    left:50%;
    transform:translateX(-50%);
    background:#fff;
    border:1px solid #ccc;
    border-radius:6px;
    padding:2px 7px;
    font-size:10px;
    font-weight:600;
    white-space:nowrap;
    box-shadow:0 1px 4px rgba(0,0,0,.15);
    display:none;
}
.map-pin:hover .pin-label { display:block; }
.map-pin.selected .pin-label { display:block; color:#198754; border-color:#198754; }
.relais-item {
    cursor:pointer;
    border:1px solid #dee2e6;
    border-radius:8px;
    padding:10px 12px;
    margin-bottom:8px;
    transition: border-color .15s, background .15s;
}
.relais-item:hover { border-color:#198754; background:#f8fffe; }
.relais-item.selected { border-color:#198754; background:#f0faf4; }
.relais-item .badge-open  { background:#d1fae5; color:#065f46; font-size:10px; border-radius:12px; padding:1px 7px; }
.relais-item .badge-close { background:#fee2e2; color:#991b1b; font-size:10px; border-radius:12px; padding:1px 7px; }
#selected-relais-info {
    border:2px solid #198754;
    border-radius:10px;
    padding:10px 14px;
    background:#f0faf4;
    display:none;
    margin-top:10px;
}
</style>

<div class="row">

    <!-- ════════════════════ COLONNE GAUCHE ════════════════════ -->
    <div class="col-md-7">

        <!-- Adresse de livraison -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i> Adresse de livraison</h4>
            </div>
            <div class="card-body">
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
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="adresse_type" id="adresse_autre">
                    <label class="form-check-label" for="adresse_autre"><strong>Utiliser une autre adresse</strong></label>
                </div>
                <div id="nouvelle_adresse" style="display:none; margin-top:12px;">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="new_prenom" placeholder="Prénom">
                                <label for="new_prenom">Prénom</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="new_nom" placeholder="Nom">
                                <label for="new_nom">Nom</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                <select class="form-select" id="new_pays">
                                    <option value="FR" selected>🇫🇷 France</option>
                                    <option value="BE">🇧🇪 Belgique</option>
                                    <option value="CH">🇨🇭 Suisse</option>
                                    <option value="LU">🇱🇺 Luxembourg</option>
                                    <option value="DE">🇩🇪 Allemagne</option>
                                    <option value="ES">🇪🇸 Espagne</option>
                                    <option value="IT">🇮🇹 Italie</option>
                                    <option value="GB">🇬🇧 Royaume-Uni</option>
                                    <option value="US">🇺🇸 États-Unis</option>
                                </select>
                                <label for="new_pays">Pays</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="new_rue" placeholder="Adresse">
                                <label for="new_rue">Adresse (numéro et rue)</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="new_complement" placeholder="Complément">
                                <label for="new_complement">Appartement, bâtiment, étage… (optionnel)</label>
                            </div>
                        </div>
                        <div class="col-7">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="new_ville" placeholder="Ville">
                                <label for="new_ville">Ville</label>
                            </div>
                        </div>
                        <div class="col-5">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="new_cp" placeholder="Code postal" maxlength="10">
                                <label for="new_cp">Code postal</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mode de livraison -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0"><i class="fas fa-truck me-2"></i> Mode de livraison</h4>
            </div>
            <div class="card-body">

                <?php if($livraisonGratuite): ?>
                <div class="alert alert-success d-flex align-items-center mb-3 py-2">
                    <i class="fas fa-gift me-2"></i>
                    <div><strong>Livraison offerte !</strong> Votre commande dépasse <?= number_format($seuilGratuit,0) ?> €.</div>
                </div>
                <?php else: $manque = $seuilGratuit - $total; ?>
                <div class="alert alert-info d-flex align-items-center mb-3 py-2">
                    <i class="fas fa-info-circle me-2"></i>
                    Plus que <strong>&nbsp;<?= number_format($manque,2) ?> €&nbsp;</strong> pour la livraison gratuite !
                </div>
                <?php endif; ?>

                <?php foreach($transporteurs as $key => $t):
                    $prix = $livraisonGratuite ? 0 : $t['prix'];
                ?>
                <div class="liv-card d-flex align-items-center gap-3"
                     id="card-<?= $key ?>"
                     onclick="selectLiv('<?= $key ?>', <?= $prix ?>)">

                    <input type="radio" name="livraison" id="liv-<?= $key ?>"
                           value="<?= $key ?>"
                           data-prix="<?= $prix ?>"
                           data-nom="<?= htmlspecialchars($t['nom'].' – '.$t['service']) ?>">

                    <div class="d-flex align-items-center justify-content-center"
                         style="width:42px; height:42px; background:#f8f9fa; border-radius:8px; flex-shrink:0;">
                        <i class="<?= $t['icon'] ?> fa-lg text-success"></i>
                    </div>

                    <div class="flex-grow-1 overflow-hidden">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <strong><?= $t['nom'] ?></strong>
                            <span class="text-secondary small">– <?= $t['service'] ?></span>
                            <?php if($t['badge']): ?>
                            <span class="badge-<?= $t['badge'] ?>"><?= $t['badge_label'] ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="liv-delay">
                            <i class="fas fa-clock me-1"></i><?= $t['delai'] ?>
                            &nbsp;·&nbsp;<span class="text-muted" style="font-size:11px;"><?= $t['desc'] ?></span>
                        </div>
                    </div>

                    <div class="liv-price">
                        <?php if($livraisonGratuite || $prix == 0): ?>Gratuit
                        <?php else: echo number_format($prix,2).' €'; endif; ?>
                    </div>
                </div>
                <?php if(!empty($t['has_relais'])): ?>
                <!-- Infos du point relais sélectionné -->
                <div id="selected-relais-info">
                    <div class="d-flex align-items-start gap-2">
                        <i class="fas fa-map-marker-alt text-success mt-1"></i>
                        <div>
                            <strong id="relais-nom-sel">—</strong>
                            <div class="small text-muted" id="relais-adresse-sel"></div>
                            <div class="small text-muted" id="relais-horaires-sel"></div>
                        </div>
                        <button class="btn btn-sm btn-outline-success ms-auto" onclick="ouvrirModalRelais(event)">
                            <i class="fas fa-exchange-alt me-1"></i>Changer
                        </button>
                    </div>
                </div>
                <!-- Bouton ouvrir carte si pas encore sélectionné -->
                <div id="btn-choisir-relais" class="mt-2 ms-1">
                    <button class="btn btn-outline-success btn-sm" onclick="ouvrirModalRelais(event)">
                        <i class="fas fa-map me-1"></i> Choisir un point relais sur la carte
                    </button>
                </div>
                <?php endif; ?>
                <?php endforeach; ?>

            </div>
        </div>

        <!-- Mode de paiement -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0"><i class="fas fa-credit-card me-2"></i> Mode de paiement</h4>
            </div>
            <div class="card-body">

                <div class="payment-method mb-3">
                    <input type="radio" class="btn-check" name="payment" id="paypal" checked>
                    <label class="btn btn-outline-primary w-100 text-start d-flex align-items-center" for="paypal">
                        <i class="fab fa-paypal fa-2x me-3" style="color:#003087;"></i>
                        <div><strong>PayPal</strong><p class="mb-0 small text-muted">Payez rapidement et en toute sécurité</p></div>
                    </label>
                </div>

                <div class="payment-method mb-3">
                    <input type="radio" class="btn-check" name="payment" id="apple-pay">
                    <label class="btn btn-outline-primary w-100 text-start d-flex align-items-center" for="apple-pay">
                        <i class="fab fa-apple fa-2x me-3"></i>
                        <div><strong>Apple Pay</strong><p class="mb-0 small text-muted">Touch ID ou Face ID</p></div>
                    </label>
                </div>

                <div class="payment-method mb-3">
                    <input type="radio" class="btn-check" name="payment" id="google-pay">
                    <label class="btn btn-outline-primary w-100 text-start d-flex align-items-center" for="google-pay">
                        <i class="fab fa-google fa-2x me-3" style="color:#4285F4;"></i>
                        <div><strong>Google Pay</strong><p class="mb-0 small text-muted">Payez avec votre compte Google</p></div>
                    </label>
                </div>

                <div class="payment-method mb-3">
                    <input type="radio" class="btn-check" name="payment" id="card">
                    <label class="btn btn-outline-primary w-100 text-start d-flex align-items-center" for="card">
                        <i class="fas fa-credit-card fa-2x me-3 text-success"></i>
                        <div><strong>Carte bancaire</strong><p class="mb-0 small text-muted">Visa, Mastercard, American Express</p></div>
                    </label>
                </div>

                <div id="card-details" style="display:none;">
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

                <div class="payment-method mb-3">
                    <input type="radio" class="btn-check" name="payment" id="bank-transfer">
                    <label class="btn btn-outline-primary w-100 text-start d-flex align-items-center" for="bank-transfer">
                        <i class="fas fa-university fa-2x me-3 text-info"></i>
                        <div><strong>Virement bancaire</strong><p class="mb-0 small text-muted">Paiement différé sous 2-3 jours</p></div>
                    </label>
                </div>

                <div class="alert alert-success d-flex align-items-center mt-3">
                    <i class="fas fa-shield-alt fa-2x me-3"></i>
                    <div><strong>Paiement 100% sécurisé</strong><p class="mb-0 small">Vos informations sont cryptées et protégées</p></div>
                </div>

            </div>
        </div>
    </div>

    <!-- ════════════════════ RÉCAPITULATIF ════════════════════ -->
    <div class="col-md-5">
        <div class="card shadow-sm sticky-top" style="top:20px;">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-shopping-bag"></i> Récapitulatif de commande</h5>
            </div>
            <div class="card-body">

                <div class="mb-3" style="max-height:280px; overflow-y:auto;">
                    <?php foreach($panierItems as $item): ?>
                    <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                        <div class="position-relative me-3">
                            <?php $img = !empty($item['image']) ? "../../public/images/produits/".$item['image'] : "https://via.placeholder.com/60"; ?>
                            <img src="<?= $img ?>" alt="<?= $item['nom'] ?>" style="width:60px;height:60px;object-fit:contain;">
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success"><?= $item['quantite'] ?></span>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0"><?= htmlspecialchars($item['nom']) ?></h6>
                            <small class="text-muted"><?= htmlspecialchars($item['taille']) ?></small>
                        </div>
                        <div class="text-end"><strong><?= number_format($item['sous_total'],2) ?> €</strong></div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- ── Code promo & Carte cadeau ── -->
                <div class="border rounded mb-3 overflow-hidden">
                    <!-- Code promo -->
                    <div onclick="toggleAccordion('promo-body','promo-chevron')"
                         class="d-flex justify-content-between align-items-center px-3 py-3 border-bottom"
                         style="cursor:pointer; background:#fafafa;">
                        <span class="fw-semibold">🏷️ Ajouter une réduction / un code promo</span>
                        <i class="fas fa-chevron-down text-muted" id="promo-chevron" style="transition:transform .2s;"></i>
                    </div>
                    <div id="promo-body" style="display:none;" class="px-3 pb-3 pt-2 bg-white">
                        <div class="input-group mt-1">
                            <input type="text" class="form-control form-control-sm" id="promo-input"
                                   placeholder="Ex : FUTSAL2026, PACK2026, FLASH20…"
                                   oninput="this.value=this.value.toUpperCase()">
                            <button class="btn btn-success btn-sm px-3" onclick="appliquerPromo()">Appliquer</button>
                        </div>
                        <div id="promo-result" class="mt-2"></div>
                        <div class="mt-2">
                            <p class="text-muted mb-1" style="font-size:11px;">Codes disponibles (démo) :</p>
                            <div class="d-flex gap-1 flex-wrap">
                                <?php foreach(['FUTSAL2026','PACK2026','FLASH20','FREESHIP'] as $c): ?>
                                <span class="badge bg-light text-dark border" style="cursor:pointer;font-size:11px;"
                                      onclick="document.getElementById('promo-input').value='<?= $c ?>'; appliquerPromo()">
                                    <?= $c ?>
                                </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Carte cadeau -->
                    <div onclick="toggleAccordion('cadeau-body','cadeau-chevron')"
                         class="d-flex justify-content-between align-items-center px-3 py-3"
                         style="cursor:pointer; background:#fafafa;">
                        <span class="fw-semibold">🎁 Ajouter une carte cadeau</span>
                        <i class="fas fa-chevron-down text-muted" id="cadeau-chevron" style="transition:transform .2s;"></i>
                    </div>
                    <div id="cadeau-body" style="display:none;" class="px-3 pb-3 pt-2 bg-white border-top">
                        <div class="input-group mt-1">
                            <input type="text" class="form-control form-control-sm" id="cadeau-input"
                                   placeholder="Numéro de carte cadeau…"
                                   oninput="this.value=this.value.toUpperCase()">
                            <button class="btn btn-success btn-sm px-3" onclick="appliquerCadeau()">Appliquer</button>
                        </div>
                        <div id="cadeau-result" class="mt-2"></div>
                        <p class="text-muted mt-2 mb-0" style="font-size:11px;">Code démo : <strong>GIFT25</strong> (−25 €)</p>
                    </div>
                </div>

                <!-- Lignes de calcul -->
                <div class="mb-2 d-flex justify-content-between">
                    <span>Sous-total produits</span>
                    <span><?= number_format($total,2) ?> €</span>
                </div>
                <div id="recap-remise-row" class="mb-2 d-flex justify-content-between" style="display:none;">
                    <span class="text-danger" id="recap-remise-label">Réduction</span>
                    <span class="text-danger fw-bold" id="recap-remise-val"></span>
                </div>
                <div class="mb-1 d-flex justify-content-between">
                    <span>Livraison</span>
                    <span id="recap-livraison" class="fw-bold text-success">
                        <?= $livraisonGratuite ? 'Gratuite' : '— Choisissez —' ?>
                    </span>
                </div>
                <div class="mb-2">
                    <small id="recap-livraison-nom" class="text-muted fst-italic"></small>
                </div>
                <div class="mb-3 d-flex justify-content-between">
                    <span>TVA (20%)</span>
                    <span id="recap-tva">—</span>
                </div>

                <hr>

                <div class="d-flex justify-content-between mb-4">
                    <strong class="fs-5">Total TTC</strong>
                    <strong class="fs-4 text-success" id="recap-total">—</strong>
                </div>

                <button type="button" class="btn btn-success btn-lg w-100 mb-3" onclick="validerCommande()">
                    <i class="fas fa-lock"></i> Valider et payer
                </button>
                <a href="index.php" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-arrow-left"></i> Retour au panier
                </a>

                <div class="mt-4 pt-3 border-top">
                    <p class="small text-muted mb-2"><i class="fas fa-check-circle text-success"></i> Livraison selon le transporteur choisi</p>
                    <p class="small text-muted mb-2"><i class="fas fa-check-circle text-success"></i> Retour gratuit sous 30 jours</p>
                    <p class="small text-muted mb-0"><i class="fas fa-check-circle text-success"></i> Garantie satisfait ou remboursé</p>
                </div>

            </div>
        </div>
    </div>

</div>

<!-- ════════════════════ MODAL POINT RELAIS ════════════════════ -->
<div class="modal fade" id="modal-relais" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="fas fa-map-marker-alt me-2"></i> Choisir un point relais</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0">

        <!-- Barre de recherche -->
        <div class="p-3 border-bottom bg-light">
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input type="text" class="form-control" id="relais-search"
                   placeholder="Entrez votre ville ou code postal (ex: Paris, 75001)"
                   value="Paris">
            <button class="btn btn-success" onclick="rechercherRelais()">
              <i class="fas fa-map-marked-alt me-1"></i> Rechercher
            </button>
          </div>
        </div>

        <div class="row g-0" style="min-height:500px;">

          <!-- Carte simulée -->
          <div class="col-md-7 p-3">
            <div class="relais-map" id="relais-map">
              <div class="relais-map-bg"></div>
              <!-- Les pins sont injectés par JS -->
            </div>
            <p class="small text-muted mt-2 mb-0">
              <i class="fas fa-info-circle me-1"></i>
              Carte de démonstration — En production, intégrez l'API Google Maps ou Leaflet.js avec les vrais points relais.
            </p>
          </div>

          <!-- Liste des points relais -->
          <div class="col-md-5 border-start" style="overflow-y:auto; max-height:540px;">
            <div class="p-3">
              <h6 class="text-muted mb-3" id="relais-count">Points relais disponibles</h6>
              <div id="relais-list">
                <!-- injecté par JS -->
              </div>
            </div>
          </div>

        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="button" class="btn btn-success" id="btn-confirmer-relais" disabled onclick="confirmerRelais()">
          <i class="fas fa-check me-1"></i> Confirmer ce point relais
        </button>
      </div>
    </div>
  </div>
</div>

<script>
// ── Point Relais — données simulées ────────────────────────────────────────
const pointsRelaisData = {
    'paris': [
        { id:1, nom:'Tabac Le Central',      adresse:'12 Rue de Rivoli, 75001 Paris',      type:'Tabac',          distance:'0,3 km', horaires:'Lun–Sam 8h–20h · Dim 9h–13h', open:true,  x:'28%', y:'52%' },
        { id:2, nom:'Bureau de Poste Louvre', adresse:'52 Rue du Louvre, 75001 Paris',      type:'Bureau de Poste',distance:'0,5 km', horaires:'Lun–Ven 8h–19h · Sam 8h–12h', open:true,  x:'38%', y:'38%' },
        { id:3, nom:'Carrefour City Marais',  adresse:'4 Rue Saint-Antoine, 75004 Paris',   type:'Supermarché',    distance:'0,8 km', horaires:'Lun–Dim 7h–22h',               open:true,  x:'58%', y:'44%' },
        { id:4, nom:'Librairie Mollat',       adresse:'91 Rue Montmartre, 75002 Paris',     type:'Librairie',      distance:'1,1 km', horaires:'Lun–Sam 9h30–19h',             open:false, x:'45%', y:'22%' },
        { id:5, nom:'Boulangerie Gontran',    adresse:'37 Rue Beaubourg, 75003 Paris',      type:'Boulangerie',    distance:'1,3 km', horaires:'Mar–Dim 7h30–20h',             open:true,  x:'66%', y:'30%' },
        { id:6, nom:'Pharmacie République',   adresse:'18 Place de la République, 75011',   type:'Pharmacie',      distance:'1,6 km', horaires:'Lun–Ven 8h–20h · Sam 9h–18h', open:true,  x:'78%', y:'55%' },
        { id:7, nom:'Fnac Paris Châtelet',    adresse:'1 Rue Pierre Lescot, 75001 Paris',   type:'Grande Surface', distance:'0,6 km', horaires:'Lun–Sam 10h–20h · Dim 11h–19h',open:true, x:'32%', y:'65%' },
    ],
    'lyon': [
        { id:1, nom:'Tabac Bellecour',        adresse:'8 Place Bellecour, 69002 Lyon',      type:'Tabac',          distance:'0,2 km', horaires:'Lun–Sam 7h–21h',               open:true,  x:'35%', y:'48%' },
        { id:2, nom:'Bureau de Poste Part-Dieu',adresse:'24 Rue du Dr Bouchut, 69003 Lyon', type:'Bureau de Poste',distance:'0,9 km', horaires:'Lun–Ven 8h–18h · Sam 8h–12h', open:true,  x:'62%', y:'35%' },
        { id:3, nom:'Relay Perrache',         adresse:'Gare de Perrache, 69002 Lyon',       type:'Relay',          distance:'1,1 km', horaires:'Lun–Dim 5h30–22h',             open:true,  x:'28%', y:'72%' },
        { id:4, nom:'Carrefour Express Croix-Rousse',adresse:'12 Bvd de la Croix-Rousse',  type:'Supermarché',    distance:'1,4 km', horaires:'Lun–Dim 7h–22h',               open:false, x:'50%', y:'20%' },
    ],
    'default': [
        { id:1, nom:'Bureau de Poste Principal', adresse:'Place du Centre, Ville',          type:'Bureau de Poste',distance:'0,4 km', horaires:'Lun–Ven 8h–18h',               open:true,  x:'40%', y:'45%' },
        { id:2, nom:'Tabac-Presse Dupont',       adresse:'3 Rue Principale, Ville',         type:'Tabac',          distance:'0,7 km', horaires:'Lun–Sam 6h30–20h',             open:true,  x:'55%', y:'30%' },
        { id:3, nom:'Supermarché Proxy',         adresse:'15 Avenue des Fleurs, Ville',     type:'Supermarché',    distance:'1,0 km', horaires:'Lun–Dim 7h30–21h',             open:true,  x:'70%', y:'60%' },
        { id:4, nom:'Pharmacie du Centre',       adresse:'7 Rue de la Mairie, Ville',       type:'Pharmacie',      distance:'1,3 km', horaires:'Lun–Ven 9h–19h · Sam 9h–13h', open:false, x:'30%', y:'65%' },
    ]
};

const typeIcons = {
    'Bureau de Poste': '📮',
    'Tabac':           '🚬',
    'Supermarché':     '🛒',
    'Librairie':       '📚',
    'Boulangerie':     '🥖',
    'Pharmacie':       '💊',
    'Grande Surface':  '🏬',
    'Relay':           '📰',
};

let relaisActifs   = [];
let relaisSelected = null;
let relaisTempSelection = null;

function getRelaisKey(ville) {
    const v = ville.toLowerCase().trim();
    if(v.includes('paris') || v.includes('750')) return 'paris';
    if(v.includes('lyon')  || v.includes('690')) return 'lyon';
    return 'default';
}

function rechercherRelais() {
    const ville = document.getElementById('relais-search').value || 'Paris';
    const key   = getRelaisKey(ville);
    relaisActifs = pointsRelaisData[key];
    relaisTempSelection = null;
    document.getElementById('btn-confirmer-relais').disabled = true;
    renderMap();
    renderList();
}

function renderMap() {
    const map = document.getElementById('relais-map');
    // Vider les pins existants
    map.querySelectorAll('.map-pin').forEach(el => el.remove());

    relaisActifs.forEach(r => {
        const pin = document.createElement('div');
        pin.className = 'map-pin' + (relaisTempSelection?.id === r.id ? ' selected' : '');
        pin.style.left = r.x;
        pin.style.top  = r.y;
        pin.dataset.id = r.id;
        pin.onclick    = () => selectRelaisTemp(r.id);
        pin.innerHTML  = `
            <div class="pin-label">${r.nom}</div>
            <div class="pin-dot${r.open ? ' green' : ''}"></div>
        `;
        map.appendChild(pin);
    });
}

function renderList() {
    const list = document.getElementById('relais-list');
    document.getElementById('relais-count').textContent =
        relaisActifs.length + ' points relais trouvés';

    list.innerHTML = relaisActifs.map(r => `
        <div class="relais-item${relaisTempSelection?.id === r.id ? ' selected' : ''}"
             id="relais-item-${r.id}"
             onclick="selectRelaisTemp(${r.id})">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong>${typeIcons[r.type] || '📍'} ${r.nom}</strong>
                    <span class="${r.open ? 'badge-open' : 'badge-close'} ms-2">
                        ${r.open ? '● Ouvert' : '● Fermé'}
                    </span>
                </div>
                <span class="text-muted small">${r.distance}</span>
            </div>
            <div class="small text-muted mt-1">${r.adresse}</div>
            <div class="small text-muted">${r.horaires}</div>
        </div>
    `).join('');
}

function selectRelaisTemp(id) {
    relaisTempSelection = relaisActifs.find(r => r.id === id);
    document.getElementById('btn-confirmer-relais').disabled = false;
    // Mettre à jour visuels map
    document.querySelectorAll('.map-pin').forEach(el => {
        el.classList.toggle('selected', parseInt(el.dataset.id) === id);
    });
    // Mettre à jour visuels liste
    document.querySelectorAll('.relais-item').forEach(el => {
        el.classList.toggle('selected', el.id === 'relais-item-' + id);
    });
    // Scroll vers l'item dans la liste
    const item = document.getElementById('relais-item-' + id);
    if(item) item.scrollIntoView({ behavior:'smooth', block:'nearest' });
}

function ouvrirModalRelais(event) {
    event.stopPropagation();
    // S'assurer que la carte Point Relais est sélectionnée
    selectLiv('point_relais', <?= $livraisonGratuite ? 0 : 3.99 ?>);
    rechercherRelais();
    const modal = new bootstrap.Modal(document.getElementById('modal-relais'));
    modal.show();
}

function confirmerRelais() {
    if(!relaisTempSelection) return;
    relaisSelected = relaisTempSelection;

    // Afficher infos dans le checkout
    document.getElementById('relais-nom-sel').textContent     = relaisSelected.nom;
    document.getElementById('relais-adresse-sel').textContent = relaisSelected.adresse;
    document.getElementById('relais-horaires-sel').textContent= relaisSelected.horaires;
    document.getElementById('selected-relais-info').style.display = 'block';
    document.getElementById('btn-choisir-relais').style.display   = 'none';

    // Fermer modal
    bootstrap.Modal.getInstance(document.getElementById('modal-relais')).hide();
}

// ── Variables PHP → JS ──────────────────────────────────────────────────────
const sousTotal        = <?= number_format($total, 5, '.', '') ?>;
const livraisonGratuite = <?= $livraisonGratuite ? 'true' : 'false' ?>;

let prixLivraison = <?= $livraisonGratuite ? '0' : 'null' ?>;
let nomLivraison  = <?= $livraisonGratuite ? '"Gratuite (commande ≥ 49 €)"' : 'null' ?>;
let remiseValeur  = 0;   // montant déduit (€)
let remisePourcent= 0;   // % si applicable
let remiseLabel   = '';
let livraisonOfferte = false; // via code FREESHIP

// ── Codes promo valides (démo) ──────────────────────────────────────────────
const codesPromo = {
    'FUTSAL2026': { type:'percent', valeur:10, label:'Code FUTSAL2026 (−10% sur tout le site)' },
    'PACK2026':   { type:'percent', valeur:15, label:'Code PACK2026 (−15% sur les packs)' },
    'FLASH20':    { type:'percent', valeur:20, label:'Code FLASH20 (−20% aujourd\'hui seulement ⚡)' },
    'FREESHIP':   { type:'ship',    valeur:0,  label:'Code FREESHIP (livraison offerte)' },
};
const cartesCadeau = {
    'GIFT25': { valeur:25, label:'Carte cadeau (−25 €)' },
    'GIFT10': { valeur:10, label:'Carte cadeau (−10 €)' },
};

// ── Accordéon ───────────────────────────────────────────────────────────────
function toggleAccordion(bodyId, chevronId) {
    const body    = document.getElementById(bodyId);
    const chevron = document.getElementById(chevronId);
    const open    = body.style.display !== 'none';
    body.style.display = open ? 'none' : 'block';
    if(chevron) chevron.style.transform = open ? '' : 'rotate(180deg)';
}

// ── Appliquer code promo ─────────────────────────────────────────────────────
function appliquerPromo() {
    const code = document.getElementById('promo-input').value.trim().toUpperCase();
    const div  = document.getElementById('promo-result');

    if(!code) { div.innerHTML = '<small class="text-danger">Veuillez entrer un code.</small>'; return; }

    if(codesPromo[code]) {
        const promo = codesPromo[code];
        // Annuler l'ancienne remise promo (pas cadeau)
        if(promo.type === 'ship') {
            livraisonOfferte = true;
            remisePourcent = 0;
            if(remiseLabel.startsWith('Code')) { remiseValeur = 0; remiseLabel = ''; }
        } else if(promo.type === 'percent') {
            livraisonOfferte = false;
            remisePourcent   = promo.valeur;
            remiseValeur     = parseFloat((sousTotal * promo.valeur / 100).toFixed(2));
            remiseLabel      = promo.label;
        } else {
            livraisonOfferte = false;
            remisePourcent   = 0;
            remiseValeur     = promo.valeur;
            remiseLabel      = promo.label;
        }
        div.innerHTML = `<small class="text-success"><i class="fas fa-check-circle me-1"></i>${promo.label} appliqué !</small>`;
        updateRecap();
    } else {
        div.innerHTML = '<small class="text-danger"><i class="fas fa-times-circle me-1"></i>Code invalide ou expiré.</small>';
    }
}

// ── Appliquer carte cadeau ───────────────────────────────────────────────────
function appliquerCadeau() {
    const code = document.getElementById('cadeau-input').value.trim().toUpperCase();
    const div  = document.getElementById('cadeau-result');

    if(!code) { div.innerHTML = '<small class="text-danger">Veuillez entrer un numéro de carte.</small>'; return; }

    if(cartesCadeau[code]) {
        const c = cartesCadeau[code];
        // Additionner avec la remise actuelle (si déjà une remise fixe)
        remiseValeur = parseFloat((remiseValeur + c.valeur).toFixed(2));
        remiseLabel  = remiseLabel ? remiseLabel + ' + ' + c.label : c.label;
        div.innerHTML = `<small class="text-success"><i class="fas fa-check-circle me-1"></i>${c.label} appliquée !</small>`;
        updateRecap();
    } else {
        div.innerHTML = '<small class="text-danger"><i class="fas fa-times-circle me-1"></i>Carte invalide ou déjà utilisée.</small>';
    }
}

// ── Sélection transporteur ──────────────────────────────────────────────────
function selectLiv(key, prix) {
    document.querySelectorAll('.liv-card').forEach(el => el.classList.remove('active'));
    document.getElementById('card-' + key).classList.add('active');
    document.getElementById('liv-' + key).checked = true;

    prixLivraison = parseFloat(prix);
    nomLivraison  = document.getElementById('liv-' + key).dataset.nom;
    updateRecap();
}

function updateRecap() {
    if (prixLivraison === null) return;

    const livEffective = (livraisonOfferte || livraisonGratuite) ? 0 : prixLivraison;
    const base  = Math.max(0, sousTotal - remiseValeur);
    const tva   = (base + livEffective) * 0.20;
    const total = base + livEffective + tva;

    // Afficher / cacher ligne remise
    const remiseRow = document.getElementById('recap-remise-row');
    if(remiseValeur > 0 || livraisonOfferte) {
        remiseRow.style.display = 'flex';
        document.getElementById('recap-remise-label').textContent = remiseLabel || 'Réduction';
        document.getElementById('recap-remise-val').textContent   =
            remiseValeur > 0 ? '−' + remiseValeur.toFixed(2) + ' €' : 'Livraison offerte';
    } else {
        remiseRow.style.display = 'none';
    }

    document.getElementById('recap-livraison').textContent =
        (livraisonOfferte || livraisonGratuite || livEffective === 0)
        ? 'Gratuite' : livEffective.toFixed(2) + ' €';
    document.getElementById('recap-livraison').style.color = '#198754';
    document.getElementById('recap-livraison-nom').textContent = nomLivraison;
    document.getElementById('recap-tva').textContent   = tva.toFixed(2) + ' €';
    document.getElementById('recap-total').textContent = total.toFixed(2) + ' €';
}

// Auto-sélection si livraison gratuite
<?php if($livraisonGratuite): ?>
document.addEventListener('DOMContentLoaded', () => {
    const first = document.querySelector('.liv-card');
    if(first) first.click();
});
<?php endif; ?>

// ── Adresse ─────────────────────────────────────────────────────────────────
document.getElementById('adresse_autre').addEventListener('change', () =>
    document.getElementById('nouvelle_adresse').style.display = 'block');
document.getElementById('adresse_compte').addEventListener('change', () =>
    document.getElementById('nouvelle_adresse').style.display = 'none');

// ── Carte bancaire ───────────────────────────────────────────────────────────
document.getElementById('card').addEventListener('change', () =>
    document.getElementById('card-details').style.display = 'block');
document.querySelectorAll('input[name="payment"]').forEach(r => {
    if(r.id !== 'card') r.addEventListener('change', () =>
        document.getElementById('card-details').style.display = 'none');
});

// ── Validation ───────────────────────────────────────────────────────────────
function validerCommande() {
    if(!document.querySelector('input[name="adresse_type"]:checked')) {
        alert('⚠️ Veuillez sélectionner une adresse de livraison'); return;
    }
    if(!document.querySelector('input[name="livraison"]:checked')) {
        Swal.fire({ icon:'warning', title:'Transporteur manquant',
            text:'Veuillez choisir un mode de livraison.', confirmButtonColor:'#198754' }); return;
    }
    // Vérifier qu'un point relais a été choisi
    const livraisonVal = document.querySelector('input[name="livraison"]:checked')?.value;
    if(livraisonVal === 'point_relais' && !relaisSelected) {
        Swal.fire({ icon:'warning', title:'Point relais non sélectionné',
            html:'Veuillez <strong>choisir un point relais</strong> sur la carte avant de continuer.',
            confirmButtonColor:'#198754',
            confirmButtonText:'Choisir un point relais'
        }).then(() => ouvrirModalRelais({ stopPropagation: ()=>{} }));
        return;
    }
    const payment = document.querySelector('input[name="payment"]:checked');
    if(!payment) { alert('⚠️ Veuillez choisir un mode de paiement'); return; }

    const btn = event.target;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Redirection...';
    btn.disabled  = true;

    const montant = ((sousTotal + prixLivraison) * 1.2).toFixed(2);

    switch(payment.id) {
        case 'paypal':        redirectToPayPal(montant);    break;
        case 'apple-pay':     redirectToApplePay(montant);  break;
        case 'google-pay':    redirectToGooglePay(montant); break;
        case 'card':          redirectToStripe(montant);    break;
        case 'bank-transfer': showBankTransfer(montant);    break;
        default: resetBtn();
    }
}

function resetBtn() {
    const btn = document.querySelector('.btn-success.btn-lg');
    if(btn) { btn.innerHTML = '<i class="fas fa-lock"></i> Valider et payer'; btn.disabled = false; }
}

// ── Paiements ────────────────────────────────────────────────────────────────
function redirectToPayPal(m) {
    Swal.fire({ title:'Redirection vers PayPal',
        html:`<p>Paiement de <strong>${m} €</strong> via PayPal</p>`,
        icon:'info', showCancelButton:true, confirmButtonText:'Continuer', cancelButtonText:'Annuler',
        confirmButtonColor:'#003087'
    }).then(r => {
        if(r.isConfirmed) { window.open('https://www.paypal.com','_blank'); setTimeout(() => Swal.fire({ icon:'info', title:'Mode Démo', html:`<p>En production vous seriez redirigé vers PayPal avec <strong>${m} €</strong>.</p>` }), 800); }
        else resetBtn();
    });
}
function redirectToGooglePay(m) {
    Swal.fire({ title:'Google Pay', html:`<p>Paiement de <strong>${m} €</strong></p>`,
        icon:'info', showCancelButton:true, confirmButtonText:'Ouvrir Google Pay', cancelButtonText:'Annuler',
        confirmButtonColor:'#4285F4'
    }).then(r => {
        if(r.isConfirmed) { window.open('https://pay.google.com','_blank'); setTimeout(() => Swal.fire({ icon:'info', title:'Mode Démo', html:`<p>Montant : <strong>${m} €</strong> — Nécessite l'API Google Pay.</p>` }), 800); }
        else resetBtn();
    });
}
function redirectToApplePay(m) {
    Swal.fire({ title:'Apple Pay', html:`<p>Paiement de <strong>${m} €</strong></p>`,
        icon:'info', showCancelButton:true, confirmButtonText:'Ouvrir Apple Pay', cancelButtonText:'Annuler'
    }).then(r => {
        if(r.isConfirmed) {
            if(window.ApplePaySession && ApplePaySession.canMakePayments()) alert('Apple Pay lancé — ' + m + ' €');
            else Swal.fire({ icon:'warning', title:'Apple Pay non disponible', html:'<p>Requiert Safari + appareil Apple + carte Wallet.</p>' });
        } else resetBtn();
    });
}
function redirectToStripe(m) {
    Swal.fire({ title:'Paiement par carte', html:`<p>Montant : <strong>${m} €</strong></p>`,
        icon:'info', showCancelButton:true, confirmButtonText:'Continuer', cancelButtonText:'Annuler',
        confirmButtonColor:'#28a745'
    }).then(r => {
        if(r.isConfirmed) { window.open('https://stripe.com','_blank'); setTimeout(() => Swal.fire({ icon:'info', title:'Mode Démo', html:`<p>Stripe sécuriserait le paiement de <strong>${m} €</strong>.</p>` }), 800); }
        else resetBtn();
    });
}
function showBankTransfer(m) {
    Swal.fire({ title:'Virement bancaire',
        html:`<div class="text-start"><p>Effectuez un virement de <strong>${m} €</strong> à :</p>
        <div class="card bg-light p-3 my-3">
            <p class="mb-1"><strong>Bénéficiaire :</strong> FutsalShop SARL</p>
            <p class="mb-1"><strong>IBAN :</strong> FR76 1234 5678 9012 3456 7890 123</p>
            <p class="mb-1"><strong>BIC :</strong> BNPAFRPP</p>
            <p class="mb-1"><strong>Référence :</strong> CMD<?= time() ?></p>
            <p class="mb-0"><strong>Montant :</strong> ${m} €</p>
        </div>
        <p class="small text-muted">⚠️ Indiquez la référence dans le libellé du virement.</p></div>`,
        icon:'info', confirmButtonText:"J'ai noté", confirmButtonColor:'#17a2b8'
    }).then(r => {
        if(r.isConfirmed) window.location.href = 'index.php?commande=en_attente';
        else resetBtn();
    });
}

// ── Format carte ─────────────────────────────────────────────────────────────
document.querySelector('input[placeholder="1234 5678 9012 3456"]')?.addEventListener('input', function(e) {
    let v = e.target.value.replace(/\s/g,'');
    e.target.value = v.match(/.{1,4}/g)?.join(' ') || v;
});
document.querySelector('input[placeholder="MM/AA"]')?.addEventListener('input', function(e) {
    let v = e.target.value.replace(/\D/g,'');
    if(v.length >= 2) v = v.slice(0,2)+'/'+v.slice(2,4);
    e.target.value = v;
});
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php include '../templates/footer.php'; ?>
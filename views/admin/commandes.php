<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-shopping-bag"></i> Gestion des Commandes</h1>
    </div>
    
    <div class="admin-panel">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Email</th>
                        <th>Total</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($commandes as $commande): ?>
                        <tr>
                            <td><strong>#<?php echo $commande['id']; ?></strong></td>
                            <td><?php echo htmlspecialchars($commande['prenom'] . ' ' . $commande['nom']); ?></td>
                            <td><?php echo htmlspecialchars($commande['email']); ?></td>
                            <td><strong><?php echo number_format($commande['total'], 2, ',', ' '); ?> €</strong></td>
                            <td>
                                <form action="index.php?controller=admin&action=updateStatut" method="POST" class="status-form">
                                    <input type="hidden" name="commande_id" value="<?php echo $commande['id']; ?>">
                                    <select name="statut" onchange="this.form.submit()" class="status-select <?php echo 'status-' . $commande['statut']; ?>">
                                        <option value="en_attente" <?php echo $commande['statut'] == 'en_attente' ? 'selected' : ''; ?>>
                                            En attente
                                        </option>
                                        <option value="confirmee" <?php echo $commande['statut'] == 'confirmee' ? 'selected' : ''; ?>>
                                            Confirmée
                                        </option>
                                        <option value="expediee" <?php echo $commande['statut'] == 'expediee' ? 'selected' : ''; ?>>
                                            Expédiée
                                        </option>
                                        <option value="livree" <?php echo $commande['statut'] == 'livree' ? 'selected' : ''; ?>>
                                            Livrée
                                        </option>
                                        <option value="annulee" <?php echo $commande['statut'] == 'annulee' ? 'selected' : ''; ?>>
                                            Annulée
                                        </option>
                                    </select>
                                </form>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($commande['date_commande'])); ?></td>
                            <td>
                                <button onclick="showCommandeDetails(<?php echo $commande['id']; ?>)" 
                                        class="btn-sm btn-primary" 
                                        title="Voir les détails">
                                    <i class="fas fa-eye"></i> Détails
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal pour les détails (simple exemple) -->
<div id="detailsModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Détails de la commande</h2>
        <div id="modalContent"></div>
    </div>
</div>

<style>
.status-form {
    margin: 0;
}

.status-select {
    padding: 8px 15px;
    border: 2px solid var(--border-color);
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.status-select:focus {
    outline: none;
    border-color: var(--primary-color);
}

.status-en_attente {
    background: #fff3cd;
    color: #856404;
    border-color: #ffc107;
}

.status-confirmee {
    background: #d1ecf1;
    color: #0c5460;
    border-color: #17a2b8;
}

.status-expediee {
    background: #cfe2ff;
    color: #084298;
    border-color: #0d6efd;
}

.status-livree {
    background: #d4edda;
    color: #155724;
    border-color: #28a745;
}

.status-annulee {
    background: #f8d7da;
    color: #721c24;
    border-color: #dc3545;
}

.modal {
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.7);
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background-color: white;
    padding: 30px;
    border-radius: 15px;
    max-width: 600px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    position: relative;
}

.close {
    position: absolute;
    right: 20px;
    top: 15px;
    font-size: 30px;
    font-weight: bold;
    cursor: pointer;
    color: #999;
}

.close:hover {
    color: #333;
}
</style>

<script>
function showCommandeDetails(id) {
    // Afficher les détails de la commande
    const modal = document.getElementById('detailsModal');
    modal.style.display = 'flex';
    
    document.getElementById('modalContent').innerHTML = `
        <p>Chargement des détails de la commande #${id}...</p>
        <p>Pour voir les détails complets, vous pouvez créer une page dédiée.</p>
    `;
}

function closeModal() {
    document.getElementById('detailsModal').style.display = 'none';
}

// Fermer le modal en cliquant en dehors
window.onclick = function(event) {
    const modal = document.getElementById('detailsModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}

// Confirmation avant changement de statut critique
document.querySelectorAll('.status-select').forEach(select => {
    const originalValue = select.value;
    
    select.addEventListener('change', function(e) {
        if (this.value === 'annulee') {
            if (!confirm('Êtes-vous sûr de vouloir annuler cette commande ?')) {
                this.value = originalValue;
                e.preventDefault();
                return false;
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
<?php echo $this->extend('layout'); ?>
<?php echo $this->section('content'); ?>

<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>

<style>
/* Surcharge légère pour adapter DataTables à ton thème "Premium Frost" */
.dataTable-table {
    border-collapse: collapse;
    width: 100%;
}

.dataTable-table th {
    background: var(--bg-body);
    color: var(--text-muted);
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    padding: 1.25rem 1.5rem;
}

.dataTable-table td {
    padding: 1.25rem 1.5rem;
    color: var(--text-main);
    border-bottom: 1px solid var(--border-color);
}

.dataTable-wrapper {
    background: var(--bg-card);
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
}

.dataTable-input {
    border: 1px solid var(--border-color);
    padding: 8px 12px;
    border-radius: var(--radius-md);
    background: var(--bg-body);
    color: var(--text-main);
}

.dataTable-selector {
    border: 1px solid var(--border-color);
    padding: 6px;
    border-radius: var(--radius-md);
    background: var(--bg-body);
    color: var(--text-main);
}
</style>

<div class="actions-container">
    <a href="<?php echo base_url('/'); ?>" class="btn btn-cancel">← Retour aux cartes</a>
</div>

<div class="container">
    <h2 style="margin-bottom: 2rem;">Gestion des Utilisateurs</h2>

    <div class="fade-in">
        <table id="myAdminTable" class="dataTable-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom d'utilisateur</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user) { ?>
                <tr class="<?php echo $user->isBanned() ? 'user-banned' : ''; ?>">
                    <td><?php echo esc($user->id); ?></td>
                    <td><strong><?php echo esc($user->username); ?></strong></td>
                    <td>
                        <?php if ($user->isBanned()) { ?>
                        <span class="status-badge banned">Suspendu</span>
                        <?php } else { ?>
                        <span class="status-badge active">Actif</span>
                        <?php } ?>
                    </td>
                    <td>
                        <div class="action-links">
                            <a href="<?php echo base_url('users/edit/'.$user->id); ?>" class="btn-action btn-edit">Modifier</a>
                            <?php if ($user->isBanned()) { ?>
                            <a href="<?php echo base_url('users/unban/'.$user->id); ?>" class="btn-action btn-unban"
                                onclick="return confirm('Réhabiliter cet utilisateur ?')">Débannir</a>
                            <?php } else { ?>
                            <a href="<?php echo base_url('users/delete/'.$user->id); ?>" class="btn-action btn-ban"
                                onclick="return confirm('Suspendre ce compte ?')">Bannir</a>
                            <?php } ?>
                        </div>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Initialisation de la DataTable
    const table = document.getElementById("myAdminTable");
    if (table) {
        new simpleDatatables.DataTable(table, {
            searchable: true,
            fixedHeight: false,
            perPage: 10,
            labels: {
                placeholder: "Rechercher un utilisateur...",
                perPage: "utilisateurs par page",
                noRows: "Aucun utilisateur trouvé",
                info: "Affichage de {start} à {end} sur {rows} utilisateurs",
            }
        });
    }
});
</script>

<?php echo $this->endSection(); ?>
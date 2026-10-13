<?php echo $this->extend('layout'); ?>

<?php echo $this->section('content'); ?>
<!-- Implémentation de Simple DataTables pour trier et paginer les utilisateurs (JS) -->
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>

<style>
    /* Intégration de Simple DataTables au thème du site */
    .dataTable-wrapper {
        font-family: inherit;
        color: var(--text-main);
    }
    .dataTable-top, .dataTable-bottom {
        padding: 1rem 0;
    }
    .dataTable-input {
        background-color: var(--bg-body);
        color: var(--text-main);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 8px 12px;
        outline: none;
        transition: border-color 0.2s;
    }
    .dataTable-input:focus {
        border-color: var(--primary);
    }
    .dataTable-selector {
        background-color: var(--bg-body);
        color: var(--text-main);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 6px;
        outline: none;
    }
    .dataTable-table {
        border-collapse: collapse;
    }
    .dataTable-table > thead > tr > th {
        border-bottom: 2px solid var(--border-color);
        color: var(--text-main);
        font-weight: 700;
        padding: 12px 10px;
    }
    .dataTable-table > tbody > tr > td {
        border-bottom: 1px solid var(--border-color);
        vertical-align: middle;
        padding: 12px 10px;
    }
    .dataTable-table > tbody > tr:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }
    [data-theme="dark"] .dataTable-table > tbody > tr:hover {
        background-color: rgba(255, 255, 255, 0.03);
    }
    .dataTable-info {
        color: var(--text-muted);
    }
    .dataTable-pagination a {
        background-color: var(--bg-body);
        color: var(--text-main);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-sm);
        transition: all 0.2s;
    }
    .dataTable-pagination a:hover {
        background-color: var(--border-color);
    }
    .dataTable-pagination .active a, 
    .dataTable-pagination .active a:hover {
        background-color: var(--primary);
        color: #fff;
        border-color: var(--primary);
    }

    /* Style des Badges et Boutons */
    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-block;
    }
    .status-badge.active {
        background-color: rgba(40, 167, 69, 0.15);
        color: #28a745;
    }
    .status-badge.banned {
        background-color: rgba(220, 53, 69, 0.15);
        color: #dc3545;
    }

    .action-links {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .btn-action {
        padding: 6px 12px;
        border-radius: var(--radius-md);
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
    }
    .btn-action:hover {
        transform: translateY(-1px);
        filter: brightness(1.1);
    }
    .btn-edit {
        background-color: rgba(255, 193, 7, 0.15);
        color: #e0a800;
        border: 1px solid rgba(255, 193, 7, 0.3);
    }
    .btn-ban {
        background-color: rgba(220, 53, 69, 0.15);
        color: #dc3545;
        border: 1px solid rgba(220, 53, 69, 0.3);
    }
    .btn-unban {
        background-color: rgba(40, 167, 69, 0.15);
        color: #28a745;
        border: 1px solid rgba(40, 167, 69, 0.3);
    }
</style>

<div class="actions-container">
    <a href="<?php echo base_url('/'); ?>" class="btn btn-cancel">Retour aux cartes</a>
</div>

<div class="container fade-in">
    <h2 class="header-title" style="margin-bottom: 2rem;">Gestion des Utilisateurs</h2>

    <?php if (session()->has('message')) { ?>
        <div class="alert alert-success"><?php echo esc(session('message')); ?></div>
    <?php } ?>
    <?php if (session()->has('error')) { ?>
        <div class="alert alert-danger"><?php echo esc(session('error')); ?></div>
    <?php } ?>

    <div class="card shadow-card">
        <div class="card-body" style="padding: 1.5rem;">
            <table id="myAdminTable" class="dataTable-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">ID</th>
                        <th style="width: 25%;">Nom d'utilisateur</th>
                        <th style="width: 15%;">Statut</th>
                        <th style="width: 35%;">Mail</th>
                        <th style="width: 20%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user) { ?>
                    <tr class="<?php echo $user->isBanned() ? 'user-banned' : ''; ?>">
                        <td style="color: var(--text-muted); font-weight: 600;"><?php echo esc($user->id); ?></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="var(--primary)" class="bi bi-person-circle" viewBox="0 0 16 16">
                                    <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/>
                                    <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1"/>
                                </svg>
                                <strong><?php echo esc($user->username); ?></strong>
                            </div>
                        </td>
                        <td>
                            <?php if ($user->isBanned()) { ?>
                            <span class="status-badge banned">Suspendu</span>
                            <?php } else { ?>
                            <span class="status-badge active">Actif</span>
                            <?php } ?>
                        </td>
                        <td style="color: var(--text-muted);"><?php echo esc($user->email); ?></td>
                        <td>
                            <div class="action-links">
                                <a href="<?php echo base_url('a/u/'.$user->id); ?>" class="btn-action btn-edit">Modifier</a>
                                <?php if ($user->isBanned()) { ?>
                                <a href="<?php echo base_url('a/u/'.$user->id.'/b'); ?>" class="btn-action btn-unban"
                                    onclick="return confirm('Réhabiliter cet utilisateur ?')">Débannir</a>
                                <?php } else { ?>
                                <a href="<?php echo base_url('a/u/'.$user->id.'/x'); ?>" class="btn-action btn-ban"
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
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Initialisation et traduction de Simple DataTables
    const table = document.getElementById("myAdminTable");
    if (table) {
        new simpleDatatables.DataTable(table, {
            searchable: true,
            fixedHeight: false,
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
<?php echo $this->extend('layout'); ?>

<?php echo $this->section('content'); ?>
<div class="actions-container">
    <a href="<?php echo base_url('a/sc/new'); ?>" class="btn btn-success">+ Ajouter une configuration</a>
    <a href="<?php echo base_url('/'); ?>" onclick="return retourAuxCartes(event, this.href);"
        class="btn btn-cancel">Retour aux cartes</a>
</div>

<div class="container fade-in">
    <h2 class="header-title" style="margin-bottom: 2rem;">Configuration des Domaines (Scraping)</h2>

    <?php if (session()->has('message')) { ?>
    <div class="alert alert-success"><?php echo esc(session('message')); ?></div>
    <?php } ?>

    <?php if (session()->has('error')) { ?>
    <div class="alert alert-danger"><?php echo esc(session('error')); ?></div>
    <?php } ?>

    <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Domaine</th>
                    <th>Regex Épisode</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($configs as $config) { ?>
                <tr>
                    <td><strong><?php echo esc($config['domain']); ?></strong></td>
                    <td style="font-family: monospace; color: var(--text-muted);">
                        <?php echo esc($config['regex_episode']); ?></td>
                    <td>
                        <?php if ($config['is_active']) { ?>
                        <span class="status-badge active">Actif</span>
                        <?php } else { ?>
                        <span class="status-badge banned">Inactif</span>
                        <?php } ?>
                    </td>
                    <td>
                        <div class="action-links">
                            <a href="<?php echo base_url('a/sc/'.$config['id']); ?>"
                                class="btn-action btn-edit">Modifier</a>
                            <a href="<?php echo base_url('a/sc/delete/'.$config['id']); ?>" class="btn-action btn-ban"
                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette configuration ?');">Supprimer</a>
                        </div>
                    </td>
                </tr>
                <?php } ?>
                <?php if (empty($configs)) { ?>
                <tr>
                    <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 2rem;">Aucune
                        configuration enregistrée.</td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<?php echo $this->endSection(); ?>
<?php echo $this->extend('layout'); ?>
<?php echo $this->section('content'); ?>
<div class="actions-container">
    <a href="<?php echo base_url('/'); ?>" class="btn btn-cancel">Retour aux cartes</a>
</div>

<!-- Bloc d'ajout -->
<div class="form-container card fade-in" style="margin-bottom: 3rem;">
    <h2 class="header-title">+ Surveiller une chaîne YouTube</h2>

    <?php if (session()->has('error')) { ?>
    <div class="alert alert-danger"><?php echo session('error'); ?></div>
    <?php } ?>
    <?php if (session()->has('message')) { ?>
    <div class="alert alert-success"><?php echo session('message'); ?></div>
    <?php } ?>

    <form action="<?php echo base_url('youtube/save'); ?>" method="POST">
        <?php echo csrf_field(); ?>

        <div class="form-group">
            <label for="channel_name" class="form-label">Nom de la chaîne (Pour l'affichage)</label>
            <input type="text" id="channel_name" name="channel_name" class="form-control" placeholder="Ex: Joyca"
                required>
        </div>

        <div class="form-group" style="margin-bottom: 2rem;">
            <label for="channel_id" class="form-label">ID de la chaîne *</label>
            <input type="text" id="channel_id" name="channel_id" class="form-control" placeholder="Ex: UCV9M..."
                required>
            <small style="display: block; margin-top: 8px; color: var(--text-muted);">
                Astuce : L'ID de chaîne de 24 caractères (commençant souvent par UC) peut être trouvé dans le code
                source de la chaîne YouTube ou via des outils en ligne.
            </small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-success" style="width: 100%;">Activer la surveillance RSS</button>
        </div>
    </form>
</div>

<!-- Bloc de liste et suppression -->
<div class="container" style="max-width: 800px; margin: 0 auto 3rem auto;">
    <h3 style="margin-bottom: 1.5rem; color: var(--text-main);">Chaînes en cours de surveillance</h3>

    <?php if (empty($channels)) { ?>
    <div class="empty-state" style="padding: 2rem;">
        <p>Aucune chaîne n'est surveillée pour le moment.</p>
    </div>
    <?php } else { ?>
    <div class="admin-table-container fade-in">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Nom de la chaîne</th>
                    <th>ID YouTube</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($channels as $channel) { ?>
                <tr>
                    <td><strong><?php echo esc($channel['channel_name']); ?></strong></td>
                    <td style="font-family: monospace; font-size: 0.9em; color: var(--text-muted);">
                        <?php echo esc($channel['channel_id']); ?>
                    </td>
                    <td style="text-align: right;">
                        <div class="action-links" style="justify-content: flex-end;">
                            <a href="<?php echo base_url('youtube/delete/'.$channel['id']); ?>"
                                class="btn-action btn-ban"
                                onclick="return confirm('Ne plus surveiller les sorties de <?php echo esc(addslashes($channel['channel_name'])); ?> ?');">
                                Supprimer
                            </a>
                        </div>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <?php } ?>
</div>
<?php echo $this->endSection(); ?>
<?php echo $this->extend('layout'); ?>
<?php echo $this->section('content'); ?>
<div class="actions-container">
    <a href="<?php echo base_url('/'); ?>" class="btn btn-cancel">Retour aux cartes</a>
</div>

<div class="form-container card fade-in">
    <h2 class="header-title">+ Surveiller une chaîne YouTube</h2>

    <?php if (session()->has('error')) : ?>
    <div class="alert alert-danger"><?php echo session('error'); ?></div>
    <?php endif; ?>

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
<?php echo $this->endSection(); ?>
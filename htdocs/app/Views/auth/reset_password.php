<?php echo $this->extend(config('Auth')->views['layout']); ?>
<?php echo $this->section('title'); ?>Nouveau mot de passe<?php echo $this->endSection(); ?>

<?php echo $this->section('content'); ?>
<div style="display: flex; justify-content: center; align-items: center; min-height: 70vh; padding: 20px;">
    <div class="form-container fade-in" style="width: 100%; max-width: 450px; margin: 0; padding: 2.5rem 2rem;">
        <h2 class="header-title" style="font-size: 1.8rem; margin-bottom: 2rem;">Nouveau mot de passe</h2>
        
        <?php if (session()->has('message')): ?>
            <div class="alert alert-success"><?php echo esc(session('message')); ?></div>
        <?php endif; ?>
        <?php if (session()->has('errors')): ?>
            <div class="alert alert-danger">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach (session('errors') as $error): ?>
                        <li><?php echo esc($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?php echo base_url('forgot-password/reset'); ?>" method="post">
            <?php echo csrf_field(); ?>
            
            <div class="form-group password-wrapper">
                <label for="password" class="form-label">Nouveau mot de passe</label>
                <input type="password" class="form-control" id="password" name="password" minlength="8" placeholder="8 caractères minimum" required>
                <button type="button" class="password-toggle" aria-label="Afficher le mot de passe"></button>
            </div>
            
            <div class="form-group password-wrapper" style="margin-bottom: 2rem;">
                <label for="password_confirm" class="form-label">Confirmer le nouveau mot de passe</label>
                <input type="password" class="form-control" id="password_confirm" name="password_confirm" minlength="8" placeholder="Retapez votre mot de passe" required>
                <button type="button" class="password-toggle" aria-label="Afficher le mot de passe"></button>
            </div>
            
            <div style="margin-top: 1.5rem;">
                <button type="submit" class="btn btn-success" style="width: 100%; padding: 0.85rem; font-size: 1.05rem;">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
<?php echo $this->endSection(); ?>
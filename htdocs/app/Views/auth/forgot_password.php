<?php echo $this->extend(config('Auth')->views['layout']); ?>
<?php echo $this->section('title'); ?>Mot de passe oublié<?php echo $this->endSection(); ?>

<?php echo $this->section('content'); ?>
<div style="display: flex; justify-content: center; align-items: center; min-height: 70vh; padding: 20px;">
    <div class="form-container fade-in" style="width: 100%; max-width: 450px; margin: 0; padding: 2.5rem 2rem;">
        <h2 class="header-title" style="font-size: 1.8rem; margin-bottom: 2rem;">Mot de passe oublié</h2>
        
        <?php if (session()->has('error')): ?>
            <div class="alert alert-danger"><?php echo esc(session('error')); ?></div>
        <?php endif; ?>

        <p style="color: var(--text-muted); margin-bottom: 1.5rem; text-align: center;">Entrez l'adresse e-mail associée à votre compte. Nous vous y enverrons un code de réinitialisation.</p>

        <form action="<?php echo base_url('forgot-password'); ?>" method="post">
            <?php echo csrf_field(); ?>
            <div class="form-group">
                <label for="email" class="form-label">Adresse e-mail</label>
                <input type="email" class="form-control" id="email" name="email" inputmode="email" autocomplete="email" placeholder="nom@exemple.com" required>
            </div>
            
            <div style="margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.85rem; font-size: 1.05rem;">Envoyer le code</button>
            </div>
            
            <p style="text-align: center; margin-top: 2rem; margin-bottom: 0; color: var(--text-muted); font-size: 0.95rem;">
                <a href="<?php echo url_to('login'); ?>" style="font-weight: 700; color: var(--primary); text-decoration: underline transparent; transition: text-decoration-color 0.2s;">Retour à la connexion</a>
            </p>
        </form>
    </div>
</div>
<?php echo $this->endSection(); ?>
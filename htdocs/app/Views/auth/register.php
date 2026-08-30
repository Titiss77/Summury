<?php echo $this->extend(config('Auth')->views['layout']); ?>

<?php echo $this->section('title'); ?><?php echo lang('Auth.register'); ?> <?php echo $this->endSection(); ?>

<?php echo $this->section('content'); ?>
<div style="display: flex; justify-content: center; align-items: center; min-height: 70vh; padding: 20px;">

    <!-- Utilisation de form-container avec une largeur restreinte -->
    <div class="form-container fade-in" style="width: 100%; max-width: 480px; margin: 0; padding: 2.5rem 2rem;">

        <h2 class="header-title" style="font-size: 1.8rem; margin-bottom: 2rem;">Créer un compte</h2>

        <!-- Alertes d'erreurs -->
        <?php if (null !== session('error')) { ?>
        <div class="alert alert-danger" role="alert"><?php echo esc(session('error')); ?></div>
        <?php } elseif (null !== session('errors')) { ?>
        <div class="alert alert-danger" role="alert">
            <?php if (is_array(session('errors'))) { ?>
            <ul style="margin: 0; padding-left: 20px;">
                <?php foreach (session('errors') as $error) { ?>
                <li><?php echo esc($error); ?></li>
                <?php } ?>
            </ul>
            <?php } else { ?>
            <?php echo esc(session('errors')); ?>
            <?php } ?>
        </div>
        <?php } ?>

        <form action="<?php echo url_to('register'); ?>" method="post">
            <?php echo csrf_field(); ?>

            <div class="form-group">
                <label for="email" class="form-label">Adresse e-mail</label>
                <input type="email" class="form-control" id="email" name="email" inputmode="email" autocomplete="email"
                    placeholder="nom@exemple.com" value="<?php echo old('email'); ?>" required>
            </div>

            <div class="form-group">
                <label for="username" class="form-label">Nom d'utilisateur</label>
                <input type="text" class="form-control" id="username" name="username" inputmode="text"
                    autocomplete="username" placeholder="Choisissez un pseudo" value="<?php echo old('username'); ?>"
                    required>
            </div>

            <div class="form-group password-wrapper">
                <label for="password" class="form-label">Mot de passe</label>
                <input type="password" class="form-control" id="password" name="password" inputmode="text"
                    autocomplete="new-password" placeholder="8 caractères minimum" required>
                <button type="button" class="password-toggle" aria-label="Afficher le mot de passe"></button>
            </div>

            <div class="form-group password-wrapper" style="margin-bottom: 2rem;">
                <label for="password_confirm" class="form-label">Confirmer le mot de passe</label>
                <input type="password" class="form-control" id="password_confirm" name="password_confirm"
                    inputmode="text" autocomplete="new-password" placeholder="Retapez votre mot de passe" required>
                <button type="button" class="password-toggle" aria-label="Afficher le mot de passe"></button>
            </div>

            <div style="margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary"
                    style="width: 100%; padding: 0.85rem; font-size: 1.05rem;">S'inscrire</button>
            </div>

            <p
                style="text-align: center; margin-top: 2rem; margin-bottom: 0; color: var(--text-muted); font-size: 0.95rem;">
                Vous avez déjà un compte ? <a href="<?php echo url_to('login'); ?>"
                    style="font-weight: 700; color: var(--primary); text-decoration: underline transparent; transition: text-decoration-color 0.2s;">Se
                    connecter</a>
            </p>
        </form>
    </div>

</div>
<?php echo $this->endSection(); ?>
<?php echo $this->extend(config('Auth')->views['layout']); ?>

<?php echo $this->section('title'); ?><?php echo lang('Auth.login'); ?> <?php echo $this->endSection(); ?>

<?php echo $this->section('content'); ?>
<div style="display: flex; justify-content: center; align-items: center; min-height: 70vh; padding: 20px;">

    <!-- Utilisation de form-container avec une largeur restreinte -->
    <div class="form-container fade-in" style="width: 100%; max-width: 450px; margin: 0; padding: 2.5rem 2rem;">

        <h2 class="header-title" style="font-size: 1.8rem; margin-bottom: 2rem;">Connexion</h2>

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

        <form action="<?php echo url_to('login'); ?>" method="post">
            <?php echo csrf_field(); ?>

            <div class="form-group">
                <label for="email" class="form-label">Adresse e-mail</label>
                <input type="email" class="form-control" id="email" name="email" inputmode="email" autocomplete="email"
                    placeholder="nom@exemple.com" value="<?php echo old('email'); ?>" required>
            </div>

            <div class="form-group password-wrapper">
                <label for="password" class="form-label">Mot de passe</label>
                <input type="password" class="form-control" id="password" name="password" inputmode="text"
                    autocomplete="current-password" placeholder="Votre mot de passe" required>
                <!-- Le bouton de visibilité du mot de passe s'alignera parfaitement -->
                <button type="button" class="password-toggle" aria-label="Afficher le mot de passe"></button>
            </div>

            <?php if (setting('Auth.sessionConfig')['allowRemembering']) { ?>
            <div class="form-check" style="margin-top: 1rem; margin-bottom: 2rem;">
                <label class="form-check-label" style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" name="remember" class="form-check-input"
                        <?php if (old('remember', true)) { echo 'checked'; } ?>>
                    Se souvenir de moi
                </label>
            </div>
            <?php } ?>

            <div style="margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary"
                    style="width: 100%; padding: 0.85rem; font-size: 1.05rem;">Se connecter</button>
            </div>

            <?php if (setting('Auth.allowRegistration')) { ?>
            <p
                style="text-align: center; margin-top: 2rem; margin-bottom: 0; color: var(--text-muted); font-size: 0.95rem;">
                Pas encore de compte ? <a href="<?php echo url_to('register'); ?>"
                    style="font-weight: 700; color: var(--primary); text-decoration: underline transparent; transition: text-decoration-color 0.2s;">S'inscrire</a>
            </p>
            <?php } ?>
        </form>
    </div>

</div>
<?php echo $this->endSection(); ?>
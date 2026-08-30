<?php echo $this->extend('layout'); ?>

<?php echo $this->section('content'); ?>

<a href="<?php echo base_url('/'); ?>" class="btn btn-warning" style="margin-bottom: 20px;">Retour aux cartes</a>

<div class="container" style="max-width: 800px; margin: 0 auto; padding: 20px;">
    <h2 class="header-title" style="margin-bottom: 2rem;">Mon Profil</h2>

    <?php if (session()->has('message')) { ?>
    <div class="alert alert-success"><?php echo session('message'); ?></div>
    <?php } ?>
    <?php if (session()->has('error')) { ?>
    <div class="alert alert-danger"><?php echo session('error'); ?></div>
    <?php } ?>
    <?php if (session()->has('errors')) { ?>
    <div class="alert alert-danger">
        <ul style="margin:0; padding-left:20px;">
            <?php foreach (session('errors') as $error) { ?>
            <li><?php echo esc($error); ?></li>
            <?php } ?>
        </ul>
    </div>
    <?php } ?>

    <!-- Informations du compte -->
    <div class="card shadow-card" style="margin-bottom: 2rem;">
        <div class="card-body">
            <h3 style="margin-top: 0;">Informations du compte</h3>
            <p><strong>Nom d'utilisateur :</strong> <?php echo esc($user->username); ?></p>
            <p><strong>Adresse e-mail :</strong> <?php echo esc($user->email); ?></p>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="card shadow-card" style="margin-bottom: 2rem;">
        <div class="card-body">
            <h3 style="margin-top: 0;">Mes Statistiques</h3>
            <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 20px;">
                <p style="margin: 0;"><strong>Total de cartes en lignes:</strong> <span
                        class="badge badge-episode"><?php echo esc($totalItems); ?></span></p>
                <p style="margin: 0;"><strong>Vos cartes publiques :</strong> <span
                        class="badge badge-season"><?php echo esc($publicItems); ?></span></p>
            </div>

            <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 0 0 20px 0;">
            <h4 style="margin-top: 0; margin-bottom: 15px; font-size: 1rem; color: var(--text-main);">Répartition par
                statut</h4>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(110px, 1fr)); gap: 15px;">
                <div
                    style="background: var(--bg-body); padding: 15px 10px; border-radius: var(--radius-md); text-align: center; border: 1px solid var(--border-color);">
                    <div style="font-size: 1.8rem; font-weight: bold; color: var(--info); line-height: 1;">
                        <?php echo esc($statusAVoir); ?></div>
                    <div
                        style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; margin-top: 8px; font-weight: 500;">
                        À voir</div>
                </div>

                <div
                    style="background: var(--bg-body); padding: 15px 10px; border-radius: var(--radius-md); text-align: center; border: 1px solid var(--border-color);">
                    <div style="font-size: 1.8rem; font-weight: bold; color: var(--primary); line-height: 1;">
                        <?php echo esc($statusEnCours); ?></div>
                    <div
                        style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; margin-top: 8px; font-weight: 500;">
                        En cours</div>
                </div>

                <div
                    style="background: var(--bg-body); padding: 15px 10px; border-radius: var(--radius-md); text-align: center; border: 1px solid var(--border-color);">
                    <div style="font-size: 1.8rem; font-weight: bold; color: #f59e0b; line-height: 1;">
                        <?php echo esc($statusEnPause); ?></div>
                    <div
                        style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; margin-top: 8px; font-weight: 500;">
                        En pause</div>
                </div>

                <div
                    style="background: var(--bg-body); padding: 15px 10px; border-radius: var(--radius-md); text-align: center; border: 1px solid var(--border-color);">
                    <div style="font-size: 1.8rem; font-weight: bold; color: var(--success); line-height: 1;">
                        <?php echo esc($statusTermine); ?></div>
                    <div
                        style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; margin-top: 8px; font-weight: 500;">
                        Terminé</div>
                </div>

                <div
                    style="background: var(--bg-body); padding: 15px 10px; border-radius: var(--radius-md); text-align: center; border: 1px solid var(--border-color);">
                    <div style="font-size: 1.8rem; font-weight: bold; color: var(--text-muted); line-height: 1;">
                        <?php echo esc($statusAucun); ?></div>
                    <div
                        style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; margin-top: 8px; font-weight: 500;">
                        Aucun</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Changement de mot de passe -->
    <div class="card shadow-card">
        <div class="card-body">
            <h3 style="margin-top: 0;">Changer mon mot de passe</h3>
            <form action="<?php echo base_url('profile/update-password'); ?>" method="POST" style="margin-top: 15px;">
                <?php echo csrf_field(); ?>

                <div class="form-group password-wrapper" style="margin-bottom: 1.5rem;">
                    <label for="current_password" class="form-label">Mot de passe actuel</label>
                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                    <button type="button" class="password-toggle" aria-label="Afficher le mot de passe"></button>
                </div>

                <div class="form-group password-wrapper" style="margin-bottom: 1.5rem;">
                    <label for="new_password" class="form-label">Nouveau mot de passe</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" required
                        minlength="8">
                    <button type="button" class="password-toggle" aria-label="Afficher le mot de passe"></button>
                </div>

                <div class="form-group password-wrapper" style="margin-bottom: 1.5rem;">
                    <label for="confirm_password" class="form-label">Confirmer le nouveau mot de passe</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required
                        minlength="8">
                    <button type="button" class="password-toggle" aria-label="Afficher le mot de passe"></button>
                </div>

                <div class="form-actions" style="margin-top: 2rem; border-top: none;">
                    <button type="submit" class="btn btn-primary">Mettre à jour le mot de passe</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php echo $this->endSection(); ?>
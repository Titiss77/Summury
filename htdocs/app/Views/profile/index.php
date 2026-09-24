<?php echo $this->extend('layout'); ?>
<?php echo $this->section('content'); ?>

<div class="actions-container">
    <a href="<?php echo base_url('/'); ?>" class="btn btn-cancel">Retour aux cartes</a>
</div>

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

    <div class="card shadow-card" style="margin-bottom: 2rem;">
        <div class="card-body">
            <h3 style="margin-top: 0;">Informations du compte</h3>
            <p><strong>Nom d'utilisateur :</strong> <?php echo esc($user->username); ?></p>
            <p><strong>Adresse e-mail :</strong> <?php echo esc($user->email); ?></p>
        </div>
    </div>

    <!-- 1. Ajoute les badges globaux à ton conteneur flex existant (vers la ligne 28) -->
    <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 20px;">
        <p style="margin: 0;"><strong>Total de cartes en lignes:</strong> <span
                class="badge badge-episode"><?php echo esc($totalItems); ?></span></p>
        <p style="margin: 0;"><strong>Vos cartes publiques :</strong> <span
                class="badge badge-season"><?php echo esc($publicItems); ?></span></p>

        <!-- Nouveaux compteurs -->
        <p style="margin: 0;"><strong>Épisodes visionnés :</strong> <span class="badge"
                style="background-color: var(--primary); color: #fff; padding: 3px 8px; border-radius: var(--radius-md);"><?php echo esc($totalVus); ?></span>
        </p>
        <p style="margin: 0;"><strong>Épisodes restants à voir :</strong> <span class="badge"
                style="background-color: var(--warning); color: var(--text-main); padding: 3px 8px; border-radius: var(--radius-md);"><?php echo esc($totalRestants); ?></span>
        </p>
    </div>

    <!-- 2. Ajoute la nouvelle section détaillant les œuvres en cours -->
    <div class="card shadow-card" style="margin-bottom: 2rem;">
        <div class="card-body">
            <h3 style="margin-top: 0; margin-bottom: 15px;">Détail de progression (En cours)</h3>

            <?php if (empty($inProgressSeries)) { ?>
            <p style="color: var(--text-muted);">Aucune série ou animé en cours de visionnage avec un total d'épisodes
                défini.</p>
            <?php } else { ?>
            <div class="admin-table-container fade-in">
                <table class="admin-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr
                            style="background-color: var(--bg-body); border-bottom: 2px solid var(--border-color); color: var(--text-main);">
                            <th style="padding: 12px; text-align: left;">Titre</th>
                            <th style="padding: 12px; text-align: center;">Épisodes restants</th>
                            <th style="padding: 12px; text-align: center;">Saisons restantes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inProgressSeries as $series) { ?>
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <!-- Colonne 1 : Titre -->
                            <td style="padding: 12px;">
                                <strong><?php echo esc($series->titre); ?></strong>
                            </td>

                            <!-- Colonne 2 : Épisodes restants (Vue globale) -->
                            <td style="padding: 12px; text-align: center;">
                                <?php if (isset($series->vu_global) && isset($series->reste_global)) { ?>
                                <span
                                    style="font-size: 0.9em; color: var(--text-muted); display: block; margin-bottom: 4px;">
                                    (Vu : <?php echo esc($series->vu_global); ?>)
                                </span>
                                <span class="badge badge-episode"
                                    style="background-color: var(--warning); color: var(--text-main);">
                                    Reste : <?php echo esc($series->reste_global); ?>
                                </span>
                                <?php } else { ?>
                                <span style="color: var(--text-muted); font-size: 0.85em; font-style: italic;">
                                    Non synchronisé
                                </span>
                                <?php } ?>
                            </td>

                            <!-- Colonne 3 : Saisons restantes -->
                            <td style="padding: 12px; text-align: center;">
                                <?php if (!empty($series->total_saisons) && !empty($series->saison)) { ?>
                                <span class="badge badge-season"><?php echo esc($series->reste_s); ?></span>
                                <?php } else { ?>
                                <span style="color: var(--text-muted);">-</span>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <?php } ?>
        </div>
    </div>

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
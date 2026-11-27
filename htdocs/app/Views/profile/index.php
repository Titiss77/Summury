<?php echo $this->extend('layout'); ?>
<?php echo $this->section('content'); ?>

<?php $activeTab = $activeTab ?? 'profile'; ?>

<div class="actions-container">
    <a href="<?php echo base_url('/'); ?>" onclick="return retourAuxCartes(event, this.href);"
        class="btn btn-cancel">Retour aux cartes</a>
</div>

<div class="container profile-settings-layout fade-in">
    <!-- SIDEBAR DE NAVIGATION -->
    <aside class="profile-sidebar" aria-label="Navigation du profil">
        <div class="profile-nav-group">
            <span class="profile-nav-group-title">Général</span>
            <a href="<?php echo base_url('p'); ?>"
                class="profile-nav-item <?php echo 'profile' === $activeTab ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0" />
                    <path fill-rule="evenodd"
                        d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1" />
                </svg>
                <span>Profil &amp; Suivi</span>
            </a>
        </div>

        <div class="profile-nav-group">
            <span class="profile-nav-group-title">Sécurité du compte</span>
            <a href="<?php echo base_url('p?tab=email'); ?>"
                class="profile-nav-item <?php echo 'email' === $activeTab ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path
                        d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1zm13 2.383-4.708 2.825L15 11.105zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741M1 11.105l4.708-2.897L1 5.383z" />
                </svg>
                <span>Adresse e-mail</span>
            </a>
            <a href="<?php echo base_url('p?tab=password'); ?>"
                class="profile-nav-item <?php echo 'password' === $activeTab ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path
                        d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2m3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2" />
                </svg>
                <span>Mot de passe</span>
            </a>
        </div>

        <div class="profile-nav-group">
            <span class="profile-nav-group-title">Données &amp; RGPD</span>
            <a href="<?php echo base_url('p?tab=danger'); ?>"
                class="profile-nav-item profile-nav-item-danger <?php echo 'danger' === $activeTab ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path
                        d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2" />
                </svg>
                <span>Zone de danger</span>
            </a>
        </div>
    </aside>

    <!-- CONTENU PRINCIPAL DE L'ONGLET ACTIF -->
    <div class="profile-content">
        <?php if (session()->has('message')) { ?>
        <div class="alert alert-success">
            <?php echo esc(session('message')); ?>
        </div>
        <?php } ?>

        <?php if (session()->has('error')) { ?>
        <div class="alert alert-danger">
            <?php echo esc(session('error')); ?>
        </div>
        <?php } ?>

        <?php if (session()->has('errors')) { ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach (session('errors') as $error) { ?>
                <li><?php echo esc($error); ?></li>
                <?php } ?>
            </ul>
        </div>
        <?php } ?>

        <?php if ('profile' === $activeTab) { ?>
        <!-- ONGLET 1 : PROFIL & SUIVI (PAR DÉFAUT) -->
        <div class="profile-section-header">
            <h2 class="profile-section-title">Profil</h2>
            <p class="profile-section-subtitle">Informations du compte, statistiques et suivi de vos séries.</p>
        </div>

        <div class="card shadow-card profile-card">
            <div class="card-body">
                <h3>Informations du compte</h3>
                <div class="profile-info-grid">
                    <div class="profile-info-field">
                        <span class="profile-info-label">Nom d'utilisateur</span>
                        <div class="profile-info-value"><?php echo esc($user->username); ?></div>
                    </div>
                    <div class="profile-info-field">
                        <span class="profile-info-label">Adresse e-mail</span>
                        <div class="profile-info-value"><?php echo esc($user->email); ?></div>
                        <small class="profile-info-hint">
                            À modifier depuis <a href="<?php echo base_url('p?tab=email'); ?>">Adresse e-mail</a>.
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="profile-stats">
            <p><strong>Total de cartes en ligne :</strong> <span
                    class="badge badge-episode"><?php echo esc($totalItems); ?></span></p>
            <p><strong>Vos cartes publiques :</strong> <span
                    class="badge badge-season"><?php echo esc($publicItems); ?></span></p>
        </div>

        <?php $hasSeries = !empty($inProgressEnCoursSeries) || !empty($inProgressEnPauseSeries); ?>
        <div class="card shadow-card profile-card">
            <div class="card-body">
                <?php if (!$hasSeries) { ?>
                <p class="profile-empty">Aucune série ou animé en cours de visionnage avec un total d'épisodes défini.
                </p>
                <?php } else { ?>
                <?php
                $seriesSections = [
                    ['title' => 'Séries en cours', 'series' => $inProgressEnCoursSeries, 'episodes' => $totalEpisodesEnCours, 'seasons' => $totalSeriesEnCours, 'empty' => 'Aucune série en cours'],
                    ['title' => 'Séries en pause', 'series' => $inProgressEnPauseSeries, 'episodes' => $totalEpisodesEnPause, 'seasons' => $totalSeriesEnPause, 'empty' => 'Aucune série en pause'],
                ];
                ?>
                <?php foreach ($seriesSections as $section) { ?>
                <section class="profile-series-section">
                    <h3><?php echo esc($section['title']); ?></h3>
                    <div class="profile-summary">
                        <p>Épisodes restants : <span
                                class="badge badge-episode"><?php echo esc($section['episodes']); ?></span></p>
                        <p>Saisons restantes : <span
                                class="badge badge-season"><?php echo esc($section['seasons']); ?></span></p>
                    </div>
                    <div class="admin-table-container fade-in">
                        <table class="admin-table profile-series-table">
                            <thead>
                                <tr>
                                    <th>Titre</th>
                                    <th>Épisodes restants</th>
                                    <th>Saisons restantes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($section['series'])) { ?>
                                <tr>
                                    <td colspan="3" class="profile-table-empty"><?php echo esc($section['empty']); ?>
                                    </td>
                                </tr>
                                <?php } else { ?>
                                <?php foreach ($section['series'] as $series) { ?>
                                <tr>
                                    <td><strong><?php echo esc($series->titre); ?></strong></td>
                                    <td class="profile-table-center">
                                        <?php if (null !== $series->reste_global) { ?>
                                        <span class="badge badge-episode">Reste :
                                            <?php echo esc($series->reste_global); ?></span>
                                        <?php } else { ?><span class="profile-not-synced">Non
                                            synchronisé</span><?php } ?>
                                    </td>
                                    <td class="profile-table-center">
                                        <?php if (null !== $series->reste_s) { ?>
                                        <span class="badge badge-season"><?php echo esc($series->reste_s); ?></span>
                                        <?php } else { ?><span class="profile-not-synced">-</span><?php } ?>
                                    </td>
                                </tr>
                                <?php } } ?>
                            </tbody>
                        </table>
                    </div>
                </section>
                <?php } } ?>
            </div>
        </div>

        <?php } elseif ('email' === $activeTab) { ?>
        <!-- ONGLET 2 : ADRESSE E-MAIL -->
        <div class="profile-section-header">
            <h2 class="profile-section-title">Adresse e-mail</h2>
            <p class="profile-section-subtitle">Mettez à jour l'adresse e-mail associée à votre compte.</p>
        </div>

        <div class="card shadow-card profile-card">
            <div class="card-body">
                <h3>Changer mon adresse e-mail</h3>
                <p class="profile-current-meta">Adresse actuelle : <strong><?php echo esc($user->email); ?></strong></p>
                <form action="<?php echo base_url('update-email'); ?>" method="POST" class="profile-password-form"
                    autocomplete="off">
                    <?php echo csrf_field(); ?>
                    <div class="form-group">
                        <label for="new_email" class="form-label">Nouvelle adresse e-mail</label>
                        <input type="email" id="new_email" name="new_email" class="form-control"
                            placeholder="nom@exemple.com" required>
                    </div>
                    <div class="form-group password-wrapper">
                        <label for="password_for_email" class="form-label">Mot de passe actuel (pour sécurité)</label>
                        <input type="password" id="password_for_email" name="password_for_email" class="form-control"
                            placeholder="Votre mot de passe actuel" autocomplete="new-password" data-bwignore="true"
                            data-lpignore="true" data-1p-ignore="true" required>
                        <button type="button" class="password-toggle" aria-label="Afficher le mot de passe"></button>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Mettre à jour l'e-mail</button>
                    </div>
                </form>
            </div>
        </div>

        <?php } elseif ('password' === $activeTab) { ?>
        <!-- ONGLET 3 : MOT DE PASSE -->
        <div class="profile-section-header">
            <h2 class="profile-section-title">Mot de passe</h2>
            <p class="profile-section-subtitle">Modifiez votre mot de passe pour renforcer la sécurité de votre compte.
            </p>
        </div>

        <div class="card shadow-card profile-card">
            <div class="card-body">
                <h3>Changer mon mot de passe</h3>
                <form action="<?php echo base_url('pw'); ?>" method="POST" class="profile-password-form"
                    autocomplete="off">
                    <?php echo csrf_field(); ?>
                    <div class="form-group password-wrapper">
                        <label for="current_password" class="form-label">Mot de passe actuel</label>
                        <input type="password" id="current_password" name="current_password" class="form-control"
                            required>
                        <button type="button" class="password-toggle" aria-label="Afficher le mot de passe"></button>
                    </div>
                    <div class="form-group password-wrapper">
                        <label for="new_password" class="form-label">Nouveau mot de passe</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" minlength="12"
                            placeholder="12 caractères minimum" autocomplete="new-password" data-bwignore="true"
                            data-lpignore="true" data-1p-ignore="true" required>
                        <button type="button" class="password-toggle" aria-label="Afficher le mot de passe"></button>
                    </div>
                    <div class="form-group password-wrapper">
                        <label for="confirm_password" class="form-label">Confirmer le nouveau mot de passe</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                            minlength="12" placeholder="12 caractères minimum" autocomplete="new-password"
                            data-bwignore="true" data-lpignore="true" data-1p-ignore="true" required>
                        <button type="button" class="password-toggle" aria-label="Afficher le mot de passe"></button>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Mettre à jour le mot de passe</button>
                    </div>
                </form>
            </div>
        </div>

        <?php } elseif ('danger' === $activeTab) { ?>
        <!-- ONGLET 4 : ZONE DE DANGER (RGPD) -->
        <div class="profile-section-header">
            <h2 class="profile-section-title profile-section-title-danger">Zone de danger</h2>
            <p class="profile-section-subtitle">Suppression définitive de votre compte et de vos données personnelles.
            </p>
        </div>

        <div class="card shadow-card profile-card profile-card-danger">
            <div class="card-body">
                <h3 style="color: var(--danger);">Supprimer mon compte</h3>
                <p style="color: var(--text-muted); font-size: 0.9rem;">
                    Conformément au RGPD, vous disposez d'un droit d'effacement. La suppression de votre compte effacera
                    définitivement toutes vos cartes privées, brouillons et données personnelles de nos serveurs.
                    <strong>Cette action est irréversible.</strong>
                </p>
                <form action="<?php echo base_url('delete-account'); ?>" method="POST" class="profile-password-form"
                    autocomplete="off"
                    onsubmit="return confirm('Êtes-vous absolument sûr de vouloir supprimer définitivement votre compte et vos données ?');">
                    <?php echo csrf_field(); ?>
                    <div class="form-group password-wrapper">
                        <label for="password_for_deletion" class="form-label">Confirmez votre mot de passe pour
                            continuer</label>
                        <input type="password" id="password_for_deletion" name="password_for_deletion"
                            class="form-control" placeholder="Votre mot de passe" autocomplete="new-password"
                            data-bwignore="true" data-lpignore="true" data-1p-ignore="true" required>
                        <button type="button" class="password-toggle" aria-label="Afficher le mot de passe"></button>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn"
                            style="background-color: var(--danger); color: #fff;">Supprimer
                            définitivement mon compte</button>
                    </div>
                </form>
            </div>
        </div>
        <?php } ?>
    </div>
</div>
<?php echo $this->endSection(); ?>
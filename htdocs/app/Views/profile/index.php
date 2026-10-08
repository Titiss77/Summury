<?php echo $this->extend('layout'); ?>
<?php echo $this->section('content'); ?>

<div class="actions-container">
    <a href="<?php echo base_url('/'); ?>" class="btn btn-cancel">
        Retour aux cartes
    </a>
</div>

<div class="container profile-container">

    <h2 class="header-title">Mon Profil</h2>

    <?php if (session()->has('message')): ?>
    <div class="alert alert-success">
        <?php echo esc(session('message')); ?>
    </div>
    <?php endif; ?>

    <?php if (session()->has('error')): ?>
    <div class="alert alert-danger">
        <?php echo esc(session('error')); ?>
    </div>
    <?php endif; ?>

    <?php if (session()->has('errors')): ?>
    <div class="alert alert-danger">
        <ul>
            <?php foreach (session('errors') as $error): ?>
            <li><?php echo esc($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>


    <!-- INFORMATIONS DU COMPTE -->
    <div class="card shadow-card profile-card">
        <div class="card-body">

            <h3>Informations du compte</h3>

            <p>
                <strong>Nom d'utilisateur :</strong>
                <?php echo esc($user->username); ?>
            </p>

            <p>
                <strong>Adresse e-mail :</strong>
                <?php echo esc($user->email); ?>
            </p>

        </div>
    </div>


    <!-- STATISTIQUES DES CARTES -->
    <div class="profile-stats">

        <p>
            <strong>Total de cartes en ligne :</strong>
            <span class="badge badge-episode">
                <?php echo esc($totalItems); ?>
            </span>
        </p>

        <p>
            <strong>Vos cartes publiques :</strong>
            <span class="badge badge-season">
                <?php echo esc($publicItems); ?>
            </span>
        </p>

    </div>


    <!-- SÉRIES -->
    <?php
    $hasSeries =
        !empty($inProgressEnCoursSeries) ||
        !empty($inProgressEnPauseSeries);
    ?>

    <div class="card shadow-card profile-card">

        <div class="card-body">

            <?php if (!$hasSeries): ?>

            <p class="profile-empty">
                Aucune série ou animé en cours de visionnage avec
                un total d'épisodes défini.
            </p>

            <?php else: ?>

            <?php
                $seriesSections = [
                    [
                        'title' => 'Séries en cours',
                        'series' => $inProgressEnCoursSeries,
                        'episodes' => $totalEpisodesEnCours,
                        'seasons' => $totalSeriesEnCours,
                        'empty' => 'Aucune série en cours',
                    ],
                    [
                        'title' => 'Séries en pause',
                        'series' => $inProgressEnPauseSeries,
                        'episodes' => $totalEpisodesEnPause,
                        'seasons' => $totalSeriesEnPause,
                        'empty' => 'Aucune série en pause',
                    ],
                ];
                ?>

            <?php foreach ($seriesSections as $section): ?>

            <section class="profile-series-section">

                <h3>
                    <?php echo esc($section['title']); ?>
                </h3>

                <div class="profile-summary">

                    <p>
                        Épisodes restants :
                        <span class="badge badge-episode">
                            <?php echo esc($section['episodes']); ?>
                        </span>
                    </p>

                    <p>
                        Saisons restantes :
                        <span class="badge badge-season">
                            <?php echo esc($section['seasons']); ?>
                        </span>
                    </p>

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

                            <?php if (empty($section['series'])): ?>

                            <tr>
                                <td colspan="3" class="profile-table-empty">
                                    <?php echo esc($section['empty']); ?>
                                </td>
                            </tr>

                            <?php else: ?>

                            <?php foreach ($section['series'] as $series): ?>

                            <tr>

                                <td>
                                    <strong>
                                        <?php echo esc($series->titre); ?>
                                    </strong>
                                </td>


                                <td class="profile-table-center">

                                    <?php if ($series->reste_global !== null): ?>

                                    <span class="badge badge-episode">
                                        Reste :
                                        <?php echo esc($series->reste_global); ?>
                                    </span>

                                    <?php else: ?>

                                    <span class="profile-not-synced">
                                        Non synchronisé
                                    </span>

                                    <?php endif; ?>

                                </td>


                                <td class="profile-table-center">

                                    <?php if ($series->reste_s !== null): ?>

                                    <span class="badge badge-season">
                                        <?php echo esc($series->reste_s); ?>
                                    </span>

                                    <?php else: ?>

                                    <span class="profile-not-synced">
                                        -
                                    </span>

                                    <?php endif; ?>

                                </td>

                            </tr>

                            <?php endforeach; ?>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </section>

            <?php endforeach; ?>

            <?php endif; ?>

        </div>

    </div>


    <!-- MOT DE PASSE -->
    <div class="card shadow-card profile-card">

        <div class="card-body">

            <h3>Changer mon mot de passe</h3>

            <form action="<?php echo base_url('pw'); ?>" method="POST" class="profile-password-form">

                <?php echo csrf_field(); ?>


                <div class="form-group password-wrapper">

                    <label for="current_password" class="form-label">
                        Mot de passe actuel
                    </label>

                    <input type="password" id="current_password" name="current_password" class="form-control" required>

                    <button type="button" class="password-toggle" aria-label="Afficher le mot de passe"></button>

                </div>


                <div class="form-group password-wrapper">

                    <label for="new_password" class="form-label">
                        Nouveau mot de passe
                    </label>

                    <input type="password" id="new_password" name="new_password" class="form-control" minlength="8"
                        required>

                    <button type="button" class="password-toggle" aria-label="Afficher le mot de passe"></button>

                </div>


                <div class="form-group password-wrapper">

                    <label for="confirm_password" class="form-label">
                        Confirmer le nouveau mot de passe
                    </label>

                    <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                        minlength="8" required>

                    <button type="button" class="password-toggle" aria-label="Afficher le mot de passe"></button>

                </div>


                <div class="form-actions">

                    <button type="submit" class="btn btn-primary">
                        Mettre à jour le mot de passe
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<?php echo $this->endSection(); ?>
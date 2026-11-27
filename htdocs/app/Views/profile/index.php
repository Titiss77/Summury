<?php echo $this->extend('layout'); ?>
<?php echo $this->section('content'); ?>

<?php
$activeTab = $activeTab ?? 'profile';
$userInitial = mb_strtoupper(mb_substr((string) ($user->username ?? 'U'), 0, 1));
?>

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
            <a href="<?php echo base_url('p?tab=contributions'); ?>"
                class="profile-nav-item <?php echo 'contributions' === $activeTab ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path
                        d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v13.5a.5.5 0 0 1-.777.416L8 13.101l-5.223 2.815A.5.5 0 0 1 2 15.5zm2-1a1 1 0 0 0-1 1v12.566l4.723-2.482a.5.5 0 0 1 .554 0L13 14.566V2a1 1 0 0 0-1-1z" />
                </svg>
                <span>Contributions</span>
            </a>
        </div>

        <div class="profile-nav-group">
            <span class="profile-nav-group-title">Préférences &amp; Données</span>
            <a href="<?php echo base_url('p?tab=display'); ?>"
                class="profile-nav-item <?php echo 'display' === $activeTab ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path
                        d="M11.5 2a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M9.05 3a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0V3zM4.5 7a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M2.05 8a2.5 2.5 0 0 1 4.9 0H16v1H6.95a2.5 2.5 0 0 1-4.9 0H0V8zm9.45 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3m-2.45 1a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0v-1z" />
                </svg>
                <span>Affichage</span>
            </a>
            <a href="<?php echo base_url('p?tab=data'); ?>"
                class="profile-nav-item <?php echo 'data' === $activeTab ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path
                        d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5" />
                    <path
                        d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z" />
                </svg>
                <span>Bibliothèque &amp; Données</span>
            </a>
        </div>

        <div class="profile-nav-group">
            <span class="profile-nav-group-title">Compte</span>
            <a href="<?php echo base_url('p?tab=security'); ?>"
                class="profile-nav-item <?php echo 'security' === $activeTab ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path
                        d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2m3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2" />
                </svg>
                <span>Sécurité &amp; Activité</span>
            </a>
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

    <!-- CONTENU PRINCIPAL -->
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
        <!-- =========================================
             ONGLET 1 : PROFIL & SUIVI (PAR DÉFAUT)
             ========================================= -->
        <div class="profile-section-header">
            <h2 class="profile-section-title">Profil</h2>
            <p class="profile-section-subtitle">Carte de membre, répartition de votre collection et suivi des séries.
            </p>
        </div>

        <!-- CARTE DE MEMBRE -->
        <div class="card shadow-card profile-member-card">
            <div class="card-body">
                <span class="profile-member-kicker">Carte de membre</span>
                <div class="profile-member-top">
                    <div class="profile-member-identity">
                        <div class="profile-avatar" aria-hidden="true"><?php echo esc($userInitial); ?></div>
                        <div>
                            <div class="profile-member-name"><?php echo esc($user->username); ?></div>
                            <div class="profile-member-role"><?php echo esc($roleLabel ?? 'Membre'); ?></div>
                        </div>
                    </div>
                    <div class="profile-member-number">
                        <span class="profile-member-number-prefix">n°</span>
                        <strong><?php echo esc(str_pad((string) $user->id, 4, '0', STR_PAD_LEFT)); ?></strong>
                    </div>
                </div>
                <div class="profile-member-footer">
                    <span><?php echo esc($memberSince ?? 'Membre vérifié'); ?></span>
                    <span class="profile-member-sep">•</span>
                    <span><?php echo esc($totalItems); ?> carte(s) en collection</span>
                    <span class="profile-member-sep">•</span>
                    <span><?php echo esc($publicItems); ?> publique(s)</span>
                </div>
            </div>
        </div>

        <!-- INFORMATIONS DU COMPTE -->
        <div class="card shadow-card profile-card">
            <div class="card-body">
                <h3>Informations du compte</h3>
                <div class="profile-info-grid">
                    <div class="profile-info-field">
                        <span class="profile-info-label">Pseudo</span>
                        <div class="profile-info-value"><?php echo esc($user->username); ?></div>
                    </div>
                    <div class="profile-info-field">
                        <span class="profile-info-label">E-mail</span>
                        <div class="profile-info-value"><?php echo esc($user->email); ?></div>
                        <small class="profile-info-hint">
                            À changer depuis <a href="<?php echo base_url('p?tab=security'); ?>">Sécurité &amp;
                                Activité</a>.
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- RÉPARTITION PAR STATUT -->
        <?php
        $statusBreakdown = [
            ['label' => 'En cours', 'count' => (int) $statusEnCours, 'class' => 'status-bar-encours'],
            ['label' => 'À voir', 'count' => (int) $statusAVoir, 'class' => 'status-bar-avoir'],
            ['label' => 'En pause', 'count' => (int) $statusEnPause, 'class' => 'status-bar-pause'],
            ['label' => 'Terminé', 'count' => (int) $statusTermine, 'class' => 'status-bar-termine'],
            ['label' => 'Aucun', 'count' => (int) $statusAucun, 'class' => 'status-bar-aucun'],
        ];
        $statusTotal = max(1, (int) $totalItems);
        ?>
        <div class="card shadow-card profile-card">
            <div class="card-body">
                <h3>Répartition de la collection</h3>
                <div class="profile-status-bar" aria-label="Répartition visuelle des statuts">
                    <?php foreach ($statusBreakdown as $st) {
                        if ($st['count'] > 0) {
                            $pct = round(($st['count'] / $statusTotal) * 100, 2); ?>
                    <span class="profile-status-segment <?php echo $st['class']; ?>"
                        style="width: <?php echo $pct; ?>%;"
                        title="<?php echo esc($st['label'] . ' : ' . $st['count']); ?>"></span>
                    <?php }
                    } ?>
                </div>
                <div class="profile-status-grid">
                    <?php foreach ($statusBreakdown as $st) { ?>
                    <div class="profile-status-tile">
                        <span class="profile-status-dot <?php echo $st['class']; ?>"></span>
                        <span class="profile-status-label"><?php echo esc($st['label']); ?></span>
                        <strong class="profile-status-count"><?php echo esc($st['count']); ?></strong>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <!-- SÉRIES EN COURS & EN PAUSE -->
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
                                <?php }
            } ?>
                            </tbody>
                        </table>
                    </div>
                </section>
                <?php }
    } ?>
            </div>
        </div>

        <?php } elseif ('contributions' === $activeTab) { ?>
        <!-- =========================================
             ONGLET 2 : CONTRIBUTIONS & SUIVI
             ========================================= -->
        <div class="profile-section-header">
            <h2 class="profile-section-title">Contributions</h2>
            <p class="profile-section-subtitle">Vos cartes partagées, propositions de modifications et signalements.</p>
        </div>

        <!-- MES CARTES PUBLIQUES / EN ATTENTE -->
        <div class="card shadow-card profile-card">
            <div class="card-body">
                <h3>Mes cartes publiques (<?php echo count($userPublicItems); ?>)</h3>
                <?php if (empty($userPublicItems)) { ?>
                <p class="profile-empty">Vous n'avez pas encore partagé de carte publiquement.</p>
                <?php } else { ?>
                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>État</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($userPublicItems as $pubItem) { ?>
                            <tr>
                                <td><strong><?php echo esc($pubItem->titre); ?></strong></td>
                                <td>
                                    <?php if (1 === (int) $pubItem->is_public) { ?>
                                    <span class="profile-pill profile-pill-success">Publiée</span>
                                    <?php } else { ?>
                                    <span class="profile-pill profile-pill-warning">En inspection</span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <a href="<?php echo base_url('m/' . $pubItem->id); ?>"
                                        class="btn-icon btn-edit-sm">Modifier</a>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <?php } ?>
            </div>
        </div>

        <!-- MES PROPOSITIONS DE MODIFICATIONS -->
        <div class="card shadow-card profile-card">
            <div class="card-body">
                <h3>Mes propositions de modifications (<?php echo count($userRevisions); ?>)</h3>
                <?php if (empty($userRevisions)) { ?>
                <p class="profile-empty">Aucune proposition de modification soumise pour le moment.</p>
                <?php } else { ?>
                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Carte concernée</th>
                                <th>Titre proposé</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($userRevisions as $rev) { ?>
                            <tr>
                                <td><strong><?php echo esc($rev['original_titre'] ?? ('Carte #' . $rev['original_item_id'])); ?></strong>
                                </td>
                                <td><?php echo esc($rev['titre']); ?></td>
                                <td>
                                    <?php if ('approved' === $rev['revision_status']) { ?>
                                    <span class="profile-pill profile-pill-success">Approuvée</span>
                                    <?php } elseif ('rejected' === $rev['revision_status']) { ?>
                                    <span class="profile-pill profile-pill-danger">Refusée</span>
                                    <?php } else { ?>
                                    <span class="profile-pill profile-pill-warning">En attente</span>
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

        <!-- MES SIGNALEMENTS -->
        <div class="card shadow-card profile-card">
            <div class="card-body">
                <h3>Mes signalements de liens (<?php echo count($userReports); ?>)</h3>
                <?php if (empty($userReports)) { ?>
                <p class="profile-empty">Vous n'avez signalé aucun lien mort.</p>
                <?php } else { ?>
                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Détails</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($userReports as $rep) { ?>
                            <tr>
                                <td style="white-space: nowrap; color: var(--text-muted);">
                                    <?php echo esc(date('d/m/Y H:i', strtotime($rep['created_at']))); ?>
                                </td>
                                <td><?php echo esc($rep['details']); ?></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <?php } ?>
            </div>
        </div>

        <?php } elseif ('display' === $activeTab) { ?>
        <!-- =========================================
             ONGLET 3 : AFFICHAGE & PRÉFÉRENCES
             ========================================= -->
        <div class="profile-section-header">
            <h2 class="profile-section-title">Affichage</h2>
            <p class="profile-section-subtitle">Mode clair/sombre, palette de couleurs et nettoyage local.</p>
        </div>

        <!-- MODE CLAIR / SOMBRE & SÉLECTEUR DE THÈMES -->
        <div class="card shadow-card profile-card">
            <div class="card-body">
                <div class="theme-header-row">
                    <h3>Apparence</h3>
                    <div class="theme-mode-switcher" role="radiogroup" aria-label="Mode d'affichage">
                        <button type="button" class="theme-mode-btn" data-mode-choice="light">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor"
                                viewBox="0 0 16 16">
                                <path
                                    d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8M8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0m0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13m8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5M3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8m10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0m-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0m9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707M4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708" />
                            </svg>
                            <span>Clair</span>
                        </button>
                        <button type="button" class="theme-mode-btn" data-mode-choice="dark">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor"
                                viewBox="0 0 16 16">
                                <path
                                    d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278" />
                            </svg>
                            <span>Sombre</span>
                        </button>
                    </div>
                </div>

                <div class="theme-picker-list" role="radiogroup" aria-label="Choisir une palette">
                    <button type="button" class="theme-option-card" data-palette-choice="gold">
                        <span class="theme-swatch theme-swatch-gold" aria-hidden="true"></span>
                        <span class="theme-option-body">
                            <span class="theme-option-title">
                                <strong>Doré</strong>
                                <span class="theme-active-badge">ACTIF</span>
                            </span>
                            <span class="theme-option-desc">Classique élégant avec accent or champagne.</span>
                        </span>
                    </button>

                    <button type="button" class="theme-option-card" data-palette-choice="obsidian">
                        <span class="theme-swatch theme-swatch-obsidian" aria-hidden="true"></span>
                        <span class="theme-option-body">
                            <span class="theme-option-title">
                                <strong>Obsidienne</strong>
                                <span class="theme-active-badge">ACTIF</span>
                            </span>
                            <span class="theme-option-desc">Gris neutre minimaliste et accent monochrome.</span>
                        </span>
                    </button>

                    <button type="button" class="theme-option-card" data-palette-choice="midnight">
                        <span class="theme-swatch theme-swatch-midnight" aria-hidden="true"></span>
                        <span class="theme-option-body">
                            <span class="theme-option-title">
                                <strong>Bleu nuit</strong>
                                <span class="theme-active-badge">ACTIF</span>
                            </span>
                            <span class="theme-option-desc">Teintes ardoise et accent bleu lumineux.</span>
                        </span>
                    </button>

                    <button type="button" class="theme-option-card" data-palette-choice="emerald">
                        <span class="theme-swatch theme-swatch-emerald" aria-hidden="true"></span>
                        <span class="theme-option-body">
                            <span class="theme-option-title">
                                <strong>Émeraude</strong>
                                <span class="theme-active-badge">ACTIF</span>
                            </span>
                            <span class="theme-option-desc">Nuances végétales et accent vert émeraude.</span>
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <!-- PRÉFÉRENCES & NETTOYAGE LOCAL -->
        <div class="card shadow-card profile-card">
            <div class="card-body">
                <h3>Interface &amp; Nettoyage</h3>
                <div class="profile-pref-list">
                    <div class="profile-pref-row">
                        <div>
                            <strong>Ouvrir les catégories par défaut</strong>
                            <p class="profile-pref-desc">Déplie automatiquement toutes les sections sur la page des
                                cartes.</p>
                        </div>
                        <label class="profile-switch">
                            <input type="checkbox" id="toggle-open-divisions">
                            <span class="profile-switch-slider"></span>
                        </label>
                    </div>

                    <div class="profile-pref-row">
                        <div>
                            <strong>Filtres de recherche mémorisés</strong>
                            <p class="profile-pref-desc">Efface la recherche, le statut et la catégorie enregistrés dans
                                ce navigateur.</p>
                        </div>
                        <button type="button" id="btn-reset-filters" class="btn btn-cancel">Réinitialiser</button>
                    </div>

                    <div class="profile-pref-row">
                        <div>
                            <strong>Notifications de sorties masquées</strong>
                            <p class="profile-pref-desc">Réaffiche les badges de sorties récentes que vous avez fermés.
                            </p>
                        </div>
                        <button type="button" id="btn-reset-releases" class="btn btn-cancel">Réafficher</button>
                    </div>
                </div>
            </div>
        </div>
        <?php } elseif ('data' === $activeTab) { ?>
        <!-- =========================================
             ONGLET 4 : BIBLIOTHÈQUE & DONNÉES
             ========================================= -->
        <div class="profile-section-header">
            <h2 class="profile-section-title">Bibliothèque &amp; Données</h2>
            <p class="profile-section-subtitle">Exportez votre collection et gérez votre corbeille.</p>
        </div>

        <!-- EXPORT -->
        <div class="card shadow-card profile-card">
            <div class="card-body">
                <h3>Exporter ma collection</h3>
                <p class="profile-current-meta">
                    Téléchargez l'ensemble de vos <strong><?php echo esc($totalItems); ?></strong> carte(s) active(s)
                    pour les sauvegarder ou les consulter hors-ligne.
                </p>
                <div class="profile-action-row">
                    <a href="<?php echo base_url('p/export/json'); ?>" class="btn btn-primary">Télécharger en JSON</a>
                    <a href="<?php echo base_url('p/export/csv'); ?>" class="btn btn-cancel">Télécharger en CSV
                        (Excel)</a>
                </div>
            </div>
        </div>

        <!-- CORBEILLE -->
        <div class="card shadow-card profile-card">
            <div class="card-body">
                <h3>Corbeille</h3>
                <p class="profile-current-meta">
                    Vous avez actuellement <span
                        class="badge badge-episode"><?php echo esc($deletedItemsCount); ?></span> carte(s) dans la
                    corbeille.
                </p>
                <div class="profile-action-row">
                    <a href="<?php echo base_url('r'); ?>" class="btn btn-cancel">Ouvrir la corbeille</a>
                    <?php if ($deletedItemsCount > 0) { ?>
                    <a href="<?php echo base_url('ra'); ?>" class="btn btn-success"
                        onclick="return confirm('Restaurer toutes les cartes présentes dans votre corbeille ?');">Tout
                        restaurer</a>
                    <a href="<?php echo base_url('re'); ?>" class="btn"
                        style="background-color: var(--danger); color: #fff;"
                        onclick="return confirm('ACTION IRRÉVERSIBLE : vider définitivement toute la corbeille ?');">Vider
                        la corbeille</a>
                    <?php } ?>
                </div>
            </div>
        </div>

        <?php } elseif ('security' === $activeTab) { ?>
        <!-- =========================================
             ONGLET 5 : SÉCURITÉ & ACTIVITÉ
             ========================================= -->
        <div class="profile-section-header">
            <h2 class="profile-section-title">Sécurité &amp; Activité</h2>
            <p class="profile-section-subtitle">Gérez votre adresse e-mail, votre mot de passe et consultez vos
                dernières actions.</p>
        </div>

        <!-- ADRESSE E-MAIL -->
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

        <!-- MOT DE PASSE -->
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

        <!-- JOURNAL D'ACTIVITÉ PERSONNEL -->
        <div class="card shadow-card profile-card">
            <div class="card-body">
                <h3>Activité récente (15 dernières actions)</h3>
                <?php if (empty($recentLogs)) { ?>
                <p class="profile-empty">Aucune activité enregistrée pour le moment.</p>
                <?php } else { ?>
                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Action</th>
                                <th>Détails</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentLogs as $log) { ?>
                            <tr>
                                <td style="white-space: nowrap; color: var(--text-muted); font-size: 0.85rem;">
                                    <?php echo esc(date('d/m/Y H:i', strtotime($log['created_at']))); ?>
                                </td>
                                <td><strong><?php echo esc($log['action']); ?></strong></td>
                                <td style="color: var(--text-muted); font-size: 0.9rem;">
                                    <?php echo esc($log['details']); ?></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <?php } ?>
            </div>
        </div>

        <?php } elseif ('danger' === $activeTab) { ?>
        <!-- =========================================
             ONGLET 6 : ZONE DE DANGER (RGPD)
             ========================================= -->
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
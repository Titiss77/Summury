<?php echo $this->extend('layout'); ?>
<?php echo $this->section('content'); ?>

<?php if (!auth()->loggedIn()) { ?>
<div class="empty-state shadow-card">
    <h2>Bienvenue sur AMFS Dashboard</h2>
    <p style="color: var(--danger);">Les cartes ne sont pas accessibles sans être connecté.</p>
    <p>Veuillez vous connecter ou créer un compte pour gérer et visualiser vos propres cartes ainsi que les publiques.
    </p>
    <br>
    <a href="<?php echo base_url('login'); ?>" class="btn btn-primary">Se connecter</a>
    <a href="<?php echo base_url('register'); ?>" class="btn btn-primary">Créer un compte</a>
</div>
<?php } else { ?>
<div class="actions-container">
    <?php if (auth()->user()->inGroup('superadmin')) { ?>
    <a href="<?php echo base_url('users'); ?>" class="btn btn-warning" style="margin-right: 15px;">Gérer les
        utilisateurs</a>
    <?php } ?>
    <?php
    $pendingTotal = 0;
    if (auth()->loggedIn() && auth()->user()->inGroup('admin', 'superadmin')) {
        $pendingItemsCount = model('App\Models\ItemModel')->where('is_public', 2)->countAllResults();
        $pendingRevisionsCount = model('App\Models\ItemRevisionModel')->where('revision_status', 'pending')->countAllResults();
        $pendingTotal = $pendingItemsCount + $pendingRevisionsCount;
    }
    ?>
    <?php if (auth()->user()->inGroup('admin', 'superadmin')) { ?>
    <a href="<?php echo base_url('items/pending'); ?>" class="btn btn-info" style="margin-right: 15px;">
        Cartes en attente
        <?php if (isset($pendingTotal) && $pendingTotal > 0) { ?>
        <span
            style="background-color: var(--danger, #dc3545); color: white; padding: 2px 6px; border-radius: 50%; font-size: 0.8em; margin-left: 5px; font-weight: bold;">
            <?php echo $pendingTotal; ?>
        </span>
        <?php } ?>
    </a>

    <a href="<?php echo base_url('items/check-to-global'); ?>" class="btn btn-warning"
        style="margin-right: 15px;">Autres publiques
        <?php if (isset($toAdminCount) && $toAdminCount > 0) { ?>
        <span
            style="background-color: var(--danger, #dc3545); color: white; padding: 2px 6px; border-radius: 50%; font-size: 0.8em; margin-left: 5px; font-weight: bold;">
            <?php echo $toAdminCount; ?>
        </span>
        <?php } ?></a>
    <?php } ?>
    <a href="<?php echo base_url('item/form'); ?>" class="btn btn-success">+ Ajouter une carte</a>
</div>
<?php } ?>

<?php if (empty($groupedItems)) { ?>
<?php if (auth()->loggedIn()) { ?>
<div class="empty-state">
    <h2>Vous n'avez pas encore de cartes.</h2>
    <p>Commencez par en ajouter une !</p>
    <br>
    <a href="<?php echo base_url('item/form'); ?>" class="btn btn-success">+ Ajouter une carte</a>
</div>
<?php } ?>
<?php } else { ?>
<div class="search-container" style="margin-bottom: 2rem;">
    <input type="text" id="liveSearch" class="form-control" placeholder="Rechercher une œuvre... (titre, description)"
        autocomplete="off">
</div>

<?php $openDivision = $_GET['open'] ?? null; ?>

<?php foreach ($groupedItems as $headerName => $divisions) { ?>
<section class="header-section">
    <h2 class="header-title">
        <?php echo htmlspecialchars($headerName); ?>
    </h2>

    <?php
        foreach ($divisions as $divisionName => $subCategories) {
            $currentDivisionId = null;
            foreach ($subCategories as $items) {
                if (!empty($items)) {
                    $currentDivisionId = $items[0]->id_division;

                    break;
                }
            }

            $isOpen = ($openDivision && $openDivision == $currentDivisionId) ? 'open' : '';
            $hasMultipleGroups = count($subCategories) > 1;
            ?>
    <details class="division-section" id="div-<?php echo $currentDivisionId; ?>" <?php echo $isOpen; ?>>
        <summary class="division-title">
            <span class="toggle-icon">&#x25B6;</span> <?php echo htmlspecialchars($divisionName); ?>
        </summary>

        <!-- Le conteneur principal pour le drag & drop des blocs de sous-catégories -->
        <div class="division-body sortable-division" data-division-id="<?php echo $currentDivisionId; ?>">

            <?php foreach ($subCategories as $subCatName => $items) {
                $isSansSub = ('Sans sous-catégorie' === $subCatName);
                $displayTitle = $isSansSub ? 'Autres cartes' : $subCatName;
                $opacity = $isSansSub ? '0.6' : '0.9';
                $lineOpacity = $isSansSub ? '0.4' : '0.7';

                $useDetails = (!$isSansSub || $hasMultipleGroups);

                // Vérifier si l'utilisateur possède au moins une carte dans ce groupe pour autoriser le déplacement global
                $canDragSub = false;
                if (auth()->loggedIn()) {
                    $isSuperAdmin = auth()->user()->inGroup('superadmin');
                    $currentUserId = (int) auth()->id();
                    foreach ($items as $itm) {
                        if ($isSuperAdmin || (int) $itm->id_user === $currentUserId) {
                            $canDragSub = true;

                            break;
                        }
                    }
                }
                ?>
            <div class="subcategory-wrapper">
                <?php if ($useDetails) { ?>
                <details class="subcategory-details" style="margin-top: 15px; margin-bottom: 15px; margin-left: 10px;">
                    <summary style="display: flex; align-items: center; gap: 10px; cursor: pointer; outline: none;">
                        <?php if ($canDragSub) { ?>
                        <span class="drag-handle-sub" title="Déplacer ce groupe">&#x2630;</span>
                        <?php } ?>
                        <span class="sub-toggle">&#x25B6;</span>
                        <h3 class="subcategory-title"
                            style="margin: 0; font-size: 1.05rem; color: var(--text-main); opacity: <?php echo $opacity; ?>; text-transform: uppercase; font-weight: 700; letter-spacing: 1px;">
                            <?php echo htmlspecialchars($displayTitle); ?>
                        </h3>
                        <div
                            style="flex-grow: 1; height: 1px; background-color: var(--border-color); opacity: <?php echo $lineOpacity; ?>;">
                        </div>
                    </summary>
                    <div class="cards-grid sortable-grid" style="padding-top: 15px;">
                        <?php } else { ?>
                        <div class="subcategory-details"
                            style="margin-top: 15px; margin-bottom: 15px; margin-left: 10px;">
                            <?php if ($hasMultipleGroups) { ?>
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                                <?php if ($canDragSub) { ?>
                                <span class="drag-handle-sub" title="Déplacer les cartes principales">&#x2630;</span>
                                <?php } ?>
                                <h3 class="subcategory-title"
                                    style="margin: 0; font-size: 1.05rem; color: var(--text-main); opacity: 0.6; text-transform: uppercase; font-weight: 700; letter-spacing: 1px;">
                                    Autres cartes
                                </h3>
                                <div
                                    style="flex-grow: 1; height: 1px; background-color: var(--border-color); opacity: 0.4;">
                                </div>
                            </div>
                            <?php } ?>
                            <div class="cards-grid sortable-grid"
                                style="padding-top: <?php echo $hasMultipleGroups ? '0' : '15px'; ?>;">
                                <?php } ?>

                                <?php foreach ($items as $item) {
                                    // Vérifier si l'utilisateur a le droit de déplacer cette carte spécifique
                                    $canDragItem = auth()->loggedIn() && (auth()->user()->inGroup('superadmin') || (int) $item->id_user === (int) auth()->id());
                                    ?>
                                <div class="card fade-in searchable-card <?php echo 'Terminé' === $item->status ? 'status-completed' : (!empty($item->episode) ? 'needs-dispo-check' : ''); ?>"
                                    data-id="<?php echo esc($item->id); ?>"
                                    data-url="<?php echo htmlspecialchars($item->getFinalLink()); ?>">

                                    <?php if ($canDragItem) { ?>
                                    <div class="drag-handle"
                                        style="cursor: grab; text-align: center; color: #ccc; padding: 5px; touch-action: none;"
                                        title="Déplacer cette carte">
                                        &#x2630;
                                    </div>
                                    <?php } ?>

                                    <a href="<?php echo htmlspecialchars($item->getFinalLink()); ?>" target="_blank"
                                        class="card-link-block">
                                        <div class="card-body">
                                            <?php
                                                    $isFuture = false;
                                    $dateSortieFormatted = '';
                                    $textColor = '';
                                    if (!empty($item->date_sortie)) {
                                        $timezone = new DateTimeZone('Europe/Paris');
                                        $dateSortie = new DateTime($item->date_sortie, $timezone);
                                        $now = new DateTime('now', $timezone);
                                        if ($dateSortie > $now) {
                                            $isFuture = true;
                                            $dateSortieFormatted = $dateSortie->format('d/m/Y à H:i');
                                            $textColor = 'color: var(--danger);';
                                        }
                                    }
                                    ?>
                                            <div class="date-container" id="date-container-<?php echo $item->id; ?>">
                                                <?php if ($isFuture) { ?>
                                                <p class="card-date" style="<?php echo $textColor; ?>">
                                                    Sortie le : <?php echo $dateSortieFormatted; ?>
                                                </p>
                                                <?php } ?>
                                            </div>

                                            <?php
                                    $isCheckable = false;
                                    if (!empty($item->episode) && isset($supportedDomains) && is_array($supportedDomains)) {
                                        foreach ($supportedDomains as $domain) {
                                            if (str_contains($item->getFinalLink(), $domain)) {
                                                $isCheckable = true;

                                                break;
                                            }
                                        }
                                    }

                                    if ('Terminé' !== $item->status && $isCheckable) {
                                        ?>
                                            <div class="live-status" id="live-status-<?php echo $item->id; ?>"
                                                style="font-size: 0.8rem; font-weight: bold; margin-bottom: 5px; text-align: center; color: var(--info);">
                                                Vérification...
                                            </div>
                                            <?php } ?>

                                            <h4 class="card-title search-target-title"
                                                style="<?php echo $textColor; ?>">
                                                <?php echo htmlspecialchars($item->titre); ?>
                                            </h4>
                                            <p style="font-size: 0.8rem; color: var(--text-muted); margin: 0;">Status :
                                                <?php echo htmlspecialchars($item->status); ?>
                                            </p>

                                            <?php
                                        $isPendingNew = (2 == $item->is_public && auth()->loggedIn() && (int) $item->id_user === (int) auth()->id());
                                    $hasPendingRevision = (isset($pendingRevisionIds) && in_array($item->id, $pendingRevisionIds));
                                    if ($isPendingNew || $hasPendingRevision) {
                                        ?>
                                            <div
                                                style="background-color: var(--warning, #ffc107); color: #000; padding: 3px 8px; border-radius: var(--radius-md); font-size: 0.8rem; display: inline-block; margin-top: 5px; margin-bottom: 5px;">
                                                <?php if ($isPendingNew) { ?>
                                                En cours d'inspection (Non public)
                                                <?php } else { ?>
                                                Modification en attente de validation
                                                <?php } ?>
                                            </div>
                                            <?php } ?>

                                            <?php if (!empty($item->description)) { ?>
                                            <p class="card-desc search-target-desc">
                                                <?php echo htmlspecialchars($item->description); ?>
                                            </p>
                                            <?php } ?>

                                            <div class="card-badges">
                                                <?php if (!empty($item->saison)) { ?>
                                                <span class="badge badge-season">Saison
                                                    <?php echo htmlspecialchars($item->saison); ?></span>
                                                <?php } ?>
                                                <?php if (!empty($item->episode)) { ?>
                                                <span class="badge badge-episode">
                                                    Ép. <span
                                                        id="ep-count-<?php echo $item->id; ?>"><?php echo htmlspecialchars($item->episode); ?></span>
                                                    <?php if (auth()->loggedIn() && (int) $item->id_user === (int) auth()->id()) { ?>
                                                    <button type="button" class="btn-increment"
                                                        data-id="<?php echo $item->id; ?>">+1</button>
                                                    <?php } ?>
                                                </span>
                                                <?php } ?>
                                            </div>
                                        </div>

                                        <div class="card-image">
                                            <?php if (!empty($item->image)) { ?>
                                            <img src="<?php echo htmlspecialchars($item->image); ?>"
                                                alt="<?php echo htmlspecialchars($item->titre); ?>" class="image-view"
                                                loading="lazy">
                                            <?php } ?>
                                        </div>
                                    </a>

                                    <?php if (1 == $item->is_public) { ?>
                                    <button type="button" class="btn-report-sm" data-id="<?php echo esc($item->id); ?>"
                                        onclick="openReportModal(this)">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            fill="currentColor" class="bi bi-flag-fill" viewBox="0 0 16 16">
                                            <path
                                                d="M14.778.085A.5.5 0 0 1 15 .5V8a.5.5 0 0 1-.314.464L14.5 8l.186.464-.003.001-.006.003-.023.009a12 12 0 0 1-.397.15c-.264.095-.631.223-1.047.35-.816.252-1.879.523-2.71.523-.847 0-1.548-.28-2.158-.525l-.028-.01C7.68 8.71 7.14 8.5 6.5 8.5c-.7 0-1.638.23-2.437.477A20 20 0 0 0 3 9.342V15.5a.5.5 0 0 1-1 0V.5a.5.5 0 0 1 1 0v.282c.226-.079.496-.17.79-.26C4.606.272 5.67 0 6.5 0c.84 0 1.524.277 2.121.519l.043.018C9.286.788 9.828 1 10.5 1c.7 0 1.638-.23 2.437-.477a20 20 0 0 0 1.349-.476l.019-.007.004-.002h.001" />
                                        </svg>
                                    </button>
                                    <?php } ?>

                                    <?php if (auth()->loggedIn() && (int) $item->id_user === (int) auth()->id()) { ?>
                                    <div class="card-actions-bottom">
                                        <a href="<?php echo base_url('item/form/'.$item->id); ?>"
                                            class="btn-icon btn-edit-sm">Modifier</a>
                                        <a href="<?php echo base_url('item/delete/'.$item->id); ?>"
                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette carte ?');"
                                            class="btn-icon btn-delete-sm">Supprimer</a>
                                    </div>
                                    <?php } ?>
                                </div>
                                <?php } ?>

                                <?php if ($useDetails) { ?>
                            </div>
                </details>
                <?php } else { ?>
            </div>
        </div>
        <?php } ?>
        </div>
        <?php } ?>

        </div>
    </details>
    <?php } ?>
</section>
<?php } ?>
<?php } ?>

<script>
window.amfsSupportedDomains = <?php echo json_encode($supportedDomains ?? []); ?>;
</script>

<?php echo $this->endSection(); ?>
<?php echo $this->extend('layout'); ?>
<?php echo $this->section('content'); ?>

<?php if (!auth()->loggedIn()) { ?>
<!-- NOUVELLE LANDING PAGE (Visiteurs) -->
<div class="landing-hero fade-in shadow-card"
    style="text-align: center; padding: 5rem 2rem; background: linear-gradient(135deg, rgba(79,70,229,0.1) 0%, var(--bg-card) 100%); border-radius: var(--radius-md); margin-bottom: 3rem; border: 1px solid var(--border-color);">
    <h2
        style="font-size: 2.8rem; color: var(--text-main); margin-bottom: 1.5rem; font-weight: 800; letter-spacing: -0.02em;">
        Centralisez vos œuvres et <span style="color: var(--primary);">suivez votre progression</span></h2>
    <p
        style="font-size: 1.15rem; color: var(--text-muted); max-width: 700px; margin: 0 auto 2.5rem auto; line-height: 1.6;">
        <?php echo env('SITENAME'); ?> est votre tableau de bord personnel. Organisez vos séries, films, animes, mangas
        et liens favoris.
        Récupérez automatiquement les métadonnées, gérez vos statuts de visionnage et explorez les collections publiques
        !
    </p>
    <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
        <a href="<?php echo base_url('register'); ?>" class="btn btn-primary"
            style="padding: 14px 32px; font-size: 1.1rem; border-radius: 50px;">Créer mon compte gratuit</a>
        <a href="<?php echo base_url('login'); ?>" class="btn btn-cancel"
            style="padding: 14px 32px; font-size: 1.1rem; border-radius: 50px;">Me
            connecter</a>
    </div>
</div>

<div class="public-invitation fade-in" style="text-align: center; margin-bottom: 3rem;">
    <h3 style="color: var(--text-main); font-size: 1.6rem; font-weight: 700;">Explorez les cartes publiques</h3>
    <p style="color: var(--text-muted); font-size: 1.05rem; max-width: 600px; margin: 0 auto;">Naviguez via les onglets
        ci-dessus pour découvrir les liens, outils et recommandations partagés par la communauté
        <?php echo env('SITENAME'); ?>.</p>
</div>
<?php } else { ?>

<!-- ACTIONS ADMIN & USER CONNECTÉ -->
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
            style="background-color: var(--danger, #dc3545); color: white; padding: 2px 6px; border-radius: 50%; font-size: 0.8em; margin-left: 5px; font-weight: bold;"><?php echo $pendingTotal; ?></span>
        <?php } ?>
    </a>
    <a href="<?php echo base_url('items/check-to-global'); ?>" class="btn btn-warning" style="margin-right: 15px;">
        Autres publiques
        <?php if (isset($toAdminCount) && $toAdminCount > 0) { ?>
        <span
            style="background-color: var(--danger, #dc3545); color: white; padding: 2px 6px; border-radius: 50%; font-size: 0.8em; margin-left: 5px; font-weight: bold;"><?php echo $toAdminCount; ?></span>
        <?php } ?>
    </a>
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
<?php } else { ?>
<div class="empty-state" style="margin-top: 2rem;">
    <h3 style="color: var(--text-main);">Aucune carte publique dans cette catégorie.</h3>
    <p>Créez un compte pour partager les vôtres !</p>
</div>
<?php } ?>
<?php } else { ?>
<div class="search-container" style="margin-bottom: 2rem;">
    <input type="text" id="liveSearch" class="form-control" placeholder="Rechercher une œuvre... (titre, description)"
        autocomplete="off">
</div>

<?php $openDivision = $_GET['open'] ?? null; ?>
<?php $openSub = $_GET['subopen'] ?? null; ?>
<?php foreach ($groupedItems as $headerName => $divisions) { ?>
<section class="header-section">
    <h2 class="header-title"><?php echo htmlspecialchars($headerName); ?></h2>
    <?php foreach ($divisions as $divisionName => $subCategories) {
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
        <div class="division-body sortable-division" data-division-id="<?php echo $currentDivisionId; ?>">
            <?php foreach ($subCategories as $subCatName => $items) {
                $isSansSub = ('Sans sous-catégorie' === $subCatName || 'Sans sous-cat gorie' === $subCatName);
                $displayTitle = $isSansSub ? 'Autres cartes' : $subCatName;
                $opacity = $isSansSub ? '0.6' : '0.9';
                $lineOpacity = $isSansSub ? '0.4' : '0.7';
                $useDetails = (!$isSansSub || $hasMultipleGroups);
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
                <?php
                $isSubOpen = '';
                if ($isOpen === 'open') {
                    if ($openSub && $openSub === $subCatName) {
                        $isSubOpen = 'open';
                    } elseif (!$openSub) {
                        $isSubOpen = 'open'; // S'ouvre par défaut s'il n'y a pas de sous-catégorie précisée
                    }
                }
                ?>
                <details class="subcategory-details" style="margin-top: 15px; margin-bottom: 15px; margin-left: 10px;"
                    <?php echo $isSubOpen; ?>>
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
                                    $canDragItem = auth()->loggedIn() && (auth()->user()->inGroup('superadmin') || (int) $item->id_user === (int) auth()->id());
                                    ?>
                                <div class="card fade-in searchable-card <?php echo 'Terminé' === $item->status ? 'status-completed' : (!empty($item->episode) ? 'needs-dispo-check' : ''); ?>"
                                    data-id="<?php echo esc($item->id); ?>"
                                    data-url="<?php echo htmlspecialchars($item->getFinalLink()); ?>">
                                    <?php if ($canDragItem) { ?>
                                    <div class="drag-handle"
                                        style="cursor: grab; text-align: center; color: #ccc; padding: 5px; touch-action: none;"
                                        title="Déplacer cette carte">&#x2630;</div>
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
                                                    $dateSortieFormatted = $dateSortie->format('d/m/Y H:i');
                                                    $textColor = 'color: var(--danger);';
                                                }
                                            }
                                            ?>
                                            <div class="date-container" id="date-container-<?php echo $item->id; ?>">
                                                <?php if ($isFuture) { ?>
                                                <p class="card-date" style="<?php echo $textColor; ?>">Sortie le :
                                                    <?php echo $dateSortieFormatted; ?></p>
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
                                                Vérification...</div>
                                            <?php } ?>

                                            <h4 class="card-title search-target-title"
                                                style="<?php echo $textColor; ?>">
                                                <?php echo htmlspecialchars($item->titre); ?></h4>
                                            <p style="font-size: 0.8rem; color: var(--text-muted); margin: 0;">Status :
                                                <?php echo htmlspecialchars($item->status); ?></p>

                                            <?php
                                            $isPendingNew = (2 == $item->is_public && auth()->loggedIn() && (int) $item->id_user === (int) auth()->id());
                                            $hasPendingRevision = (isset($pendingRevisionIds) && in_array($item->id, $pendingRevisionIds));
                                            if ($isPendingNew || $hasPendingRevision) {
                                                ?>
                                            <div
                                                style="background-color: var(--warning, #ffc107); color: #000; padding: 3px 8px; border-radius: var(--radius-md); font-size: 0.8rem; display: inline-block; margin-top: 5px; margin-bottom: 5px;">
                                                <?php echo $isPendingNew ? "En cours d'inspection (Non public)" : 'Modification en attente de validation'; ?>
                                            </div>
                                            <?php } ?>

                                            <?php if (!empty($item->description)) { ?>
                                            <p class="card-desc search-target-desc">
                                                <?php echo htmlspecialchars($item->description); ?></p>
                                            <?php } ?>

                                            <div class="card-badges">
                                                <?php if (!empty($item->saison)) { ?>
                                                <div
                                                    style="display: flex; flex-direction: column; align-items: center;">
                                                    <span class="badge badge-season">S. <span
                                                            id="s-count-<?php echo $item->id; ?>"><?php echo htmlspecialchars($item->saison); ?></span><?php if (!empty($item->total_saisons)) { echo ' / '.htmlspecialchars($item->total_saisons); } ?></span>
                                                </div>
                                                <?php } ?>
                                                <?php if (!empty($item->episode)) { ?>
                                                <div
                                                    style="display: flex; flex-direction: column; align-items: center;">
                                                    <span class="badge badge-episode">Ép. <span
                                                            id="ep-count-<?php echo $item->id; ?>"><?php echo htmlspecialchars($item->episode); ?></span><?php if (!empty($item->total_episodes)) { echo ' / '.htmlspecialchars($item->total_episodes); } ?>
                                                        <?php if (auth()->loggedIn() && (int) $item->id_user === (int) auth()->id()) { ?>
                                                        <button type="button"
                                                            class="btn-increment btn-increment-episode"
                                                            data-id="<?php echo $item->id; ?>"
                                                            data-division="<?php echo $item->id_division; ?>"
                                                            data-sub="<?php echo htmlspecialchars($item->sous_categorie ?? ''); ?>">
                                                            +1
                                                        </button>
                                                        <?php } ?>
                                                    </span>
                                                </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <?php if (!empty($item->image)) { ?>
                                        <div class="card-image">
                                            <img src="<?php echo htmlspecialchars($item->image); ?>"
                                                alt="<?php echo htmlspecialchars($item->titre); ?>" class="image-view"
                                                loading="lazy" decoding="async" fetchpriority="low">
                                        </div>
                                        <?php } ?>
                                    </a>
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
window.<?php echo env('SITENAME'); ?>SupportedDomains = <?php echo json_encode($supportedDomains ?? []); ?>;
</script>

<?php echo $this->endSection(); ?>
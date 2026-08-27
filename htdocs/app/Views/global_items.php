<?php echo $this->extend('layout'); ?>
<?php echo $this->section('content'); ?>
<a href="<?php echo base_url('/'); ?>" class="btn btn-warning">Retour aux cartes</a>
<div style="margin-bottom: 2.5rem;">
    <h2>Cartes intéressantes</h2>
    <p style="color: var(--text-muted);">Voici les cartes publiques qui ne viennent pas de l'admin.</p>
</div>
<div class="cards-grid">
    <?php foreach ($items as $item) { ?>
    <?php if (1 != $item->id_user) { ?>
    <div class="card fade-in <?php echo 'Terminé' === $item->status ? 'status-completed' : ''; ?>" data-id="<?php echo esc($item->id); ?>">
        <a href="<?php echo htmlspecialchars($item->getFinalLink()); ?>" target="_blank" class="card-link-block">
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
                        $dateSortieFormatted = $dateSortie->format('d/m/Y   H:i');
                        $textColor = 'color: var(--danger);';
                    }
                }
                ?>
                <h4 class="card-title search-target-title" style="<?php echo $textColor; ?>"><?php echo htmlspecialchars($item->titre); ?></h4>
                <?php if ($isFuture) { ?>
                <p class="card-date" style="<?php echo $textColor; ?>">Suivant le : <?php echo $dateSortieFormatted; ?></p>
                <?php } ?>
                <p style="font-size: 0.8rem; color: var(--text-muted); margin: 0;">Status : <?php echo htmlspecialchars($item->status); ?></p>
                <?php
                $isPendingNew = (2 == $item->is_public && auth()->loggedIn() && (int) $item->id_user === (int) auth()->id());
                $hasPendingRevision = (isset($pendingRevisionIds) && in_array($item->id, $pendingRevisionIds));
                if ($isPendingNew || $hasPendingRevision) {
                ?>
                <div style="background-color: var(--warning, #ffc107); color: #000; padding: 3px 8px; border-radius: var(--radius-md); font-size: 0.8rem; display: inline-block; margin-top: 5px; margin-bottom: 5px;">
                    <?php echo $isPendingNew ? "En cours d'inspection (Non public)" : "Modification en attente de validation"; ?>
                </div>
                <?php } ?>
                <?php if (!empty($item->description)) { ?>
                <p class="card-desc search-target-desc"><?php echo htmlspecialchars($item->description); ?></p>
                <?php } ?>
                <div class="card-badges">
                    <?php if (!empty($item->saison)) { ?>
                    <span class="badge badge-season">Saison <?php echo htmlspecialchars($item->saison); ?></span>
                    <?php } ?>
                    <?php if (!empty($item->episode)) { ?>
                    <span class="badge badge-episode">
                        Ép. <span id="ep-count-<?php echo $item->id; ?>"><?php echo htmlspecialchars($item->episode); ?></span><?php if (!empty($item->total_episodes)) { echo ' / ' . htmlspecialchars($item->total_episodes); } ?>
                    </span>
                    <?php if (!empty($item->total_episodes) && !empty($item->episode)) {
                        $restants = max(0, $item->total_episodes - $item->episode);
                        if ($restants > 0) {
                            echo "<span style='font-size: 0.75rem; color: var(--text-muted); display: block; margin-top: 4px; font-weight: 500;'>({$restants} restants)</span>";
                        } else {
                            echo "<span style='font-size: 0.75rem; color: var(--success); display: block; margin-top: 4px; font-weight: 500;'>Terminé</span>";
                        }
                    } ?>
                    <?php } ?>
                </div>
            </div>
            <?php if (!empty($item->image)) { ?>
            <div class="card-image">
                <img src="<?php echo htmlspecialchars($item->image); ?>" alt="<?php echo htmlspecialchars($item->titre); ?>" class="image-view" loading="lazy" decoding="async" fetchpriority="low">
            </div>
            <?php } ?>
        </a>
        <div class="card-actions-bottom">
            <a href="<?php echo base_url('item/turn/'.esc($item->id)); ?>" class="btn-icon btn-edit-sm">Passer en admin</a>
        </div>
    </div>
    <?php } ?>
    <?php } ?>
</div>
<?php echo $this->endSection(); ?>
<?php echo $this->extend('layout'); ?>
<?php echo $this->section('content'); ?>

<div class="actions-container">
    <a href="<?php echo base_url('/'); ?>" class="btn btn-cancel">Retour aux cartes</a>
</div>

<div class="container">
    <div
        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 15px;">
        <h2 style="margin: 0;">Corbeille</h2>

        <?php if (!empty($deletedItems)) { ?>
        <div style="display: flex; gap: 10px;">
            <a href="<?php echo base_url('items/restore-all'); ?>" class="btn btn-success"
                onclick="return confirm('Êtes-vous sûr de vouloir restaurer TOUTES les cartes de la corbeille ?');">
                Tout restaurer
            </a>
            <a href="<?php echo base_url('items/empty-trash'); ?>" class="btn"
                style="background-color: var(--danger); color: white;"
                onclick="return confirm('ATTENTION ACTION IRRÉVERSIBLE !\nÊtes-vous sûr de vouloir détruire DÉFINITIVEMENT toutes les cartes de cette page ?');">
                Tout détruire
            </a>
        </div>
        <?php } ?>
    </div>

    <?php if (empty($deletedItems)) { ?>
    <div class="empty-state">
        <h3 style="color: var(--text-main);">Aucune carte dans la corbeille.</h3>
        <p>Les cartes supprimées apparaîtront ici.</p>
    </div>
    <?php } else { ?>

    <div class="cards-grid">
        <?php foreach ($deletedItems as $item) { ?>
        <div class="card fade-in" data-id="<?php echo esc($item->id); ?>" style="opacity: 0.8; filter: grayscale(20%);">

            <a href="<?php echo htmlspecialchars($item->getFinalLink()); ?>" target="_blank" class="card-link-block">
                <div class="card-body">
                    <!-- Date de suppression au format badge (flux naturel, pas d'absolute) -->
                    <div style="margin-bottom: 10px;">
                        <span
                            style="background-color: var(--danger-bg, rgba(239, 68, 68, 0.1)); color: var(--danger); padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold;">
                            Supprimée le : <?php echo date('d/m/Y', strtotime($item->deleted_at)); ?>
                        </span>
                    </div>

                    <h4 class="card-title search-target-title"><?php echo htmlspecialchars($item->titre); ?></h4>
                    <p style="font-size: 0.8rem; color: var(--text-muted); margin: 0;">Auteur :
                        <?php echo htmlspecialchars($item->author_name ?? 'Inconnu'); ?></p>

                    <?php if (!empty($item->description)) { ?>
                    <p class="card-desc search-target-desc"><?php echo htmlspecialchars($item->description); ?></p>
                    <?php } ?>

                    <div class="card-badges">
                        <?php if (!empty($item->saison)) { ?>
                        <div style="display: flex; flex-direction: column; align-items: center;">
                            <span class="badge badge-season">S. <span
                                    id="s-count-<?php echo $item->id; ?>"><?php echo htmlspecialchars($item->saison); ?></span><?php if (!empty($item->total_saisons)) { echo ' / '.htmlspecialchars($item->total_saisons); } ?></span>
                        </div>
                        <?php } ?>

                        <?php if (!empty($item->episode)) { ?>
                        <div style="display: flex; flex-direction: column; align-items: center;">
                            <span class="badge badge-episode">Ép. <span
                                    id="ep-count-<?php echo $item->id; ?>"><?php echo htmlspecialchars($item->episode); ?></span><?php if (!empty($item->total_episodes)) { echo ' / '.htmlspecialchars($item->total_episodes); } ?></span>
                        </div>
                        <?php } ?>
                    </div>
                </div>

                <?php if (!empty($item->image)) { ?>
                <div class="card-image">
                    <img src="<?php echo htmlspecialchars($item->image); ?>"
                        alt="<?php echo htmlspecialchars($item->titre); ?>" class="image-view" loading="lazy"
                        decoding="async" fetchpriority="low">
                </div>
                <?php } ?>
            </a>

            <!-- NOUVELLES ACTIONS POUR LA CORBEILLE -->
            <div class="card-actions-bottom" style="justify-content: space-between;">
                <a href="<?php echo base_url('item/restore/' . $item->id); ?>" class="btn-icon btn-edit-sm"
                    style="color: var(--success); font-weight: bold;">
                    Restaurer
                </a>
                <a href="<?php echo base_url('item/permanent-delete/' . $item->id); ?>"
                    onclick="return confirm('Êtes-vous sûr de vouloir détruire définitivement cette carte ?');"
                    class="btn-icon btn-delete-sm" style="color: var(--danger);">
                    Détruire
                </a>
            </div>

        </div>
        <?php } ?>
    </div>

    <?php } ?>
</div>

<?php echo $this->endSection(); ?>
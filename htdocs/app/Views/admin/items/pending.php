<?php echo $this->extend('layout'); ?>
<?php echo $this->section('content'); ?>

<a href="<?php echo base_url('/'); ?>" class="btn btn-warning" style="margin-bottom: 20px;">Retour aux cartes</a>

<div class="container">
    <h2>Tableau de bord de Modération</h2>
    <p>Gérez les nouvelles publications et les propositions de modifications en attente.</p>

    <?php if (session()->has('message')) { ?>
    <div class="alert alert-success"><?php echo session('message'); ?></div>
    <?php } ?>
    <?php if (session()->has('error')) { ?>
    <div class="alert alert-danger"><?php echo session('error'); ?></div>
    <?php } ?>

    <?php if (empty($pendingItems) && empty($pendingRevisions)) { ?>
    <div class="empty-state">
        <p>🎉 Super, aucune carte ni modification n'est en attente d'inspection !</p>
    </div>
    <?php } else { ?>

    <?php if (!empty($pendingItems)) { ?>
    <h3 style="margin-top: 30px; margin-bottom: 15px;">Nouvelles cartes en attente</h3>
    <div class="admin-table-container fade-in">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Titre de l'œuvre</th>
                    <th>Description / Info</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendingItems as $item) { ?>
                <tr>
                    <td><strong><?php echo esc($item->titre); ?></strong></td>
                    <td><?php echo esc($item->description); ?></td>
                    <td>
                        <div class="action-links">
                            <a href="<?php echo base_url('item/form/'.$item->id); ?>"
                                class="btn-action btn-edit">Examiner</a>
                            <a href="<?php echo base_url('items/approve/'.$item->id); ?>" class="btn-action"
                                style="background:var(--success); color:white;">Valider</a>
                            <a href="<?php echo base_url('items/reject/'.$item->id); ?>" class="btn-action btn-ban"
                                onclick="return confirm('Refuser cette carte ? Elle redeviendra privée.')">Refuser</a>
                        </div>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <?php } ?>

    <?php if (!empty($pendingRevisions)) { ?>
    <h3 style="margin-top: 40px; margin-bottom: 15px;">Modifications en attente</h3>
    <div class="admin-table-container fade-in">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Carte Originale</th>
                    <th>Modifications apportées</th>
                    <th>Modifié par</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendingRevisions as $revision) { ?>
                <tr>
                    <td>
                        <strong><?php echo esc($revision['original_titre']); ?></strong><br>
                        <small style="color: #666;">ID Original:
                            <?php echo esc($revision['original_item_id']); ?></small>
                    </td>
                    <td>
                        <?php if (!empty($revision['changes'])) { ?>
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            <?php foreach ($revision['changes'] as $change) { ?>
                            <li style="margin-bottom: 12px; border-bottom: 1px dashed #eee; padding-bottom: 8px;">
                                <strong style="color: #444; font-size: 0.9em;"><?php echo esc($change['label']); ?>
                                    :</strong><br>

                                <?php if ('image' === $change['field']) { ?>
                                <div style="display: flex; gap: 15px; align-items: center; margin-top: 5px;">
                                    <div style="text-align: center;">
                                        <?php if (!empty($change['old'])) { ?>
                                        <img src="<?php echo esc($change['old']); ?>" alt="Ancienne"
                                            style="max-height: 60px; border-radius: var(--radius-md); border: 1px solid #ddd; opacity: 0.5;">
                                        <?php } else { ?>
                                        <span
                                            style="color: #dc3545; font-size: 0.85em; text-decoration: line-through;">Aucune</span>
                                        <?php } ?>
                                        <div style="font-size: 0.75em; color: #999;">Ancienne</div>
                                    </div>
                                    <span style="font-weight: bold; color: #aaa; font-size: 1.2em;">➔</span>
                                    <div style="text-align: center;">
                                        <?php if (!empty($change['new'])) { ?>
                                        <img src="<?php echo esc($change['new']); ?>" alt="Nouvelle"
                                            style="max-height: 60px; border-radius: var(--radius-md); border: 2px solid var(--success, #28a745);">
                                        <?php } else { ?>
                                        <span
                                            style="color: #28a745; font-weight: bold; font-size: 0.85em;">Supprimée</span>
                                        <?php } ?>
                                        <div style="font-size: 0.75em; color: #999;">Nouvelle</div>
                                    </div>
                                </div>
                                <?php } else { ?>
                                <span
                                    style="color: #dc3545; text-decoration: line-through; font-size: 0.9em; background-color: #fdf2f2; padding: 1px 4px; border-radius: 3px;">
                                    <?php echo !empty($change['old']) ? esc($change['old']) : '<em>Vide</em>'; ?>
                                </span>
                                <br>
                                <span
                                    style="color: #28a745; font-weight: bold; font-size: 0.95em; background-color: #f3faf4; padding: 1px 4px; border-radius: 3px; display: inline-block; margin-top: 2px;">
                                    ➔ <?php echo !empty($change['new']) ? esc($change['new']) : '<em>Vide</em>'; ?>
                                </span>
                                <?php } ?>
                            </li>
                            <?php } ?>
                        </ul>
                        <?php } else { ?>
                        <span style="color: #888; font-style: italic; font-size: 0.9em;">Aucun changement détecté sur
                            les valeurs.</span>
                        <?php } ?>
                    </td>
                    <td>
                        <span
                            style="background: var(--primary); color: white; padding: 3px 8px; border-radius: var(--radius-md); font-size: 0.85em;">
                            <?php echo esc($revision['author_name']); ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-links">
                            <a href="<?php echo base_url('items/approve-revision/'.$revision['id']); ?>"
                                class="btn-action" style="background:var(--success); color:white;"
                                onclick="return confirm('Approuver cette modification ? Elle écrasera la version publique actuelle.')">Approuver</a>
                            <a href="<?php echo base_url('items/reject-revision/'.$revision['id']); ?>"
                                class="btn-action btn-ban"
                                onclick="return confirm('Refuser cette modification ? Elle sera rejetée sans impacter la carte publique.')">Refuser</a>
                        </div>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <?php } ?>

    <?php } ?>
</div>

<?php echo $this->endSection(); ?>
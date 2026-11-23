<?php echo $this->extend('layout'); ?>

<?php echo $this->section('content'); ?>
<div class="actions-container">
    <a href="<?php echo base_url('/'); ?>" onclick="return retourAuxCartes(event, this.href);"
        class="btn btn-cancel">Retour aux cartes</a>
</div>
<div class="container" style="max-width: 1200px; margin: 0 auto; padding: 20px;">
    <div
        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
        <div>
            <h2 style="color: var(--danger); margin: 0;">Signalements de liens</h2>
            <p style="margin: 5px 0 0 0;">Les signalements en attente doivent être vérifiés avant d’affecter les cartes.
            </p>
        </div>
    </div>

    <?php if (session()->has('message')) { ?>
    <div class="alert alert-success" style="margin-bottom: 20px;"><?php echo session('message'); ?></div>
    <?php } ?>

    <?php if (session()->has('error')) { ?>
    <div class="alert alert-danger" style="margin-bottom: 20px;"><?php echo session('error'); ?></div>
    <?php } ?>

    <div
        style="background: var(--bg-body); padding: 15px 20px; border-radius: var(--radius-md); margin-bottom: 25px; border: 1px solid var(--border-color);">
        <h4 style="margin-top: 0; color: var(--text-main); display: flex; align-items: center; gap: 8px;">🔄 Migration
            de Domaine</h4>
        <p style="font-size: 0.9em; color: var(--text-muted); margin-bottom: 15px;">
            Sélectionnez l'ancien domaine et entrez la nouvelle adresse pour mettre à jour tous les liens cassés d'un
            coup.
        </p>
        <form action="<?php echo base_url('a/b'); ?>" method="post"
            style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <?php echo csrf_field(); ?>
            <select name="old_domain" required
                style="flex: 1; min-width: 200px; padding: 10px; border: 1px solid var(--border-color); border-radius: var(--radius-md); background-color: var(--bg-card); color: var(--text-main);">
                <option value="">-- Sélectionner l'ancien domaine --</option>
                <?php foreach ($domains as $domain) { ?>
                <option value="<?php echo esc($domain); ?>"><?php echo esc($domain); ?></option>
                <?php } ?>
            </select>
            <span style="font-weight: bold; color: var(--text-muted);">➔</span>
            <input type="text" name="new_domain" placeholder="Nouveau domaine (ex: https://site.fr)" required
                style="flex: 1; min-width: 200px; padding: 10px; border: 1px solid var(--border-color); border-radius: var(--radius-md); background-color: var(--bg-card); color: var(--text-main);">
            <button type="submit" class="btn"
                style="background: var(--success); color: #fff; border: none; padding: 10px 15px; border-radius: var(--radius-md); cursor: pointer; font-weight: bold;"
                onclick="return confirm('⚠️ Attention : Cette action va chercher TOUTES les cartes contenant l\'ancien domaine et les modifier. Êtes-vous sûr ?')">
                Mettre à jour les liens
            </button>
        </form>
    </div>

    <h3>Signalements en attente (<?php echo count($pendingReports); ?>)</h3>
    <?php if (!empty($pendingReports)) { ?>
    <div class="admin-table-container fade-in" style="margin-bottom: 30px;">
        <table class="admin-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th>Domaine</th>
                    <th>Carte signalée</th>
                    <th>Date</th>
                    <th>Modération</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendingReports as $report) { ?>
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <td style="padding: 12px; font-family: monospace;"><?php echo esc($report['url_testee']); ?></td>
                    <td style="padding: 12px;"><?php echo esc($report['titre']); ?></td>
                    <td style="padding: 12px;"><?php echo esc($report['last_run']); ?></td>
                    <td style="padding: 12px;">
                        <div class="action-links" style="display: flex; gap: 8px; flex-wrap: wrap;">
                            <form action="<?php echo base_url('a/d/report/' . $report['id'] . '/approve'); ?>"
                                method="post">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn-action"
                                    style="background: var(--danger); color: #fff;">Confirmer mort</button>
                            </form>
                            <form action="<?php echo base_url('a/d/report/' . $report['id'] . '/reject'); ?>"
                                method="post">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn-action"
                                    style="background: var(--success); color: #fff;">Rejeter / fonctionnel</button>
                            </form>
                            <a href="<?php echo esc($report['url_testee'], 'attr'); ?>" target="_blank"
                                rel="noopener noreferrer" class="btn-action"
                                style="background: var(--text-muted); color: #fff;">Tester</a>
                        </div>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <?php } else { ?>
    <p class="text-muted">Aucun signalement à modérer.</p>
    <?php } ?>

    <h3>Domaines confirmés comme morts</h3>
    <?php if (empty($deadItems)) { ?>
    <div class="empty-state"
        style="text-align: center; padding: 40px 0; color: var(--text-muted); background: var(--bg-body); border-radius: var(--radius-md); border: 1px dashed var(--border-color); margin-bottom: 30px;">
        <p style="font-size: 1.2em; margin: 0;">Aucun domaine n’a été signalé comme mort pour le moment.</p>
    </div>
    <?php
} else {
    // REGROUPEMENT PAR DOMAINE POUR ÉLIMINER LES DOUBLONS
    $grouped = [];
    foreach ($deadItems as $item) {
        $url = $item['url_testee'];
        if (!isset($grouped[$url])) {
            $grouped[$url] = [];
        }
        $grouped[$url][] = $item;
    }
    ?>
    <div class="admin-table-container fade-in" style="margin-bottom: 30px;">
        <table class="admin-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th>Domaine signalé cassé</th>
                    <th>Cartes impactées</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($grouped as $url => $items) { ?>
                <tr style="background-color: var(--danger-bg); border-bottom: 1px solid var(--border-color);">
                    <td
                        style="padding: 12px; max-width: 300px; word-break: break-all; font-family: monospace; font-size: 0.9em; color: var(--danger);">
                        <strong><?php echo esc($url); ?></strong>
                    </td>
                    <td style="padding: 12px;">
                        <ul style="margin: 0; padding-left: 20px; font-size: 0.9em; color: var(--text-main);">
                            <?php foreach ($items as $i) { ?>
                            <li><strong><?php echo esc($i['titre']); ?></strong></li>
                            <?php } ?>
                        </ul>
                    </td>
                    <td style="padding: 12px;">
                        <div class="action-links" style="display: flex; gap: 8px; flex-wrap: wrap;">
                            <a href="<?php echo base_url('a/fnd?url=' . urlencode($url)); ?>" class="btn-action"
                                style="background-color: var(--warning); color: #fff;"
                                onclick="return confirm('Marquer ce domaine comme fonctionnel (Faux Négatif) pour toutes ces cartes ? L\'alerte disparaîtra.');">Faux
                                Négatif</a>
                            <a href="<?php echo esc($url, 'attr'); ?>" target="_blank" rel="noopener noreferrer"
                                class="btn-action" style="background: var(--text-muted); color: #fff;">Tester le
                                lien</a>
                        </div>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <?php } ?>

    <h3>Domaines déclarés comme valides (<?php echo count($validDomains ?? []); ?>)</h3>
    <?php if (empty($validDomains)) { ?>
    <div class="empty-state"
        style="text-align: center; padding: 40px 0; color: var(--text-muted); background: var(--bg-body); border-radius: var(--radius-md); border: 1px dashed var(--border-color);">
        <p style="font-size: 1.2em; margin: 0;">Aucun domaine n’a été déclaré comme valide pour le moment.</p>
    </div>
    <?php } else { ?>
    <div class="admin-table-container fade-in">
        <table class="admin-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th>Domaine déclaré valide</th>
                    <th>Cartes associées</th>
                    <th>Validé le</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($validDomains as $validDomain) { ?>
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <td
                        style="padding: 12px; max-width: 300px; word-break: break-all; font-family: monospace; font-size: 0.9em; color: var(--success);">
                        <strong><?php echo esc($validDomain['url']); ?></strong>
                    </td>
                    <td style="padding: 12px;">
                        <?php if (!empty($validDomain['items'])) { ?>
                        <ul style="margin: 0; padding-left: 20px; font-size: 0.9em; color: var(--text-main);">
                            <?php foreach ($validDomain['items'] as $card) { ?>
                            <li><strong><?php echo esc($card->titre); ?></strong></li>
                            <?php } ?>
                        </ul>
                        <?php } else { ?>
                        <span style="font-size: 0.9em; color: var(--text-muted);">Aucune carte active</span>
                        <?php } ?>
                    </td>
                    <td style="padding: 12px; font-size: 0.9em;">
                        <div><?php echo esc($validDomain['created_at'] ?? '-'); ?></div>
                        <?php if (!empty($validDomain['is_protected'])) { ?>
                        <small style="color: var(--success);">
                            Protection 7j active<?php if (!empty($validDomain['expires_at'])) { ?> (jusqu’au
                            <?php echo esc($validDomain['expires_at']); ?>)<?php } ?>
                        </small>
                        <?php } else { ?>
                        <small style="color: var(--text-muted);">Protection 7j expirée</small>
                        <?php } ?>
                    </td>
                    <td style="padding: 12px;">
                        <div class="action-links" style="display: flex; gap: 8px; flex-wrap: wrap;">
                            <form action="<?php echo base_url('a/d/valid/' . $validDomain['id'] . '/dead'); ?>"
                                method="post">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn-action" style="background: var(--danger); color: #fff;"
                                    onclick="return confirm('Retirer ce domaine des liens valides et déclarer toutes ses cartes actives comme mortes ?');">
                                    Déclarer mort
                                </button>
                            </form>
                            <a href="<?php echo esc($validDomain['url'], 'attr'); ?>" target="_blank"
                                rel="noopener noreferrer" class="btn-action"
                                style="background: var(--text-muted); color: #fff;">Tester le lien</a>
                        </div>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <?php } ?>
</div>
<?php echo $this->endSection(); ?>
<?php echo $this->extend('layout'); ?>

<?php echo $this->section('content'); ?>
<div class="actions-container">
    <a href="<?php echo base_url('/'); ?>" onclick="return retourAuxCartes(event, this.href);"
        class="btn btn-cancel">Retour aux cartes</a>
    <button type="button" id="btn-force-cron"
        data-endpoint="<?php echo esc(base_url('a/d/scan')); ?>"
        class="btn" style="background: var(--warning); color: #fff; border: 0; cursor: pointer;">
        Lancer un scan complet
    </button>
    <span id="cron-scan-status" role="status" aria-live="polite"></span>
</div>

<div class="container" style="max-width: 1200px; margin: 0 auto; padding: 20px;">
    <div
        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
        <div>
            <h2 style="color: var(--danger); margin: 0;">Alertes Liens Morts (Erreur 404)</h2>
            <p style="margin: 5px 0 0 0;">Ces domaines ont été signalés par les utilisateurs et vérifiés comme
                inaccessibles.</p>
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

    <?php if (empty($deadItems)) { ?>
    <div class="empty-state"
        style="text-align: center; padding: 40px 0; color: var(--text-muted); background: var(--bg-body); border-radius: var(--radius-md); border: 1px dashed var(--border-color);">
        <p style="font-size: 1.2em; margin: 0;">🎉 Excellente nouvelle, tous les liens de streaming testés fonctionnent
            correctement !</p>
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
    <div class="admin-table-container fade-in">
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
                            <a href="<?php echo esc($url); ?>" target="_blank" class="btn-action"
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

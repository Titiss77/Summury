<?php echo $this->extend('layout'); ?>
<?php echo $this->section('content'); ?>

<div class="container" style="max-width: 1200px; margin: 0 auto; padding: 20px;">

    <div
        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
        <div>
            <h2 style="color: var(--danger); margin: 0;">Alertes Liens Morts (Erreur 404)</h2>
            <p style="margin: 5px 0 0 0;">Ces cartes possèdent des URL identifiées comme rompues lors du dernier cycle
                de maintenance automatique.</p>
        </div>
        <button id="btn-force-cron" class="btn"
            style="background-color: var(--primary); color: white; border: none; padding: 10px 18px; border-radius: 4px; font-weight: bold; cursor: pointer; transition: background 0.2s;">
            🔄 Relancer la vérification (Forcer le scan)
        </button>
    </div>

    <?php if (session()->has('message')) { ?>
    <div class="alert alert-success" style="margin-bottom: 20px;"><?php echo session('message'); ?></div>
    <?php } ?>
    <?php if (session()->has('error')) { ?>
    <div class="alert alert-danger" style="margin-bottom: 20px;"><?php echo session('error'); ?></div>
    <?php } ?>

    <div
        style="background: #f8f9fa; padding: 15px 20px; border-radius: 8px; margin-bottom: 25px; border: 1px solid #dee2e6;">
        <h4 style="margin-top: 0; color: #333; display: flex; align-items: center; gap: 8px;">
            🛠️ Migration de Domaine
        </h4>
        <p style="font-size: 0.9em; color: #555; margin-bottom: 15px;">
            Un site a changé d'adresse (ex: <em>https://sushiscan.net</em> devient <em>https://sushiscan.fr</em>) ? <br>
            Sélectionnez l'ancien domaine et entrez la nouvelle adresse. Le système mettra à jour tous les liens
            associés en conservant les structures de chapitres/épisodes.
        </p>
        <form action="<?php echo base_url('items/bulk-update-domain'); ?>" method="post"
            style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <?php echo csrf_field(); ?>

            <select name="old_domain" required
                style="flex: 1; min-width: 200px; padding: 10px; border: 1px solid #ccc; border-radius: 4px; background-color: white;">
                <option value="">-- Sélectionner l'ancien domaine --</option>
                <?php foreach ($domains as $domain) { ?>
                <option value="<?php echo esc($domain); ?>"><?php echo esc($domain); ?></option>
                <?php } ?>
            </select>

            <span style="font-weight: bold; color: #888;">➔</span>
            <input type="text" name="new_domain" placeholder="Nouveau domaine (ex: https://site.fr)" required
                style="flex: 1; min-width: 200px; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            <button type="submit" class="btn"
                style="background: var(--success, #28a745); color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; font-weight: bold;"
                onclick="return confirm('⚠️ Attention : Cette action va chercher TOUTES les cartes contenant l\'ancien domaine et les modifier. Êtes-vous sûr ?')">
                Mettre à jour les liens
            </button>
        </form>
    </div>

    <?php if (empty($deadItems)) { ?>
    <div class="empty-state"
        style="text-align: center; padding: 40px 0; color: #666; background: #fdfdfd; border-radius: 8px; border: 1px dashed #ddd;">
        <p style="font-size: 1.2em; margin: 0;">✅ Excellente nouvelle, tous les liens de streaming testés fonctionnent
            correctement !</p>
    </div>
    <?php } else { ?>
    <div class="admin-table-container fade-in">
        <table class="admin-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th>Titre de la carte</th>
                    <th>Domaine signalé cassé</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($deadItems as $item) { ?>
                <tr style="background-color: #fff8f8; border-bottom: 1px solid #eee;">
                    <td style="padding: 12px;"><strong><?php echo esc($item['titre']); ?></strong></td>
                    <td
                        style="padding: 12px; max-width: 400px; word-break: break-all; font-family: monospace; font-size: 0.85em; color: #dc3545;">
                        <?php echo esc($item['url_testee']); ?>
                    </td>
                    <td style="padding: 12px;">
                        <div class="action-links" style="display: flex; gap: 8px; flex-wrap: wrap;">
                            <a href="<?php echo base_url('item/form/'.$item['item_id']); ?>"
                                class="btn-action btn-edit">Mettre à jour manuellement</a>
                            <a href="<?php echo esc($item['url_testee']); ?>" target="_blank" class="btn-action"
                                style="background: #6c757d; color: white;">Tester</a>
                            <a href="<?php echo base_url('items/delete/'.$item['item_id']); ?>"
                                class="btn-action btn-ban"
                                onclick="return confirm('⚠️ Êtes-vous sûr de vouloir supprimer définitivement cette carte ainsi que tout son historique ? Cette action est irréversible.')"
                                style="background-color: #dc3545; color: white;">Supprimer la carte</a>
                        </div>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <?php } ?>
</div>

<script>
document.getElementById('btn-force-cron').addEventListener('click', function() {
    const btn = this;
    if (confirm(
            'Lancer la vérification complète de tous les liens maintenant ? Cela peut prendre quelques dizaines de secondes.'
        )) {
        btn.disabled = true;
        btn.style.opacity = '0.6';
        btn.innerHTML = '⏳ Analyse en cours... Veuillez patienter...';

        fetch('<?php echo base_url('cron/run?force=1'); ?>')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'executed') {
                    alert('Scan terminé avec succès !\n\nCartes inspectées : ' + data.total_cards +
                        '\nDomaines uniques interrogés : ' + data.unique_domains +
                        '\nNouveaux liens rompus identifiés : ' + data.dead_count);
                } else {
                    alert('Le scan a retourné un statut inattendu.');
                }
                window.location.reload();
            })
            .catch(error => {
                alert('Une erreur réseau ou un timeout est survenu durant le scan des serveurs distants.');
                btn.disabled = false;
                btn.style.opacity = '1';
                btn.innerHTML = '🔄 Relancer la vérification (Forcer le scan)';
            });
    }
});
</script>

<?php echo $this->endSection(); ?>
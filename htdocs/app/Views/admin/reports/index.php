<?php echo $this->extend('layout'); ?>
<?php echo $this->section('content'); ?>
<div class="container" style="max-width: 1200px; margin: 0 auto; padding: 20px;">

    <a href="<?php echo base_url('audit'); ?>" class="btn btn-cancel" style="margin-bottom: 20px;">Retour aux logs</a>

    <h2>Administration des Signalements</h2>
    <p>Gérez les retours des utilisateurs concernant les cartes (liens morts, erreurs, etc.).</p>

    <?php if (session()->has('message')) { ?>
    <div class="alert alert-success"><?php echo session('message'); ?></div>
    <?php } ?>
    <?php if (session()->has('error')) { ?>
    <div class="alert alert-danger"><?php echo session('error'); ?></div>
    <?php } ?>

    <div class="admin-table-container fade-in" style="margin-top: 20px;">
        <table class="admin-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Utilisateur</th>
                    <th>Carte ciblée</th>
                    <th>Type de problème</th>
                    <th>Description</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reports)) { ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px; color: #666;">Aucun signalement pour le
                        moment.</td>
                </tr>
                <?php } else { ?>
                <?php
                foreach ($reports as $report) {
                    // Reconstitution du lien final avec gestion des balises {ep}, {s}, etc.
                    $lien = $report['item_lien'] ?? '#';
                    $ep = $report['item_episode'] ?? '1';
                    $saison = $report['item_saison'] ?? '1';

                    $ep2 = str_pad((string) $ep, 2, '0', STR_PAD_LEFT);
                    $ep3 = str_pad((string) $ep, 3, '0', STR_PAD_LEFT);
                    $ep4 = str_pad((string) $ep, 4, '0', STR_PAD_LEFT);

                    $finalLink = str_replace(
                        ['{ep}', '{s}', '{ep2}', '{ep3}', '{ep4}'],
                        [$ep, $saison, $ep2, $ep3, $ep4],
                        $lien
                    );
                    ?>
                <tr
                    style="<?php echo 'resolved' === $report['status'] ? 'opacity: 0.6; background-color: #f8f9fa;' : ''; ?>">
                    <td><?php echo date('d/m/Y H:i', strtotime($report['created_at'])); ?></td>
                    <td>
                        <?php if ($report['username']) { ?>
                        <span
                            style="background: var(--primary); color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.85em;"><?php echo esc($report['username']); ?></span>
                        <?php } else { ?>
                        <span style="color: #999; font-style: italic;">Anonyme</span>
                        <?php } ?>
                    </td>
                    <td>
                        <strong><?php echo esc($report['item_titre'] ?? 'Carte supprimée'); ?></strong><br>
                        <small style="color: #666;">ID: <?php echo esc($report['item_id']); ?></small>
                    </td>
                    <td>
                        <span
                            style="font-weight: bold; color: var(--danger);"><?php echo esc($report['type']); ?></span>
                    </td>
                    <td style="max-width: 250px; font-size: 0.9em;"><?php echo esc($report['description']); ?></td>
                    <td>
                        <?php if ('pending' === $report['status']) { ?>
                        <span class="status-badge" style="background: var(--warning); color: #000;">En attente</span>
                        <?php } else { ?>
                        <span class="status-badge" style="background: var(--success); color: #fff;">Résolu</span>
                        <?php } ?>
                    </td>
                    <td>
                        <div class="action-links">
                            <?php if ('pending' === $report['status'] && $report['item_titre']) { ?>
                            <!-- Bouton "Tester le lien" -->
                            <a href="<?php echo htmlspecialchars($finalLink); ?>" target="_blank" class="btn-action"
                                style="background: #6c757d; color: white;">Tester le lien</a>

                            <!-- Nouveau bouton "Copier le lien" remplaçant "Modifier" -->
                            <button type="button" class="btn-action btn-edit"
                                onclick="copierLien('<?php echo htmlspecialchars($finalLink, ENT_QUOTES); ?>')"
                                style="border: none; cursor: pointer; font-family: inherit;">Copier le lien</button>

                            <!-- Bouton "Résoudre" -->
                            <a href="<?php echo base_url('reports/delete/'.$report['id']); ?>"
                                class="btn-action btn-unban"
                                style="background: var(--success); color: white;">Résolu</a>
                            <?php } ?>
                        </div>
                    </td>
                </tr>
                <?php } ?>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function copierLien(lien) {
    // 1. Essayer l'API moderne (HTTPS ou localhost)
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(lien).then(() => {
            notifierSucces();
        }).catch(err => {
            console.error('Erreur API Clipboard :', err);
            notifierErreur();
        });
    } else {
        // 2. Solution de secours (Fallback) pour le HTTP classique
        let textArea = document.createElement("textarea");
        textArea.value = lien;

        // Cacher la textarea pour que ça soit invisible pour l'utilisateur
        textArea.style.position = "fixed";
        textArea.style.top = "0";
        textArea.style.left = "0";
        textArea.style.opacity = "0";

        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();

        try {
            let successful = document.execCommand('copy');
            if (successful) {
                notifierSucces();
            } else {
                notifierErreur();
            }
        } catch (err) {
            console.error('Erreur Fallback :', err);
            notifierErreur();
        }

        document.body.removeChild(textArea);
    }
}

// Fonctions utilitaires pour éviter de répéter le code de notification
function notifierSucces() {
    if (typeof showToast === 'function') {
        showToast('Lien copié dans le presse-papiers !', 'success');
    } else {
        alert('Lien copié !');
    }
}

function notifierErreur() {
    if (typeof showToast === 'function') {
        showToast('Erreur lors de la copie du lien.', 'danger');
    } else {
        alert('Erreur lors de la copie.');
    }
}
</script>
<?php echo $this->endSection(); ?>
<?php echo $this->extend('layout'); ?>
<?php echo $this->section('content'); ?>
<div class="actions-container">
    <a href="<?php echo base_url('/'); ?>" class="btn btn-cancel">Retour aux cartes</a>
</div>

<div class="container" style="max-width: 1200px; margin: 0 auto; padding: 20px;">
    <h2>Journal d'Audit & Sécurité</h2>
    <p>Historique des actions récentes effectuées sur la plateforme.</p>

    <div style="display: flex; gap: 15px; margin-bottom: 20px;">
        <a href="<?php echo base_url('items/dead-links'); ?>" class="btn btn-warning">Gérer les liens morts</a>
    </div>

    <div class="admin-table-container fade-in" style="margin-top: 20px;">
        <table class="admin-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr
                    style="background-color: var(--bg-body); border-bottom: 2px solid var(--border-color); color: var(--text-main);">
                    <th style="padding: 12px; text-align: left;">Date & Heure</th>
                    <th style="padding: 12px; text-align: left;">Utilisateur</th>
                    <th style="padding: 12px; text-align: left;">Action</th>
                    <th style="padding: 12px; text-align: left;">Détails</th>
                    <th style="padding: 12px; text-align: left;">Adresse IP</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)) { ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px; color: var(--text-muted);">Aucun
                        historique
                        disponible.</td>
                </tr>
                <?php } else { ?>
                <?php foreach ($logs as $log) { ?>
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <td style="padding: 12px; font-size: 0.9em; color: var(--text-muted);">
                        <?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?>
                    </td>
                    <td style="padding: 12px;">
                        <?php if ($log['username']) { ?>
                        <span
                            style="background: var(--primary); color: #fff; padding: 3px 8px; border-radius: var(--radius-md); font-size: 0.85em;">
                            <?php echo esc($log['username']); ?>
                        </span>
                        <?php } else { ?>
                        <span style="color: var(--text-muted); font-style: italic;">Système / Invité</span>
                        <?php } ?>
                    </td>
                    <td style="padding: 12px; font-weight: bold; color: var(--text-main);">
                        <?php echo esc($log['action']); ?>
                    </td>
                    <td style="padding: 12px; font-size: 0.9em; color: var(--text-muted);">
                        <?php echo esc($log['details']); ?>
                    </td>
                    <td style="padding: 12px; font-size: 0.85em; font-family: monospace; color: var(--text-muted);">
                        <?php echo esc($log['ip_address']); ?>
                    </td>
                </tr>
                <?php } ?>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<?php echo $this->endSection(); ?>
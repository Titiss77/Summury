<?php echo $this->extend('layout'); ?>

<?php echo $this->section('content'); ?>
<div class="actions-container">
    <a href="<?php echo base_url('/'); ?>" class="btn btn-cancel">Retour aux cartes</a>
</div>

<div class="container" style="max-width: 800px; margin: 0 auto; padding: 20px;">
    <h2 style="margin-bottom: 2rem;">Mes Automatisations</h2>
    
    <?php if (session()->has('message')) { ?>
    <div class="alert alert-success"><?php echo session('message'); ?></div>
    <?php } ?>
    <?php if (session()->has('error')) { ?>
    <div class="alert alert-danger"><?php echo session('error'); ?></div>
    <?php } ?>

    <div class="card shadow-card" style="margin-bottom: 2rem;">
        <div class="card-body">
            <h3>Ajouter une automatisation</h3>
            <p style="color: var(--text-muted); font-size: 0.9em;">
                Le système scannera le lien RSS ou YouTube que vous fournissez à chaque maintenance (cron). S'il détecte une nouveauté, il l'ajoutera dans la catégorie choisie. <br>
                <strong>Astuce YouTube :</strong> Mettez le lien du flux RSS de la chaine : <code>https://www.youtube.com/feeds/videos.xml?channel_id=ID_DE_LA_CHAINE</code>
            </p>
            <form action="<?php echo base_url('automatisation/save'); ?>" method="POST">
                <?php echo csrf_field(); ?>
                
                <div class="form-group">
                    <label for="type" class="form-label">Type d'automatisation</label>
                    <select id="type" name="type" class="form-control" required>
                        <option value="youtube">Chaîne YouTube</option>
                        <option value="rss">Flux RSS (Anime/Série)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="source_url" class="form-label">Lien (URL) du Flux RSS ou Chaîne</label>
                    <input type="url" id="source_url" name="source_url" class="form-control" placeholder="https://..." required>
                </div>

                <div class="form-group row">
                    <div class="col-half">
                        <label for="id_division" class="form-label">Ranger dans : Division *</label>
                        <select id="id_division" name="id_division" class="form-control" required>
                            <option value="" disabled selected>-- Sélectionner --</option>
                            <?php if (isset($divisions)) { foreach ($divisions as $div) { ?>
                            <option value="<?php echo esc($div['id']); ?>"><?php echo esc($div['nom']); ?></option>
                            <?php } } ?>
                        </select>
                    </div>
                    <div class="col-half">
                        <label for="sous_categorie" class="form-label">Sous-catégorie (Optionnel)</label>
                        <input type="text" id="sous_categorie" name="sous_categorie" class="form-control" placeholder="Ex: Vidéos à voir">
                    </div>
                </div>

                <div class="form-actions" style="margin-top: 1rem; border: none;">
                    <button type="submit" class="btn btn-success">+ Créer l'automatisation</button>
                </div>
            </form>
        </div>
    </div>

    <h3 style="margin-bottom: 1.5rem;">Automatisations actives</h3>
    <?php if (empty($automations)) { ?>
    <div class="empty-state" style="padding: 2rem;">
        <p>Aucune automatisation en place pour le moment.</p>
    </div>
    <?php } else { ?>
    <div class="admin-table-container fade-in">
        <table class="admin-table" style="width: 100%;">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Lien Source</th>
                    <th>Dernier ajout</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($automations as $auto) { ?>
                <tr>
                    <td>
                        <span class="status-badge active" style="text-transform: uppercase;">
                            <?php echo esc($auto['type']); ?>
                        </span>
                    </td>
                    <td style="max-width: 200px; word-wrap: break-word; font-size: 0.9em;">
                        <a href="<?php echo esc($auto['source_url']); ?>" target="_blank" style="color: var(--primary);">Ouvrir le lien</a>
                    </td>
                    <td>
                        <?php if ($auto['last_item_id']) { ?>
                            <span style="color: var(--success); font-size: 0.85em;">Actif</span>
                        <?php } else { ?>
                            <span style="color: var(--text-muted); font-size: 0.85em;">En attente de scan</span>
                        <?php } ?>
                    </td>
                    <td>
                        <a href="<?php echo base_url('automatisation/delete/' . $auto['id']); ?>" class="btn-action btn-ban" onclick="return confirm('Supprimer cette règle ?');">Supprimer</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <?php } ?>
</div>
<?php echo $this->endSection(); ?>

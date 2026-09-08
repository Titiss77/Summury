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
                Le système surveillera automatiquement le lien que vous lui donnez. S'il détecte une nouveauté, il
                créera la carte dans la catégorie choisie. <br>
                <strong>Pour une nouvelle saison :</strong> Collez le lien de la saison actuelle. Le système devinera
                l'URL de la saison suivante (ex: <i>s=4</i> deviendra <i>s=5</i>) et vérifiera tous les jours si elle
                est sortie !
            </p>
            <form action="<?php echo base_url('automatisation/save'); ?>" method="POST">
                <?php echo csrf_field(); ?>

                <div class="form-group">
                    <label for="type" class="form-label">Type d'automatisation</label>
                    <select id="type" name="type" class="form-control" required>
                        <option value="youtube">Chaîne YouTube (Nouvelle vidéo)</option>
                        <option value="rss">Site Web (Nouvel article/épisode via RSS)</option>
                        <option value="next_season">Surveillance de suite (Saison suivante)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="source_url" class="form-label">Lien du site, de la chaîne ou de la saison
                        actuelle</label>
                    <input type="url" id="source_url" name="source_url" class="form-control"
                        placeholder="ex: https://franime.fr/...s=4..." required>
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
                        <input type="text" id="sous_categorie" name="sous_categorie" class="form-control"
                            placeholder="Ex: À regarder">
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
                    <th>Lien Surveillé</th>
                    <th>Dernier ajout</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($automations as $auto) { ?>
                <tr>
                    <td>
                        <span class="status-badge active" style="text-transform: uppercase;">
                            <?php echo esc(str_replace('_', ' ', $auto['type'])); ?>
                        </span>
                    </td>
                    <td style="max-width: 200px; word-wrap: break-word; font-size: 0.9em;">
                        <?php if ($auto['type'] === 'next_season') { ?>
                        <i>Cible :</i> <br>
                        <a href="<?php echo esc($auto['last_item_id']); ?>" target="_blank"
                            style="color: var(--primary); font-size: 0.85em;"><?php echo esc($auto['last_item_id']); ?></a>
                        <?php } else { ?>
                        <a href="<?php echo esc($auto['source_url']); ?>" target="_blank"
                            style="color: var(--primary);">Ouvrir le flux</a>
                        <?php } ?>
                    </td>
                    <td>
                        <?php if ($auto['type'] === 'next_season') { ?>
                        <span style="color: var(--warning); font-size: 0.85em;">En attente de la sortie</span>
                        <?php } elseif ($auto['last_item_id']) { ?>
                        <span style="color: var(--success); font-size: 0.85em;">Actif</span>
                        <?php } else { ?>
                        <span style="color: var(--text-muted); font-size: 0.85em;">En attente de scan</span>
                        <?php } ?>
                    </td>
                    <td>
                        <a href="<?php echo base_url('automatisation/delete/' . $auto['id']); ?>"
                            class="btn-action btn-ban"
                            onclick="return confirm('Supprimer cette règle ?');">Supprimer</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <?php } ?>
</div>
<?php echo $this->endSection(); ?>
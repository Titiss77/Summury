<?php echo $this->extend('layout'); ?>
<?php echo $this->section('content'); ?>

<div class="form-container card">
    <h2 class="header-title"><?php echo isset($item) ? '📝 Modifier la carte' : '+ Ajouter une carte'; ?></h2>

    <form action="<?php echo base_url('item/save'); ?>" method="POST">
        <input type="hidden" name="redirect_url" value="<?php echo esc($redirect_url); ?>">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="id" value="<?php echo isset($item) ? esc($item->id) : ''; ?>">

        <div style="text-align: right; margin-bottom: 10px;">
            <button type="button" id="btn-api-search" class="btn btn-primary btn-sm">🔍 Auto-remplir</button>
            <small id="api-status" style="display:none; color: var(--success);"></small>
        </div>

        <div class="form-group" style="position: relative;">
            <label for="titre" class="form-label">Titre *</label>
            <input type="text" id="titre" name="titre" class="form-control"
                value="<?php echo isset($item) ? esc($item->titre) : ''; ?>" required>

            <div id="api-results-container"
                style="display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 1000; background: var(--bg-card, #fff); border: 1px solid var(--border-color, #ccc); border-radius: var(--radius-md); max-height: 350px; overflow-y: auto; box-shadow: 0 4px 12px rgba(0,0,0,0.15); margin-top: 5px;">
            </div>
        </div>

        <div class="form-group row">
            <div class="col-half">
                <label for="id_division" class="form-label">Division *</label>
                <select id="id_division" name="id_division" class="form-control" required>
                    <option value="" disabled <?php echo !isset($item) ? 'selected' : ''; ?>>-- Sélectionner --</option>
                    <?php foreach ($divisions as $div) { ?>
                    <option value="<?php echo esc($div['id']); ?>"
                        <?php echo (isset($item) && $item->id_division == $div['id']) ? 'selected' : ''; ?>>
                        <?php echo esc($div['nom']); ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="col-half">
                <label for="sous_categorie_select" class="form-label">Sous-catégorie (Optionnel)</label>
                <select id="sous_categorie_select" name="sous_categorie_select" class="form-control"
                    onchange="toggleNewSubCategory()">
                    <option value="">-- Aucune --</option>
                    <?php
                    $itemSub = isset($item) ? $item->sous_categorie : '';
$found = false;

if (isset($subCategories) && is_array($subCategories)) {
    foreach ($subCategories as $sub) {
        $selected = ($itemSub === $sub) ? 'selected' : '';
        if ($selected) {
            $found = true;
        }
        ?>
                    <option value="<?php echo esc($sub); ?>" <?php echo $selected; ?>><?php echo esc($sub); ?></option>
                    <?php }
    } ?>

                    <?php if ($itemSub && !$found) { ?>
                    <option value="<?php echo esc($itemSub); ?>" selected><?php echo esc($itemSub); ?></option>
                    <?php } ?>

                    <option value="__NEW__" style="font-weight: bold; color: var(--primary);">+ Créer une nouvelle...
                    </option>
                </select>

                <input type="text" id="sous_categorie_new" name="sous_categorie_new" class="form-control"
                    style="display: none; margin-top: 10px;" placeholder="Nom de la nouvelle sous-catégorie">
            </div>
        </div>

        <div class="form-group row">
            <div class="col-half">
                <label for="status" class="form-label">Statut</label>
                <select id="status" name="status" class="form-control">
                    <?php $currentStatus = isset($item) ? $item->status : 'Aucun'; ?>
                    <option value="Aucun" <?php echo 'Aucun' == $currentStatus ? 'selected' : ''; ?>>Aucun</option>
                    <option value="À voir" <?php echo 'À voir' == $currentStatus ? 'selected' : ''; ?>>À voir</option>
                    <option value="En cours" <?php echo 'En cours' == $currentStatus ? 'selected' : ''; ?>>En cours
                    </option>
                    <option value="En pause" <?php echo 'En pause' == $currentStatus ? 'selected' : ''; ?>>En pause
                    </option>
                    <option value="Terminé" <?php echo 'Terminé' == $currentStatus ? 'selected' : ''; ?>>Terminé
                    </option>
                </select>
            </div>
            <div class="col-half" style="display: flex; align-items: flex-end; padding-bottom: 5px;">
                <div>
                    <input type="checkbox" id="is_public" name="is_public" value="1"
                        <?php echo (isset($item) && in_array($item->is_public, [1, 2])) ? 'checked' : ''; ?>>
                    <label for="is_public" class="form-label" style="display:inline; margin-left: 8px;">Rendre ce lien
                        visible au public</label>
                    <small style="display: block; margin-top: 5px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="red"
                            class="bi bi-exclamation-circle-fill" viewBox="0 0 16 16">
                            <path
                                d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4m.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2" />
                        </svg>
                        Attention : Perte de l'épisode si publié.
                    </small>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="description" class="form-label">Description</label>
            <textarea id="description" name="description" class="form-control" rows="1"
                maxlength="250"><?php echo isset($item) ? esc($item->description) : ''; ?></textarea>
            <small id="char-count"
                style="display: block; text-align: right; margin-top: 5px; font-weight: bold;"></small>
        </div>

        <div class="form-group">
            <label for="date_sortie" class="form-label">Date et heure de sortie</label>
            <div style="display: grid; gap: 10px;">
                <input type="datetime-local" id="date_sortie" name="date_sortie" class="form-control"
                    value="<?php echo (isset($item) && $item->date_sortie) ? date('Y-m-d\TH:i', strtotime($item->date_sortie)) : ''; ?>">
                <button type="button" class="btn btn-cancel"
                    onclick="document.getElementById('date_sortie').value = '';">Ne pas définir</button>
            </div>
        </div>

        <div class="form-group">
            <label for="img" class="form-label">Image (URL) :</label>
            <small style="display: block; margin-bottom: 10px; color: var(--text-muted);">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="yellow"
                    class="bi bi-lightbulb-fill" viewBox="0 0 16 16">
                    <path
                        d="M2 6a6 6 0 1 1 10.174 4.31c-.203.196-.359.4-.453.619l-.762 1.769A.5.5 0 0 1 10.5 13h-5a.5.5 0 0 1-.46-.302l-.761-1.77a2 2 0 0 0-.453-.618A5.98 5.98 0 0 1 2 6m3 8.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1l-.224.447a1 1 0 0 1-.894.553H6.618a1 1 0 0 1-.894-.553L5.5 15a.5.5 0 0 1-.5-.5" />
                </svg>
                <strong>Astuce :</strong> Si l'image automatique ne convient pas, trouvez des affiches de haute
                qualité sur
                <a href="https://theposterdb.com/" target="_blank"
                    style="color: var(--primary); text-decoration: underline; font-weight: 500;">TPDb (Films/Séries)</a>
                ou
                <a href="https://myanimelist.net/" target="_blank"
                    style="color: var(--primary); text-decoration: underline; font-weight: 500;">MyAnimeList</a>.<br>
                Format recommandé : <strong>400x600 (Ratio 2:3)</strong>.
            </small>

            <div style="display: flex; gap: 15px; align-items: flex-start;">
                <div style="flex-grow: 1;">
                    <input type="text" id="img" name="image" class="form-control"
                        value="<?php echo htmlspecialchars($item->image ?? ''); ?>"
                        placeholder="https://exemple.com/image.jpg">
                </div>
                <div
                    style="flex-shrink: 0; width: 80px; height: 120px; border: 2px dashed var(--border-color); border-radius: var(--radius-md); overflow: hidden; display: flex; align-items: center; justify-content: center; background: var(--bg-body); transition: var(--transition);position: relative; top: -40px;">
                    <img id="img-preview" src="<?php echo htmlspecialchars($item->image ?? ''); ?>" alt="Aperçu"
                        style="width: 100%; height: 100%; object-fit: cover; display: <?php echo !empty($item->image) ? 'block' : 'none'; ?>;">
                    <span id="img-placeholder"
                        style="color: var(--text-muted); font-size: 0.8rem; text-align: center; padding: 5px; display: <?php echo empty($item->image) ? 'block' : 'none'; ?>;">
                        Aperçu
                    </span>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="lien" class="form-label">Lien (URL) :</label>
            <small><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="yellow"
                    class="bi bi-lightbulb-fill" viewBox="0 0 16 16">
                    <path
                        d="M2 6a6 6 0 1 1 10.174 4.31c-.203.196-.359.4-.453.619l-.762 1.769A.5.5 0 0 1 10.5 13h-5a.5.5 0 0 1-.46-.302l-.761-1.77a2 2 0 0 0-.453-.618A5.98 5.98 0 0 1 2 6m3 8.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1l-.224.447a1 1 0 0 1-.894.553H6.618a1 1 0 0 1-.894-.553L5.5 15a.5.5 0 0 1-.5-.5" />
                </svg>
                Astuce : <b>{s}</b> = saison, <b>{ep}</b> = épisode normal (1). <br>
                Utilise <b>{ep2}</b>, <b>{ep3}</b> ou <b>{ep4}</b> pour forcer les zéros (ex: <b>01</b>, <b>001</b>,
                <b>0001</b>).
            </small>
            <input type="text" id="lien" name="lien" class="form-control"
                value="<?php echo htmlspecialchars($item->lien ?? ''); ?>">
        </div>

        <div class="form-group row">
            <div class="col-half">
                <label for="saison" class="form-label">Saison</label>
                <input type="number" id="saison" name="saison" min="0" class="form-control"
                    value="<?php echo isset($item) ? esc($item->saison) : ''; ?>">
            </div>
            <div class="col-half">
                <label for="episode" class="form-label">Épisode</label>
                <input type="number" id="episode" name="episode" min="0" class="form-control"
                    value="<?php echo isset($item) ? esc($item->episode) : ''; ?>">
            </div>
        </div>

        <div class="form-actions">
            <a href="<?php echo base_url('/'); ?>" class="btn btn-cancel">Annuler</a>
            <button type="submit" class="btn btn-success">Enregistrer</button>
        </div>
    </form>
</div>

<?php echo $this->endSection(); ?>
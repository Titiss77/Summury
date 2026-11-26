<?php echo $this->extend('layout'); ?>

<?php echo $this->section('content'); ?>
<div class="actions-container">
    <a href="<?php echo base_url('a/sc'); ?>" class="btn btn-cancel">Retour aux configurations</a>
</div>

<div class="form-container card fade-in" style="max-width: 800px; margin: 0 auto; padding: 3rem;">
    <h2 class="header-title"><?php echo isset($config) ? 'Modifier la configuration' : 'Ajouter une configuration'; ?>
    </h2>

    <?php if (session()->has('errors')) { ?>
    <div class="alert alert-danger">
        <ul style="margin: 0; padding-left: 20px;">
            <?php foreach (session('errors') as $error) { ?>
            <li><?php echo esc($error); ?></li>
            <?php } ?>
        </ul>
    </div>
    <?php } ?>
    <?php if (session()->has('error')) { ?>
    <div class="alert alert-danger"><?php echo esc(session('error')); ?></div>
    <?php } ?>

    <form action="<?php echo base_url('a/sc/save'); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="id" value="<?php echo isset($config) ? esc($config['id']) : ''; ?>">

        <div class="form-group row">
            <div class="col-half">
                <label for="domain" class="form-label">Domaine *</label>
                <input type="text" id="domain" name="domain" class="form-control" placeholder="ex: voir-anime.to"
                    value="<?php echo isset($config) ? esc($config['domain']) : old('domain'); ?>" required>
            </div>
            <div class="col-half">
                <label for="regex_episode" class="form-label">Regex Épisode *</label>
                <input type="text" id="regex_episode" name="regex_episode" class="form-control"
                    placeholder="ex: /-(\d+)-vostfr/i"
                    value="<?php echo isset($config) ? esc($config['regex_episode']) : old('regex_episode'); ?>"
                    required>
            </div>
        </div>

        <div class="form-group">
            <label for="indicateurs_page_invalide" class="form-label">Indicateurs de page invalide (Tableau
                JSON)</label>
            <textarea id="indicateurs_page_invalide" name="indicateurs_page_invalide" class="form-control" rows="3"
                placeholder='["Premier EP", "Dernier EP"]'><?php echo isset($config) ? esc($config['indicateurs_page_invalide']) : old('indicateurs_page_invalide', '[]'); ?></textarea>
            <small class="form-help">Doit être un format JSON valide (ex: <code>["Texte 1", "Texte 2"]</code>).</small>
        </div>

        <div class="form-group">
            <label for="indicateurs_lecteur" class="form-label">Indicateurs de lecteur (Tableau JSON)</label>
            <textarea id="indicateurs_lecteur" name="indicateurs_lecteur" class="form-control" rows="3"
                placeholder='["class=\"lecteur\"", "<iframe"]'><?php echo isset($config) ? esc($config['indicateurs_lecteur']) : old('indicateurs_lecteur', '[]'); ?></textarea>
            <small class="form-help">Doit être un format JSON valide. (Si vous utilisez <code>"</code> dans la chaine,
                il faut l'échapper <code>\"</code>)</small>
        </div>

        <div class="form-group">
            <div class="form-check">
                <input type="checkbox" id="is_active" name="is_active" class="form-check-input" value="1"
                    <?php echo (!isset($config) || $config['is_active'] == 1) ? 'checked' : ''; ?>>
                <label for="is_active" class="form-check-label"
                    style="font-weight: 600; color: var(--text-main);">Activer le parsing pour ce domaine</label>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-success" style="width: 100%;">Enregistrer</button>
        </div>
    </form>
</div>
<?php echo $this->endSection(); ?>

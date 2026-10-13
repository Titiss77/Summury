<?php echo $this->extend('layout'); ?>

<?php echo $this->section('content'); ?>
<div class="actions-container">
    <a href="<?php echo base_url('a/u'); ?>" class="btn btn-cancel">Retour aux utilisateurs</a>
</div>

<div class="container mt-5 fade-in">
    <h2 class="header-title" style="margin-bottom: 2rem;">Profil & Modification : <?php echo esc($user->username); ?></h2>

    <?php if (session()->has('errors')) { ?>
        <div class="alert alert-danger">
            <ul style="margin: 0; padding-left: 20px;">
                <?php foreach (session('errors') as $error) { ?>
                    <li><?php echo esc($error); ?></li>
                <?php } ?>
            </ul>
        </div>
    <?php } ?>

    <div style="display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-start;">
        <!-- Colonne 1 : Formulaire de modification -->
        <div class="card shadow-card" style="flex: 1; min-width: 300px;">
            <div class="card-body">
                <h3 style="margin-bottom: 1.5rem; color: var(--primary); font-size: 1.25rem;">Paramètres du compte</h3>
                <form action="<?php echo base_url('a/u/'.$user->id); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    
                    <div class="form-group mb-3">
                        <label for="username" class="form-label">Nom d'utilisateur</label>
                        <input type="text" name="username" id="username" class="form-control"
                            value="<?php echo esc($user->username); ?>" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="group" class="form-label">Rôle (Groupe)</label>
                        <select name="group" id="group" class="form-control">
                            <?php foreach ($availableGroups as $group => $info) { ?>
                                <option value="<?php echo $group; ?>" <?php echo $user->inGroup($group) ? 'selected' : ''; ?>>
                                    <?php echo esc($info['title'] ?? $group); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Adresse e-mail</label>
                        <input type="text" class="form-control" value="<?php echo esc($user->email); ?>" disabled style="background: var(--bg-body); cursor: not-allowed; opacity: 0.7;">
                        <small style="color: var(--text-muted); display: block; margin-top: 4px;">L'adresse e-mail n'est pas modifiable par l'administration.</small>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-success" style="width: 100%;">Enregistrer les modifications</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Colonne 2 : Statistiques et données de l'utilisateur -->
        <div class="card shadow-card" style="flex: 1; min-width: 300px;">
            <div class="card-body">
                <h3 style="margin-bottom: 1.5rem; color: var(--info); font-size: 1.25rem;">Données de la collection</h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px;">
                    <div style="background: var(--bg-body); padding: 15px; border-radius: var(--radius-md); text-align: center; border: 1px solid var(--border-color);">
                        <div style="font-size: 2.2rem; font-weight: 800; color: var(--primary); line-height: 1;"><?php echo esc($stats['total_items']); ?></div>
                        <div style="font-size: 0.9rem; color: var(--text-muted); margin-top: 5px;">Cartes totales</div>
                    </div>
                    <div style="background: var(--bg-body); padding: 15px; border-radius: var(--radius-md); text-align: center; border: 1px solid var(--border-color);">
                        <div style="font-size: 2.2rem; font-weight: 800; color: var(--success); line-height: 1;"><?php echo esc($stats['public_items']); ?></div>
                        <div style="font-size: 0.9rem; color: var(--text-muted); margin-top: 5px;">Cartes publiques</div>
                    </div>
                </div>

                <h4 style="font-size: 1.05rem; margin-bottom: 10px; color: var(--text-main); text-transform: uppercase; letter-spacing: 0.5px;">Répartition par statut</h4>
                <ul style="list-style: none; padding: 0; margin: 0; color: var(--text-muted);">
                    <li style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border-color);">
                        <span>En cours</span> <strong><?php echo esc($stats['status_en_cours']); ?></strong>
                    </li>
                    <li style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border-color);">
                        <span>À voir</span> <strong><?php echo esc($stats['status_a_voir']); ?></strong>
                    </li>
                    <li style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border-color);">
                        <span>Terminé</span> <strong><?php echo esc($stats['status_termine']); ?></strong>
                    </li>
                    <li style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border-color);">
                        <span>En pause</span> <strong><?php echo esc($stats['status_en_pause']); ?></strong>
                    </li>
                </ul>

                <h4 style="font-size: 1.05rem; margin-top: 25px; margin-bottom: 10px; color: var(--text-main); text-transform: uppercase; letter-spacing: 0.5px;">Séries (En cours & En pause)</h4>
                <ul style="list-style: none; padding: 0; margin: 0; color: var(--text-muted);">
                    <li style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border-color); align-items: center;">
                        <span>Épisodes restants à voir</span> 
                        <span class="badge badge-episode" style="font-size: 1rem; padding: 4px 10px;"><?php echo esc($stats['total_episodes_en_cours'] + $stats['total_episodes_en_pause']); ?></span>
                    </li>
                    <li style="display: flex; justify-content: space-between; padding: 8px 0; align-items: center;">
                        <span>Saisons restantes</span> 
                        <span class="badge badge-season" style="font-size: 1rem; padding: 4px 10px;"><?php echo esc($stats['total_series_en_cours'] + $stats['total_series_en_pause']); ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php echo $this->endSection(); ?>
<?php echo $this->extend('layout'); ?>
<?php echo $this->section('content'); ?>

<div class="container mt-5">
    <h2>Modifier l'utilisateur : <?php echo esc($user->username); ?></h2>

    <?php if (session()->has('errors')) { ?>
    <div class="alert alert-danger">
        <ul>
            <?php foreach (session('errors') as $error) { ?>
            <li><?php echo esc($error); ?></li>
            <?php } ?>
        </ul>
    </div>
    <?php } ?>

    <form action="<?php echo base_url('users/update/'.$user->id); ?>" method="POST">
        <?php echo csrf_field(); ?>

        <div class="mb-3">
            <label for="username" class="form-label">Nom d'utilisateur</label>
            <input type="text" name="username" id="username" class="form-control"
                value="<?php echo esc($user->username); ?>" required>
        </div>

        <div class="mb-3">
            <label for="group" class="form-label">Rôle (Groupe)</label>
            <select name="group" id="group" class="form-select">
                <?php foreach ($availableGroups as $group => $info) { ?>
                <option value="<?php echo $group; ?>" <?php echo $user->inGroup($group) ? 'selected' : ''; ?>>
                    <?php echo esc($info['title'] ?? $group); ?>
                </option>
                <?php } ?>
            </select>
        </div>

        <?php /* BLOC : Réservé au superadmin */ ?>
        <?php if (auth()->user()->inGroup('superadmin')) { ?>
        <div class="form-floating form-floating2 mb-3 password-wrapper">
            <label for="floatingPasswordInput" class="form-label">Nouveau mot de passe <small
                    class="text-muted">(laisser
                    vide
                    pour conserver l'actuel)</small></label>
            <input type="password" class="form-control" id="floatingPasswordInput" name="password" inputmode="text"
                autocomplete="current-password" placeholder="<?php echo lang('Auth.password'); ?>" required>
            <button type="button" class="password-toggle password-toggle2"
                aria-label="Afficher le mot de passe"></button>
        </div>
        <?php } ?>

        <div class="mt-4">
            <button type="submit" class="btn btn-success">Enregistrer les modifications</button>
            <a href="<?php echo base_url('users'); ?>" class="btn btn-secondary">Retour</a>
        </div>
    </form>
</div>
<?php echo $this->endSection(); ?>
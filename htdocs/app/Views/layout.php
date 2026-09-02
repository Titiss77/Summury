<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo env('SITENAME'); ?></title>
    <!-- DNS Prefetch / Preconnect pour accélérer le chargement des images externes -->
    <link rel="preconnect" href="https://image.tmdb.org" crossorigin>
    <link rel="preconnect" href="https://cdn.myanimelist.net" crossorigin>
    <link rel="dns-prefetch" href="https://image.tmdb.org">
    <link rel="dns-prefetch" href="https://cdn.myanimelist.net">

    <script>
    /* Theme Sombre */
    if (localStorage.getItem('theme') === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
        document.documentElement.setAttribute('data-bs-theme', 'dark');
    }
    </script>

    <?php
    // Cache-busting intelligent : Ne met à jour la version que si le fichier a été modifié
    $rootCssVersion = file_exists(FCPATH.'assets/root.css') ? filemtime(FCPATH.'assets/root.css') : '1';
    $styleCssVersion = file_exists(FCPATH.'assets/style.css') ? filemtime(FCPATH.'assets/style.css') : '1';
    $scriptJsVersion = file_exists(FCPATH.'assets/script.js') ? filemtime(FCPATH.'assets/script.js') : '1';
    ?>

    <link rel="stylesheet" href="<?php echo base_url('assets/root.css?v='.$rootCssVersion); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/style.css?v='.$styleCssVersion); ?>">

    <meta name="csrf-token" content="<?php echo csrf_hash(); ?>">
    <meta name="csrf-header" content="<?php echo csrf_header(); ?>">
    <meta id="meta-theme-color" name="theme-color" content="#fcfcfd" media="(prefers-color-scheme: light)">

    <script>
    /* Configuration Globale (Sécurisée contre la minification) */
    window.siteConfig = {
        "baseUrl": "<?php echo rtrim(base_url(), '/').'/'; ?>",
        "updateOrderUrl": "<?php echo base_url('items/update-order'); ?>",
        "cronUrl": "<?php echo base_url('cron/run'); ?>",
        "csrfHeader": "<?php echo csrf_header(); ?>",
        "csrfToken": "<?php echo csrf_hash(); ?>",
        "tmdbApiKey": "9774091bee3bd236f4438cd6d8caa8d8"
    };
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"
        defer>
    </script>
    <script src="<?php echo base_url('assets/script.js?v='.$scriptJsVersion); ?>" defer></script>
</head>

<body>
    <div id="toast-container" class="toast-container"></div>
    <header class="main-header">
        <h1>
            <a href="<?php echo base_url('/'); ?>" style="color:inherit;">
                <!-- Décodage asynchrone pour le logo -->
                <img class="logo-site" src="<?php echo base_url('favicon.ico'); ?>"
                    alt="<?php echo env('SITENAME'); ?> Logo" decoding="async"><?php echo env('SITENAME'); ?>
            </a>
        </h1>
        <div class="user-nav">
            <button id="theme-toggle" class="btn-theme" aria-label="Toggle Theme">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                    class="bi bi-moon-stars-fill" viewBox="0 0 16 16">
                    <path
                        d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278" />
                    <path
                        d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.73 1.73 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.73 1.73 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.73 1.73 0 0 0 1.097-1.097zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.16 1.16 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.16 1.16 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732z" />
                </svg>
            </button>
            <?php if (auth()->loggedIn()) { ?>
            <?php if (auth()->user()->inGroup('admin', 'superadmin')) { ?>
            <a href="<?php echo base_url('audit'); ?>" class="logs">
                logs
            </a>
            <?php } ?>

            <a href="<?php echo base_url('profile'); ?>" class="welcome-text"
                style="text-decoration: none; display: flex; align-items: center; gap: 5px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor"
                    class="bi bi-person-circle" viewBox="0 0 16 16">
                    <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0" />
                    <path fill-rule="evenodd"
                        d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1" />
                </svg>
                <?php echo esc(auth()->user()->username); ?>
            </a>
            <a href="<?php echo base_url('logout'); ?>" class="btn-logout">Déconnexion</a>
            <?php } else { ?>
            <a href="<?php echo base_url('login'); ?>" class="btn-login">Connexion</a>
            <a href="<?php echo base_url('register'); ?>" class="btn-register">Créer un compte</a>
            <?php } ?>
        </div>
    </header>

    <?php 
    // Modification : on affiche le menu des headers même si l'utilisateur n'est pas connecté
    if (isset($headersWithNoLogin) && !empty($headersWithNoLogin)) { 
    ?>
    <?php 
    // Modification : on affiche le menu des headers même si l'utilisateur n'est pas connecté
    if (auth()->loggedIn() && isset($headersWithLogin) && !empty($headersWithLogin)) { 
    ?>
    <nav class="category-nav container">
        <?php foreach ($headersWithLogin as $h) { ?>
        <a href="<?php echo base_url('categorie/'.$h['id']); ?>"
            class="nav-tab <?php echo (isset($currentHeaderId) && $currentHeaderId == $h['id']) ? 'active' : ''; ?>">
            <?php echo esc($h['nom']); ?>
        </a>
        <?php } ?>
    </nav>
    <?php } else { ?>
    <nav class="category-nav container">
        <?php foreach ($headersWithNoLogin as $h) { ?>
        <a href="<?php echo base_url('categorie/'.$h['id']); ?>"
            class="nav-tab <?php echo (isset($currentHeaderId) && $currentHeaderId == $h['id']) ? 'active' : ''; ?>">
            <?php echo esc($h['nom']); ?>
        </a>
        <?php } ?>
    </nav>
    <?php } ?>
    <?php } else { ?>
    <nav class="category-nav container"></nav>
    <?php } ?>

    <main class="container">
        <?php echo $this->renderSection('content'); ?>
    </main>

    <footer class="main-footer"
        style="margin-top: 4rem; padding: 3rem 0 1.5rem; background: var(--bg-card); border-top: 1px solid var(--border-color);">
        <div class="container"
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-bottom: 2rem;">

            <!-- Colonne 1 : Logo & Présentation -->
            <div>
                <h3
                    style="color: var(--text-main); font-size: 1.2rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 10px;">
                    <img src="<?php echo base_url('favicon.ico'); ?>" alt="Logo" class="logo-footer">
                    <?php echo env('SITENAME'); ?>
                </h3>
                <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.6;">
                    Votre tableau de bord personnel pour centraliser et suivre votre progression sur vos œuvres
                    préférées (Films, Séries, Animés, Mangas).
                </p>
            </div>

            <!-- Colonne 2 : Navigation Rapide -->
            <div>
                <h4 style="color: var(--text-main); font-size: 1rem; margin-bottom: 1rem;">Navigation</h4>
                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.6rem;"
                    class="footer-links">
                    <li><a href="<?php echo base_url('/'); ?>">Accueil</a></li>
                    <?php if (auth()->loggedIn()) { ?>
                    <li><a href="<?php echo base_url('profile'); ?>">Mon Profil</a></li>
                    <li><a href="<?php echo base_url('item/form'); ?>">Ajouter une carte</a></li>
                    <?php } else { ?>
                    <li><a href="<?php echo base_url('login'); ?>">Connexion</a></li>
                    <li><a href="<?php echo base_url('register'); ?>">Créer un compte</a></li>
                    <?php } ?>
                </ul>
            </div>

            <!-- Colonne 3 : Informations & Légal -->
            <div>
                <h4 style="color: var(--text-main); font-size: 1rem; margin-bottom: 1rem;">Informations</h4>
                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.6rem;"
                    class="footer-links">
                    <li><a href="<?php echo base_url('legal'); ?>">Mentions légales</a></li>
                    <li><a href="<?php echo base_url('privacy'); ?>">Politique de confidentialité</a></li>
                    <li><a href="mailto:contact@ton-domaine.com">Contactez-nous</a></li>
                </ul>
            </div>

        </div>

        <!-- Copyright (Ton code d'origine centré) -->
        <div style="text-align: center; padding-top: 1.5rem; border-top: 1px solid rgba(128, 128, 128, 0.1);">
            <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0;">
                &copy; <?php echo date('Y'); ?> <?php echo env('SITENAME'); ?>. Tous droits réservés.
            </p>
        </div>
    </footer>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        /* Interception des Flashdata de CodeIgniter 4 pour lancer des Toasts automatiquement */
        <?php if (session()->getFlashdata('success')) { ?>
        if (typeof showToast === 'function') showToast(
            "<?php echo esc(session()->getFlashdata('success')); ?>",
            "success");
        <?php } ?>
        <?php if (session()->getFlashdata('error')) { ?>
        if (typeof showToast === 'function') showToast(
            "<?php echo esc(session()->getFlashdata('error')); ?>",
            "danger");
        <?php } ?>
        <?php if (session()->getFlashdata('message')) { ?>
        if (typeof showToast === 'function') showToast(
            "<?php echo esc(session()->getFlashdata('message')); ?>",
            "info");
        <?php } ?>
    });
    </script>
</body>

</html>
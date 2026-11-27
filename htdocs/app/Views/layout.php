<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo env('SITENAME'); ?></title>
    <meta name="description"
        content="Votre tableau de bord personnel pour centraliser et suivre votre progression sur vos œuvres préférées.">
    <meta property="og:title" content="<?php echo env('SITENAME'); ?>">
    <meta property="og:description" content="Centralisez et suivez votre progression sur vos œuvres préférées.">
    <meta property="og:image" content="<?php echo base_url('favicon.ico?v=2'); ?>">
    <meta property="og:url" content="<?php echo base_url(); ?>">
    <meta property="og:type" content="website">

    <link rel="icon" type="image/x-icon" href="<?php echo base_url('favicon.ico?v=2'); ?>">
    <link rel="apple-touch-icon" href="<?php echo base_url('favicon.ico?v=2'); ?>">

    <!-- Préconnexion aux API externes pour optimiser le chargement des images -->
    <link rel="preconnect" href="https://image.tmdb.org" crossorigin>
    <link rel="preconnect" href="https://cdn.myanimelist.net" crossorigin>
    <link rel="dns-prefetch" href="https://image.tmdb.org">
    <link rel="dns-prefetch" href="https://cdn.myanimelist.net">

    <!-- Restauration du thème avant le rendu pour éviter les flashs visuels -->
    <script>
    (function() {
        var palettes = ['gold', 'obsidian', 'midnight', 'emerald'];
        var modes = ['light', 'dark'];
        var palette = 'gold';
        var mode = 'light';
        try {
            var storedPalette = localStorage.getItem('theme_palette');
            var storedMode = localStorage.getItem('theme_mode');
            if (palettes.indexOf(storedPalette) !== -1) {
                palette = storedPalette;
            }
            if (modes.indexOf(storedMode) !== -1) {
                mode = storedMode;
            } else {
                // Compatibilité avec l'ancienne clé localStorage "theme"
                var legacy = localStorage.getItem('theme');
                if (legacy === 'dark') {
                    palette = 'gold';
                    mode = 'dark';
                } else if (legacy === 'obsidian') {
                    palette = 'obsidian';
                    mode = 'dark';
                } else if (legacy === 'midnight') {
                    palette = 'midnight';
                    mode = 'dark';
                } else if (legacy === 'emerald') {
                    palette = 'emerald';
                    mode = 'dark';
                }
            }
        } catch (_) {}
        document.documentElement.setAttribute('data-theme', palette + '-' + mode);
        document.documentElement.setAttribute('data-mode', mode);
        document.documentElement.setAttribute('data-bs-theme', mode);
    })();
    </script>

    <!-- Gestion du cache des assets -->
    <?php
$rootCssVersion = file_exists(FCPATH . 'assets/root.css') ? filemtime(FCPATH . 'assets/root.css') : '1';
$styleCssVersion = file_exists(FCPATH . 'assets/style.css') ? filemtime(FCPATH . 'assets/style.css') : '1';
$scriptJsVersion = file_exists(FCPATH . 'assets/script.js') ? filemtime(FCPATH . 'assets/script.js') : '1';
$profileJsVersion = file_exists(FCPATH . 'assets/profile.js') ? filemtime(FCPATH . 'assets/profile.js') : '1';
$categoriesNavVersion = file_exists(FCPATH . 'assets/categories-nav.js') ? filemtime(FCPATH . 'assets/categories-nav.js') : '1';
$currentPath = trim((string) uri_string(), '/');
$isFormPage = ($currentPath === 'm' || str_starts_with($currentPath, 'm/'));
?>
    <link rel="stylesheet" href="<?php echo base_url('assets/root.css?v=' . $rootCssVersion); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/style.css?v=' . $styleCssVersion); ?>">

    <!-- Sécurité CSRF pour les requêtes AJAX -->
    <meta name="csrf-token" content="<?php echo csrf_hash(); ?>">
    <meta name="csrf-header" content="<?php echo csrf_header(); ?>">
    <meta id="meta-theme-color" name="theme-color" content="#fcfcfd" media="(prefers-color-scheme: light)">

    <!-- Variables globales exposées pour les scripts JS -->
    <script>
    window.siteConfig = {
        "baseUrl": "<?php echo rtrim(base_url(), '/') . '/'; ?>",
        "updateOrderUrl": "<?php echo base_url('o'); ?>",
        "csrfHeader": "<?php echo csrf_header(); ?>",
        "csrfToken": "<?php echo csrf_hash(); ?>"
    };
    </script>

    <?php if (auth()->loggedIn()) { ?>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js" defer></script>
    <?php } ?>
    <script src="<?php echo base_url('assets/script.js?v=' . $scriptJsVersion); ?>" defer></script>
    <script src="<?php echo base_url('assets/profile.js?v=' . $profileJsVersion); ?>" defer></script>
    <script src="<?php echo base_url('assets/categories-nav.js?v=' . $categoriesNavVersion); ?>" defer></script>
    <?php if ($isFormPage) {
    $mediaAutofillVersion = file_exists(FCPATH . 'assets/media-autofill.js') ? filemtime(FCPATH . 'assets/media-autofill.js') : '1';
    ?>
    <script src="<?php echo base_url('assets/media-autofill.js?v=' . $mediaAutofillVersion); ?>" defer></script>
    <?php } ?>

    <?php
    $basePath = rtrim((string) parse_url(base_url(), PHP_URL_PATH), '/');
    $currentUri = trim((string) uri_string(), '/');
    $isOnCategoryPage = ($currentUri === '' || str_starts_with($currentUri, 'c/'));
    ?>
    <script type="speculationrules">
        {
      "prerender": [
        {
          "where": {
            "and": [
              { "href_matches": "<?php echo $basePath; ?>/*" },
              <?php if ($isOnCategoryPage) { ?>
              { "not": { "href_matches": "<?php echo $basePath; ?>/c/*" } },
              <?php } ?>
              { "not": { "href_matches": "<?php echo $basePath; ?>/x" } },
              { "not": { "href_matches": "<?php echo $basePath; ?>/d/*" } },
              { "not": { "href_matches": "<?php echo $basePath; ?>/u/*" } },
              { "not": { "href_matches": "<?php echo $basePath; ?>/rr/*" } },
              { "not": { "href_matches": "<?php echo $basePath; ?>/rd/*" } },
              { "not": { "href_matches": "<?php echo $basePath; ?>/ra" } },
              { "not": { "href_matches": "<?php echo $basePath; ?>/re" } },
              { "not": { "href_matches": "<?php echo $basePath; ?>/p/export/*" } },
              { "not": { "href_matches": "<?php echo $basePath; ?>/a/i/*" } },
              { "not": { "href_matches": "<?php echo $basePath; ?>/a/v/*" } },
              { "not": { "href_matches": "<?php echo $basePath; ?>/a/u/*/x" } },
              { "not": { "href_matches": "<?php echo $basePath; ?>/a/u/*/b" } },
              { "not": { "href_matches": "<?php echo $basePath; ?>/a/sc/delete/*" } },
              { "not": { "href_matches": "<?php echo $basePath; ?>/a/fnd*" } }
            ]
          },
          "eagerness": "moderate"
        }
      ]
    }
    </script>
</head>

<body>
    <div id="toast-container" class="toast-container"></div>

    <header class="main-header">
        <h1>
            <a href="<?php echo base_url('/'); ?>" style="color:inherit;">
                <img class="logo-site" src="<?php echo base_url('favicon.ico?v=2'); ?>"
                    alt="<?php echo env('SITENAME'); ?> Logo" decoding="async"><?php echo env('SITENAME'); ?>
            </a>
        </h1>

        <div class="user-nav">
            <!-- Navigation conditionnelle selon l'état de connexion -->
            <?php if (auth()->loggedIn()) { ?>
            <?php if (auth()->user()->inGroup('admin', 'superadmin')) { ?>
            <a href="<?php echo base_url('a/l'); ?>" class="logs">logs</a>
            <?php } ?>
            <a href="<?php echo base_url('p'); ?>" class="welcome-text"
                style="text-decoration: none; display: flex; align-items: center; gap: 5px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor"
                    class="bi bi-person-circle" viewBox="0 0 16 16">
                    <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0" />
                    <path fill-rule="evenodd"
                        d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1" />
                </svg>
                <?php echo esc(auth()->user()->username); ?>
            </a>
            <a href="<?php echo base_url('x'); ?>" class="btn-logout">Déconnexion</a>
            <?php } else { ?>
            <a href="<?php echo base_url('z'); ?>" class="btn-login">Connexion</a>
            <a href="<?php echo base_url('y'); ?>" class="btn-register">Créer un compte</a>
            <?php } ?>
        </div>
    </header>

    <!-- Onglets de navigation principaux (Headers) -->
    <?php if (isset($headersWithNoLogin) && !empty($headersWithNoLogin)) { ?>
    <?php if (auth()->loggedIn() && isset($headersWithLogin) && !empty($headersWithLogin)) { ?>
    <nav class="category-nav container">
        <?php foreach ($headersWithLogin as $h) { ?>
        <a href="<?php echo base_url('c/' . $h['id']); ?>"
            class="nav-tab <?php echo (isset($currentHeaderId) && $currentHeaderId == $h['id']) ? 'active' : ''; ?>">
            <?php echo esc($h['nom']); ?>
        </a>
        <?php } ?>
    </nav>
    <?php } else { ?>
    <nav class="category-nav container">
        <?php foreach ($headersWithNoLogin as $h) { ?>
        <a href="<?php echo base_url('c/' . $h['id']); ?>"
            class="nav-tab <?php echo (isset($currentHeaderId) && $currentHeaderId == $h['id']) ? 'active' : ''; ?>">
            <?php echo esc($h['nom']); ?>
        </a>
        <?php } ?>
    </nav>
    <?php } ?>
    <?php } else { ?>
    <nav class="category-nav container"></nav>
    <?php } ?>

    <!-- Conteneur principal injectant le contenu des vues enfants -->
    <main class="container">
        <?php echo $this->renderSection('content'); ?>
    </main>

    <footer class="main-footer"
        style="margin-top: 4rem; padding: 3rem 0 1.5rem; background: var(--bg-card); border-top: 1px solid var(--border-color);">
        <div class="container"
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
            <div>
                <h3
                    style="color: var(--text-main); font-size: 1.2rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 10px;">
                    <img src="<?php echo base_url('favicon.ico?v=2'); ?>" alt="Logo" class="logo-footer">
                    <?php echo env('SITENAME'); ?>
                </h3>
                <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.6;">
                    Votre tableau de bord personnel pour centraliser et suivre votre progression sur vos œuvres
                    préférées.
                </p>
            </div>
            <div>
                <h4 style="color: var(--text-main); font-size: 1rem; margin-bottom: 1rem;">Navigation</h4>
                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.6rem;"
                    class="footer-links">
                    <li><a href="<?php echo base_url('/'); ?>">Accueil</a></li>
                    <?php if (auth()->loggedIn()) { ?>
                    <li><a href="<?php echo base_url('p'); ?>">Mon Profil</a></li>
                    <li><a href="<?php echo base_url('m'); ?>">Ajouter une carte</a></li>
                    <?php } else { ?>
                    <li><a href="<?php echo base_url('z'); ?>">Connexion</a></li>
                    <li><a href="<?php echo base_url('y'); ?>">Créer un compte</a></li>
                    <?php } ?>
                </ul>
            </div>
            <div>
                <h4 style="color: var(--text-main); font-size: 1rem; margin-bottom: 1rem;">Informations</h4>
                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.6rem;"
                    class="footer-links">
                    <li><a href="<?php echo base_url('l'); ?>">Mentions légales</a></li>
                    <li><a href="<?php echo base_url('t'); ?>">CGU</a></li>
                    <li><a href="<?php echo base_url('v'); ?>">Politique de confidentialité</a></li>
                    <li><a href="mailto:<?php echo env('EMAILPRO'); ?>">Contactez-nous</a></li>
                </ul>
            </div>
        </div>
        <div style="text-align: center; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
            <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0;">
                &copy; <?php echo date('Y'); ?> <?php echo env('SITENAME'); ?>. Tous droits réservés.
            </p>
        </div>
    </footer>

    <!-- Bannière de consentement pour les cookies essentiels -->
    <div id="site-consent-box"
        style="display: none; position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); background: var(--bg-card); border: 1px solid var(--border-color); padding: 15px 25px; border-radius: var(--radius-md); box-shadow: var(--shadow-lg); z-index: 9999; flex-direction: row; align-items: center; gap: 20px; width: 90%; max-width: 600px;">
        <p style="margin: 0; font-size: 0.9rem; color: var(--text-main);">
            Nous utilisons des cookies techniques strictement nécessaires au fonctionnement du site. En continuant, vous
            acceptez leur utilisation. <a href="<?php echo base_url('v'); ?>" style="color: var(--primary);">En
                savoir plus</a>.
        </p>
        <button id="btn-understand-consent" class="btn btn-primary"
            style="padding: 8px 16px; white-space: nowrap;">Compris</button>
    </div>

    <!-- Script de gestion du consentement (Local Storage) -->
    <script>
    (function() {
        var banner = document.getElementById('site-consent-box');
        var btn = document.getElementById('btn-understand-consent');
        var consentAccepted = false;
        try {
            consentAccepted = localStorage.getItem('site_consent_ok') === 'true';
        } catch (_) {}
        if (!consentAccepted) {
            banner.style.display = 'flex';
        }
        btn.addEventListener('click', function() {
            try {
                localStorage.setItem('site_consent_ok', 'true');
            } catch (_) {}
            banner.style.display = 'none';
        });
    })();
    </script>

    <!-- Système de notifications (Toasts) via Flashdata de CodeIgniter -->
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if (session()->getFlashdata('success')) { ?>
        if (typeof showToast === 'function') showToast(
            <?php echo json_encode(session()->getFlashdata('success')); ?>, "success");
        <?php } ?>
        <?php if (session()->getFlashdata('error')) { ?>
        if (typeof showToast === 'function') showToast(
            <?php echo json_encode(session()->getFlashdata('error')); ?>, "danger");
        <?php } ?>
        <?php if (session()->getFlashdata('message')) { ?>
        if (typeof showToast === 'function') showToast(
            <?php echo json_encode(session()->getFlashdata('message')); ?>, "info");
        <?php } ?>
    });
    </script>
</body>

</html>
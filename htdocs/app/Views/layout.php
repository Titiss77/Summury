<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AMFS</title>

    <script>
    /* Theme Sombre */
    if (localStorage.getItem('theme') === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
        document.documentElement.setAttribute('data-bs-theme', 'dark'); /* Ajout pour Bootstrap 5 */
    }
    </script>

    <link rel="stylesheet" href="<?php echo base_url('assets/root.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/style.css'); ?>">
    <meta name="csrf-token" content="<?php echo csrf_hash(); ?>">
    <meta name="csrf-header" content="<?php echo csrf_header(); ?>">
    <meta name="theme-color" content="#fcfcfd" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#09090b" media="(prefers-color-scheme: dark)">

    <script>
    /* Configuration Globale (Securisee contre la minification) */
    window.amfsConfig = {
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
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>
    <script src="<?php echo base_url('assets/script.js?v='.time()); ?>" defer></script>
</head>

<body>
    <div id="toast-container" class="toast-container"></div>

    <header class="main-header">
        <h1><a href="<?php echo base_url('/'); ?>" style="color:inherit;">AMFS</a></h1>

        <div class="user-nav">
            <button id="theme-toggle" class="btn-theme">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                    class="bi bi-moon-stars-fill" viewBox="0 0 16 16">
                    <path
                        d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278" />
                    <path
                        d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.73 1.73 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.73 1.73 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.73 1.73 0 0 0 1.097-1.097zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.16 1.16 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.16 1.16 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732z" />
                </svg></button>

            <?php if (auth()->loggedIn()) { ?>
            <?php if (auth()->user()->inGroup('admin', 'superadmin')) { ?>
            <?php
                // Calcul du total des signalements en attente
                $pendingReportsTotal = model('App\Models\ReportModel')->where('status', 'pending')->countAllResults();
                ?>
            <a href="<?php echo base_url('audit'); ?>"
                style="color: #80808099; font-size: small; display: flex; align-items: center;">
                logs
                <?php if ($pendingReportsTotal > 0) { ?>
                <span
                    style="background-color: var(--danger, #dc3545); color: white; padding: 2px 5px; border-radius: 50%; font-size: 0.7em; margin-left: 4px; font-weight: bold; line-height: 1;">
                    <?php echo $pendingReportsTotal; ?>
                </span>
                <?php } ?>
            </a>
            <?php } ?>
            <span class="welcome-text">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor"
                    class="bi bi-person-circle" viewBox="0 0 16 16">
                    <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0" />
                    <path fill-rule="evenodd"
                        d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1" />
                </svg>
                <?php echo esc(auth()->user()->username); ?></span>
            <a href="<?php echo base_url('logout'); ?>" class="btn-logout">Déconnexion</a>
            <?php } else { ?>
            <a href="<?php echo base_url('login'); ?>" class="btn-login">Connexion</a>
            <a href="<?php echo base_url('register'); ?>" class="btn-register">Créer un compte</a>
            <?php } ?>
        </div>
    </header>

    <?php if (isset($headers) && !empty($headers)) { ?>
    <nav class="category-nav container">
        <?php foreach ($headers as $h) { ?>
        <a href="<?php echo base_url('categorie/'.$h['id']); ?>"
            class="nav-tab <?php echo (isset($currentHeaderId) && $currentHeaderId == $h['id']) ? 'active' : ''; ?>">
            <?php echo esc($h['nom']); ?>
        </a>
        <?php } ?>
    </nav>
    <?php } ?>

    <main class="container">
        <?php echo $this->renderSection('content'); ?>
    </main>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const currentUrl = new URL(window.location.href);

        if (currentUrl.searchParams.has('open') || currentUrl.hash) {
            setTimeout(() => {
                window.history.replaceState({}, document.title, currentUrl.pathname);
            }, 10);
        }

        /* Interception des Flashdata de CodeIgniter 4 pour lancer des Toasts automatiquement */
        <?php if (session()->getFlashdata('success')) { ?>
        if (typeof showToast === 'function') showToast("<?php echo esc(session()->getFlashdata('success')); ?>",
            "success");
        <?php } ?>

        <?php if (session()->getFlashdata('error')) { ?>
        if (typeof showToast === 'function') showToast("<?php echo esc(session()->getFlashdata('error')); ?>",
            "danger");
        <?php } ?>

        <?php if (session()->getFlashdata('message')) { ?>
        if (typeof showToast === 'function') showToast("<?php echo esc(session()->getFlashdata('message')); ?>",
            "info");
        <?php } ?>
    });

    /* Cron Job silencieux */
    window.addEventListener('load', function() {
        setTimeout(function() {
            fetch('<?php echo base_url('cron/run'); ?>')
                .catch(error => console.log('Tache de fond ignoree.'));
        }, 5000);
    });
    </script>
</body>

</html>
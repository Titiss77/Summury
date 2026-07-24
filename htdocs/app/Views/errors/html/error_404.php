<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Introuvable</title>

    <link rel="stylesheet" href="<?php echo base_url('assets/root.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/style.css'); ?>">

    <script>
    // Maintien du Dark Mode sur la page d'erreur
    if (localStorage.getItem('theme') === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
    }
    </script>
</head>

<body
    style="display: flex; align-items: center; justify-content: center; height: 100vh; background-color: var(--bg-body);">

    <div class="empty-state shadow-card fade-in" style="max-width: 500px; padding: 3rem; margin-top: -10vh;">
        <h1 style="font-size: 5rem; margin: 0; color: var(--primary); line-height: 1;">404</h1>

        <h2 style="margin-top: 10px;">Oups ! Page introuvable.</h2>

        <p style="color: var(--text-muted); margin-bottom: 2.5rem; font-size: 1.05rem;">
            <?php if (ENVIRONMENT !== 'production') { ?>
            <?php echo nl2br(esc($message)); ?>
            <?php } else { ?>
            La page que vous recherchez n'existe pas, a été renommée ou est temporairement indisponible.
            <?php } ?>
        </p>

        <a href="<?php echo base_url('/'); ?>" class="btn btn-primary" style="padding: 12px 24px; font-size: 1.1rem;">
            Retourner à l'accueil
        </a>
    </div>

</body>

</html>
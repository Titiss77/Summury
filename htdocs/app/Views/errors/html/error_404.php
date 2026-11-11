<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo lang('Errors.badRequest'); ?></title>

    <?php $styleVersion = file_exists(FCPATH.'assets/style.css') ? filemtime(FCPATH.'assets/style.css') : '1'; ?>
    <link rel="stylesheet" href="<?php echo base_url('assets/root.css?v=1'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/style.css?v='.$styleVersion); ?>">

    <script>
    // Maintien du Dark Mode sur la page d'erreur
    let errorTheme = 'light';
    try {
        errorTheme = localStorage.getItem('theme') === 'dark' ? 'dark' : 'light';
    } catch (_) {}
    document.documentElement.setAttribute('data-theme', errorTheme);
    </script>
</head>

<body
    style="display: flex; align-items: center; justify-content: center; height: 100vh; background-color: var(--bg-body);">

    <div class="empty-state shadow-card fade-in" style="max-width: 500px; padding: 3rem; margin-top: -10vh;">
        <h1 style="font-size: 5rem; margin: 0; color: var(--primary); line-height: 1;">
            <?php echo lang('Errors.number404'); ?></h1>

        <h2 style="margin-top: 10px;"><?php echo lang('Errors.pageNotFound'); ?></h2>

        <p style="color: var(--text-muted); margin-bottom: 2.5rem; font-size: 1.05rem;">
            <?php if (ENVIRONMENT !== 'production') { ?>
            <?php echo nl2br(esc($message)); ?>
            <?php } else { ?>
        <p class="lead"><?php echo lang('Errors.sorryCannotFind'); ?></p>
        <?php } ?>
        </p>
    </div>

</body>

</html>
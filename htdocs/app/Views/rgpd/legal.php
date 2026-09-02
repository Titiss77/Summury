<?php echo $this->extend('layout'); ?>

<?php echo $this->section('content'); ?>
<div class="container" style="max-width: 800px; margin: 40px auto;">
    <a href="<?php echo base_url('/'); ?>" class="btn btn-cancel" style="margin-bottom: 20px;">Retour à l'accueil</a>

    <div class="card fade-in">
        <div class="card-body">
            <h2 class="header-title" style="margin-bottom: 1.5rem;">Mentions Légales</h2>

            <h3 style="color: var(--primary); font-size: 1.1rem; margin-top: 1.5rem;">1. Éditeur du site</h3>
            <p style="color: var(--text-muted);">
                Le site <strong><?php echo env('SITENAME'); ?></strong> est un projet personnel.<br>
                Directeur de la publication : <?php echo env('PSEUDO'); ?><br>
                Contact : <a href="mailto:<?php echo env('EMAILPRO'); ?>"
                    style="color: var(--primary);"><?php echo env('EMAILPRO'); ?></a>
            </p>

            <h3 style="color: var(--primary); font-size: 1.1rem; margin-top: 1.5rem;">2. Hébergement</h3>
            <p style="color: var(--text-muted);">
                Ce site est hébergé par :<br>
                Byethost<br>
                https://byet.host/
            </p>

            <h3 style="color: var(--primary); font-size: 1.1rem; margin-top: 1.5rem;">3. Propriété intellectuelle</h3>
            <p style="color: var(--text-muted);">
                Le code source, le design et l'architecture du site <strong><?php echo env('SITENAME'); ?></strong> sont
                la propriété exclusive de son créateur. <br>
                Les affiches, images, titres et descriptions d'œuvres (Films, Séries, Animés, Mangas) appartiennent à
                leurs ayants droit respectifs et sont utilisés ici à des fins d'illustration et d'organisation
                personnelle.
            </p>

            <h3 style="color: var(--primary); font-size: 1.1rem; margin-top: 1.5rem;">4. Responsabilité</h3>
            <p style="color: var(--text-muted);">
                <strong><?php echo env('SITENAME'); ?></strong> est un outil de gestion de collection (tableau de bord)
                permettant d'indexer des liens de visionnage. Le site n'héberge aucun contenu vidéo ou fichier illégal
                sur ses serveurs. L'éditeur ne saurait être tenu responsable du contenu des sites tiers pointés par les
                liens ajoutés par les utilisateurs.
            </p>
        </div>
    </div>
</div>
<?php echo $this->endSection(); ?>
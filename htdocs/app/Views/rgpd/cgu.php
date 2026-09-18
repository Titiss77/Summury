<?php echo $this->extend('layout'); ?>
<?php echo $this->section('content'); ?>
<div class="actions-container">
    <a href="<?php echo base_url('/'); ?>" class="btn btn-cancel">Retour aux cartes</a>
</div>

<div class="container" style="max-width: 800px; margin: 40px auto;">
    <div class="card fade-in">
        <div class="card-body">
            <h2 class="header-title" style="margin-bottom: 1.5rem;">Conditions Générales d'Utilisation (CGU)</h2>
            <p style="color: var(--text-muted);">Dernière mise à jour : <?php echo date('d/m/Y'); ?></p>

            <h3 style="color: var(--primary); font-size: 1.1rem; margin-top: 1.5rem;">1. Présentation du service</h3>
            <p style="color: var(--text-muted);">
                <strong><?php echo env('SITENAME'); ?></strong> est un tableau de bord personnel permettant à
                l'utilisateur de centraliser, organiser et suivre l'état d'avancement de ses œuvres (films, séries,
                animés, mangas).
            </p>

            <h3 style="color: var(--primary); font-size: 1.1rem; margin-top: 1.5rem;">2. Accès et Inscription</h3>
            <p style="color: var(--text-muted);">
                L'utilisation de certaines fonctionnalités requiert la création d'un compte. Vous êtes responsable du
                maintien de la confidentialité de vos identifiants de connexion.
            </p>

            <h3 style="color: var(--primary); font-size: 1.1rem; margin-top: 1.5rem;">3. Responsabilité de l'utilisateur
            </h3>
            <p style="color: var(--text-muted);">
                L'utilisateur s'engage à utiliser le site dans le respect des lois en vigueur. Les liens ajoutés sur la
                plateforme le sont sous la seule responsabilité de l'utilisateur. L'éditeur ne peut être tenu
                responsable du contenu des sites tiers référencés via ces liens.
            </p>

            <h3 style="color: var(--primary); font-size: 1.1rem; margin-top: 1.5rem;">4. Propriété et Modération</h3>
            <p style="color: var(--text-muted);">
                L'éditeur se réserve le droit de modérer, refuser ou supprimer tout contenu public qui ne respecterait
                pas l'esprit du site ou la législation, sans préavis.
            </p>
        </div>
    </div>
</div>
<?php echo $this->endSection(); ?>
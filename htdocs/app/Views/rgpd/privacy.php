<?php echo $this->extend('layout'); ?>

<?php echo $this->section('content'); ?>
<div class="container" style="max-width: 800px; margin: 40px auto;">
    <a href="<?php echo base_url('/'); ?>" class="btn btn-cancel" style="margin-bottom: 20px;">Retour à l'accueil</a>

    <div class="card fade-in">
        <div class="card-body">
            <h2 class="header-title" style="margin-bottom: 1.5rem;">Politique de Confidentialité</h2>

            <p style="color: var(--text-muted);">
                La protection de vos données personnelles est importante pour nous. Cette page vous explique comment
                <strong><?php echo env('SITENAME'); ?></strong> gère vos informations.
            </p>

            <h3 style="color: var(--primary); font-size: 1.1rem; margin-top: 1.5rem;">1. Données collectées</h3>
            <p style="color: var(--text-muted);">
                Lors de votre inscription, nous collectons uniquement les données strictement nécessaires au
                fonctionnement de votre compte :
            </p>
            <ul style="color: var(--text-muted);">
                <li>Votre nom d'utilisateur (Pseudo)</li>
                <li>Votre adresse e-mail</li>
                <li>Votre mot de passe (stocké de manière sécurisée et cryptée)</li>
            </ul>

            <h3 style="color: var(--primary); font-size: 1.1rem; margin-top: 1.5rem;">2. Utilisation des données</h3>
            <p style="color: var(--text-muted);">
                Vos données sont utilisées dans l'unique but de sécuriser votre compte, de sauvegarder vos cartes
                personnalisées, et de vous permettre de récupérer votre compte en cas d'oubli de mot de passe.
                <strong>En aucun cas</strong> vos données ne seront revendues ou cédées à des tiers.
            </p>

            <h3 style="color: var(--primary); font-size: 1.1rem; margin-top: 1.5rem;">3. Cookies</h3>
            <p style="color: var(--text-muted);">
                <strong><?php echo env('SITENAME'); ?></strong> n'utilise pas de cookies publicitaires ou de traçage
                intrusif. Nous utilisons uniquement des cookies techniques dits "strictement nécessaires" :
            </p>
            <ul style="color: var(--text-muted);">
                <li><strong>Cookies de session :</strong> Pour vous maintenir connecté.</li>
                <li><strong>Préférences :</strong> Pour mémoriser votre choix de thème (Sombre/Clair).</li>
            </ul>

            <h3 style="color: var(--primary); font-size: 1.1rem; margin-top: 1.5rem;">4. Vos droits (RGPD)</h3>
            <p style="color: var(--text-muted);">
                Conformément à la réglementation européenne (RGPD), vous disposez d'un droit d'accès, de rectification,
                et de suppression de vos données. Vous pouvez supprimer toutes vos cartes depuis votre espace, ou
                demander la suppression définitive de votre compte en contactant l'administrateur du site.
            </p>
        </div>
    </div>
</div>
<?php echo $this->endSection(); ?>
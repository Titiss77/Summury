<?php echo $this->extend('layout'); ?>
<?php echo $this->section('content'); ?>

<div class="form-container card" style="max-width: 600px; margin: 3rem auto; padding: 2.5rem;">
    <h2 class="header-title" style="text-align: center; margin-bottom: 2rem;">Vérificateur d'Épisodes</h2>

    <form id="checkForm">
        <div class="form-group">
            <label for="urlInput" class="form-label">Entrez l'URL complète de l'épisode :</label>
            <input type="url" id="urlInput" class="form-control" placeholder="https://voir-anime.to/anime/..." required>
        </div>
        <div class="form-actions" style="justify-content: center; border-top: none; margin-top: 1rem;">
            <button type="submit" id="submitBtn" class="btn btn-primary" style="width: 100%;">Vérifier la
                disponibilité</button>
        </div>
    </form>

    <!-- Zone de résultat (cachée par défaut) -->
    <div id="resultatDiv"
        style="display: none; margin-top: 2rem; border-radius: var(--radius-md); overflow: hidden; border: 1px solid var(--border-color);">
        <div id="resultatHeader" style="padding: 15px; text-align: center; font-weight: bold; font-size: 1.1rem;"></div>
        <div
            style="padding: 15px; background: var(--bg-body); font-size: 0.85em; color: var(--text-muted); border-top: 1px solid var(--border-color);">
            <strong>Rapport d'analyse :</strong><br>
            <span id="debugTitre"></span><br>
            <span id="debugFiche"></span><br>
            <span id="debugLecteur"></span><br>
            <span id="debugEpisode"></span>
        </div>
    </div>
</div>

<script>
document.getElementById('checkForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const urlInput = document.getElementById('urlInput').value;
    const btn = document.getElementById('submitBtn');
    const resDiv = document.getElementById('resultatDiv');
    const resHeader = document.getElementById('resultatHeader');

    // État de chargement (utilise tes classes CSS existantes)
    btn.classList.add('loading');
    resDiv.style.display = 'none';

    try {
        const response = await fetch(amfsConfig.baseUrl + 'item/check-dispo', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest',
                [amfsConfig.csrfHeader]: amfsConfig.csrfToken
            },
            // Envoi des données au format x-www-form-urlencoded natif à PHP
            body: new URLSearchParams({
                urlCible: urlInput
            })
        });

        const data = await response.json();

        // Renouvellement du token CSRF essentiel dans CodeIgniter 4
        if (data.csrf_token) amfsConfig.csrfToken = data.csrf_token;

        if (!data.success) {
            showToast(data.error, 'danger');
            btn.classList.remove('loading');
            return;
        }

        // Affichage des résultats
        resDiv.style.display = 'block';
        if (data.disponible) {
            resHeader.style.backgroundColor = 'var(--success-bg)';
            resHeader.style.color = 'var(--success-text)';
            resHeader.innerHTML =
                `✅ ÉPISODE EN LIGNE ! <br><a href="${urlInput}" target="_blank" style="color: var(--success); text-decoration: underline; font-size: 0.9em; margin-top: 5px; display: inline-block;">Ouvrir l'épisode</a>`;
        } else {
            resHeader.style.backgroundColor = 'var(--danger-bg)';
            resHeader.style.color = 'var(--danger-text)';
            resHeader.innerHTML =
                `❌ Pas encore disponible. <br><span style="font-size: 0.85em; font-weight: normal;">La page redirige vers la fiche de l'anime.</span>`;
        }

        // Injection des métadonnées
        document.getElementById('debugTitre').innerText =
            `Titre détecté : ${data.details.titrePage || 'Aucun'}`;
        document.getElementById('debugFiche').innerText =
            `Bouton "Premier EP" détecté : ${data.details.estSurFicheAnime ? 'Oui' : 'Non'}`;
        document.getElementById('debugLecteur').innerText =
            `Indicateur de lecteur vidéo : ${data.details.lecteurPresent ? 'Oui' : 'Non'}`;
        document.getElementById('debugEpisode').innerText =
            `Épisode extrait de l'URL : ${data.details.episodeDetecte || 'Non trouvé'}`;

    } catch (error) {
        console.error("Erreur Fetch:", error);
        showToast("Erreur de communication avec le serveur.", "danger");
    } finally {
        btn.classList.remove('loading');
    }
});
</script>

<?php echo $this->endSection(); ?>
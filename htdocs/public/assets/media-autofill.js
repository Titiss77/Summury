/*
 * Auto-remplissage multi-sources de Summury.
 * Ce fichier remplace le comportement de l'ancien auto-remplissage sans
 * modifier le reste de public/assets/script.js.
 */
(function () {
    'use strict';

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function detectType() {
        const select = document.getElementById('id_division');
        if (!select || select.selectedIndex < 0) return 'autre';

        const text = select.options[select.selectedIndex].text.toLowerCase();
        if (text.includes('manga')) return 'manga';
        if (text.includes('anime') || text.includes('animé')) return 'anime';
        if (text.includes('série') || text.includes('serie')) return 'serie';
        if (text.includes('film')) return 'film';
        if (text.includes('vidéo') || text.includes('video') || text.includes('youtube')) return 'video';
        if (text.includes('stream')) return 'streaming';
        if (text.includes('lien') || text.includes('web') || text.includes('site')) return 'autre';
        return 'autre';
    }

    function setField(id, value, force = false) {
        const field = document.getElementById(id);
        if (!field) return;
        if (force || !field.value.trim()) {
            field.value = value ?? '';
            field.dispatchEvent(new Event('input', { bubbles: true }));
            field.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    function makeFallback() {
        const div = document.createElement('div');
        div.style.cssText = 'width:50px;height:70px;flex:0 0 50px;border-radius:8px;background:var(--bg-body);border:1px solid var(--border-color);display:flex;align-items:center;justify-content:center;font-size:22px;';
        div.textContent = '🎬';
        return div;
    }

    function makeResult(res, limitCut) {
        const item = document.createElement('div');
        item.style.cssText = 'display:flex;align-items:center;gap:12px;padding:10px 12px;border-bottom:1px solid var(--border-color);cursor:pointer;transition:background .15s;';
        item.addEventListener('mouseenter', () => item.style.background = 'rgba(128,128,128,.10)');
        item.addEventListener('mouseleave', () => item.style.background = 'transparent');

        const imageWrap = document.createElement('div');
        imageWrap.style.cssText = 'width:50px;height:70px;flex:0 0 50px;overflow:hidden;border-radius:9px;display:flex;align-items:center;justify-content:center;';

        if (res.imageThumb) {
            const img = document.createElement('img');
            img.src = res.imageThumb;
            img.alt = '';
            img.loading = 'lazy';
            img.decoding = 'async';
            img.style.cssText = 'width:100%;height:100%;object-fit:cover;';
            img.onerror = () => {
                imageWrap.innerHTML = '';
                imageWrap.appendChild(makeFallback());
            };
            imageWrap.appendChild(img);
        } else {
            imageWrap.appendChild(makeFallback());
        }

        const body = document.createElement('div');
        body.style.cssText = 'flex:1;min-width:0;';

        const title = document.createElement('strong');
        title.style.cssText = 'display:block;white-space:nowrap;text-overflow:ellipsis;overflow:hidden;color:var(--text-main);font-size:.98rem;';
        title.textContent = res.titre || 'Sans titre';

        const info = document.createElement('small');
        info.style.cssText = 'display:block;color:var(--text-muted);margin-top:3px;';
        info.textContent = res.info || '';

        const meta = document.createElement('small');
        meta.style.cssText = 'display:block;color:var(--primary);font-size:.72rem;margin-top:4px;font-weight:600;';
        meta.textContent = `${res.sourcesLabel || res.source || 'API'} • Pertinence ${res.score ?? 0}%`;

        body.appendChild(title);
        body.appendChild(info);
        body.appendChild(meta);

        item.appendChild(imageWrap);
        item.appendChild(body);

        item.addEventListener('click', () => {
            setField('titre', res.titre || '', true);
            setField('img', res.imageLarge || res.imageThumb || '', true);

            let description = res.description || '';
            if (description.length > limitCut) description = description.substring(0, limitCut) + '...';
            setField('description', description, true);

            if (res.lien) setField('lien', res.lien, false);

            if (res.total_episodes !== '' && res.total_episodes !== null && res.total_episodes !== undefined) {
                setField('total_episodes', String(res.total_episodes), true);
            }
            if (res.total_saisons !== '' && res.total_saisons !== null && res.total_saisons !== undefined) {
                setField('total_saisons', String(res.total_saisons), true);
            }

            window.currentSeasonsData = res.seasons_data || null;

            const saison = document.getElementById('saison');
            const totalEpisodes = document.getElementById('total_episodes');
            if (window.currentSeasonsData && saison && totalEpisodes && saison.value) {
                const seasonEpisodes = window.currentSeasonsData[saison.value];
                if (seasonEpisodes !== undefined) totalEpisodes.value = seasonEpisodes;
            }

            const textarea = document.getElementById('description');
            if (textarea) textarea.dispatchEvent(new Event('input', { bubbles: true }));

            const imgInput = document.getElementById('img');
            if (imgInput) imgInput.dispatchEvent(new Event('input', { bubbles: true }));

            const container = document.getElementById('api-results-container');
            const status = document.getElementById('api-status');
            if (container) container.style.display = 'none';
            if (status) status.textContent = 'Sélectionné !';
            if (typeof window.showToast === 'function') window.showToast('Meilleur résultat sélectionné.', 'success');
        });

        return item;
    }

    async function runSearch() {
        const button = document.getElementById('btn-api-search');
        const input = document.getElementById('titre');
        const container = document.getElementById('api-results-container');
        const status = document.getElementById('api-status');

        if (!button || !input || !container) return;

        const query = input.value.trim();
        if (!query) {
            if (typeof window.showToast === 'function') window.showToast("Entre d'abord un titre ou un lien !", 'danger');
            return;
        }

        let type = detectType();
        if (/^https?:\/\//i.test(query)) type = 'autre';

        if (status) {
            status.style.display = 'inline';
            status.textContent = 'Recherche multi-sources...';
        }
        button.disabled = true;
        container.innerHTML = '';
        container.style.display = 'none';

        const baseUrl = window.siteConfig.baseUrl.endsWith('/') ? window.siteConfig.baseUrl : window.siteConfig.baseUrl + '/';
        const url = `${baseUrl}f?q=${encodeURIComponent(query)}&type=${encodeURIComponent(type)}`;

        try {
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            const data = await response.json();
            if (data.error) throw new Error(data.error);

            let results = Array.isArray(data.unified) ? data.unified : [];

            const description = document.getElementById('description');
            const maxLength = description && description.hasAttribute('maxlength')
                ? parseInt(description.getAttribute('maxlength'), 10)
                : 250;
            const limitCut = maxLength > 3 ? maxLength - 3 : maxLength;

            if (Array.isArray(data) && data.length && data[0].is_link) {
                results = [{
                    titre: data[0].titre,
                    imageThumb: data[0].image,
                    imageLarge: data[0].image,
                    description: data[0].description || '',
                    info: 'LIEN WEB',
                    lien: data[0].lien,
                    score: 100,
                    source: 'OpenGraph',
                    sourcesLabel: 'OpenGraph'
                }];
            }

            if (!results.length) {
                if (status) status.textContent = type === 'video' ? 'Aucune vidéo trouvée (vérifie YOUTUBE_API_KEY).' : 'Aucun résultat suffisamment pertinent';
                return;
            }

            if (status) status.textContent = `${results.length} résultat(s)`;
            container.style.display = 'block';

            results.forEach(result => container.appendChild(makeResult(result, limitCut)));
        } catch (error) {
            console.error('Auto-remplissage multi-sources :', error);
            if (status) status.textContent = 'Erreur de recherche';
            if (typeof window.showToast === 'function') window.showToast('Impossible de récupérer les résultats.', 'danger');
        } finally {
            button.disabled = false;
        }
    }

    function init() {
        const button = document.getElementById('btn-api-search');
        const container = document.getElementById('api-results-container');
        if (!button || !container) return;

        // Capture avant le listener historique de script.js : cela empêche l'ancien
        // moteur TMDB/MangaDex de lancer une seconde recherche.
        button.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopImmediatePropagation();
            runSearch();
        }, true);

        document.addEventListener('click', function (event) {
            if (!container.contains(event.target) && event.target !== button && event.target.id !== 'titre') {
                container.style.display = 'none';
            }
        });

        // Recherche automatique quand l'utilisateur arrête de taper un titre.
        const titleInput = document.getElementById('titre');
        let timer = null;
        if (titleInput) {
            titleInput.addEventListener('input', function () {
                clearTimeout(timer);
                const value = this.value.trim();
                if (value.length < 3 || /^https?:\/\//i.test(value)) return;
                timer = setTimeout(runSearch, 650);
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, { once: true });
    } else {
        init();
    }
})();

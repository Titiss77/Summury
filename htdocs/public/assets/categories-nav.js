(function () {
    'use strict';

    const CACHE_PREFIX = 'cat_cache_v1_';
    const CACHE_TTL_MS = 2 * 60 * 1000; // 2 minutes
    const pendingFetches = new Map();

    function normalizeCategoryUrl(rawUrl) {
        const parsed = new URL(rawUrl, window.location.origin);
        parsed.search = '';
        parsed.hash = '';
        return parsed.toString();
    }

    function getCachedHtml(key) {
        try {
            const raw = sessionStorage.getItem(CACHE_PREFIX + key);
            if (!raw) return null;
            const entry = JSON.parse(raw);
            if (Date.now() - entry.ts > CACHE_TTL_MS) {
                sessionStorage.removeItem(CACHE_PREFIX + key);
                return null;
            }
            return entry.html;
        } catch (_) {
            return null;
        }
    }

    function setCachedHtml(key, html) {
        try {
            sessionStorage.setItem(CACHE_PREFIX + key, JSON.stringify({ html, ts: Date.now() }));
        } catch (_) {
            // Si sessionStorage est plein, on vide l'ancien cache des catégories
            clearAllCategoryCache();
        }
    }

    function removeCachedHtml(key) {
        try {
            sessionStorage.removeItem(CACHE_PREFIX + key);
        } catch (_) {}
    }

    function clearAllCategoryCache() {
        try {
            for (let i = sessionStorage.length - 1; i >= 0; i--) {
                const k = sessionStorage.key(i);
                if (k && k.startsWith(CACHE_PREFIX)) {
                    sessionStorage.removeItem(k);
                }
            }
        } catch (_) {}
    }

    function getBaseUrl() {
        const base = (window.siteConfig && window.siteConfig.baseUrl) ? window.siteConfig.baseUrl : '/';
        return base.endsWith('/') ? base : base + '/';
    }

    function updateNavActive(targetUrl) {
        const targetKey = normalizeCategoryUrl(targetUrl);
        document.querySelectorAll('nav.category-nav .nav-tab').forEach((tab) => {
            tab.classList.toggle('active', normalizeCategoryUrl(tab.href) === targetKey);
        });
    }

    async function fetchCategoryHtml(url) {
        const key = normalizeCategoryUrl(url);
        const cached = getCachedHtml(key);
        if (cached !== null) {
            return cached;
        }
        if (pendingFetches.has(key)) {
            return pendingFetches.get(key);
        }

        const promise = fetch(key, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
            .then((res) => {
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                return res.text();
            })
            .then((html) => {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const main = doc.querySelector('main.container');
                if (!main) throw new Error('Contenu introuvable');
                const inner = main.innerHTML;
                setCachedHtml(key, inner);
                return inner;
            })
            .finally(() => {
                pendingFetches.delete(key);
            });

        pendingFetches.set(key, promise);
        return promise;
    }

    // --- Ré-initialisation des fonctionnalités après injection du HTML ---

    function initReleaseBadges(container) {
        let dismissed = [];
        try {
            dismissed = JSON.parse(localStorage.getItem('dismissedReleases') || '[]');
        } catch (_) {}

        container.querySelectorAll('.release-badge').forEach((badge) => {
            const key = badge.getAttribute('data-key');
            if (!dismissed.includes(key)) {
                badge.style.display = 'inline-block';
                badge.classList.add('fade-in');
                badge.addEventListener('click', function () {
                    this.style.display = 'none';
                    dismissed.push(key);
                    try {
                        localStorage.setItem('dismissedReleases', JSON.stringify(dismissed));
                    } catch (_) {}
                });
            }
        });
    }

    function initSearchAndFilters(container) {
        const searchInput = container.querySelector('#liveSearch');
        if (!searchInput) return;

        const cards = Array.from(container.querySelectorAll('.searchable-card'));
        const statusFilter = container.querySelector('#statusFilter');
        const categoryFilter = container.querySelector('#categoryFilter');
        const resultCount = container.querySelector('#cardResultCount');
        const filterKey = 'cardFilters';

        const categories = [...new Set(cards.map((c) => c.dataset.category).filter(Boolean))].sort((a, b) => a.localeCompare(b, 'fr'));
        categories.forEach((category) => {
            const option = document.createElement('option');
            option.value = category;
            option.textContent = category;
            categoryFilter?.appendChild(option);
        });

        const statuses = [...new Set(cards.map((c) => c.dataset.status).filter(Boolean))].sort((a, b) => a.localeCompare(b, 'fr'));
        statuses.forEach((value) => {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = value;
            statusFilter?.appendChild(option);
        });

        try {
            const saved = JSON.parse(localStorage.getItem(filterKey) || '{}');
            searchInput.value = saved.query || '';
            if (statusFilter) statusFilter.value = saved.status || '';
            if (categoryFilter) categoryFilter.value = saved.category || '';

            if (categoryFilter && !Array.from(categoryFilter.options).some((o) => o.value === categoryFilter.value)) {
                categoryFilter.value = '';
            }
            if (statusFilter && !Array.from(statusFilter.options).some((o) => o.value === statusFilter.value)) {
                statusFilter.value = '';
            }
        } catch (_) {}

        const applyFilters = () => {
            const query = searchInput.value.trim().toLocaleLowerCase('fr');
            const status = statusFilter?.value || '';
            const category = categoryFilter?.value || '';
            let visible = 0;

            cards.forEach((card) => {
                const title = card.querySelector('.card-title, .search-target-title')?.innerText.toLocaleLowerCase('fr') || '';
                const desc = card.querySelector('.card-desc, .search-target-desc')?.innerText.toLocaleLowerCase('fr') || '';
                const match =
                    (!query || title.includes(query) || desc.includes(query)) &&
                    (!status || card.dataset.status === status) &&
                    (!category || card.dataset.category === category);
                card.style.display = match ? '' : 'none';
                if (match) visible++;
            });

            let openByDefault = false;
            try {
                openByDefault = localStorage.getItem('defaultOpenDivisions') === 'true';
            } catch (_) {}

            const hasFilter = Boolean(query || status || category);

            container.querySelectorAll('details.division-section, details.subcategory-details').forEach((detail) => {
                const hasVisible = Array.from(detail.querySelectorAll('.searchable-card')).some((c) => c.style.display !== 'none');
                detail.hidden = !hasVisible;
                if (hasFilter) {
                    detail.open = hasVisible;
                } else if (openByDefault && detail.classList.contains('division-section')) {
                    detail.open = hasVisible;
                }
            });

            if (resultCount) resultCount.textContent = `${visible} carte${visible > 1 ? 's' : ''}`;

            try {
                localStorage.setItem(filterKey, JSON.stringify({ query: searchInput.value, status, category }));
            } catch (_) {}
        };

        searchInput.addEventListener('input', applyFilters);
        statusFilter?.addEventListener('change', applyFilters);
        categoryFilter?.addEventListener('change', applyFilters);
        applyFilters();
        window.applyCardFilters = applyFilters;
    }

    async function checkCardDispo(itemId, targetUrl) {
        const liveStatus = document.getElementById(`live-status-${itemId}`);
        if (!liveStatus || !targetUrl) return;

        const domains = Array.isArray(window.siteSupportedDomains) ? window.siteSupportedDomains : [];
        if (!domains.some((domain) => targetUrl.includes(domain))) return;

        liveStatus.style.color = 'var(--info)';
        liveStatus.textContent = 'Vérification...';

        try {
            const resp = await fetch(`${getBaseUrl()}q?urlCible=${encodeURIComponent(targetUrl)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await resp.json();
            if (data.success) {
                liveStatus.style.color = data.disponible ? 'var(--success)' : 'var(--danger)';
                liveStatus.textContent = data.disponible ? '✔ Disponible' : '✖ Indisponible';
            } else {
                liveStatus.style.color = 'var(--text-muted)';
                liveStatus.textContent = 'Indisponible';
            }
        } catch (_) {
            liveStatus.style.color = 'var(--text-muted)';
            liveStatus.textContent = 'Vérif. impossible';
        }
    }

    function initLiveDispoChecks(container) {
        container.querySelectorAll('.card.needs-dispo-check').forEach((card) => {
            const itemId = card.dataset.id;
            const url = card.dataset.url;
            if (itemId && url && document.getElementById(`live-status-${itemId}`)) {
                checkCardDispo(itemId, url);
            }
        });
    }

    function initIncrementButtons(container) {
        container.querySelectorAll('.btn-increment-episode').forEach((button) => {
            button.addEventListener('click', async (e) => {
                e.preventDefault();
                e.stopPropagation();

                if (button.disabled) return;
                button.disabled = true;

                const itemId = button.getAttribute('data-id');
                const card = document.getElementById(`card-${itemId}`);
                const epSpan = document.getElementById(`ep-count-${itemId}`);
                const epTotalSpan = document.getElementById(`ep-total-${itemId}`);
                const seasonSpan = document.getElementById(`s-count-${itemId}`);
                const statusSpan = document.getElementById(`status-text-${itemId}`);

                try {
                    const response = await fetch(`${getBaseUrl()}e/${itemId}`, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            [window.siteConfig.csrfHeader]: window.siteConfig.csrfToken,
                        },
                    });

                    if (!response.ok) throw new Error(`HTTP ${response.status}`);
                    const data = await response.json();

                    if (data.csrf_token) {
                        window.siteConfig.csrfToken = data.csrf_token;
                    }

                    if (data.success) {
                        if (epSpan) {
                            epSpan.innerText = data.new_episode;
                            epSpan.style.color = 'var(--success)';
                            epSpan.style.transform = 'scale(1.2)';
                            setTimeout(() => {
                                epSpan.style.color = '';
                                epSpan.style.transform = 'scale(1)';
                            }, 400);
                        }
                        if (seasonSpan && data.new_saison) seasonSpan.innerText = data.new_saison;
                        if (epTotalSpan && 'new_total_episodes' in data) {
                            epTotalSpan.innerText = data.new_total_episodes ? ` / ${data.new_total_episodes}` : '';
                        }
                        if (data.status) {
                            if (statusSpan) statusSpan.innerText = data.status;
                            if (card) {
                                card.dataset.status = data.status;
                                if (data.status === 'Terminé') {
                                    card.classList.add('status-completed');
                                    card.classList.remove('needs-dispo-check');
                                    const liveStatus = document.getElementById(`live-status-${itemId}`);
                                    if (liveStatus) liveStatus.style.display = 'none';
                                }
                            }
                        }
                        if (card && data.new_link) {
                            card.dataset.url = data.new_link;
                            const linkEl = card.querySelector('.card-link-block');
                            if (linkEl) linkEl.href = data.new_link;
                            if (data.status !== 'Terminé') {
                                checkCardDispo(itemId, data.new_link);
                            }
                        }

                        removeCachedHtml(normalizeCategoryUrl(window.location.href));

                        if (typeof window.showToast === 'function') {
                            window.showToast(
                                data.status === 'Terminé' ? 'Série marquée comme terminée !' : 'Épisode ajouté avec succès !',
                                'success'
                            );
                        }
                    } else if (typeof window.showToast === 'function') {
                        window.showToast(data.error || 'Erreur lors de la mise à jour.', 'danger');
                    }
                } catch (_) {
                    if (typeof window.showToast === 'function') {
                        window.showToast('Erreur réseau lors de la mise à jour.', 'danger');
                    }
                } finally {
                    button.disabled = false;
                }
            });
        });
    }

    function initReportButtons(container) {
        container.querySelectorAll('.btn-report-link-inline').forEach((button) => {
            button.addEventListener('click', async (e) => {
                e.preventDefault();
                e.stopPropagation();

                if (!window.confirm('Signaler ce domaine comme lien mort ?')) return;
                if (button.disabled) return;
                button.disabled = true;

                const itemId = button.getAttribute('data-id');
                try {
                    const response = await fetch(`${getBaseUrl()}report-link`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            [window.siteConfig.csrfHeader]: window.siteConfig.csrfToken,
                        },
                        body: JSON.stringify({ id: itemId }),
                    });
                    const data = await response.json();

                    if (data.csrf_token) {
                        window.siteConfig.csrfToken = data.csrf_token;
                    }

                    if (data.success) {
                        const badge = document.createElement('span');
                        badge.className = 'dead-link-badge-inline';
                        badge.title = 'En attente de vérification par l’administration.';
                        badge.textContent = '⚠️ Signalé';
                        button.replaceWith(badge);
                        clearAllCategoryCache();
                        if (typeof window.showToast === 'function') {
                            window.showToast(data.message || 'Signalement enregistré.', 'success');
                        }
                    } else {
                        button.disabled = false;
                        if (typeof window.showToast === 'function') {
                            window.showToast(data.message || 'Impossible de signaler ce lien.', 'danger');
                        }
                    }
                } catch (_) {
                    button.disabled = false;
                    if (typeof window.showToast === 'function') {
                        window.showToast('Erreur réseau lors du signalement.', 'danger');
                    }
                }
            });
        });
    }

    function initSortable(container) {
        if (typeof Sortable === 'undefined') return;

        async function updateOrderBackend(newOrder) {
            try {
                const response = await fetch(window.siteConfig.updateOrderUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        [window.siteConfig.csrfHeader]: window.siteConfig.csrfToken,
                    },
                    body: JSON.stringify({ order: newOrder }),
                });
                const data = await response.json();
                if (!response.ok || !data.success) throw new Error(data.error || 'Sauvegarde impossible');
                if (data.csrf_token) window.siteConfig.csrfToken = data.csrf_token;
                removeCachedHtml(normalizeCategoryUrl(window.location.href));
                if (typeof window.showToast === 'function') window.showToast('Ordre mis à jour !', 'success');
            } catch (_) {
                if (typeof window.showToast === 'function') {
                    window.showToast("Erreur lors de la sauvegarde de l'ordre", 'danger');
                }
            }
        }

        container.querySelectorAll('.sortable-grid').forEach((el) => {
            Sortable.create(el, {
                animation: 150,
                ghostClass: 'sortable-ghost',
                handle: '.drag-handle',
                delay: 200,
                delayOnTouchOnly: true,
                onEnd: (evt) => {
                    if (evt.oldIndex === evt.newIndex) return;
                    const divisionBody = el.closest('.sortable-division');
                    if (!divisionBody) return;
                    const newOrder = Array.from(divisionBody.querySelectorAll('.card'))
                        .map((item) => item.getAttribute('data-id'))
                        .filter(Boolean);
                    updateOrderBackend(newOrder);
                },
            });
        });

        container.querySelectorAll('.sortable-division').forEach((el) => {
            Sortable.create(el, {
                animation: 150,
                ghostClass: 'sortable-ghost',
                handle: '.drag-handle-sub',
                delay: 150,
                delayOnTouchOnly: true,
                onEnd: (evt) => {
                    if (evt.oldIndex === evt.newIndex) return;
                    const newOrder = Array.from(el.querySelectorAll('.card'))
                        .map((item) => item.getAttribute('data-id'))
                        .filter(Boolean);
                    updateOrderBackend(newOrder);
                },
            });
        });
    }

    function bindCategoryEvents(container) {
        initReleaseBadges(container);
        initSearchAndFilters(container);
        initIncrementButtons(container);
        initReportButtons(container);
        initSortable(container);
        initLiveDispoChecks(container);
    }

    let activeNavToken = 0;

    async function switchCategory(url, pushHistory = true) {
        const mainEl = document.querySelector('main.container');
        if (!mainEl) {
            window.location.href = url;
            return;
        }

        const key = normalizeCategoryUrl(url);
        const token = ++activeNavToken;

        updateNavActive(key);
        if (pushHistory && normalizeCategoryUrl(window.location.href) !== key) {
            window.history.pushState({ categoryUrl: key }, '', key);
        }

        try {
            const html = await fetchCategoryHtml(key);
            if (token !== activeNavToken) return;

            mainEl.innerHTML = html;
            try {
                sessionStorage.setItem('last_cards_url', key);
            } catch (_) {}
            bindCategoryEvents(mainEl);
        } catch (_) {
            if (token === activeNavToken) {
                window.location.href = url;
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const navTabs = document.querySelectorAll('nav.category-nav .nav-tab');
        const mainEl = document.querySelector('main.container');

        if (!navTabs.length || !mainEl) return;

        // Si on revient d'une création/édition/suppression (?open=...), on vide le cache pour repartir sur des données fraîches
        if (new URL(window.location.href).searchParams.has('open')) {
            clearAllCategoryCache();
        }

        // Met la catégorie courante en cache dans sessionStorage
        const currentKey = normalizeCategoryUrl(window.location.href);
        setCachedHtml(currentKey, mainEl.innerHTML);

        navTabs.forEach((tab) => {
            tab.addEventListener('mouseenter', () => {
                fetchCategoryHtml(tab.href).catch(() => {});
            });
            tab.addEventListener('focus', () => {
                fetchCategoryHtml(tab.href).catch(() => {});
            });
            tab.addEventListener('click', (e) => {
                if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) return;
                e.preventDefault();
                switchCategory(tab.href, true);
            });
        });

        // Précharge les autres onglets un par un (séquentiel) pour ne pas saturer le serveur
        const prefetchQueue = Array.from(navTabs)
            .map((tab) => normalizeCategoryUrl(tab.href))
            .filter((key) => getCachedHtml(key) === null);

        const prefetchNext = async () => {
            while (prefetchQueue.length > 0) {
                const nextKey = prefetchQueue.shift();
                if (!nextKey || getCachedHtml(nextKey) !== null) continue;
                try {
                    await fetchCategoryHtml(nextKey);
                } catch (_) {}
                await new Promise((r) => setTimeout(r, 150));
            }
        };

        if ('requestIdleCallback' in window) {
            window.requestIdleCallback(() => prefetchNext(), { timeout: 1500 });
        } else {
            setTimeout(prefetchNext, 500);
        }

        // Support des boutons Précédent / Suivant du navigateur
        window.addEventListener('popstate', () => {
            if (document.querySelector('nav.category-nav .nav-tab')) {
                switchCategory(window.location.href, false);
            }
        });
    });
})();
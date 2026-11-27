(function () {
    'use strict';

    const ALLOWED_MODES = ['light', 'dark'];
    const ALLOWED_PALETTES = ['gold', 'obsidian', 'midnight', 'emerald', 'ruby', 'rose', 'lime'];
    const THEME_COLORS = {
        'gold-light': '#f3f5f8',
        'gold-dark': '#111318',
        'obsidian-light': '#f4f4f5',
        'obsidian-dark': '#09090b',
        'midnight-light': '#f1f5f9',
        'midnight-dark': '#0b1120',
        'emerald-light': '#f0f7f4',
        'emerald-dark': '#0b1411',
        'ruby-light': '#faf3f3',
        'ruby-dark': '#160c0e',
        'rose-light': '#fcf4f8',
        'rose-dark': '#160c12',
        'lime-light': '#f5f9f0',
        'lime-dark': '#0f150a',
    };

    const SVG_EYE = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/></svg>';
    const SVG_EYE_SLASH = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.944 5.944 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z"/><path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829l.822.822zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829z"/><path d="M3.35 5.47c-.18.16-.353.322-.518.487A13.134 13.134 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7.029 7.029 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12-.708.708z"/></svg>';

    const CACHE_PREFIX = 'prof_cache_v1_';
    const CACHE_TTL_MS = 2 * 60 * 1000; // 2 minutes
    const pendingFetches = new Map();

    function startTopProgressBar() {
        let bar = document.getElementById('nav-progress-bar');
        if (!bar) {
            bar = document.createElement('div');
            bar.id = 'nav-progress-bar';
            document.body.appendChild(bar);
        }
        bar.classList.remove('active');
        void bar.offsetWidth;
        bar.classList.add('active');
    }

    function enhanceReturnToCardsLinks() {
        let lastCards = null;
        try {
            lastCards = sessionStorage.getItem('last_cards_url');
        } catch (_) {}

        document.querySelectorAll('a[onclick*="retourAuxCartes"]').forEach((link) => {
            if (lastCards) {
                link.href = lastCards;
            }
            link.removeAttribute('onclick');
            link.addEventListener('click', (e) => {
                if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) return;
                link.classList.add('is-navigating');
                startTopProgressBar();
            });
        });
    }

    function getCachedTab(key) {
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

    function setCachedTab(key, html) {
        try {
            sessionStorage.setItem(CACHE_PREFIX + key, JSON.stringify({ html, ts: Date.now() }));
        } catch (_) {
            clearProfileCache();
        }
    }

    function clearProfileCache() {
        try {
            for (let i = sessionStorage.length - 1; i >= 0; i--) {
                const k = sessionStorage.key(i);
                if (k && k.startsWith(CACHE_PREFIX)) {
                    sessionStorage.removeItem(k);
                }
            }
        } catch (_) {}
    }

    function getSavedThemeConfig() {
        let palette = 'gold';
        let mode = 'dark';
        try {
            const storedPalette = localStorage.getItem('theme_palette');
            const storedMode = localStorage.getItem('theme_mode');
            if (ALLOWED_PALETTES.includes(storedPalette)) {
                palette = storedPalette;
            }
            if (ALLOWED_MODES.includes(storedMode)) {
                mode = storedMode;
            } else {
                const legacy = localStorage.getItem('theme');
                if (legacy === 'light') { palette = 'gold'; mode = 'light'; }
                else if (legacy === 'obsidian') { palette = 'obsidian'; mode = 'dark'; }
                else if (legacy === 'midnight') { palette = 'midnight'; mode = 'dark'; }
                else if (legacy === 'emerald') { palette = 'emerald'; mode = 'dark'; }
            }
        } catch (_) {}
        return { palette, mode };
    }

    function applyTheme(palette, mode) {
        const finalPalette = ALLOWED_PALETTES.includes(palette) ? palette : 'gold';
        const finalMode = ALLOWED_MODES.includes(mode) ? mode : 'dark';
        const themeKey = `${finalPalette}-${finalMode}`;

        document.documentElement.setAttribute('data-theme', themeKey);
        document.documentElement.setAttribute('data-mode', finalMode);
        document.documentElement.setAttribute('data-bs-theme', finalMode);

        try {
            localStorage.setItem('theme_palette', finalPalette);
            localStorage.setItem('theme_mode', finalMode);
            localStorage.setItem('theme', finalMode);
        } catch (_) {}

        const metaThemeColor = document.getElementById('meta-theme-color');
        if (metaThemeColor) {
            metaThemeColor.setAttribute('content', THEME_COLORS[themeKey] || '#111318');
        }

        document.querySelectorAll('.theme-mode-btn').forEach((btn) => {
            const active = btn.getAttribute('data-mode-choice') === finalMode;
            btn.classList.toggle('is-active', active);
            btn.setAttribute('aria-checked', active ? 'true' : 'false');
        });

        document.querySelectorAll('.theme-option-card').forEach((card) => {
            const active = card.getAttribute('data-palette-choice') === finalPalette;
            card.classList.toggle('is-active', active);
            card.setAttribute('aria-checked', active ? 'true' : 'false');
        });
    }

    function notify(message, type = 'success') {
        if (typeof window.showToast === 'function') {
            window.showToast(message, type);
        }
    }

    function normalizeTabUrl(rawUrl) {
        const parsed = new URL(rawUrl, window.location.origin);
        const tab = parsed.searchParams.get('tab');
        parsed.search = tab && tab !== 'profile' ? `?tab=${encodeURIComponent(tab)}` : '';
        parsed.hash = '';
        return parsed.toString();
    }

    function bindProfileContentEvents(container) {
        if (!container) return;

        // 1. Mode Clair / Sombre + 4 Palettes (?tab=display)
        const { palette: currentPalette, mode: currentMode } = getSavedThemeConfig();

        container.querySelectorAll('.theme-mode-btn').forEach((btn) => {
            const active = btn.getAttribute('data-mode-choice') === currentMode;
            btn.classList.toggle('is-active', active);
            btn.setAttribute('aria-checked', active ? 'true' : 'false');

            btn.addEventListener('click', function () {
                const chosenMode = this.getAttribute('data-mode-choice');
                const { palette } = getSavedThemeConfig();
                applyTheme(palette, chosenMode);
            });
        });

        container.querySelectorAll('.theme-option-card').forEach((card) => {
            const active = card.getAttribute('data-palette-choice') === currentPalette;
            card.classList.toggle('is-active', active);
            card.setAttribute('aria-checked', active ? 'true' : 'false');

            card.addEventListener('click', function () {
                const chosenPalette = this.getAttribute('data-palette-choice');
                const { mode } = getSavedThemeConfig();
                applyTheme(chosenPalette, mode);
            });
        });

        // 2. Option : ouvrir les catégories par défaut
        const openDivisionsToggle = container.querySelector('#toggle-open-divisions');
        if (openDivisionsToggle) {
            let enabled = false;
            try {
                enabled = localStorage.getItem('defaultOpenDivisions') === 'true';
            } catch (_) {}
            openDivisionsToggle.checked = enabled;

            openDivisionsToggle.addEventListener('change', function () {
                try {
                    localStorage.setItem('defaultOpenDivisions', this.checked ? 'true' : 'false');
                } catch (_) {}
                notify(
                    this.checked
                        ? 'Les catégories seront ouvertes par défaut.'
                        : 'Les catégories resteront repliées par défaut.',
                    'info'
                );
            });
        }

        // 3. Nettoyage : réinitialiser les filtres mémorisés
        const resetFiltersBtn = container.querySelector('#btn-reset-filters');
        if (resetFiltersBtn) {
            resetFiltersBtn.addEventListener('click', function () {
                try {
                    localStorage.removeItem('cardFilters');
                } catch (_) {}
                notify('Filtres de recherche réinitialisés.', 'success');
            });
        }

        // 4. Nettoyage : réafficher les notifications de sorties masquées
        const resetReleasesBtn = container.querySelector('#btn-reset-releases');
        if (resetReleasesBtn) {
            resetReleasesBtn.addEventListener('click', function () {
                try {
                    localStorage.removeItem('dismissedReleases');
                } catch (_) {}
                notify('Les notifications de sorties seront de nouveau visibles.', 'success');
            });
        }

        // 5. Ré-attache les boutons "Afficher le mot de passe"
        container.querySelectorAll('.password-toggle').forEach((btn) => {
            if (btn.dataset.bound === '1') return;
            btn.dataset.bound = '1';
            btn.innerHTML = SVG_EYE;
            btn.addEventListener('click', function () {
                const wrapper = this.closest('.password-wrapper');
                const input = wrapper ? wrapper.querySelector('input') : null;
                if (!input) return;
                if (input.type === 'password') {
                    input.type = 'text';
                    this.innerHTML = SVG_EYE_SLASH;
                } else {
                    input.type = 'password';
                    this.innerHTML = SVG_EYE;
                }
            });
        });

        // 6. Vide le cache du profil lors de la soumission d'un formulaire
        container.querySelectorAll('form').forEach((form) => {
            form.addEventListener('submit', clearProfileCache);
        });

        // 7. Liens internes vers un autre onglet du profil
        container.querySelectorAll('a[href*="?tab="]').forEach((link) => {
            link.addEventListener('click', function (e) {
                if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) return;
                e.preventDefault();
                switchProfileTab(this.href, true);
            });
        });
    }

    async function fetchTabHtml(url) {
        const key = normalizeTabUrl(url);
        const cached = getCachedTab(key);
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
                const content = doc.querySelector('.profile-content');
                if (!content) throw new Error('Contenu profil introuvable');
                const inner = content.innerHTML;
                setCachedTab(key, inner);
                return inner;
            })
            .finally(() => {
                pendingFetches.delete(key);
            });

        pendingFetches.set(key, promise);
        return promise;
    }

    function updateSidebarActive(targetUrl) {
        const targetKey = normalizeTabUrl(targetUrl);
        document.querySelectorAll('.profile-sidebar .profile-nav-item').forEach((item) => {
            const itemKey = normalizeTabUrl(item.href);
            item.classList.toggle('active', itemKey === targetKey);
        });
    }

    let activeNavToken = 0;

    async function switchProfileTab(url, pushHistory = true) {
        const contentEl = document.querySelector('.profile-content');
        if (!contentEl) {
            window.location.href = url;
            return;
        }

        const key = normalizeTabUrl(url);
        const token = ++activeNavToken;

        updateSidebarActive(key);
        if (pushHistory && normalizeTabUrl(window.location.href) !== key) {
            window.history.pushState({ profileTabUrl: key }, '', key);
        }

        try {
            const html = await fetchTabHtml(key);
            if (token !== activeNavToken) return;

            contentEl.innerHTML = html;
            bindProfileContentEvents(contentEl);
        } catch (_) {
            if (token === activeNavToken) {
                window.location.href = url;
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const { palette, mode } = getSavedThemeConfig();
        applyTheme(palette, mode);
        enhanceReturnToCardsLinks();

        document.addEventListener('click', (e) => {
            const link = e.target.closest('a[href]');
            if (!link) return;
            if (
                e.defaultPrevented ||
                e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0 ||
                link.target === '_blank' ||
                link.hasAttribute('download') ||
                link.classList.contains('nav-tab') ||
                link.classList.contains('profile-nav-item')
            ) {
                return;
            }
            const href = link.getAttribute('href') || '';
            if (!href || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('javascript:')) {
                return;
            }
            try {
                const url = new URL(link.href, window.location.origin);
                if (url.origin === window.location.origin) {
                    link.classList.add('is-navigating');
                    startTopProgressBar();
                }
            } catch (_) {}
        });

        let openByDefault = false;
        try {
            openByDefault = localStorage.getItem('defaultOpenDivisions') === 'true';
        } catch (_) {}

        if (openByDefault) {
            const searchInput = document.getElementById('liveSearch');
            const statusFilter = document.getElementById('statusFilter');
            const categoryFilter = document.getElementById('categoryFilter');
            const hasActiveFilter = Boolean(
                (searchInput && searchInput.value.trim()) ||
                (statusFilter && statusFilter.value) ||
                (categoryFilter && categoryFilter.value)
            );
            if (!hasActiveFilter) {
                document.querySelectorAll('details.division-section').forEach((detail) => {
                    if (!detail.hidden) {
                        detail.open = true;
                    }
                });
            }
        }

        const profileContent = document.querySelector('.profile-content');
        const navItems = document.querySelectorAll('.profile-sidebar .profile-nav-item');

        if (profileContent && navItems.length > 0) {
            const currentKey = normalizeTabUrl(window.location.href);
            setCachedTab(currentKey, profileContent.innerHTML);

            profileContent.querySelectorAll('.password-toggle').forEach((btn) => {
                btn.dataset.bound = '1';
            });

            bindProfileContentEvents(profileContent);

            navItems.forEach((link) => {
                link.addEventListener('mouseenter', () => {
                    fetchTabHtml(link.href).catch(() => {});
                });
                link.addEventListener('focus', () => {
                    fetchTabHtml(link.href).catch(() => {});
                });
                link.addEventListener('click', (e) => {
                    if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) return;
                    e.preventDefault();
                    switchProfileTab(link.href, true);
                });
            });

            const prefetchQueue = Array.from(navItems)
                .map((link) => normalizeTabUrl(link.href))
                .filter((key) => getCachedTab(key) === null);

            const prefetchNext = async () => {
                while (prefetchQueue.length > 0) {
                    const nextKey = prefetchQueue.shift();
                    if (!nextKey || getCachedTab(nextKey) !== null) continue;
                    try {
                        await fetchTabHtml(nextKey);
                    } catch (_) {}
                    await new Promise((r) => setTimeout(r, 120));
                }
            };

            if ('requestIdleCallback' in window) {
                window.requestIdleCallback(() => prefetchNext(), { timeout: 1200 });
            } else {
                setTimeout(prefetchNext, 300);
            }

            window.addEventListener('popstate', () => {
                if (document.querySelector('.profile-content')) {
                    switchProfileTab(window.location.href, false);
                }
            });
        }
    });
})();
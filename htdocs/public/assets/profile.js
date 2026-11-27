(function () {
    'use strict';

    const ALLOWED_THEMES = ['light', 'dark', 'obsidian', 'midnight', 'emerald'];
    const THEME_COLORS = {
        light: '#fcfcfd',
        dark: '#111318',
        obsidian: '#09090b',
        midnight: '#0b1120',
        emerald: '#0b1411',
    };

    const SVG_SUN = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sun-fill" viewBox="0 0 16 16"><path d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8M8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0m0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13m8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5M3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8m10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0m-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0m9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707M4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708"/></svg>';
    const SVG_MOON = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-moon-stars-fill" viewBox="0 0 16 16"><path d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278"/><path d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.73 1.73 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.73 1.73 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.73 1.73 0 0 0 1.097-1.097zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.16 1.16 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.16 1.16 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732z"/></svg>';

    function getSavedTheme() {
        try {
            const stored = localStorage.getItem('theme');
            if (ALLOWED_THEMES.includes(stored)) {
                return stored;
            }
        } catch (_) {}
        return 'light';
    }

    function applyTheme(theme) {
        const finalTheme = ALLOWED_THEMES.includes(theme) ? theme : 'light';
        const isDark = finalTheme !== 'light';

        document.documentElement.setAttribute('data-theme', finalTheme);
        document.documentElement.setAttribute('data-bs-theme', isDark ? 'dark' : 'light');

        try {
            localStorage.setItem('theme', finalTheme);
            if (isDark) {
                localStorage.setItem('lastDarkTheme', finalTheme);
            }
        } catch (_) {}

        const metaThemeColor = document.getElementById('meta-theme-color');
        if (metaThemeColor) {
            metaThemeColor.setAttribute('content', THEME_COLORS[finalTheme] || '#fcfcfd');
        }

        const headerToggle = document.getElementById('theme-toggle');
        if (headerToggle) {
            headerToggle.innerHTML = isDark ? SVG_SUN : SVG_MOON;
        }

        document.querySelectorAll('.theme-option-card').forEach((card) => {
            const active = card.getAttribute('data-theme-choice') === finalTheme;
            card.classList.toggle('is-active', active);
            card.setAttribute('aria-checked', active ? 'true' : 'false');
        });
    }

    function notify(message, type = 'success') {
        if (typeof window.showToast === 'function') {
            window.showToast(message, type);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Réapplique le thème exact (y compris obsidian/midnight/emerald) après script.js
        applyTheme(getSavedTheme());

        // Synchronise le bouton du header avec le dernier thème sombre choisi
        const headerToggle = document.getElementById('theme-toggle');
        if (headerToggle) {
            headerToggle.addEventListener('click', function () {
                setTimeout(() => {
                    const current = document.documentElement.getAttribute('data-theme');
                    if (current === 'dark') {
                        let preferredDark = 'dark';
                        try {
                            const savedDark = localStorage.getItem('lastDarkTheme');
                            if (savedDark && savedDark !== 'light' && ALLOWED_THEMES.includes(savedDark)) {
                                preferredDark = savedDark;
                            }
                        } catch (_) {}
                        applyTheme(preferredDark);
                    } else {
                        applyTheme('light');
                    }
                }, 0);
            });
        }

        // Cartes de sélection de thème (?tab=display)
        document.querySelectorAll('.theme-option-card').forEach((card) => {
            card.addEventListener('click', function () {
                const chosen = this.getAttribute('data-theme-choice');
                applyTheme(chosen);
                notify('Thème mis à jour !', 'success');
            });
        });

        // Option : ouvrir les catégories par défaut
        const openDivisionsToggle = document.getElementById('toggle-open-divisions');
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

        // Applique l'ouverture par défaut sur la page des cartes si aucun filtre n'est actif
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

        // Nettoyage : réinitialiser les filtres mémorisés
        const resetFiltersBtn = document.getElementById('btn-reset-filters');
        if (resetFiltersBtn) {
            resetFiltersBtn.addEventListener('click', function () {
                try {
                    localStorage.removeItem('cardFilters');
                } catch (_) {}
                notify('Filtres de recherche réinitialisés.', 'success');
            });
        }

        // Nettoyage : réafficher les notifications de sorties masquées
        const resetReleasesBtn = document.getElementById('btn-reset-releases');
        if (resetReleasesBtn) {
            resetReleasesBtn.addEventListener('click', function () {
                try {
                    localStorage.removeItem('dismissedReleases');
                } catch (_) {}
                notify('Les notifications de sorties seront de nouveau visibles.', 'success');
            });
        }
    });
})();
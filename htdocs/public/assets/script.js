// ==========================================
// 1. SYSTEME DE NOTIFICATIONS (TOASTS) 
// ==========================================
window.showToast = function(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerText = message;
    
    container.appendChild(toast);
    
    setTimeout(() => toast.classList.add('show'), 50);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 400);
    }, 4000);
}

// ==========================================
// INITIALISATION DES ELEMENTS DU DOM
// ==========================================
document.addEventListener('DOMContentLoaded', function() {
    function svgWithColor($color) {
        if ($color === 'Sombre') {
            return `
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
            fill="currentColor" class="bi bi-moon-stars-fill" viewBox="0 0 16 16">
            <path
                d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278" />
            <path
                d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.73 1.73 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.73 1.73 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.73 1.73 0 0 0 1.097-1.097zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.16 1.16 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.16 1.16 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732z" />
            </svg>
            `;
        } else if ($color === 'Clair') {
            return `
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sun-fill" viewBox="0 0 16 16">
                <path d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8M8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0m0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13m8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5M3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8m10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0m-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0m9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707M4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708"/>
                </svg>
                `;
        }
    }

    // ==========================================
    // 2. THEME SOMBRE (Dark Mode)
    // ==========================================
    const themeToggleBtn = document.getElementById('theme-toggle');
    const currentTheme = localStorage.getItem('theme') || 'light';
    const metaThemeColor = document.getElementById('meta-theme-color');
    
    if (currentTheme === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
        document.documentElement.setAttribute('data-bs-theme', 'dark');
        if(themeToggleBtn) themeToggleBtn.innerHTML = svgWithColor('Clair');
        if(metaThemeColor) metaThemeColor.setAttribute('content', '#09090b');
    } else {
        document.documentElement.setAttribute('data-theme', 'light');
        document.documentElement.setAttribute('data-bs-theme', 'light');
        if(metaThemeColor) metaThemeColor.setAttribute('content', '#fcfcfd');
    }

    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', () => {
            let theme = document.documentElement.getAttribute('data-theme');
            let switchToTheme = theme === 'dark' ? 'light' : 'dark';
            
            document.documentElement.setAttribute('data-theme', switchToTheme);
            document.documentElement.setAttribute('data-bs-theme', switchToTheme);
            localStorage.setItem('theme', switchToTheme);

            themeToggleBtn.innerHTML = switchToTheme === 'dark' ? svgWithColor('Clair') : svgWithColor('Sombre');

            if(metaThemeColor) {
                metaThemeColor.setAttribute('content', switchToTheme === 'dark' ? '#09090b' : '#fcfcfd');
            }
        });
    }

    // ==========================================
    // 3. COMPTEUR DE CARACTERES (Description)
    // ==========================================
    const textarea = document.getElementById('description');
    const charCount = document.getElementById('char-count');

    if (textarea && charCount) {
        const maxLength = textarea.getAttribute('maxlength');
        
        function updateCounter() {
            const currentLength = textarea.value.length;
            charCount.textContent = `${currentLength} / ${maxLength}`;
            
            if (currentLength >= maxLength) {
                charCount.style.color = 'var(--danger)';
            } else {
                charCount.style.color = 'var(--text-muted)';
            }
        }
        
        updateCounter();
        textarea.addEventListener('input', updateCounter);
    }

    // ==========================================
    // 4. INCREMENTATION ASYNCHRONE (+1 Episode)
    // ==========================================
    document.querySelectorAll('.btn-increment').forEach(button => {
        button.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();

            const itemId = button.getAttribute('data-id');
            const baseUrl = amfsConfig.baseUrl.endsWith('/') ? amfsConfig.baseUrl : amfsConfig.baseUrl + '/';
            const url = baseUrl + 'item/increment-episode/' + itemId;
            const counterSpan = document.getElementById(`ep-count-${itemId}`);

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        [amfsConfig.csrfHeader]: amfsConfig.csrfToken
                    }
                });

                if (!response.ok) throw new Error(`Erreur HTTP: ${response.status}`);

                const data = await response.json();

                if (data.success) {
                    counterSpan.innerText = data.new_episode;
                    counterSpan.style.color = 'var(--success)';
                    counterSpan.style.transform = 'scale(1.2)';
                    
                    setTimeout(() => {
                        counterSpan.style.color = '';
                        counterSpan.style.transform = 'scale(1)';
                    }, 400);

                    if (data.csrf_token) amfsConfig.csrfToken = data.csrf_token; 
                    if (typeof showToast === 'function') showToast('Episode ajoute avec succes !', 'success');
                } else {
                    console.error("Erreur renvoyee par PHP :", data);
                }
            } catch (error) {
                console.error("Echec de la requete Fetch :", error);
            }
        });
    });

    // ==========================================
    // 5. BOUTONS DE CHARGEMENT SUR FORMULAIRES
    // ==========================================
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn && this.checkValidity()) {
                submitBtn.classList.add('loading');
            }
        });
    });

    // ==========================================
    // 6. TOGGLE VISIBILITE MOT DE PASSE
    // ==========================================
    const svgEye = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/></svg>`;
    const svgEyeSlash = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.944 5.944 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z"/><path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829l.822.822zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829z"/><path d="M3.35 5.47c-.18.16-.353.322-.518.487A13.134 13.134 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7.029 7.029 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12-.708.708z"/></svg>`;

    document.querySelectorAll('.password-toggle').forEach(btn => {
        btn.innerHTML = svgEye;
        btn.addEventListener('click', function() {
            const wrapper = this.closest('.password-wrapper');
            const input = wrapper.querySelector('input');
            
            if (input.type === 'password') {
                input.type = 'text';
                this.innerHTML = svgEyeSlash;
            } else {
                input.type = 'password';
                this.innerHTML = svgEye;
            }
        });
    });

    // ==========================================
    // 7. RECHERCHE EN DIRECT (Live Search)
    // ==========================================
    const searchInput = document.getElementById('liveSearch');

    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase();
            const cards = document.querySelectorAll('.searchable-card, .card');
            
            cards.forEach(card => {
                const title = card.querySelector('.card-title, .search-target-title')?.innerText.toLowerCase() || '';
                const desc = card.querySelector('.card-desc, .search-target-desc')?.innerText.toLowerCase() || '';
                
                if (title.includes(term) || desc.includes(term)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }

    // ==========================================
    // 8. DRAG AND DROP (SortableJS)
    // ==========================================
    var grids = document.querySelectorAll('.sortable-grid');
    if (typeof Sortable !== 'undefined') {
        grids.forEach(function(el) {
            Sortable.create(el, {
                animation: 150,
                ghostClass: 'sortable-ghost',
                handle: '.drag-handle',
                delay: 200, 
                delayOnTouchOnly: true, 
                onEnd: async function(evt) {
                    if (evt.oldIndex === evt.newIndex) return;
                    
                    var itemEls = el.querySelectorAll('.card');
                    var newOrder = [];
                    
                    itemEls.forEach(function(item) {
                        var id = item.getAttribute('data-id');
                        if (id) newOrder.push(id);
                    });

                    try {
                        const response = await fetch(amfsConfig.updateOrderUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                [amfsConfig.csrfHeader]: amfsConfig.csrfToken
                            },
                            body: JSON.stringify({ order: newOrder })
                        });
                        const data = await response.json();
                        
                        if (data.csrf_token) {
                            amfsConfig.csrfToken = data.csrf_token;
                        }
                        
                        if (typeof showToast === 'function') showToast("Ordre mis à jour !", 'success');
                    } catch (err) {
                        console.error("Erreur Drag&Drop:", err);
                        if (typeof showToast === 'function') showToast("Erreur lors de la sauvegarde de l'ordre", "danger");
                    }
                }
            });
        });
    }

    // ==========================================
    // 9. AUTO-REMPLISSAGE UNIFIE (Avec Selection)
    // ==========================================
    const btnApiSearch = document.getElementById('btn-api-search');
    const resultsContainer = document.getElementById('api-results-container');

    if (btnApiSearch && resultsContainer) {
        document.addEventListener('click', function(event) {
            if (!resultsContainer.contains(event.target) && event.target !== btnApiSearch && event.target.id !== 'titre') {
                resultsContainer.style.display = 'none';
            }
        });

        btnApiSearch.addEventListener('click', async function() {
            const titreInput = document.getElementById('titre').value.trim();
            if (!titreInput) {
                showToast("Entre d'abord un titre ou un lien !", "danger");
                return;
            }

            const statusTxt = document.getElementById('api-status');
            statusTxt.style.display = 'inline';
            statusTxt.innerText = 'Recherche en cours...';
            
            resultsContainer.innerHTML = '';
            resultsContainer.style.display = 'none';

            let typeSelectionne = 'film';
            const divisionSelect = document.getElementById('id_division');
            if (divisionSelect && divisionSelect.selectedIndex >= 0) {
                const divText = divisionSelect.options[divisionSelect.selectedIndex].text.toLowerCase();
                if (divText.includes('manga')) typeSelectionne = 'manga';
                else if (divText.includes('anime') || divText.includes('animé')) typeSelectionne = 'anime';
                else if (divText.includes('série') || divText.includes('serie')) typeSelectionne = 'serie';
                else if (divText.includes('lien') || divText.includes('web') || divText.includes('autre')) typeSelectionne = 'lien';
            }

            if (titreInput.startsWith('http://') || titreInput.startsWith('https://')) {
                typeSelectionne = 'lien';
            }

            const baseUrl = amfsConfig.baseUrl.endsWith('/') ? amfsConfig.baseUrl : amfsConfig.baseUrl + '/';
            const url = `${baseUrl}item/search?q=${encodeURIComponent(titreInput)}&type=${typeSelectionne}`;

            try {
                const response = await fetch(url);
                const data = await response.json();

                if (data.error) {
                    showToast(data.error, "danger");
                    statusTxt.innerText = 'Erreur';
                    return;
                }

                const descField = document.getElementById('description');
                const maxDescLength = descField && descField.hasAttribute('maxlength') ? parseInt(descField.getAttribute('maxlength')) : 250;
                const limitCut = maxDescLength > 3 ? maxDescLength - 3 : maxDescLength;

                let listeResultats = [];

                if (data.data && data.data.length > 0) {
                    listeResultats = data.data.map(item => ({
                        titre: item.title,
                        imageThumb: item.images?.jpg?.image_url || '',
                        imageLarge: item.images?.jpg?.large_image_url || item.images?.jpg?.image_url || '',
                        description: item.synopsis ? (item.synopsis.length > limitCut ? item.synopsis.substring(0, limitCut) + "..." : item.synopsis) : "",
                        info: (item.year || '') + ' - ' + (item.type || typeSelectionne).toUpperCase(),
                        lien: ''
                    }));
                } else if (data.results && data.results.length > 0) {
                    listeResultats = data.results.map(item => ({
                        titre: item.title || item.name || item.original_name,
                        imageThumb: item.poster_path ? `https://image.tmdb.org/t/p/w200${item.poster_path}` : '',
                        imageLarge: item.poster_path ? `https://image.tmdb.org/t/p/w500${item.poster_path}` : '',
                        description: item.overview ? (item.overview.length > limitCut ? item.overview.substring(0, limitCut) + "..." : item.overview) : "",
                        info: (item.release_date || item.first_air_date || '').substring(0,4) + ' - ' + (item.media_type || typeSelectionne).toUpperCase(),
                        lien: ''
                    }));
                } else if (Array.isArray(data) && data.length > 0 && data[0].is_link) {
                    listeResultats = [{
                        titre: data[0].titre,
                        imageThumb: data[0].image,
                        imageLarge: data[0].image,
                        description: data[0].description ? (data[0].description.length > limitCut ? data[0].description.substring(0, limitCut) + "..." : data[0].description) : "",
                        info: "LIEN WEB",
                        lien: data[0].lien
                    }];
                }

                if (listeResultats.length > 0) {
                    statusTxt.innerText = `${listeResultats.length} resultat(s)`;
                    resultsContainer.style.display = 'block';

                    listeResultats.forEach(res => {
                        const divItem = document.createElement('div');
                        divItem.style.cssText = 'display: flex; align-items: center; padding: 10px; border-bottom: 1px solid var(--border-color); cursor: pointer; transition: background 0.2s;';
                        
                        divItem.onmouseover = () => divItem.style.background = 'rgba(128, 128, 128, 0.1)';
                        divItem.onmouseout = () => divItem.style.background = 'transparent';

                        const fallbackImg = 'https://via.placeholder.com/40x60?text=IMG';
                        const imgSrc = res.imageThumb || fallbackImg;

                        divItem.innerHTML = `
                            <img src="${imgSrc}" alt="Affiche" style="width: 40px; height: 60px; object-fit: cover; border-radius: var(--radius-md); margin-right: 15px;">
                            <div style="flex-grow: 1; overflow: hidden;">
                                <strong style="display: block; white-space: nowrap; text-overflow: ellipsis; overflow: hidden;">${res.titre}</strong>
                                <small style="color: gray;">${res.info}</small>
                            </div>
                        `;

                        divItem.addEventListener('click', () => {
                            document.getElementById('titre').value = res.titre;
                            
                            const imgField = document.getElementById('img');
                            if (res.imageLarge && imgField) {
                                imgField.value = res.imageLarge;
                                imgField.dispatchEvent(new Event('input')); 
                            }

                            if (res.description) document.getElementById('description').value = res.description;
                            
                            const inputLien = document.getElementById('lien');
                            if (res.lien && inputLien) inputLien.value = res.lien;
                            
                            if (textarea) textarea.dispatchEvent(new Event('input'));
                            
                            resultsContainer.style.display = 'none';
                            statusTxt.innerText = 'Selectionne !';
                            showToast("Formulaire mis a jour.", "success");
                        });

                        resultsContainer.appendChild(divItem);
                    });

                } else {
                    statusTxt.innerText = 'Aucun resultat';
                    resultsContainer.style.display = 'none';
                }

            } catch (e) {
                console.error("Erreur Fetch API:", e);
                statusTxt.innerText = 'Erreur serveur';
            }
        });
    }

    // ==========================================
    // 10. APERÇU EN DIRECT DE L'IMAGE
    // ==========================================
    const imgInput = document.getElementById('img');
    const imgPreview = document.getElementById('img-preview');
    const imgPlaceholder = document.getElementById('img-placeholder');

    if (imgInput && imgPreview && imgPlaceholder) {
        imgInput.addEventListener('input', function() {
            const url = this.value.trim();
            if (url) {
                imgPreview.src = url;
                imgPreview.style.display = 'block';
                imgPlaceholder.style.display = 'none';
            } else {
                imgPreview.style.display = 'none';
                imgPlaceholder.style.display = 'block';
                imgPlaceholder.innerText = 'Apercu';
                imgPlaceholder.style.color = 'var(--text-muted)';
                imgPreview.src = '';
            }
        });
        
        imgPreview.addEventListener('error', function() {
            this.style.display = 'none';
            imgPlaceholder.style.display = 'block';
            imgPlaceholder.innerHTML = 'Erreur';
            imgPlaceholder.style.color = 'var(--danger)'; 
        });

        imgPreview.addEventListener('load', function() {
            imgPlaceholder.style.color = 'var(--text-muted)';
            imgPlaceholder.innerText = 'Apercu';
        });
    }

    // ==========================================
    // SYSTEME DE SIGNALEMENT DE PROBLEME
    // ==========================================
    window.openReportModal = async function(button) {
        const itemId = button.getAttribute('data-id');
        
        const type = prompt("Que souhaitez-vous signaler ?\nTapez 1 pour : Lien mort\nTapez 2 pour : Autre problème");
        
        if (!type) return; 

        let issueType = 'autre';
        if (type === '1') issueType = 'lien_mort';
        if (type === '2') issueType = 'bug';
        
        const description = prompt("Pouvez-vous préciser le problème ? (Optionnel mais recommandé)");
        
        if (description === null) return; 

        const baseUrl = amfsConfig.baseUrl.endsWith('/') ? amfsConfig.baseUrl : amfsConfig.baseUrl + '/';
        const url = baseUrl + 'report/submit';

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    [amfsConfig.csrfHeader]: amfsConfig.csrfToken
                },
                body: JSON.stringify({
                    item_id: itemId,
                    type: issueType,
                    description: description.trim()
                })
            });

            const data = await response.json();
            
            if (data.csrf_token) {
                amfsConfig.csrfToken = data.csrf_token;
            }

            if (data.success) {
                if (typeof showToast === 'function') showToast(data.message, 'success');
            } else {
                if (typeof showToast === 'function') showToast(data.error || 'Erreur lors de l\'envoi.', 'danger');
            }
        } catch (error) {
            console.error("Erreur d'envoi du signalement:", error);
            if (typeof showToast === 'function') showToast("Une erreur réseau est survenue.", "danger");
        }
    };
}); // Fin DOMContentLoaded


// ==========================================
// 11. VÉRIFICATION DE DISPONIBILITÉ EN DIRECT
// ==========================================
window.addEventListener('load', function() {
    const cardsToCheck = document.querySelectorAll('.needs-dispo-check');
    
    // --- On récupère dynamiquement les domaines supportés transmis par PHP ---
    const supportedDomains = window.amfsSupportedDomains || [];
    
    cardsToCheck.forEach(async function(card) {
        const itemId = card.getAttribute('data-id');
        const url = card.getAttribute('data-url');
        const statusDiv = document.getElementById(`live-status-${itemId}`);
        const dateContainer = document.getElementById(`date-container-${itemId}`);

        const isSupported = url && supportedDomains.some(domain => url.includes(domain));

        if (!isSupported) {
            if (statusDiv) statusDiv.style.display = 'none';
            return;
        }

        // ========================================================
        // NOUVEAU : SYSTÈME DE CACHE (sessionStorage)
        // La clé de cache inclut l'URL complète. 
        // Si la carte est modifiée (ex: épisode +1), l'URL change, 
        // le cache est invalidé et la carte est re-vérifiée !
        // ========================================================
        const cacheKey = `dispo_check_${itemId}_${url}`;
        const cachedResult = sessionStorage.getItem(cacheKey);

        // Si un résultat récent existe (moins de 30 minutes), on l'utilise sans requête HTTP
        if (cachedResult) {
            const cacheData = JSON.parse(cachedResult);
            const ageInMinutes = (Date.now() - cacheData.timestamp) / 60000;
            
            if (ageInMinutes < 30) {
                applyDispoResult(cacheData.disponible, statusDiv, dateContainer);
                return; // On arrête l'exécution ici, la carte ne sera pas re-vérifiée
            }
        }

        try {
            const baseUrl = amfsConfig.baseUrl.endsWith('/') ? amfsConfig.baseUrl : amfsConfig.baseUrl + '/';
            
            const response = await fetch(`${baseUrl}item/check-dispo?urlCible=${encodeURIComponent(url)}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                throw new Error(`Erreur serveur HTTP: ${response.status}`);
            }
            
            const data = await response.json();

            if (!data.success) {
                console.error(`Erreur PHP pour la carte ${itemId} :`, data.error);
                if (statusDiv) statusDiv.style.display = 'none';
                return;
            }

            // --- SAUVEGARDE DU RÉSULTAT EN CACHE ---
            sessionStorage.setItem(cacheKey, JSON.stringify({
                timestamp: Date.now(),
                disponible: data.disponible
            }));

            // Mise à jour de l'interface
            applyDispoResult(data.disponible, statusDiv, dateContainer);

        } catch (err) {
            console.error("Erreur réseau/Fetch pour la carte " + itemId, err);
            if (statusDiv) statusDiv.style.display = 'none';
        }
    });

    // Fonction utilitaire pour appliquer l'affichage (Mutualisée pour le cache et les requêtes)
    function applyDispoResult(disponible, statusDiv, dateContainer) {
        if (statusDiv) {
            statusDiv.style.display = 'none';
        }

        if (disponible) {
            if (dateContainer) dateContainer.style.display = 'none'; 
        } else {
            if (dateContainer) {
                dateContainer.style.display = 'block';
                if (dateContainer.innerHTML.trim() === '') {
                    dateContainer.innerHTML = `<p class="card-date" style="color: var(--danger); cursor: help;" title="L'épisode/chapitre n'est pas encore mis en ligne ou la saison s'est terminée.">Episode non disponible.</p>`;
                }
            }
        }
    }
});
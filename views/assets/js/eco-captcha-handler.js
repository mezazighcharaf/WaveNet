// ================================================
// ECO-CAPTCHA HANDLER pour Grille Écologique avec API Drag & Drop NATIVE
// ================================================

function initializeEcoCaptcha() {
    console.log('[initializeEcoCaptcha - CLICK MODE] STARTING initialization...');
    
    try {
        const captchaContainer = document.getElementById('eco-captcha-container');
        const elementsContainer = document.getElementById('elements-container'); // <- Garder pour trouver les éléments
        const cityGrid = document.getElementById('city-grid'); // <- Sert maintenant d'affichage
        const captchaForm = document.getElementById('captcha-form');
        const scoreDisplay = document.getElementById('durability-score');
        const feedbackDisplay = document.getElementById('captcha-feedback');
        const attemptsDisplay = document.getElementById('attempts-left'); 
        const hiddenScoreInput = document.getElementById('captcha-score');
        const hiddenElementsInput = document.getElementById('captcha-elements');
        const resetButton = document.getElementById('reset-captcha');
        const validateButton = document.getElementById('validate-captcha');

        // Logs détaillés pour comprendre l'état des éléments DOM
        console.log("[CAPTCHA Debug] Container found:", !!captchaContainer);
        console.log("[CAPTCHA Debug] Elements container found:", !!elementsContainer);
        console.log("[CAPTCHA Debug] City grid found:", !!cityGrid);
        
        // PAS BESOIN DE GÉNÉRER LES CELLULES dynamiquement pour le mode clic
        if (!cityGrid) {
            console.error('[initializeEcoCaptcha] #city-grid (display area) not found!');
            if (captchaContainer) captchaContainer.innerHTML = '<p style="color: red; text-align: center;">Erreur: Zone d\'affichage CAPTCHA (#city-grid) manquante.</p>';
            return;
        }

        // Sélection des éléments cliquables
        const clickableElements = document.querySelectorAll('.element.drag-item'); 

        console.log(`[initializeEcoCaptcha] Found ${clickableElements.length} clickable elements (.element.drag-item)`);
        
        // Log pour examiner chaque élément trouvé
        clickableElements.forEach(elem => {
            console.log(`[CAPTCHA Debug] Element: ${elem.id}, Type: ${elem.dataset.type}, Visible: ${window.getComputedStyle(elem).display !== 'none'}`);
        });

        // Vérifier les éléments essentiels (sans les dropCells)
        let missingElement = null;
        if (!captchaContainer) missingElement = "#eco-captcha-container";
        else if (!elementsContainer) missingElement = "#elements-container"; // Le conteneur des éléments
        else if (clickableElements.length === 0) missingElement = ".element.drag-item (aucun trouvé)";
        else if (!cityGrid) missingElement = "#city-grid";
        else if (!captchaForm) missingElement = "#captcha-form";
        else if (!scoreDisplay) missingElement = "#durability-score";
        else if (!feedbackDisplay) missingElement = "#captcha-feedback";
        else if (!attemptsDisplay) missingElement = "#attempts-left";
        else if (!hiddenScoreInput) missingElement = "#captcha-score";
        else if (!hiddenElementsInput) missingElement = "#captcha-elements";
        else if (!resetButton) missingElement = "#reset-captcha";
        else if (!validateButton) missingElement = "#validate-captcha";
        
        if (missingElement) {
            console.error(`[initializeEcoCaptcha] Essential DOM element missing: ${missingElement}.`);
            if (captchaContainer) captchaContainer.innerHTML = `<p style="color: red; text-align: center;">Erreur chargement CAPTCHA (manquant: ${missingElement}).</p>`;
            return;
        }
        
        // Tentative de forcer l'affichage des éléments
        cityGrid.style.display = 'flex';
        elementsContainer.style.display = 'block';
        document.querySelectorAll('.elements-list').forEach(el => el.style.display = 'grid');
        
        let currentScore = 0;
        // placedElements stocke maintenant les ID des éléments sélectionnés
        let placedElements = {}; // { "element-bike": true, "element-solar": true, ... }
        const MAX_ATTEMPTS = 3; 
        let attemptsLeft = MAX_ATTEMPTS;

        function updateAttemptsDisplay() {
            if(attemptsDisplay) {
                attemptsDisplay.textContent = `Tentatives restantes : ${attemptsLeft}`;
                // Afficher l'élément si ce n'est pas déjà fait
                attemptsDisplay.style.display = 'inline'; 
            } else {
                console.warn("Element #attempts-left not found for display.");
            }
        }
        function updateScore(change) {
            currentScore += change;
            scoreDisplay.textContent = currentScore;
            hiddenScoreInput.value = currentScore;
            // console.log(`Score mis à jour: ${currentScore}`);
        }
        function updatePlacedElementsData() {
            hiddenElementsInput.value = JSON.stringify(placedElements);
            // console.log('Champ éléments cachés mis à jour:', hiddenElementsInput.value);
        }
        function showFeedback(message, type = 'info') {
            feedbackDisplay.textContent = message;
            feedbackDisplay.className = 'captcha-feedback';
            if (type === 'success') feedbackDisplay.classList.add('success');
            else if (type === 'error') feedbackDisplay.classList.add('error');
        }

        // --- LOGIQUE DE CLIC SUR UN ÉLÉMENT --- 
        function handleElementClick(event) {
            const clickedElement = event.currentTarget;
            const elementId = clickedElement.id;
            const elementType = clickedElement.dataset.type;
            const elementValue = parseInt(clickedElement.dataset.value || '0');
            const isPlaced = placedElements.hasOwnProperty(elementId);
            const maxItems = 6; // Limite arbitraire (taille de la grille visuelle)

            console.log(`[Click Event] Element clicked: ${elementId}, Type: ${elementType}, Value: ${elementValue}, IsPlaced: ${isPlaced}`);

            if (!isPlaced) {
                // --- Sélection --- 
                if (Object.keys(placedElements).length >= maxItems) {
                    showFeedback('Zone de dépôt pleine.', 'error');
                    console.warn('Attempted to place item, but grid is full.');
                    return;
                }
                
                // Ajouter tous les éléments, qu'ils soient durables ou non
                if (elementType === 'durable') {
                    placedElements[elementId] = { type: elementType, value: 20 };
                    updateScore(20);
                    showFeedback(`Élément durable ajouté. Score: +20`, 'success');
                } else {
                    placedElements[elementId] = { type: elementType, value: -10 };
                    updateScore(-10);
                    showFeedback(`Élément non-durable ajouté. Score: -10`, 'error');
                }

                try {
                    // Ajout visuel à la grille avec plus de détails de debug
                    console.log(`[Debug Grid] Adding visual element for ${elementId} to grid`);
                    
                    // Vérifier si l'icône existe
                    const iconElement = clickedElement.querySelector('i');
                    if (!iconElement) {
                        console.error(`[Debug Grid] Icon not found in element ${elementId}`);
                    }
                    
                    const iconClone = iconElement ? iconElement.cloneNode(true) : document.createElement('i');
                    if (!iconElement) {
                        // Créer une icône de secours
                        iconClone.className = 'fas fa-check';
                    }
                    
                    // Assurer que l'icône a le bon style pour Font Awesome 6.4
                    iconClone.style.fontFamily = '"Font Awesome 6 Free"';
                    iconClone.style.fontWeight = '900';
                    iconClone.style.display = 'inline-block';
                    iconClone.style.fontSize = '1.6rem';
                    iconClone.style.color = elementType === 'durable' ? '#4caf50' : '#f44336';
                    
                    // Créer l'élément visuel pour la grille avec style forcé
                    const elementDiv = document.createElement('div');
                    elementDiv.className = `element-in-grid ${elementType}`;
                    elementDiv.dataset.originId = elementId; // Lier l'élément de grille à l'original
                    
                    // Appliquer des styles inline pour s'assurer de la visibilité
                    elementDiv.style.width = '48px';
                    elementDiv.style.height = '48px';
                    elementDiv.style.display = 'flex';
                    elementDiv.style.justifyContent = 'center';
                    elementDiv.style.alignItems = 'center';
                    elementDiv.style.margin = '5px';
                    elementDiv.style.float = 'left';
                    elementDiv.style.backgroundColor = elementType === 'durable' ? '#e8f5e9' : '#ffebee';
                    elementDiv.style.border = `1px solid ${elementType === 'durable' ? '#81c784' : '#e57373'}`;
                    elementDiv.style.borderRadius = '6px';
                    elementDiv.style.position = 'relative';
                    
                    elementDiv.appendChild(iconClone);
                    
                    // Ajouter un bouton de suppression sur l'élément dans la grille
                    const removeBtn = document.createElement('button');
                    removeBtn.innerHTML = '&times;'; // Croix
                    removeBtn.className = 'remove-item-grid'; // Nouvelle classe pour style/logique
                    removeBtn.title = 'Retirer cet élément';
                    
                    // Styles forcés pour le bouton de suppression
                    removeBtn.style.position = 'absolute';
                    removeBtn.style.top = '-5px';
                    removeBtn.style.right = '-5px';
                    removeBtn.style.width = '20px';
                    removeBtn.style.height = '20px';
                    removeBtn.style.borderRadius = '50%';
                    removeBtn.style.backgroundColor = '#f44336';
                    removeBtn.style.color = 'white';
                    removeBtn.style.border = 'none';
                    removeBtn.style.cursor = 'pointer';
                    removeBtn.style.display = 'flex';
                    removeBtn.style.alignItems = 'center';
                    removeBtn.style.justifyContent = 'center';
                    removeBtn.style.zIndex = '10';
                    
                    removeBtn.onclick = function(e) {
                        e.stopPropagation();
                        handleRemoveFromGrid(elementId);
                    };
                    
                    elementDiv.appendChild(removeBtn);
                    
                    // Vérifier si le cityGrid existe
                    console.log(`[Debug Grid] cityGrid exists: ${!!cityGrid}, has content: ${cityGrid.innerHTML !== ''}`);
                    
                    // Ajouter l'élément au début du conteneur pour qu'ils s'affichent dans l'ordre
                    if (cityGrid.children.length > 0) {
                        cityGrid.insertBefore(elementDiv, cityGrid.firstChild);
                    } else {
                        cityGrid.appendChild(elementDiv);
                    }
                    
                    console.log(`[Debug Grid] Element added to grid. Grid now has ${cityGrid.children.length} children`);
                } catch (error) {
                    console.error('[Debug Grid] Error adding element to grid:', error);
                }

                // Marquer l'original comme sélectionné/inactif
                clickedElement.style.opacity = '0.5';
                clickedElement.classList.add('selected'); // Ajouter classe pour style
                
            } else {
                // --- Désélection (en cliquant sur l'original) ---
                console.log(`Deselecting ${elementId}`);
                const placedData = placedElements[elementId];
                updateScore(-placedData.value); // Soustraire la valeur
                delete placedElements[elementId];

                // Retirer de la grille visuelle
                const gridElementToRemove = cityGrid.querySelector(`[data-origin-id="${elementId}"]`);
                if (gridElementToRemove) {
                    cityGrid.removeChild(gridElementToRemove);
                    console.log(`[Debug Grid] Removed element from grid. Grid now has ${cityGrid.children.length} children`);
                } else {
                    console.warn(`[Debug Grid] Could not find element with data-origin-id="${elementId}" to remove`);
                }

                // Réactiver l'élément original
                clickedElement.style.opacity = '1';
                clickedElement.classList.remove('selected');
                
                showFeedback('Élément retiré.');
            }

            updatePlacedElementsData(); // Mettre à jour le champ caché
        }

        // --- LOGIQUE DE SUPPRESSION DEPUIS LA GRILLE --- (appelée par le bouton X)
        function handleRemoveFromGrid(elementIdToRemove) {
            console.log(`[Remove Event] Removing ${elementIdToRemove} from grid.`);
            if (placedElements.hasOwnProperty(elementIdToRemove)) {
                const placedData = placedElements[elementIdToRemove];
                updateScore(-placedData.value);
                delete placedElements[elementIdToRemove];

                const gridElementToRemove = cityGrid.querySelector(`[data-origin-id="${elementIdToRemove}"]`);
                if (gridElementToRemove) {
                    cityGrid.removeChild(gridElementToRemove);
                }

                const originalElement = document.getElementById(elementIdToRemove);
                if (originalElement) {
                    originalElement.style.opacity = '1';
                    originalElement.classList.remove('selected');
                    // originalElement.style.pointerEvents = 'auto';
                }
                showFeedback('Élément retiré.');
                updatePlacedElementsData();
            } else {
                console.warn(`Tried to remove ${elementIdToRemove} from grid, but it wasn\'t listed as placed.`);
            }
        }


        // --- Attacher les écouteurs de CLIC --- 
        clickableElements.forEach(element => {
            console.log(`[initializeEcoCaptcha] Adding CLICK listener to: ${element.id}`);
            
            // S'assurer que l'élément est visible et cliquable
            element.style.pointerEvents = 'auto';
            element.style.cursor = 'pointer';
            
            // Ajouter un style pour indiquer qu'il est cliquable  
            element.addEventListener('mouseover', () => {
                element.style.transform = 'scale(1.1)';
                element.style.boxShadow = '0 2px 8px rgba(0,0,0,0.3)';
            });
            
            element.addEventListener('mouseout', () => {
                element.style.transform = '';
                element.style.boxShadow = '';
            });
            
            // Utiliser UN SEUL gestionnaire de clic pour éviter la double gestion
            // Suppression du onclick = ... précédent
            
            element.addEventListener('click', function clickHandler(event) {
                console.log("[DEBUG-CLICK] Élément cliqué:", element.id);
                // Empêcher la propagation pour éviter des clics multiples
                event.preventDefault();
                event.stopPropagation();
                
                // Traiter le clic après un court délai pour éviter les doubles clics accidentels
                setTimeout(() => {
                    handleElementClick({currentTarget: element});
                }, 10);
            });
            
            // Ajouter un clic visible pour debug
            element.addEventListener('mousedown', () => {
                console.log("[DEBUG] Mouse down on element:", element.id);
                element.style.backgroundColor = "#ffff99";
                setTimeout(() => element.style.backgroundColor = "", 300);
            });
        });

        // --- SUPPRIMER les écouteurs DRAG & DROP --- 
        // (Les blocs forEach pour draggableElements et dropCells avec addEventListener 
        // pour dragstart, dragend, dragover, drop, etc. sont supprimés)

        // Reset et Validate restent similaires, mais reset doit gérer le nouvel état
        function resetCaptchaState() {
            currentScore = 0;
            placedElements = {};
            attemptsLeft = MAX_ATTEMPTS;
            updateAttemptsDisplay();
            updateScore(0);
            updatePlacedElementsData();
            showFeedback('Sélectionnez des éléments durables pour augmenter votre score.');

            clickableElements.forEach(element => {
                // Réinitialiser l'apparence et l'état cliquable
                element.style.opacity = '1';
                element.classList.remove('selected');
            });

            // Vider la grille d'affichage
            cityGrid.innerHTML = '';
            
            validateButton.disabled = false;
            if(attemptsDisplay) attemptsDisplay.style.color = '#666';
            console.log('[initializeEcoCaptcha] CAPTCHA reset (Click Mode).');
        }

        // Le listener du bouton Valider reste le même (il vérifie currentScore et placedCount)
        validateButton.addEventListener('click', () => {
            console.log('Bouton Valider cliqué (Click Mode).');
            
            if (attemptsLeft <= 0) {
                console.warn("Validation attempt blocked: No attempts left.");
                return; 
            }

            const requiredScore = 50; // Score minimum requis
            const requiredElements = 3; // Nombre minimum d'éléments requis
            const placedCount = Object.keys(placedElements).length; 
            
            // Log de débogage pour voir ce qui est actuellement placé
            console.log(`[Validation Debug] Current score: ${currentScore}, Elements placed: ${placedCount}`);
            console.log(`[Validation Debug] Placed elements:`, placedElements);
            console.log(`[Validation Debug] City grid children count: ${cityGrid.children.length}`);
            
            let isValid = (currentScore >= requiredScore && placedCount >= requiredElements);
            let validationData = { score: currentScore, elements: placedElements };
            let failureMessage = '';

            if (!isValid) {
                attemptsLeft--; 
                updateAttemptsDisplay();
                console.log(`[Validation] Attempt failed. Attempts left: ${attemptsLeft}`);

                if (placedCount < requiredElements) {
                    failureMessage = `Veuillez sélectionner au moins ${requiredElements} éléments. (${placedCount}/${requiredElements})`;
                } else { 
                    failureMessage = `Score insuffisant (${currentScore}/${requiredScore}). Ajoutez plus d'éléments durables.`;
                }
                console.warn(`[Validation] Échec: ${failureMessage}`);

                if (attemptsLeft <= 0) {
                    failureMessage = "Nombre maximum de tentatives atteint.";
                    console.error("Max attempts reached. Disabling validate button.");
                    validateButton.disabled = true;
                    if(attemptsDisplay) attemptsDisplay.style.color = 'red';
                }
            }
            
            if (isValid) {
                console.log('[Validation] Succès côté client.');
                showFeedback('Vérification réussie !', 'success');
                if (window.handleEcoCaptchaSuccess) {
                    window.handleEcoCaptchaSuccess(validationData);
                } else {
                    console.error('Callback handleEcoCaptchaSuccess not found!');
                }
            } else {
                console.log('[Validation] Échec côté client.');
                showFeedback(failureMessage, 'error');
                
                // Surligner le city-grid pour indiquer visuellement où déposer les éléments
                cityGrid.style.boxShadow = '0 0 8px rgba(255, 0, 0, 0.5)';
                setTimeout(() => {
                    cityGrid.style.boxShadow = '';
                }, 1500);
            }
        });

        console.log('[initializeEcoCaptcha] FINISHED initialization (Click Mode).');
        updateAttemptsDisplay();
        resetCaptchaState(); // Appel initial pour configurer l'état
    
    } catch (error) {
        console.error('[initializeEcoCaptcha] Error during initialization:', error);
        // Afficher un message d'erreur visible pour l'utilisateur
        const container = document.getElementById('eco-captcha-container');
        if (container) {
            container.innerHTML = '<p style="color: red; text-align: center;">Erreur d\'initialisation du CAPTCHA. Veuillez rafraîchir la page.</p>';
        }
    }
}
// NOTE: Le listener DOMContentLoaded a été supprimé précédemment, c'est correct.


// ================================================
// INITIALISATION AU CHARGEMENT DU DOM - SUPPRIMER CE BLOC
// ================================================

document.addEventListener('DOMContentLoaded', () => {
    console.log('[eco-captcha-handler.js] DOM Chargé (appel automatique désactivé).');
    
    // Ne pas initialiser automatiquement si on est sur la page de login
    // (car le captcha sera chargé et initialisé par le bouton de vérification)
    if (document.getElementById('login-form')) {
        console.log('[eco-captcha-handler.js] Page de login détectée, initialisation automatique désactivée.');
        return;
    }
    
    if (document.getElementById('eco-captcha-container')) {
        console.log('[eco-captcha-handler.js] Conteneur CAPTCHA trouvé, initialisation...');
        initializeEcoCaptcha(); 
    } else {
         console.log('[eco-captcha-handler.js] Conteneur CAPTCHA non trouvé sur cette page.');
     }
});


// Optionnel : Gestion spécifique pour Turbo Drive / Hotwire / etc.
// ... (ce bloc peut rester si nécessaire pour d'autres usages, mais ne doit pas appeler initializeEcoCaptcha)
/*
document.addEventListener('turbo:load', () => {
    console.log('[eco-captcha-handler.js] Turbo Chargé (appel automatique désactivé).');
    // Ne pas appeler initializeEcoCaptcha ici non plus
    // if (document.getElementById('eco-captcha-container')) {
    //     initializeEcoCaptcha();
    // }
});
*/

// ================================================
// LOGIN FORM & CAPTCHA INTEGRATION LOGIC
// ================================================
document.addEventListener('DOMContentLoaded', () => {
    console.log('[Login Integration] DOMContentLoaded.');

    const loginForm = document.getElementById('login-form');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const captchaTriggerButton = document.getElementById('captcha-trigger-button');
    const captchaModal = document.getElementById('captcha-modal');
    const captchaContainer = document.getElementById('eco-captcha-container');
    const closeModalButton = document.querySelector('.close-modal'); // Assumes one close button
    const captchaStatusDiv = document.getElementById('captcha-status');
    const loginSubmitButton = document.getElementById('login-submit-button');
    const loginSpinner = document.getElementById('login-spinner');
    const loginFeedbackDiv = document.getElementById('login-feedback'); // Need to add this div in login.php

    let isCaptchaVerified = false; // Flag to track CAPTCHA status

    if (!loginForm || !captchaTriggerButton || !captchaModal || !captchaContainer || !closeModalButton || !captchaStatusDiv || !loginSubmitButton || !loginSpinner || !emailInput || !passwordInput) {
        console.error('[Login Integration] One or more required elements for login/CAPTCHA integration are missing.');
        // Optionally disable the trigger button if setup fails
        if (captchaTriggerButton) captchaTriggerButton.disabled = true;
        return;
    }

    // --- 1. Trigger CAPTCHA Modal --- 
    captchaTriggerButton.addEventListener('click', async () => {
        console.log('[Login Integration] CAPTCHA trigger button CLICKED!'); // <-- LOG 1
        captchaTriggerButton.disabled = true;
        captchaTriggerButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Chargement...';

        try {
            console.log('[Login Integration] Starting fetch to /WaveNet/controller/CaptchaController.php?action=getEcoCaptchaHTML'); // <-- LOG 2
            
            // Fonction de diagnostic pour détecter les conflits CSS/JS
            function diagnoseCSS(selector) {
                try {
                    const elements = document.querySelectorAll(selector);
                    if (elements.length === 0) {
                        console.warn(`[CSS Diagnostic] Aucun élément trouvé pour "${selector}"`);
                        return;
                    }
                    
                    elements.forEach((el, i) => {
                        const styles = window.getComputedStyle(el);
                        console.log(`[CSS Diagnostic] ${selector} #${i}:`, {
                            display: styles.display,
                            visibility: styles.visibility,
                            opacity: styles.opacity,
                            zIndex: styles.zIndex,
                            position: styles.position,
                            height: styles.height,
                            width: styles.width,
                            element: el
                        });
                        
                        // Vérifier les styles qui peuvent cacher l'élément
                        if (styles.display === 'none' || 
                            styles.visibility === 'hidden' || 
                            styles.opacity === '0' || 
                            parseInt(styles.height) === 0 || 
                            parseInt(styles.width) === 0) {
                            console.error(`[CSS Diagnostic] L'élément ${selector} #${i} est caché!`);
                        }
                    });
                } catch (e) {
                    console.error('[CSS Diagnostic] Erreur:', e);
                }
            }
            
            // Chercher les modules qui pourraient causer des conflits
            function detectConflicts() {
                console.log('[Conflict Detector] Recherche de conflits potentiels...');
                
                // Vérifier les styles globaux qui pourraient affecter notre modal
                const allStylesheets = Array.from(document.styleSheets);
                try {
                    allStylesheets.forEach(sheet => {
                        try {
                            const rules = Array.from(sheet.cssRules || []);
                            const conflictingRules = rules.filter(rule => {
                                const selector = rule.selectorText || '';
                                return (selector.includes('modal') || 
                                       selector.includes('captcha') || 
                                       selector.includes('.element') ||
                                       selector === '*' ||
                                       selector.includes('drag-item'));
                            });
                            
                            if (conflictingRules.length > 0) {
                                console.warn(`[Conflict Detector] Feuille de style potentiellement conflictuelle: ${sheet.href || 'inline'}`);
                                conflictingRules.forEach(rule => {
                                    console.warn(`[Conflict Detector] Règle: ${rule.selectorText}`);
                                });
                            }
                        } catch (e) {
                            // Certaines feuilles de style CORS peuvent lever des erreurs
                            if (sheet.href) {
                                console.log(`[Conflict Detector] Impossible d'analyser: ${sheet.href}`);
                            }
                        }
                    });
                } catch (e) {
                    console.error('[Conflict Detector] Erreur lors de l\'analyse des styles:', e);
                }
                
                // Vérifier les scripts qui pourraient manipuler les modals
                const allScripts = Array.from(document.scripts);
                const potentialConflicts = allScripts.filter(script => 
                    (script.src && (script.src.includes('modal') || 
                                  script.src.includes('captcha') ||
                                  script.src.includes('reward')))
                );
                
                if (potentialConflicts.length > 0) {
                    console.warn('[Conflict Detector] Scripts potentiellement conflictuels:');
                    potentialConflicts.forEach(script => {
                        console.warn(`[Conflict Detector] Script: ${script.src}`);
                    });
                }
            }
            
            const response = await fetch('/WaveNet/controller/CaptchaController.php?action=getEcoCaptchaHTML'); 
            console.log('[Login Integration] Fetch response received. Status:', response.status); // <-- LOG 3
            
            if (!response.ok) {
                 console.error('[Login Integration] Fetch response not OK. Status:', response.status, response.statusText);
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const captchaHTML = await response.text();
            console.log('[Login Integration] CAPTCHA HTML received (length):', captchaHTML.length); // <-- LOG 4 (Log length, not full HTML)
            // console.log('Received HTML snippet:', captchaHTML); // Uncomment temporarily if needed, but can clutter console

            if (captchaHTML.trim().length === 0) {
                 console.warn('[Login Integration] Received empty HTML from server.');
                 throw new Error('Empty CAPTCHA HTML received');
            }

            // Afficher d'abord le modal pour que le DOM soit prêt
            captchaModal.style.display = 'block'; // Show the modal
            console.log('[Login Integration] CAPTCHA Modal display set to block.'); // <-- LOG 8
            
            // Force le rafraîchissement du layout du DOM
            captchaModal.style.opacity = '0.99';
            setTimeout(() => captchaModal.style.opacity = '1', 10);
            
            // Puis injecter le HTML
            captchaContainer.innerHTML = captchaHTML; 
            console.log('[Login Integration] CAPTCHA HTML injected into modal container.'); // <-- LOG 5
            
            // Ajout de styles forcés pour s'assurer que les éléments sont visibles
            document.querySelectorAll('.wavenet-captcha-container *').forEach(el => {
                // Réinitialiser tout style potentiellement hérité
                el.style.visibility = 'visible';
                el.style.opacity = '1';
                
                // S'assurer que les affichages spécifiques sont corrects
                if (el.classList.contains('element')) {
                    el.style.display = 'flex';
                } else if (el.classList.contains('elements-list')) {
                    el.style.display = 'grid';
                } else if (el.id === 'city-grid') {
                    el.style.display = 'flex';
                }
            });
            
            // Initialize the CAPTCHA script *after* HTML is injected and modal is visible
            console.log('[Login Integration] Calling initializeEcoCaptcha()...'); // <-- LOG 6
            setTimeout(() => {
                try {
                    // Laisser le temps au DOM de se mettre à jour
                    console.log('[Login Integration] DOM ready for CAPTCHA initialization: checking element counts');
                    
                    // Forcer un redimensionnement pour que les navigateurs recalculent le layout
                    window.dispatchEvent(new Event('resize'));
                    
                    // Verify key elements before initialization
                    const elemCount = document.querySelectorAll('.element.drag-item').length;
                    console.log(`[Login Integration] Found ${elemCount} .element.drag-item elements`);
                    
                    // Force l'application des styles directement sur les éléments
                    document.querySelectorAll('.element.drag-item').forEach(el => {
                        el.style.display = 'flex';
                        el.style.visibility = 'visible';
                        el.style.opacity = '1';
                        el.style.width = '48px';
                        el.style.height = '48px';
                        el.style.cursor = 'pointer';
                        el.style.zIndex = '1000';
                        
                        // Ajouter une bordure temporaire pour visualiser les éléments
                        el.style.border = '2px solid red';
                        setTimeout(() => {
                            if (el.classList.contains('durable')) {
                                el.style.border = '1px solid #81c784';
                            } else {
                                el.style.border = '1px solid #e57373';
                            }
                        }, 2000);
                    });
                    
                    if (elemCount === 0) {
                        console.error('[Login Integration] ERROR: No drag-item elements found before initialization!');
                        captchaContainer.innerHTML += '<div style="color:red; padding:10px;">Erreur: Éléments CAPTCHA non trouvés. Fermez et réessayez.</div>';
                    }
                    
                    // Appliquer des styles directs au conteneur de la grille
                    const cityGrid = document.getElementById('city-grid');
                    if (cityGrid) {
                        cityGrid.style.display = 'flex';
                        cityGrid.style.flexWrap = 'wrap';
                        cityGrid.style.minHeight = '80px';
                        cityGrid.style.backgroundColor = '#f8f8f8';
                        cityGrid.style.border = '2px dashed red'; // Bordure temporaire pour visualiser
                        cityGrid.style.zIndex = '1000';
                        
                        // Remettre la bordure normale après 2 secondes
                        setTimeout(() => {
                            cityGrid.style.border = '2px dashed #ccc';
                        }, 2000);
                    }
                    
                    initializeEcoCaptcha();
                    console.log('[Login Integration] initializeEcoCaptcha() finished.'); // <-- LOG 7
                    
                    // Verify elements after initialization
                    const clickableElements = document.querySelectorAll('.element.drag-item');
                    console.log(`[Login Integration] After initialization: ${clickableElements.length} clickable elements`);
                    
                    // Force refresh of layout
                    captchaModal.style.opacity = '0.99';
                    setTimeout(() => captchaModal.style.opacity = '1', 50);
                    
                    // Assurer que les éléments sont au premier plan
                    document.querySelector('.wavenet-captcha-modal').style.zIndex = '9999';

                    // Appel des fonctions de diagnostic après injection du contenu
                    setTimeout(() => {
                        console.log('[Diagnostic] Lancement des diagnostics...');
                        diagnoseCSS('#captcha-modal');
                        diagnoseCSS('#eco-captcha-container');
                        diagnoseCSS('.element.drag-item');
                        diagnoseCSS('#city-grid');
                        detectConflicts();
                    }, 1000);

                } catch (error) {
                    console.error('[Login Integration] Error during CAPTCHA initialization:', error);
                    captchaContainer.innerHTML += '<div style="color:red; padding:10px;">Erreur d\'initialisation. Fermez et réessayez.</div>';
                }
            }, 800); // Augmenter à 800ms pour laisser plus de temps au DOM

        } catch (error) {
            console.error('[Login Integration] Failed to load or initialize CAPTCHA:', error); // <-- LOG 9 (Catch block)
            captchaStatusDiv.innerHTML = '<span style="color: red;">Erreur chargement CAPTCHA</span>';
            captchaTriggerButton.disabled = false; 
             captchaTriggerButton.innerHTML = '<i class="fas fa-shield-alt"></i> Vérification de sécurité';
        }
    });

    // --- 2. Close CAPTCHA Modal --- 
    closeModalButton.addEventListener('click', () => {
        captchaModal.style.display = 'none';
        captchaContainer.innerHTML = ''; // Clear content when closing
        // Re-enable trigger button only if CAPTCHA wasn't verified
        if (!isCaptchaVerified) {
            captchaTriggerButton.disabled = false;
             captchaTriggerButton.innerHTML = '<i class="fas fa-shield-alt"></i> Vérification de sécurité';
        }
        console.log('[Login Integration] CAPTCHA Modal closed.');
    });

    // Close modal if clicking outside the content
    window.addEventListener('click', (event) => {
        if (event.target === captchaModal) {
            captchaModal.style.display = 'none';
            captchaContainer.innerHTML = '';
            if (!isCaptchaVerified) {
                captchaTriggerButton.disabled = false;
                 captchaTriggerButton.innerHTML = '<i class="fas fa-shield-alt"></i> Vérification de sécurité';
            }
             console.log('[Login Integration] CAPTCHA Modal closed (click outside).');
        }
    });

    // --- 3. Handle CAPTCHA Validation Success (Callback) ---
    // This function needs to be called by initializeEcoCaptcha when the user *successfully* validates the custom CAPTCHA.
    // Modify initializeEcoCaptcha: instead of captchaForm.submit(), it should call this function.
    window.handleEcoCaptchaSuccess = function(captchaData) { // Make it global or pass reference
        console.log('[Login Integration] Eco CAPTCHA validation successful.', captchaData);
        isCaptchaVerified = true;
        captchaModal.style.display = 'none'; // Close modal
        captchaContainer.innerHTML = ''; // Clear content
        captchaStatusDiv.innerHTML = '<i class="fas fa-check-circle" style="color: green; font-size: 1.2em;"></i> Vérifié';
        captchaTriggerButton.disabled = true; // Keep trigger disabled
        captchaTriggerButton.innerHTML = '<i class="fas fa-shield-alt"></i> Vérification Complétée';
        loginSubmitButton.disabled = false; // **ENABLE LOGIN BUTTON**
        
        // Optional: Store validation data if needed for the main login request
        // loginForm.dataset.captchaToken = captchaData.token; // Example
    };
    
    window.handleEcoCaptchaFailure = function(message) {
        console.warn('[Login Integration] Eco CAPTCHA validation failed:', message);
        // Potentially show feedback within the modal
        // Re-enable trigger? Or rely on modal close button?
         if (!isCaptchaVerified) {
             captchaTriggerButton.disabled = false;
             captchaTriggerButton.innerHTML = '<i class="fas fa-shield-alt"></i> Vérification de sécurité';
         }
    };

    // --- 4. Handle Login Form Submission --- 
    loginForm.addEventListener('submit', async (event) => {
        event.preventDefault(); // PREVENT default form submission
        console.log('[Login Integration] event.preventDefault() called.'); // <-- Log pour confirmer preventDefault
        console.log('[Login Integration] Login form submitted via AJAX.');

        if (!isCaptchaVerified) {
            console.warn('[Login Integration] Login attempt without CAPTCHA verification.');
            if (loginFeedbackDiv) loginFeedbackDiv.innerHTML = '<p style="color: orange;">Veuillez compléter la vérification de sécurité.</p>';
            // Shake the trigger button maybe?
            captchaTriggerButton.style.animation = 'shake 0.5s';
            setTimeout(() => captchaTriggerButton.style.animation = '', 500);
            return; // Stop submission
        }

        loginSubmitButton.disabled = true;
        loginSpinner.style.display = 'inline-block';
        if (loginFeedbackDiv) loginFeedbackDiv.innerHTML = ''; // Clear previous feedback

        const formData = new FormData(loginForm);
        // Optional: Append CAPTCHA data if needed server-side for login
        // if(loginForm.dataset.captchaToken) {
        //    formData.append('captcha_token', loginForm.dataset.captchaToken);
        // }

        try {
            const response = await fetch(loginForm.action, {
                method: 'POST',
                body: formData
            });

            const result = await response.json(); 
            // Log CRUCIAL: voir la réponse exacte reçue du serveur
            console.log('[Login Integration] Server response received:', JSON.stringify(result, null, 2)); 

            if (result.status === 'success' && result.redirect_url) { 
                console.log(`[Login Integration] Login successful (status='success'). Redirecting to ${result.redirect_url}...`);
                window.location.href = result.redirect_url;
            } else {
                // Display error message
                const errorMessage = result.message || 'Erreur de connexion inconnue.';
                console.error('[Login Integration] Login failed:', errorMessage);
                if (loginFeedbackDiv) loginFeedbackDiv.innerHTML = `<p style="color: red;">${errorMessage}</p>`;
                 loginSubmitButton.disabled = false; // Re-enable button on error
                 
                 // Reset CAPTCHA state on login failure? Maybe
                 isCaptchaVerified = false;
                 captchaStatusDiv.innerHTML = '';
                 captchaTriggerButton.disabled = false;
                 captchaTriggerButton.innerHTML = '<i class="fas fa-shield-alt"></i> Vérification de sécurité';
            }

        } catch (error) {
            console.error('[Login Integration] Error during login AJAX request:', error);
            if (loginFeedbackDiv) loginFeedbackDiv.innerHTML = '<p style="color: red;">Une erreur technique est survenue. Veuillez réessayer.</p>';
            loginSubmitButton.disabled = false; // Re-enable button on error
             // Also reset CAPTCHA on network error
             isCaptchaVerified = false;
             captchaStatusDiv.innerHTML = '';
             captchaTriggerButton.disabled = false;
             captchaTriggerButton.innerHTML = '<i class="fas fa-shield-alt"></i> Vérification de sécurité';
        } finally {
            loginSpinner.style.display = 'none'; // Hide spinner
        }
    });

    console.log('[Login Integration] Event listeners attached.');
});

// Make sure initializeEcoCaptcha is defined *before* this DOMContentLoaded block if it's in the same file,
// or ensure eco-captcha-handler.js is loaded before this new logic if separated.
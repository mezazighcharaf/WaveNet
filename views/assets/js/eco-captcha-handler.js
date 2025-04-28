// ================================================
// ECO-CAPTCHA HANDLER pour Grille Écologique avec API Drag & Drop NATIVE
// ================================================

function initializeEcoCaptcha() {
    console.log('[initializeEcoCaptcha - CLICK MODE] STARTING initialization...');
    
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

    // PAS BESOIN DE GÉNÉRER LES CELLULES dynamiquement pour le mode clic
    if (!cityGrid) {
        console.error('[initializeEcoCaptcha] #city-grid (display area) not found!');
        if (captchaContainer) captchaContainer.innerHTML = '<p style="color: red; text-align: center;">Erreur: Zone d\'affichage CAPTCHA (#city-grid) manquante.</p>';
        return;
    }

    // Sélection des éléments cliquables
    const clickableElements = captchaContainer.querySelectorAll('.element.drag-item'); 

    console.log(`[initializeEcoCaptcha] Found ${clickableElements.length} clickable elements (.element.drag-item)`);

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

            // Ajout visuel à la grille
            const iconClone = clickedElement.querySelector('i').cloneNode(true);
            const elementDiv = document.createElement('div');
            elementDiv.className = `element-in-grid ${elementType}`;
            elementDiv.dataset.originId = elementId; // Lier l'élément de grille à l'original
            elementDiv.appendChild(iconClone);
            
            // Ajouter un bouton de suppression sur l'élément dans la grille
            const removeBtn = document.createElement('button');
            removeBtn.innerHTML = '&times;'; // Croix
            removeBtn.className = 'remove-item-grid'; // Nouvelle classe pour style/logique
            removeBtn.title = 'Retirer cet élément';
            removeBtn.onclick = () => handleRemoveFromGrid(elementId);
            elementDiv.appendChild(removeBtn);
            
            // Ajouter l'élément au début du conteneur pour qu'ils s'affichent dans l'ordre
            if (cityGrid.firstChild) {
                cityGrid.insertBefore(elementDiv, cityGrid.firstChild);
            } else {
                cityGrid.appendChild(elementDiv);
            }

            // Marquer l'original comme sélectionné/inactif
            clickedElement.style.opacity = '0.5';
            clickedElement.classList.add('selected'); // Ajouter classe pour style ?
            
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
        element.addEventListener('click', handleElementClick);
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
        }
    });

    console.log('[initializeEcoCaptcha] FINISHED initialization (Click Mode).');
    updateAttemptsDisplay();
    resetCaptchaState(); // Appel initial pour configurer l'état

}
// NOTE: Le listener DOMContentLoaded a été supprimé précédemment, c'est correct.


// ================================================
// INITIALISATION AU CHARGEMENT DU DOM - SUPPRIMER CE BLOC
// ================================================
/*
document.addEventListener('DOMContentLoaded', () => {
    console.log('[eco-captcha-handler.js] DOM Chargé (appel automatique désactivé).');
    // Ne pas appeler initializeEcoCaptcha ici
    // L'appel doit être fait manuellement après l'injection du contenu
    // if (document.getElementById('eco-captcha-container')) {
    //     console.log('[eco-captcha-handler.js] Conteneur CAPTCHA trouvé, initialisation...');
    //     initializeEcoCaptcha(); 
    // } else {
    //     console.log('[eco-captcha-handler.js] Conteneur CAPTCHA non trouvé sur cette page.');
    // }
});
*/

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

            captchaContainer.innerHTML = captchaHTML; 
            console.log('[Login Integration] CAPTCHA HTML injected into modal container.'); // <-- LOG 5
            
            // Initialize the CAPTCHA script *after* HTML is injected
            console.log('[Login Integration] Calling initializeEcoCaptcha()...'); // <-- LOG 6
            initializeEcoCaptcha(); 
            console.log('[Login Integration] initializeEcoCaptcha() finished.'); // <-- LOG 7
            
            captchaModal.style.display = 'block'; // Show the modal
            console.log('[Login Integration] CAPTCHA Modal display set to block.'); // <-- LOG 8

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
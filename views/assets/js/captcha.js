/**
 * CAPTCHA avec drag and drop - WaveNet
 * Permet de déplacer des éléments urbains (durables ou non) dans une zone cible
 */

document.addEventListener('DOMContentLoaded', function() {
    // Éléments du DOM
    const dragItems = document.querySelectorAll('.drag-item');
    const dropZone = document.getElementById('captcha-drop-zone');
    const scoreDisplay = document.getElementById('captcha-score');
    const scoreInput = document.getElementById('captcha-score-input');
    const elementsInput = document.getElementById('captcha-elements');
    const captchaContainer = document.getElementById('captcha-container');
    const loginButton = document.querySelector('button[type="submit"]'); // Bouton de connexion
    
    // Variables pour le score
    let score = 0;
    const droppedElements = [];
    
    // Vérification de l'existence des éléments
    if (!dropZone) {
        console.error('Zone de drop non trouvée');
        return;
    }
    
    if (dragItems.length === 0) {
        console.error('Aucun élément draggable trouvé');
        return;
    }
    
    // Initialisation du token CAPTCHA
    const captchaToken = generateToken();
    const tokenInput = document.getElementById('captcha-token');
    if (tokenInput) {
        tokenInput.value = captchaToken;
    }
    
    // Initialisation des événements de drag & drop
    initDragAndDrop();
    
    /**
     * Initialise les événements de drag & drop
     */
    function initDragAndDrop() {
        // Pour chaque élément draggable
        dragItems.forEach(item => {
            // S'assurer que l'attribut draggable est bien défini
            item.setAttribute('draggable', 'true');
            
            // Ajouter une classe pour indiquer qu'il est draggable visuellement
            item.classList.add('draggable');
            
            // Supprimer les écouteurs existants pour éviter les doublons
            item.removeEventListener('dragstart', handleDragStart);
            item.removeEventListener('dragend', handleDragEnd);
            
            // Événement au début du drag
            item.addEventListener('dragstart', handleDragStart);
            
            // Événement à la fin du drag
            item.addEventListener('dragend', handleDragEnd);
            
            // Pour le tactile
            item.addEventListener('touchstart', handleTouchStart, { passive: false });
            item.addEventListener('touchmove', handleTouchMove, { passive: false });
            item.addEventListener('touchend', handleTouchEnd, { passive: false });
        });
        
        // Événements pour la zone de drop
        dropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            dropZone.classList.add('drag-over');
        });
        
        dropZone.addEventListener('dragleave', function() {
            dropZone.classList.remove('drag-over');
        });
        
        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            dropZone.classList.remove('drag-over');
            
            const itemId = e.dataTransfer.getData('text/plain');
            
            if (!itemId) {
                console.error('Aucun ID d\'élément dans le transfert');
                return;
            }
            
            const draggedItem = document.getElementById(itemId);
            
            if (!draggedItem) {
                console.error('Élément non trouvé:', itemId);
                return;
            }
            
            // Vérifier que l'élément n'est pas déjà dans la zone
            if (!isElementInDropZone(draggedItem)) {
                // Créer un clone de l'élément et l'ajouter à la drop zone
                const clone = draggedItem.cloneNode(true);
                clone.id = `${itemId}-dropped`;
                clone.classList.add('dropped');
                clone.setAttribute('draggable', false);
                
                // Ajouter un bouton de suppression
                const removeBtn = document.createElement('span');
                removeBtn.className = 'remove-item';
                removeBtn.innerHTML = '&times;';
                removeBtn.addEventListener('click', function() {
                    removeDroppedElement(clone);
                });
                clone.appendChild(removeBtn);
                
                // Ajouter le clone à la zone
                dropZone.appendChild(clone);
                
                // Mettre à jour le score et les éléments
                updateScore(draggedItem);
            }
        });
    }
    
    /**
     * Gestion des événements tactiles
     */
    function handleTouchStart(e) {
        if (e.touches.length !== 1) return;
        const touch = e.touches[0];
        const item = e.currentTarget;
        
        item.classList.add('dragging');
        item.touchOffsetX = touch.clientX - item.getBoundingClientRect().left;
        item.touchOffsetY = touch.clientY - item.getBoundingClientRect().top;
    }
    
    function handleTouchMove(e) {
        if (e.touches.length !== 1) return;
        e.preventDefault();
        
        const touch = e.touches[0];
        const item = e.currentTarget;
        
        // Positionner l'élément sous le doigt
        item.style.position = 'fixed';
        item.style.left = (touch.clientX - item.touchOffsetX) + 'px';
        item.style.top = (touch.clientY - item.touchOffsetY) + 'px';
        item.style.zIndex = 1000;
    }
    
    function handleTouchEnd(e) {
        const item = e.currentTarget;
        item.classList.remove('dragging');
        item.style.position = '';
        item.style.left = '';
        item.style.top = '';
        item.style.zIndex = '';
        
        // Vérifier si l'élément est au-dessus de la zone de drop
        const touch = e.changedTouches[0];
        const dropRect = dropZone.getBoundingClientRect();
        
        if (
            touch.clientX >= dropRect.left &&
            touch.clientX <= dropRect.right &&
            touch.clientY >= dropRect.top &&
            touch.clientY <= dropRect.bottom
        ) {
            // Simuler un drop
            if (!isElementInDropZone(item)) {
                const clone = item.cloneNode(true);
                clone.id = `${item.id}-dropped`;
                clone.classList.add('dropped');
                
                const removeBtn = document.createElement('span');
                removeBtn.className = 'remove-item';
                removeBtn.innerHTML = '&times;';
                removeBtn.addEventListener('click', function() {
                    removeDroppedElement(clone);
                });
                clone.appendChild(removeBtn);
                
                dropZone.appendChild(clone);
                updateScore(item);
            }
        }
    }
    
    /**
     * Gère le début du drag
     */
    function handleDragStart(e) {
        this.classList.add('dragging');
        e.dataTransfer.setData('text/plain', this.id);
        e.dataTransfer.effectAllowed = 'move';
    }
    
    /**
     * Gère la fin du drag
     */
    function handleDragEnd() {
        this.classList.remove('dragging');
    }
    
    /**
     * Vérifie si un élément est déjà dans la zone de drop
     */
    function isElementInDropZone(element) {
        return dropZone.querySelector(`#${element.id}-dropped`) !== null;
    }
    
    /**
     * Supprime un élément déposé
     */
    function removeDroppedElement(element) {
        const itemType = element.getAttribute('data-type');
        dropZone.removeChild(element);
        
        // Mettre à jour le score
        if (itemType === 'durable') {
            score -= 20;
        } else {
            score += 15;
        }
        
        // Mettre à jour l'affichage
        updateScoreDisplay();
        
        // Mettre à jour la liste des éléments
        const elementId = element.id.replace('-dropped', '');
        const index = droppedElements.findIndex(el => el.id === elementId);
        if (index !== -1) {
            droppedElements.splice(index, 1);
            elementsInput.value = JSON.stringify(droppedElements);
        }
        
        // Vérifier si le bouton de connexion doit être activé/désactivé
        updateLoginButtonState();
    }
    
    /**
     * Met à jour le score et la liste des éléments
     */
    function updateScore(element) {
        const itemType = element.getAttribute('data-type');
        const elementInfo = {
            id: element.id,
            type: itemType,
            icon: element.getAttribute('data-icon')
        };
        
        droppedElements.push(elementInfo);
        elementsInput.value = JSON.stringify(droppedElements);
        
        // Ajuster le score en fonction du type d'élément
        if (itemType === 'durable') {
            score += 20;
        } else {
            score -= 15;
        }
        
        // Mettre à jour l'affichage du score
        updateScoreDisplay();
        
        // Vérifier si le bouton de connexion doit être activé/désactivé
        updateLoginButtonState();
    }
    
    /**
     * Met à jour l'affichage du score
     */
    function updateScoreDisplay() {
        // Limiter le score entre 0 et 100
        score = Math.max(0, Math.min(100, score));
        scoreDisplay.textContent = score;
        
        // Définir la couleur en fonction du score
        if (score >= 70) {
            scoreDisplay.style.color = '#28a745';
        } else if (score >= 40) {
            scoreDisplay.style.color = '#ffc107'; 
        } else {
            scoreDisplay.style.color = '#dc3545';
        }
        
        // Mettre à jour l'input caché
        scoreInput.value = score;
    }
    
    /**
     * Met à jour l'état du bouton de connexion
     */
    function updateLoginButtonState() {
        if (loginButton) {
            // Activer le bouton de connexion si le score est suffisant
            if (score >= 50 && countDurableElements() >= 3) {
                loginButton.disabled = false;
                loginButton.classList.remove('disabled');
            } else {
                loginButton.disabled = true;
                loginButton.classList.add('disabled');
            }
        }
    }
    
    /**
     * Compte le nombre d'éléments durables déposés
     */
    function countDurableElements() {
        return droppedElements.filter(el => el.type === 'durable').length;
    }
    
    /**
     * Génère un token aléatoire
     */
    function generateToken() {
        const array = new Uint8Array(16);
        window.crypto.getRandomValues(array);
        return Array.from(array, byte => byte.toString(16).padStart(2, '0')).join('');
    }
    
    /**
     * Réinitialise le CAPTCHA
     */
    document.getElementById('captcha-reset').addEventListener('click', function() {
        // Vider la zone de drop
        while (dropZone.querySelector('.dropped')) {
            dropZone.removeChild(dropZone.querySelector('.dropped'));
        }
        
        // Réinitialiser le score et les éléments
        score = 0;
        droppedElements.length = 0;
        elementsInput.value = '[]';
        
        // Mettre à jour l'affichage
        updateScoreDisplay();
        
        // Désactiver le bouton de connexion
        if (loginButton) {
            loginButton.disabled = true;
            loginButton.classList.add('disabled');
        }
    });
}); 
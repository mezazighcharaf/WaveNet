let model;
let stream;
let currentEtapeId;
let isModelLoading = false;

// Initialiser le modèle TensorFlow.js uniquement quand nécessaire
async function initModel() {
    if (model || isModelLoading) return;
    
    isModelLoading = true;
    try {
        // Charger TensorFlow.js dynamiquement
        if (!window.tf) {
            await new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@3.11.0/dist/tf.min.js';
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            });
        }

        // Charger le modèle MobileNet pré-entraîné
        model = await tf.loadLayersModel('https://storage.googleapis.com/tfjs-models/tfjs/mobilenet_v1_0.25_224/model.json');
        console.log('Modèle TensorFlow.js chargé avec succès');
    } catch (error) {
        console.error('Erreur lors du chargement du modèle:', error);
    } finally {
        isModelLoading = false;
    }
}

// Initialiser la modale
document.addEventListener('DOMContentLoaded', function() {
    // Créer la modale si elle n'existe pas
    if (!document.getElementById('imageCaptureModal')) {
        const modalHTML = `
            <div id="imageCaptureModal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2>Valider l'étape</h2>
                    <p>Prenez une photo ou uploadez une image pour prouver que vous avez accompli cette étape.</p>
                    
                    <div class="capture-options">
                        <button id="useCamera" class="btn btn-primary">Utiliser la caméra</button>
                        <button id="uploadImage" class="btn btn-secondary">Uploader une image</button>
                    </div>
                    
                    <div id="cameraContainer" style="display: none;">
                        <video id="cameraPreview" autoplay playsinline></video>
                        <button id="capturePhoto" class="btn btn-success">Prendre la photo</button>
                    </div>
                    
                    <div id="previewContainer" style="display: none;">
                        <canvas id="imagePreview"></canvas>
                        <div class="preview-actions">
                            <button id="retakePhoto" class="btn btn-secondary">Reprendre</button>
                            <button id="submitPhoto" class="btn btn-primary">Valider</button>
                        </div>
                    </div>
                    
                    <input type="file" id="imageUpload" accept="image/*" style="display: none;">
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }

    const modal = document.getElementById('imageCaptureModal');
    const btnAccomplir = document.getElementById('btnAccomplir');
    const closeBtn = document.querySelector('.close');
    const useCameraBtn = document.getElementById('useCamera');
    const uploadImageBtn = document.getElementById('uploadImage');
    const cameraContainer = document.getElementById('cameraContainer');
    const previewContainer = document.getElementById('previewContainer');
    const cameraPreview = document.getElementById('cameraPreview');
    const capturePhotoBtn = document.getElementById('capturePhoto');
    const retakePhotoBtn = document.getElementById('retakePhoto');
    const submitPhotoBtn = document.getElementById('submitPhoto');
    const imageUpload = document.getElementById('imageUpload');
    const imagePreview = document.getElementById('imagePreview');

    // Modifier le comportement du bouton "Étape accomplie"
    btnAccomplir.addEventListener('click', async () => {
        // Récupérer l'ID de l'étape actuelle
        currentEtapeId = etapeActuelle;
        modal.style.display = 'block';
        
        // Charger le modèle en arrière-plan
        initModel().catch(console.error);
    });

    // Fermer la modale
    closeBtn.addEventListener('click', () => {
        modal.style.display = 'none';
        stopCamera();
    });

    // Utiliser la caméra
    useCameraBtn.addEventListener('click', async () => {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: true });
            cameraPreview.srcObject = stream;
            cameraContainer.style.display = 'block';
            previewContainer.style.display = 'none';
        } catch (error) {
            console.error('Erreur lors de l\'accès à la caméra:', error);
            alert('Impossible d\'accéder à la caméra. Veuillez vérifier les permissions.');
        }
    });

    // Uploader une image
    uploadImageBtn.addEventListener('click', () => {
        imageUpload.click();
    });

    // Gérer l'upload d'image
    imageUpload.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (event) => {
                const img = new Image();
                img.onload = () => {
                    const canvas = imagePreview;
                    canvas.width = img.width;
                    canvas.height = img.height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0);
                    previewContainer.style.display = 'block';
                    cameraContainer.style.display = 'none';
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    // Prendre une photo
    capturePhotoBtn.addEventListener('click', () => {
        const canvas = imagePreview;
        canvas.width = cameraPreview.videoWidth;
        canvas.height = cameraPreview.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(cameraPreview, 0, 0);
        previewContainer.style.display = 'block';
        cameraContainer.style.display = 'none';
        stopCamera();
    });

    // Reprendre la photo
    retakePhotoBtn.addEventListener('click', () => {
        previewContainer.style.display = 'none';
        cameraContainer.style.display = 'block';
        startCamera();
    });

    // Valider la photo
    submitPhotoBtn.addEventListener('click', async () => {
        try {
            // Attendre que le modèle soit chargé si nécessaire
            if (!model && !isModelLoading) {
                await initModel();
            }

            // Convertir l'image en tensor
            const imageTensor = tf.browser.fromPixels(imagePreview)
                .resizeBilinear([224, 224])
                .expandDims()
                .toFloat()
                .div(255.0);

            // Faire la prédiction
            const predictions = await model.predict(imageTensor).data();
            
            // Analyser les résultats
            const isValid = analyzePredictions(predictions);
            
            if (isValid) {
                // Envoyer l'image au serveur
                const imageData = imagePreview.toDataURL('image/jpeg');
                const result = await sendImageToServer(imageData);
                
                if (result.success) {
                    // Fermer la modale
                    modal.style.display = 'none';
                    
                    // Déplacer le stickman
                    deplacerStickman(etapeActuelle + 1);
                    
                    // Afficher les points gagnés
                    if (result.points > 0) {
                        afficherNotificationPoints(result.points);
                    }
                } else {
                    alert(result.message || 'Erreur lors de la validation de l\'étape');
                }
            } else {
                alert('L\'image ne semble pas correspondre à l\'étape. Veuillez réessayer.');
            }
        } catch (error) {
            console.error('Erreur lors de la validation:', error);
            alert('Une erreur est survenue lors de la validation de l\'image.');
        }
    });
});

// Analyser les prédictions du modèle
function analyzePredictions(predictions) {
    // Pour l'instant, on accepte toutes les images
    // À adapter selon vos besoins spécifiques
    return true;
}

// Envoyer l'image au serveur
async function sendImageToServer(imageData) {
    try {
        const response = await fetch('validate_etape.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                image: imageData,
                etape_id: currentEtapeId
            })
        });
        
        const result = await response.json();
        return result;
    } catch (error) {
        console.error('Erreur lors de l\'envoi de l\'image:', error);
        throw error;
    }
}

// Gérer la caméra
function stopCamera() {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
    }
}

function startCamera() {
    if (stream) {
        cameraPreview.srcObject = stream;
    }
} 
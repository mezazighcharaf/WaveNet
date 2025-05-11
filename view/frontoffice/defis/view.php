<?php
session_start();
require_once __DIR__ . '/../../../model/Database.php';
require_once __DIR__ . '/../../../controller/EtapeController.php';

// Établir la connexion à la base de données
$database = new Database();
$db = $database->getConnection();
?>

<div class="etapes-section">
    <h2 class="section-title">Étapes du défi</h2>
    
    <?php
    // Get etapes for this defi
    $etapeController = new EtapeController();
    $etapes = $etapeController->getEtapesByDefi($defi['Id_Defi']);
    
    if (empty($etapes)): 
    ?>
        <div class="no-etapes">
            <p>Aucune étape n'est disponible pour ce défi.</p>
        </div>
    <?php else: ?>
        <!-- Animation Stickman Container -->
        <div class="stickman-animation-container">
            <!-- Stickman Animation Frame -->
            <div class="stickman-frame">
                <svg width="900" height="300" viewBox="0 0 900 300">
                    <!-- Segments de chemin qui seront colorés individuellement -->
                    <line id="path-segment-1" x1="120" y1="200" x2="360" y2="200" stroke="black" stroke-width="3"/>
                    <line id="path-segment-2" x1="360" y1="200" x2="600" y2="200" stroke="black" stroke-width="3"/>
                    <line id="path-segment-3" x1="600" y1="200" x2="740" y2="200" stroke="black" stroke-width="3"/>
                    
                    <!-- Points sur la ligne -->
                    <circle cx="120" cy="200" r="4" fill="black"/>
                    <circle cx="360" cy="200" r="4" fill="black"/>
                    <circle cx="600" cy="200" r="4" fill="black"/>
                    <circle cx="740" cy="200" r="4" fill="black"/> <!-- Point à l'extrémité de la ligne -->
                    
                    <!-- Drapeau exactement au bout de la ligne, même taille que le stickman -->
                    <g transform="translate(740, 100)">
                        <!-- Poteau du drapeau plus grand -->
                        <line x1="0" y1="0" x2="0" y2="100" stroke="black" stroke-width="2"/>
                        
                        <!-- Triangle du drapeau plus grand -->
                        <polygon points="0,0 30,15 0,30" fill="black"/>
                    </g>
                    
                    <!-- Stickman - position fixe à gauche avec ID pour l'animation -->
                    <g id="stickman" transform="translate(60, 200)">
                        <!-- Tête -->
                        <circle cx="0" cy="-100" r="20" stroke="black" stroke-width="2" fill="white"/>
                        
                        <!-- Casquette rose -->
                        <path d="M-23,-115 C-20,-125 20,-125 23,-115 L23,-110 L-23,-110 Z" fill="#FF69B4" stroke="black" stroke-width="1.5"/>
                        
                        <!-- Visière de la casquette (vers l'avant) -->
                        <path d="M-20,-112 L-35,-112 L-25,-105 L-20,-105 Z" fill="#FF69B4" stroke="black" stroke-width="1.5"/>
                        
                        <!-- Yeux -->
                        <circle cx="-7" cy="-105" r="2" fill="black"/>
                        <circle cx="7" cy="-105" r="2" fill="black"/>
                        
                        <!-- Sourire -->
                        <path d="M-10,-90 Q0,-85 10,-90" stroke="black" stroke-width="2" fill="none"/>
                        
                        <!-- Corps - exactement 60px de long -->
                        <line x1="0" y1="-80" x2="0" y2="-20" stroke="black" stroke-width="2"/>
                        
                        <!-- Bras -->
                        <line id="arm-left" x1="-20" y1="-60" x2="0" y2="-60" stroke="black" stroke-width="2"/>
                        <line id="arm-right" x1="0" y1="-60" x2="20" y2="-60" stroke="black" stroke-width="2"/>
                        
                        <!-- Jambes - partant du même point, formant un V exact -->
                        <line id="leg-left" x1="0" y1="-20" x2="-20" y2="0" stroke="black" stroke-width="2"/>
                        <line id="leg-right" x1="0" y1="-20" x2="20" y2="0" stroke="black" stroke-width="2"/>
                    </g>
                </svg>
                    </div>
            
            <div class="stickman-controls">
                <button id="btnAvancer" class="btn btn-primary">Avancer</button>
                <button id="btnRetour" class="btn btn-secondary" disabled>Retour</button>
                            </div>
            
            <!-- Conteneur pour les confettis sur tout l'écran -->
            <div id="confetti-container"></div>
            
            <!-- Message de succès -->
            <div id="message-succes">Défi réussi avec succès !</div>
                        </div>
                    <?php endif; ?>
                </div>

<!-- CSS Styles pour Stickman et Confettis -->
<style>
    .stickman-animation-container {
        margin: 0 auto;
        width: 100%;
        max-width: 920px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    
    .stickman-frame {
        position: relative;
        width: 900px;
        height: 300px;
        border: 1px solid #ccc;
        margin-bottom: 20px;
        overflow: hidden;
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    .stickman-controls {
        display: flex;
        gap: 20px;
        margin-top: 10px;
        margin-bottom: 30px;
    }
    
    /* Confettis */
    #confetti-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        pointer-events: none;
        z-index: 9999;
    }
    
    .confetti {
        position: fixed;
        width: 10px;
        height: 10px;
        background-color: #f00;
        opacity: 0.8;
        z-index: 9999;
        animation: fall linear forwards;
    }
    
    @keyframes fall {
        0% { 
            transform: translateY(-50px) rotate(0deg); 
            opacity: 1;
        }
        100% { 
            transform: translateY(100vh) rotate(360deg); 
            opacity: 0;
        }
    }

    /* Message de succès */
    #message-succes {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: rgba(255, 255, 255, 0.9);
        padding: 20px 40px;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
        font-size: 24px;
        font-weight: bold;
        color: #4CAF50;
        text-align: center;
        opacity: 0;
        transition: opacity 0.5s;
        pointer-events: none;
        z-index: 10000;
    }
</style>

<!-- Script pour l'animation du stickman -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ID du défi passé directement du PHP
        const defiId = <?php echo $defi['Id_Defi']; ?>;
        
        // Vérifier si l'utilisateur est connecté
        const estConnecte = <?php echo (isset($_SESSION['user_id']) && $_SESSION['user_id'] !== 'demo_user') ? 'true' : 'false'; ?>;
        console.log("Utilisateur connecté:", estConnecte);
        
        // Vérifier si l'utilisateur participe au défi
        const estInscritAuDefi = <?php 
            if (isset($_SESSION['user_id']) && $_SESSION['user_id'] !== 'demo_user') {
                $userId = $_SESSION['user_id'];
                $query = "SELECT Defi_En_Cours FROM utilisateur WHERE Id_Utilisateur = ? AND Defi_En_Cours = ?";
                $stmt = $db->prepare($query);
                $stmt->bindParam(1, $userId);
                $stmt->bindParam(2, $defi['Id_Defi']);
                $stmt->execute();
                echo ($stmt->rowCount() > 0) ? 'true' : 'false';
            } else {
                echo 'false';
            }
        ?>;
        console.log("Inscrit au défi:", estInscritAuDefi);
        
        // Étape actuelle récupérée du serveur (si l'utilisateur participe déjà au défi)
        let etapeActuelle = <?php 
            // Vérifier si l'utilisateur participe au défi et récupérer son étape
            if (isset($_SESSION['user_id']) && $_SESSION['user_id'] !== 'demo_user') {
                $userId = $_SESSION['user_id'];
                $query = "SELECT Etape_En_Cours FROM utilisateur WHERE Id_Utilisateur = ? AND Defi_En_Cours = ?";
                $stmt = $db->prepare($query);
                $stmt->bindParam(1, $userId);
                $stmt->bindParam(2, $defi['Id_Defi']);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
                    echo isset($userInfo['Etape_En_Cours']) ? (int)$userInfo['Etape_En_Cours'] : 0;
                } else {
                    echo 0; // L'utilisateur ne participe pas encore au défi
                }
            } else {
                echo 0; // Utilisateur non connecté ou utilisateur démo
            }
        ?>;
        console.log("Étape actuelle chargée:", etapeActuelle);
        
        const stickman = document.getElementById('stickman');
        const legLeft = document.getElementById('leg-left');
        const legRight = document.getElementById('leg-right');
        const armLeft = document.getElementById('arm-left');
        const armRight = document.getElementById('arm-right');
        const btnAvancer = document.getElementById('btnAvancer');
        const btnRetour = document.getElementById('btnRetour');
        
        // Gérer l'interface en fonction de l'état de l'utilisateur
        if (!estConnecte) {
            // Cas 1: L'utilisateur n'est pas connecté
            btnAvancer.disabled = true;
            btnRetour.disabled = true;
            
            // Ajouter un message informatif
            const messageNonConnecte = document.createElement('div');
            messageNonConnecte.classList.add('alert', 'alert-warning', 'text-center', 'mt-3');
            messageNonConnecte.style.width = '100%';
            messageNonConnecte.style.maxWidth = '900px';
            messageNonConnecte.style.margin = '0 auto 20px auto';
            messageNonConnecte.innerHTML = '<strong>Connectez-vous</strong> pour participer à ce défi et sauvegarder votre progression.';
            const container = document.querySelector('.stickman-controls');
            container.parentNode.insertBefore(messageNonConnecte, container.nextSibling);
            
            // Ajouter des tooltips sur les boutons
            btnAvancer.title = "Connectez-vous pour avancer";
            btnRetour.title = "Connectez-vous pour revenir";
        } else if (!estInscritAuDefi) {
            // Cas 2: L'utilisateur est connecté mais n'est pas inscrit au défi
            btnAvancer.disabled = true;
            btnRetour.disabled = true;
            
            // Ajouter un message informatif
            const messageNonInscrit = document.createElement('div');
            messageNonInscrit.classList.add('alert', 'alert-info', 'text-center', 'mt-3');
            messageNonInscrit.style.width = '100%';
            messageNonInscrit.style.maxWidth = '900px';
            messageNonInscrit.style.margin = '0 auto 20px auto';
            messageNonInscrit.innerHTML = 'Vous devez <strong>participer à ce défi</strong> pour pouvoir progresser. <a href="../participer_defi.php?id=<?php echo $defi["Id_Defi"]; ?>" class="btn btn-sm btn-primary ml-3">Participer maintenant</a>';
            const container = document.querySelector('.stickman-controls');
            container.parentNode.insertBefore(messageNonInscrit, container.nextSibling);
            
            // Ajouter des tooltips sur les boutons
            btnAvancer.title = "Inscrivez-vous au défi pour avancer";
            btnRetour.title = "Inscrivez-vous au défi pour revenir";
        }
        
        // Chemins à colorer
        const pathSegments = [
            document.getElementById('path-segment-1'),
            document.getElementById('path-segment-2'),
            document.getElementById('path-segment-3')
        ];
        
        // Positions des étapes
        const etapes = [
            60,    // Position initiale
            120,   // Premier point
            360,   // Deuxième point
            600,   // Troisième point
            840    // Position finale après le drapeau
        ];
        
        let enMouvement = false;
        let successCelebrated = false;
        let intervalMarche = null;
        let intervalCelebration = null;
        
        // Fonction pour sauvegarder la progression dans la base de données
        function sauvegarderProgression(etape) {
            // Vérifier si l'utilisateur est connecté et inscrit au défi
            if (!estConnecte || !estInscritAuDefi) {
                console.log("Utilisateur non connecté ou non inscrit, la progression ne sera pas sauvegardée");
                return;
            }
            
            console.log("🔄 Sauvegarde de l'étape:", etape, "pour le défi ID:", defiId);
            
            // Utiliser un chemin relatif fiable
            const saveUrl = '../../sauvegarder_etape.php';
            console.log("📡 URL de sauvegarde:", saveUrl);
            
            // Construire les données POST
            const postData = 'etape=' + etape + '&defi_id=' + defiId;
            console.log("📦 Données POST:", postData);
            
            // Utiliser fetch pour envoyer l'étape au serveur
            fetch(saveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: postData
            })
            .then(response => {
                console.log("⬅️ Statut de la réponse:", response.status);
                if (!response.ok) {
                    throw new Error('Erreur réseau: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log("✅ Progression sauvegardée - Réponse complète:", data);
                
                // Afficher notification de points gagnés si applicable
                if (data.success && data.points > 0) {
                    console.log("🌟 Points gagnés:", data.points);
                    afficherNotificationPoints(data.points);
                }
            })
            .catch(error => {
                console.error("❌ Erreur lors de la sauvegarde:", error);
            });
        }
        
        // Fonction pour afficher une notification de points gagnés
        function afficherNotificationPoints(points) {
            const notification = document.createElement('div');
            notification.className = 'points-notification';
            notification.innerHTML = `<i class="fas fa-leaf"></i> +${points} points!`;
            notification.style.position = 'fixed';
            notification.style.top = '20%';
            notification.style.left = '50%';
            notification.style.transform = 'translate(-50%, -50%)';
            notification.style.backgroundColor = 'rgba(76, 175, 80, 0.9)';
            notification.style.color = 'white';
            notification.style.padding = '15px 25px';
            notification.style.borderRadius = '10px';
            notification.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.2)';
            notification.style.zIndex = '10000';
            notification.style.fontSize = '20px';
            notification.style.fontWeight = 'bold';
            notification.style.opacity = '0';
            notification.style.transition = 'opacity 0.5s, transform 0.5s';
            
            document.body.appendChild(notification);
            
            // Animation d'apparition
            setTimeout(() => {
                notification.style.opacity = '1';
                notification.style.transform = 'translate(-50%, -50%) scale(1.1)';
            }, 10);
            
            // Animation de disparition
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translate(-50%, -50%) scale(0.9)';
                
                // Supprimer l'élément après la fin de l'animation
                setTimeout(() => {
                    notification.remove();
                }, 500);
            }, 3000);
        }
        
        // Fonction pour colorer le segment de chemin parcouru
        function colorerChemin(index) {
            if (index < 0 || index >= pathSegments.length) return;
            pathSegments[index].setAttribute('stroke', '#4CAF50'); // Vert
            pathSegments[index].setAttribute('stroke-width', '4'); // Un peu plus épais pour l'effet
        }
        
        // Fonction pour réinitialiser les couleurs de tous les segments de chemin
        function reinitialiserChemins() {
            pathSegments.forEach(segment => {
                segment.setAttribute('stroke', 'black');
                segment.setAttribute('stroke-width', '3');
            });
        }
        
        // Fonction pour animer le stickman qui sautille avec les bras en l'air
        function animerCelebration() {
            // Arrêter toute animation en cours
            if (intervalCelebration) {
                clearInterval(intervalCelebration);
            }
            
            // Lever les bras en l'air (position fixe)
            armLeft.setAttribute('x1', '0');
            armLeft.setAttribute('y1', '-60');
            armLeft.setAttribute('x2', '-15');
            armLeft.setAttribute('y2', '-85');
            
            armRight.setAttribute('x1', '0');
            armRight.setAttribute('y1', '-60');
            armRight.setAttribute('x2', '15');
            armRight.setAttribute('y2', '-85');
            
            // Animation de sautillement
            let hauteur = 0;
            let montant = true;
            let cycles = 0;
            
            intervalCelebration = setInterval(() => {
                if (montant) {
                    hauteur += 2;
                    if (hauteur >= 15) {
                        montant = false;
                    }
                } else {
                    hauteur -= 2;
                    if (hauteur <= 0) {
                        montant = true;
                        cycles++;
                    }
                }
                
                // Déplacer tout le stickman vers le haut/bas
                stickman.setAttribute('transform', `translate(840, ${200 - hauteur})`);
                
                // Bouger les jambes pour l'effet de saut
                if (hauteur > 7) {
                    // Jambes plus écartées en montant
                    legLeft.setAttribute('x2', '-25');
                    legRight.setAttribute('x2', '25');
                } else {
                    // Jambes moins écartées en descendant
                    legLeft.setAttribute('x2', '-15');
                    legRight.setAttribute('x2', '15');
                }
                
                // Arrêter l'animation après un certain nombre de cycles
                if (cycles > 100) { // Animation en continu, mais on peut limiter si besoin
                    clearInterval(intervalCelebration);
                    
                    // Remettre les jambes en position normale
                    legLeft.setAttribute('x2', '-20');
                    legRight.setAttribute('x2', '20');
                }
            }, 50); // Animation plus rapide pour un sautillement naturel
        }
        
        // Fonction pour déplacer le stickman avec animation de marche
        function deplacerStickman(vers) {
            if (enMouvement) return;
            enMouvement = true;
            
            const depart = etapes[etapeActuelle];
            const arrivee = etapes[vers];
            const distance = arrivee - depart;
            const duree = Math.abs(distance) / 100; // Vitesse constante
            const depart_time = Date.now();
            
            // Réinitialiser les bras à leur position normale pendant le déplacement
            armLeft.setAttribute('x1', '-20');
            armLeft.setAttribute('y1', '-60');
            armLeft.setAttribute('x2', '0');
            armLeft.setAttribute('y2', '-60');
            
            armRight.setAttribute('x1', '0');
            armRight.setAttribute('y1', '-60');
            armRight.setAttribute('x2', '20');
            armRight.setAttribute('y2', '-60');
            
            // Mouvement de marche animé pendant le déplacement
            let pasGauche = true;
            let cycleMarche = 0;
            
            intervalMarche = setInterval(() => {
                if (pasGauche) {
                    // Pas avec jambe gauche
                    legLeft.setAttribute('x2', '-5');
                    legLeft.setAttribute('y2', '-10');
                    legRight.setAttribute('x2', '30');
                    legRight.setAttribute('y2', '0');
                } else {
                    // Pas avec jambe droite
                    legLeft.setAttribute('x2', '-30');
                    legLeft.setAttribute('y2', '0');
                    legRight.setAttribute('x2', '5');
                    legRight.setAttribute('y2', '-10');
                }
                
                // Alterner les pas
                pasGauche = !pasGauche;
                cycleMarche++;
                
            }, 150); // Vitesse de l'animation de marche
            
            // Animation de déplacement fluide
            function animer() {
                const now = Date.now();
                const elapsed = (now - depart_time) / 1000; // temps écoulé en secondes
                const ratio = Math.min(elapsed / duree, 1); // proportion de l'animation terminée
                
                const currentPos = depart + ratio * distance;
                stickman.setAttribute('transform', `translate(${currentPos}, 200)`);
                
                if (ratio < 1) {
                    requestAnimationFrame(animer);
                } else {
                    // Animation terminée
                    etapeActuelle = vers;
                    enMouvement = false;
                    
                    // Arrêter l'animation de marche
                    clearInterval(intervalMarche);
                    
                    // Remettre les jambes en position normale
                    legLeft.setAttribute('x2', '-20');
                    legLeft.setAttribute('y2', '0');
                    legRight.setAttribute('x2', '20');
                    legRight.setAttribute('y2', '0');
                    
                    // Colorer le segment de chemin si on avance
                    if (distance > 0 && etapeActuelle > 0 && etapeActuelle <= pathSegments.length) {
                        colorerChemin(etapeActuelle - 1);
                    } else if (distance < 0) {
                        reinitialiserChemins();
                        for (let i = 0; i < etapeActuelle - 1; i++) {
                            colorerChemin(i);
                        }
                    }
                    
                    // Mettre à jour les boutons
                    btnRetour.disabled = (etapeActuelle === 0);
                    btnAvancer.disabled = (etapeActuelle === etapes.length - 1);
                    
                    // Sauvegarder la progression dans la base de données
                    sauvegarderProgression(etapeActuelle);
                    
                    // Si c'est l'étape finale, déclencher la célébration
                    if (etapeActuelle === etapes.length - 1 && !successCelebrated) {
                        successCelebrated = true;
                        animerCelebration();
                        celebrerSucces();
                        
                        // Notifier le parent que le défi est réussi
                        if (window.parent) {
                            window.parent.postMessage('success', '*');
                        }
                    }
                }
            }
            
            animer();
        }
        
        // Gestionnaires d'événements pour les boutons
        btnAvancer.addEventListener('click', function() {
            if (!enMouvement && etapeActuelle < etapes.length - 1) {
                deplacerStickman(etapeActuelle + 1);
            }
        });
        
        btnRetour.addEventListener('click', function() {
            if (!enMouvement && etapeActuelle > 0) {
                deplacerStickman(etapeActuelle - 1);
                
                // Réinitialiser l'état de célébration si on revient en arrière
                if (successCelebrated) {
                    successCelebrated = false;
                    if (intervalCelebration) {
                        clearInterval(intervalCelebration);
                    }
                    
                    // Remettre les bras et les jambes en position normale
                    armLeft.setAttribute('x1', '-20');
                    armLeft.setAttribute('y1', '-60');
                    armLeft.setAttribute('x2', '0');
                    armLeft.setAttribute('y2', '-60');
                    
                    armRight.setAttribute('x1', '0');
                    armRight.setAttribute('y1', '-60');
                    armRight.setAttribute('x2', '20');
                    armRight.setAttribute('y2', '-60');
                    
                    legLeft.setAttribute('x2', '-20');
                    legLeft.setAttribute('y2', '0');
                    legRight.setAttribute('x2', '20');
                    legRight.setAttribute('y2', '0');
                }
            }
        });
        
        // Fonction pour créer et afficher les confettis
        function celebrerSucces() {
            // Afficher le message de succès
            document.getElementById('message-succes').style.opacity = "1";
            
            // Vider le conteneur de confettis existants
            const confettiContainer = document.getElementById('confetti-container');
            confettiContainer.innerHTML = '';
            
            // Créer les confettis pour couvrir tout l'écran
            const confettiCount = 700; // Augmenté pour plus de densité
            const colors = ['#f00', '#0f0', '#00f', '#ff0', '#0ff', '#f0f', '#fd0', '#0fd', '#f83', '#8f3', '#3f8', '#83f', '#f38'];
            
            for (let i = 0; i < confettiCount; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                
                // Position aléatoire sur toute la largeur
                const startPosX = Math.random() * window.innerWidth;
                
                // Propriétés CSS aléatoires
                confetti.style.left = startPosX + 'px';
                confetti.style.top = '-50px'; // Commence au-dessus de l'écran
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                
                // Tailles variables pour plus de diversité
                const size = Math.random() * 15 + 5;
                confetti.style.width = size + 'px';
                confetti.style.height = size + 'px';
                
                // Formes variées (carrés, cercles, triangles)
                const shapeNum = Math.floor(Math.random() * 3);
                if (shapeNum === 0) {
                    // Cercle
                    confetti.style.borderRadius = '50%';
                } else if (shapeNum === 1) {
                    // Carré
                    confetti.style.borderRadius = '0';
                } else {
                    // Forme étoile/losange
                    confetti.style.borderRadius = '0';
                    confetti.style.transform = 'rotate(45deg)';
                }
                
                // Vitesse de chute aléatoire
                const fallDuration = Math.random() * 5 + 3; // 3-8 secondes
                confetti.style.animation = `fall ${fallDuration}s linear forwards`;
                
                // Délai avant l'apparition pour créer un effet continu
                const delay = Math.random() * 5;
                confetti.style.animationDelay = `${delay}s`;
                
                confettiContainer.appendChild(confetti);
                
                // Supprimer le confetti après son animation
                setTimeout(() => {
                    confetti.remove();
                }, (fallDuration + delay) * 1000 + 500); // +500ms pour être sûr
            }
            
            // Faire disparaître le message après un certain temps
            setTimeout(() => {
                document.getElementById('message-succes').style.opacity = "0";
            }, 5000);
            
            // Vider complètement le conteneur après la durée maximale
            setTimeout(() => {
                confettiContainer.innerHTML = '';
            }, 15000); // 15 secondes pour être sûr que tous les confettis sont terminés
        }
        
        // Fonction pour initialiser le stickman à sa position actuelle
        function initialiserStickman() {
            console.log("Initialisation du stickman avec étape:", etapeActuelle);
            
            // Positionner le stickman à la position correspondante à son étape
            if (etapeActuelle >= 0 && etapeActuelle < etapes.length) {
                stickman.setAttribute('transform', `translate(${etapes[etapeActuelle]}, 200)`);
                
                // Colorer les segments de chemin déjà parcourus
                for (let i = 0; i < etapeActuelle && i < pathSegments.length; i++) {
                    colorerChemin(i);
                }
                
                // Mettre à jour les boutons (seulement si l'utilisateur est connecté et inscrit)
                if (estConnecte && estInscritAuDefi) {
                    btnRetour.disabled = (etapeActuelle === 0);
                    btnAvancer.disabled = (etapeActuelle === etapes.length - 1);
                    
                    // Si c'est l'étape finale, lancer la célébration
                    if (etapeActuelle === etapes.length - 1) {
                        successCelebrated = true;
                        animerCelebration();
                    }
                } else {
                    // L'utilisateur n'est pas connecté ou pas inscrit, désactiver les boutons
                    btnRetour.disabled = true;
                    btnAvancer.disabled = true;
                }
            }
        }
        
        // Initialiser le stickman au chargement
        initialiserStickman();
    });
</script>

<!-- Dans defi.php, remplacer le bouton Participer -->
<?php
// Vérifier si l'utilisateur participe déjà à ce défi
$defiEnCours = false;
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $defiId = $defi['Id_Defi'];
    
    $query = "SELECT Defi_En_Cours FROM utilisateur WHERE Id_Utilisateur = ? AND Defi_En_Cours = ?";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $userId);
    $stmt->bindParam(2, $defiId);
    $stmt->execute();
    
    $defiEnCours = ($stmt->rowCount() > 0);
}
?>

<div class="participation-cta">
    <?php if (isset($_SESSION['user_id'])): ?>
        <?php if ($defiEnCours): ?>
            <a href="quitter_defi.php?id=<?php echo $defi['Id_Defi']; ?>" class="btn-quit">Quitter ce défi</a>
        <?php else: ?>
            <a href="participer_defi.php?id=<?php echo $defi['Id_Defi']; ?>" class="btn-participate">Participer à ce défi</a>
        <?php endif; ?>
    <?php else: ?>
        <a href="login.php" class="btn-login">Connectez-vous pour participer</a>
    <?php endif; ?>
</div>
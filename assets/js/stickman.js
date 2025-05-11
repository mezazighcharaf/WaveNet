// Variables globales nécessaires (à définir dans la page avant d'inclure ce fichier)
// - stickman, legLeft, legRight, armLeft, armRight
// - etapes (tableau des positions X)
// - pathSegments (tableau des segments de chemin)
// - etapeActuelle (index de l'étape courante)
// - enMouvement, successCelebrated, intervalMarche, intervalCelebration

function colorerChemin(index) {
    if (index < 0 || index >= pathSegments.length) return;
    pathSegments[index].setAttribute('stroke', '#4CAF50'); // Vert
    pathSegments[index].setAttribute('stroke-width', '4');
}

function reinitialiserChemins() {
    pathSegments.forEach(segment => {
        segment.setAttribute('stroke', 'black');
        segment.setAttribute('stroke-width', '3');
    });
}

function animerCelebration() {
    if (intervalCelebration) {
        clearInterval(intervalCelebration);
    }
    armLeft.setAttribute('x1', '0');
    armLeft.setAttribute('y1', '-60');
    armLeft.setAttribute('x2', '-15');
    armLeft.setAttribute('y2', '-85');
    armRight.setAttribute('x1', '0');
    armRight.setAttribute('y1', '-60');
    armRight.setAttribute('x2', '15');
    armRight.setAttribute('y2', '-85');
    let hauteur = 0;
    let montant = true;
    let cycles = 0;
    intervalCelebration = setInterval(() => {
        if (montant) {
            hauteur += 2;
            if (hauteur >= 15) montant = false;
        } else {
            hauteur -= 2;
            if (hauteur <= 0) {
                montant = true;
                cycles++;
            }
        }
        stickman.setAttribute('transform', `translate(840, ${200 - hauteur})`);
        if (hauteur > 7) {
            legLeft.setAttribute('x2', '-25');
            legRight.setAttribute('x2', '25');
        } else {
            legLeft.setAttribute('x2', '-15');
            legRight.setAttribute('x2', '15');
        }
        if (cycles > 100) {
            clearInterval(intervalCelebration);
            legLeft.setAttribute('x2', '-20');
            legRight.setAttribute('x2', '20');
        }
    }, 50);
}

function deplacerStickman(vers) {
    if (vers <= 0) return; // Ne jamais animer vers le départ
    if (typeof enMouvement === 'undefined') window.enMouvement = false;
    if (enMouvement) return;
    if (!etapes || typeof etapes[vers] !== 'number' || isNaN(etapes[vers])) return; // Sécurité anti-NaN
    enMouvement = true;
    const depart = etapes[etapeActuelle];
    const arrivee = etapes[vers];
    const distance = arrivee - depart;
    const duree = Math.abs(distance) / 100;
    const depart_time = Date.now();
    armLeft.setAttribute('x1', '-20');
    armLeft.setAttribute('y1', '-60');
    armLeft.setAttribute('x2', '0');
    armLeft.setAttribute('y2', '-60');
    armRight.setAttribute('x1', '0');
    armRight.setAttribute('y1', '-60');
    armRight.setAttribute('x2', '20');
    armRight.setAttribute('y2', '-60');
    let pasGauche = true;
    let cycleMarche = 0;
    intervalMarche = setInterval(() => {
        if (pasGauche) {
            legLeft.setAttribute('x2', '-5');
            legLeft.setAttribute('y2', '-10');
            legRight.setAttribute('x2', '30');
            legRight.setAttribute('y2', '0');
        } else {
            legLeft.setAttribute('x2', '-30');
            legLeft.setAttribute('y2', '0');
            legRight.setAttribute('x2', '5');
            legRight.setAttribute('y2', '-10');
        }
        pasGauche = !pasGauche;
        cycleMarche++;
    }, 150);
    function animer() {
        const now = Date.now();
        const elapsed = (now - depart_time) / 1000;
        const ratio = Math.min(elapsed / duree, 1);
        const currentPos = depart + ratio * distance;
        stickman.setAttribute('transform', `translate(${currentPos}, 200)`);
        if (ratio < 1) {
            requestAnimationFrame(animer);
        } else {
            etapeActuelle = vers;
            enMouvement = false;
            clearInterval(intervalMarche);
            legLeft.setAttribute('x2', '-20');
            legLeft.setAttribute('y2', '0');
            legRight.setAttribute('x2', '20');
            legRight.setAttribute('y2', '0');
            if (distance > 0 && etapeActuelle > 0 && etapeActuelle <= pathSegments.length) {
                colorerChemin(etapeActuelle - 1);
            } else if (distance < 0) {
                reinitialiserChemins();
                for (let i = 0; i < etapeActuelle - 1; i++) {
                    colorerChemin(i);
                }
            }
            // Célébration si on est à la fin
            if (typeof celebrerSucces === 'function' && etapeActuelle === etapes.length - 1 && !successCelebrated) {
                successCelebrated = true;
                animerCelebration();
                celebrerSucces();
                if (window.parent) {
                    window.parent.postMessage('success', '*');
                }
            }
        }
    }
    animer();
} 
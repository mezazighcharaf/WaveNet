// Fonction pour mettre à jour les comptes à rebours
function updateCountdowns() {
    const countdownElements = document.querySelectorAll('.countdown');
    
    countdownElements.forEach(element => {
        const endDateStr = element.getAttribute('data-end-date');
        if (!endDateStr) return;
        
        const endDate = new Date(endDateStr).getTime();
        const now = new Date().getTime();
        const distance = endDate - now;
        
        if (distance < 0) {
            element.innerHTML = "EXPIRÉ!";
            return;
        }
        
        // Calcul des jours, heures, minutes, secondes
        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
        
        element.innerHTML = `⏰ ${days}j ${hours}h ${minutes}m ${seconds}s`;
    });
}

// Lancer les comptes à rebours quand la page est chargée
document.addEventListener('DOMContentLoaded', function() {
    updateCountdowns();
    setInterval(updateCountdowns, 1000);
});
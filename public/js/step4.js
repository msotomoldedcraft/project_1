document.addEventListener('DOMContentLoaded', () => {

    const form = document.querySelector('.step-card form');
    const confirmBtn = form ? form.querySelector('button[type="submit"]') : null;
    const card = document.querySelector('.step-card');

    if (!form || !confirmBtn || !card) return;

    form.addEventListener('submit', () => {

        // Prevent double submission
        confirmBtn.disabled = true;

        // Visual feedback
        card.style.transition = 'opacity 0.3s';
        card.style.opacity = '0.7';

    });

});
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('.confirmation-card form');
    const confirmBtn = form ? form.querySelector('button[type="submit"]') : null;
    const card = document.querySelector('.confirmation-card');

    if (!form || !confirmBtn || !card) return;

    form.addEventListener('submit', (e) => {
        // Disable button to prevent double submission
        confirmBtn.disabled = true;

        // Optional: fade out the card slightly to indicate processing
        card.style.transition = 'opacity 0.3s';
        card.style.opacity = '0.7';

        // Allow actual submission to proceed
        // (Remove e.preventDefault() so form still submits)
    });
});

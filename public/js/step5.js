document.addEventListener('DOMContentLoaded', () => {

    let countdown = 5;
    const countdownEl = document.getElementById('countdown');
    const btn = document.getElementById('resendBtn');
    const progress = document.getElementById('progress');
    const total = countdown;

    const interval = setInterval(() => {

        countdown--;
        countdownEl.textContent = countdown;

        const progressPercent = ((total - countdown) / total) * 100;
        progress.style.width = progressPercent + '%';

        if (countdown <= 0) {
            clearInterval(interval);
            btn.disabled = false;
            countdownEl.textContent = "0";
            progress.style.width = '100%';
        }

    }, 1000);

});
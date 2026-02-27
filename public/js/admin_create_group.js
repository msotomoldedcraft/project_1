document.addEventListener("DOMContentLoaded", function () {

    const card = document.querySelector(".create-card");
    if (!card) return;

    // Fade-in animation
    card.style.opacity = 0;
    card.style.transform = "translateY(20px)";
    card.style.transition = "all 0.4s ease";

    setTimeout(() => {
        card.style.opacity = 1;
        card.style.transform = "translateY(0)";
    }, 100);

    // Form validation
    const form = card.querySelector("form");
    if (!form) return;

    form.addEventListener("submit", function (e) {

        const input = form.querySelector("input[name='group_name']");
        if (!input) return;

        if (!input.value.trim()) {
            e.preventDefault();
            input.focus();
        }
    });

});
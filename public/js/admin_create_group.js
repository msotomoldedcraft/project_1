document.addEventListener("DOMContentLoaded", function() {
    const card = document.querySelector(".group-create-card");
    if (!card) return;

    // Fade-in animation
    card.style.opacity = 0;
    card.style.transform = "translateY(20px)";
    setTimeout(() => {
        card.style.transition = "opacity 0.4s ease, transform 0.4s ease";
        card.style.opacity = 1;
        card.style.transform = "translateY(0)";
    }, 100);

    // Optional: simple form validation highlight
    const form = card.querySelector("form");
    form.addEventListener("submit", (e) => {
        const input = form.querySelector("input[name='group_name']");
        if (!input.value.trim()) {
            e.preventDefault();
            input.style.borderColor = "#dc2626";
            input.focus();
        }
    });
});
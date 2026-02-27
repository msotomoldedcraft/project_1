document.addEventListener("DOMContentLoaded", function () {

    const hero = document.querySelector(".intro-hero");
    const cards = document.querySelectorAll(".feature-card");

    // Hero fade in
    hero.style.opacity = 0;
    hero.style.transform = "translateY(20px)";
    hero.style.transition = "all 0.8s ease";

    setTimeout(() => {
        hero.style.opacity = 1;
        hero.style.transform = "translateY(0)";
    }, 100);

    // Cards animation
    cards.forEach((card, index) => {
        card.style.opacity = 0;
        card.style.transform = "translateY(20px)";
        card.style.transition = "all 0.6s ease";

        setTimeout(() => {
            card.style.opacity = 1;
            card.style.transform = "translateY(0)";
        }, 300 + (index * 150));
    });

});
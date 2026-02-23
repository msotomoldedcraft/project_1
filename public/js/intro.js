document.addEventListener('DOMContentLoaded', () => {
    const downArrows = document.querySelectorAll('.scroll-down');
    const upArrow = document.querySelector('.scroll-up');
    const sections = document.querySelectorAll('section');

    // =========================
    // Arrow navigation
    // =========================
    downArrows.forEach(arrow => {
        arrow.addEventListener('click', () => {
            const currentSection = arrow.closest('section');
            const currentIndex = Array.from(sections).indexOf(currentSection);
            const nextSection = sections[currentIndex + 1];
            if (nextSection) {
                nextSection.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    upArrow.addEventListener('click', () => {
        sections[0].scrollIntoView({ behavior: 'smooth' });
    });

    // =========================
    // Arrow visibility
    // =========================
    function updateArrows() {
        const scrollPosition = window.scrollY;
        const viewportHeight = window.innerHeight;

        sections.forEach((section, index) => {
            const downArrow = section.querySelector('.scroll-down');
            const sectionTop = section.offsetTop;
            const sectionBottom = sectionTop + section.offsetHeight;

            // Show down arrow if not last section and mostly in view
            if (downArrow) {
                if (index === sections.length - 1) {
                    downArrow.style.display = 'none';
                } else if (scrollPosition + viewportHeight / 2 >= sectionTop && scrollPosition + viewportHeight / 2 < sectionBottom) {
                    downArrow.style.display = 'block';
                } else {
                    downArrow.style.display = 'none';
                }
            }
        });

        // Up arrow visible if scrolled past first section
        upArrow.style.display = scrollPosition > 50 ? 'block' : 'none';
    }

    // =========================
    // Snap-to-section when halfway
    // =========================
    let scrollTimeout;
    window.addEventListener('scroll', () => {
        updateArrows();

        // Clear previous timeout
        clearTimeout(scrollTimeout);

        scrollTimeout = setTimeout(() => {
            const viewportHeight = window.innerHeight;

            sections.forEach((section, index) => {
                const rect = section.getBoundingClientRect();
                const sectionTop = rect.top;
                const sectionHeight = rect.height;

                // Prevent scroll past last section
                if (index === sections.length - 1 && sectionTop < 0) {
                    window.scrollTo({
                        top: window.scrollY + sectionTop,
                        behavior: 'smooth'
                    });
                    return;
                }

                // Snap if half of section is visible
                if (sectionTop <= viewportHeight / 2 && sectionTop + sectionHeight >= viewportHeight / 2) {
                    section.scrollIntoView({ behavior: 'smooth' });
                }
            });
        }, 80);
    });

    // Initialize arrows
    updateArrows();
});
